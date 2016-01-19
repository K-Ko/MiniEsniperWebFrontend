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

function p($p) { echo '<pre>'. print_r($p,1). '</pre>'; }

function snipes() {
    $snipes = array();

    foreach (glob(DATADIR.'/*.txt') as $file) {
        $name = basename($file, '.txt');
        $pid  = 'ps -fea | grep "[ /]esniper" | grep '.escapeshellarg($name).' | awk \'{print $2}\'';
        $log  = DATADIR.'/'.$name.'.log';
        $snipes[$name] = array(
            'name' => $name,
            'data' => file_get_contents($file),
            'pid'  => exec($pid),
            'log'  => is_file($log) ? trim(file_get_contents($log)) : ''
        );
    }
    ksort($snipes);
    return $snipes;
}

// ---------------------------------------------------------------------------
// Make hidden server unique directory name to store files
define('DATADIR', __DIR__.DS.'.d'.substr(md5(__DIR__), -11));
define('APPVERSION', trim(file_get_contents(__DIR__.DS.'.version')));

is_dir(DATADIR) || mkdir(DATADIR);

session_start();

$config = file_exists('config.php') ? include 'config.php' : include 'config.default.php';

$snipes = snipes();

#p($snipes);
  
if (empty($_POST)) return;

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
        file_put_contents($logFile, $cmd.PHP_EOL.str_repeat('-',79).PHP_EOL);
        exec($cmd);

        $ts = microtime(true);
        $i = 30;
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
        $data = $snipes[$name]['data'];
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
