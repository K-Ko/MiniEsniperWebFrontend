<?php
/**
 *
 *
 * @author     Knut Kohl <github@knutkohl.de>
 * @copyright  (c) 2016 Knut Kohl
 * @licence    MIT License - http://opensource.org/licenses/MIT
 * @version    1.0.0
 */

if (!defined('ROOTDIR')) exit;

/**
 * Debugging
 */
function p($p) { echo '<pre>'. print_r($p,1). '</pre>'; }

/**
 * Collect snipes data
 */
function snipes() {
    $snipes = array();

    foreach (glob(DATADIR.'/*.txt') as $file) {
        $name = basename($file, '.txt');
        $raw = file($file, FILE_IGNORE_NEW_LINES);
        $log  = DATADIR.'/'.$name.'.log';
        // Find process Id
        $pid  = 'ps -fea | grep "[ /]esniper" | grep '.escapeshellarg($name).' | awk \'{print $2}\'';

        $data = '';
        foreach ($raw as $line) {
            // Ignore empty and comment lines
            if ('' == $line = preg_replace('~^\s*#.*~m', '', $line)) continue;
            $data .= $line . PHP_EOL;
        }

        $snipes[$name] = array(
            'name' => $name,
            'raw'  => implode(PHP_EOL, $raw),
            'data' => trim($data),
            'pid'  => exec($pid),
            'log'  => is_file($log) ? trim(file_get_contents($log)) : ''
        );
    }
    ksort($snipes);
    return $snipes;
}

// ---------------------------------------------------------------------------
define('APPVERSION', trim(file_get_contents(__DIR__.DS.'.version')));

// Make hidden server unique directory name to store files
define('DATADIR', __DIR__.DS.'.d'.substr(md5(__DIR__), -11));

is_dir(DATADIR) || mkdir(DATADIR);

session_start();

$snipes = snipes();

#p($snipes);
  
if (empty($_POST)) return;

// ---------------------------------------------------------------------------
// Continue ONLY for POST requests
// ---------------------------------------------------------------------------
extract(array_merge(
    array('action' => null, 'name' => null, 'data' => null),
    $_POST
));

if ($action == '' || $name == '') return;

$dataFile = DATADIR.'/'.$name.'.txt';
$logFile  = DATADIR.'/'.$name.'.log';

// ---------------------------------------------------------------------------
switch ($action) {

    // ---------------
    case 'start':
        if ($data == '') break;

        file_put_contents($dataFile, $data);

        $cmd = sprintf(
            'esniper -bc %s %s >>%s 2>&1 &',
            $config['.esniper'],
            escapeshellarg($dataFile),
            escapeshellarg($logFile)
        );

        @unlink($logFile);

        $sep = str_repeat('-',79) . PHP_EOL;

        file_put_contents(
            $logFile,
            $cmd . PHP_EOL . $sep . $data . PHP_EOL . $sep
        );

        set_time_limit(0);
        exec($cmd);

        $ts = microtime(true);
        $i = 90;
        // Waits max. 30 sec. for esniper log containing string "sleeping"
        do {
            sleep(1);
            $snipes = snipes();
        } while ($i-- && strpos($snipes[$name]['log'], 'Sleeping for') === false);

        $_SESSION[$name]['time'] = microtime(true) - $ts;
        session_write_close();
        header('Location: /');
        break;

    // ---------------
    case 'kill':
        exec('kill '.$snipes[$name]['pid']);
        unset($name);
        $snipes = snipes();
        break;

    // ---------------
    case 'edit':
        @unlink($logFile);
        $data = $snipes[$name]['raw'];
        unset($snipes[$name]);
        break;

    // ---------------
    case 'remove':
        @unlink($dataFile);
        @unlink($logFile);
        unset($snipes[$name]);
        unset($name);
        break;

}
