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
 * /
function _d($p)
{
    echo '<pre>'. print_r($p,1). '</pre>';
}

/**
 * Find process Id
 */
function pid($name)
{
    return exec('ps -fea | grep "[ /]esniper" | grep '.hashname($name).' | awk \'{print $2}\'');
}

/**
 * Find process Id
 */
function hashname($name)
{
    return substr(md5($name), -8);
}

/**
 * Collect snipes data
 */
function snipes()
{
    $snipes = array();

    foreach (glob(DATADIR.'/*.txt') as $file) {
        $data = file($file, FILE_IGNORE_NEW_LINES);
        $name = trim($data[0], '# ');
        $log  = DATADIR.'/'.hashname($name).'.log';

        $items = array();
        foreach ($data as $line) {
            // Ignore empty and comment lines
            if ('' == $line = preg_replace('~^\s*#.*~m', '', $line)) continue;
            $items[] = explode(' ', $line)[0];
        }

        $snipes[$name] = array(
            'name'  => $name,
            'data'  => implode(PHP_EOL, $data),
            'items' => $items,
            'pid'   => pid($name),
            'log'   => is_file($log) ? utf8_encode(trim(file_get_contents($log))) : ''
        );
    }
    ksort($snipes);

    return $snipes;
}

// ---------------------------------------------------------------------------
// Make hidden server unique directory name to store files
// Start with a fixed .d for .gitignore :-)
define('DATADIR', __DIR__.DS.'.d'.substr(md5(__DIR__), -11));

is_dir(DATADIR) || mkdir(DATADIR);

if (!is_dir(DATADIR)) {
    die('Unable to create data directory:<pre>'.DATADIR.'</pre>Please check permissions.');
}

$name = $data = '';

session_start();

$snipes = snipes();

if (empty($_POST)) return;

// ---------------------------------------------------------------------------
// Continue ONLY for POST requests
// ---------------------------------------------------------------------------
extract(array_merge(
    array('action' => null, 'name' => null, 'data' => null),
    $_POST
));

if ($action == '') return;

$dataFile = DATADIR.'/'.hashname($name).'.txt';
$logFile  = DATADIR.'/'.hashname($name).'.log';

// ---------------------------------------------------------------------------
switch ($action) {

    // ---------------
    case 'start':
        $data = trim(str_replace(',', '.', $data));

        if ($data == '') break;

        $cmd = sprintf(
            'esniper -bc %s %s >>%s 2>&1 &',
            $config['.esniper'], $dataFile, $logFile
        );

        @unlink($logFile);

        $sep = str_repeat('-', 79) . PHP_EOL;

//         file_put_contents(
//             $logFile,
//             $cmd . PHP_EOL . $sep . $data . PHP_EOL . $sep
//         );

        $data = '# ' . $name . PHP_EOL . $data;

        file_put_contents($dataFile, $data);

        set_time_limit(0);
        exec($cmd);

        $ts = microtime(true);
        $i = 90;
        // Waits max. 30 sec. for esniper log containing string "sleeping"
        do {
            sleep(1);
            $snipes = snipes();
        } while ($i-- &&
                 (strpos($snipes[$name]['log'], 'Sleeping for') === false) &&
                 (strpos($snipes[$name]['log'], 'Sorting auctions') === false));

        $_SESSION[$name]['time'] = microtime(true) - $ts;
        session_write_close();
        header('Location: /');
        break;

    // ---------------
    case 'kill':
        exec('kill '.$snipes[$name]['pid']);
        $name = '';
        $snipes = snipes();
        break;

    // ---------------
    case 'edit':
        @unlink($logFile);
        $data = explode(PHP_EOL, $snipes[$name]['data']);
        array_shift($data);
        $data = implode(PHP_EOL, $data);
        unset($snipes[$name]);
        break;

    // ---------------
    case 'kill-edit':
        exec('kill '.$snipes[$name]['pid']);
        $snipes = snipes();
        @unlink($logFile);
        $data = explode(PHP_EOL, $snipes[$name]['data']);
        array_shift($data);
        $data = implode(PHP_EOL, $data);
        unset($snipes[$name]);
        break;

    // ---------------
    case 'remove':
        @unlink($dataFile);
        @unlink($logFile);
        unset($snipes[$name]);
        $name = '';
        break;

    // ---------------
    case 'bug':
        if (isset($_POST['bug'])) {
            foreach ($_POST['bug'] as $name) @unlink($name);
            $name = '';
        }
        break;

}
