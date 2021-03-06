#!/usr/bin/env php
<?php
/**
 *
 */
use App\Config;
use App\Snipes;
use App\User;

/**
 *
 *
 * @author     Knut Kohl <github@knutkohl.de>
 * @copyright  (c) 2016 Knut Kohl
 * @licence    MIT License - http://opensource.org/licenses/MIT
 */
function usage($rc = 0)
{
    global $argv;

    echo PHP_EOL;
    echo 'Minimal esniper web frontend console v', trim(file_get_contents('.version')), PHP_EOL, PHP_EOL;
    echo "Usage: $argv[0] <command> [options]", PHP_EOL, PHP_EOL;
    echo "       $argv[0] start <ebay user name> <password>          Start missing esniper processes", PHP_EOL;
    echo "       $argv[0] start <ebay user name> <password> <group>  Start esniper for group", PHP_EOL;
    echo "       $argv[0] restart <ebay user name> <password>        Restart all esniper processes", PHP_EOL;
    echo "       $argv[0] gc [days]                                  Remove snipe data older ? days, default 5", PHP_EOL, PHP_EOL;
    die($rc);
}

function e()
{
    $args = func_get_args();
    echo date('[H:i:s] '), implode(' ', $args), PHP_EOL;
}

function startSnipe($snipe)
{
    e($snipe->name);
    if ($snipe->pid) {
        e('Running, PID ', $snipe->pid);
    } else {
        e('Start ...');
        if ($pid = $snipe->start()) {
            e('PID', $pid);
        } else {
            e('Failed');
        }
    }
}

if ($argc < 2) {
    usage(1);
}

require '../vendor/autoload.php';

// Load default config and custom config if exists
$config = Config::getInstance()->load('config.default.php')->load('config.local.php');

switch ($argv[1]) {
    // ------------------
    case 'start':
        if ($argc < 4) {
            usage(2);
        }

        User::init($argv[2], $argv[3]);

        // Load all auction groups
        $snipes = new Snipes();

        if (isset($argv[4])) {
            if ($snipe = $snipes->get($argv[4])) {
                startSnipe($snipe);
            }
        } else {
            e('::: Start snipes of', $argv[2]);
            foreach ($snipes as $snipe) {
                e(str_repeat('-', 70));
                startSnipe($snipe);
            }
        }

        foreach (glob('esniper.*.bug.html') as $bug) {
            unlink($bug);
        }

        break;

    // ------------------
    case 'restart':
        if ($argc != 4) {
            usage(2);
        }

        e('::: Restart snipes of', $argv[2]);

        User::init($argv[2], $argv[3]);

        // Load all auction groups
        $snipes = new Snipes();

        foreach ($snipes as $snipe) {
            e(str_repeat('-', 70));
            e($snipe->name);
            if ($snipe->pid) {
                e('Stop PID', $snipe->pid);
            }
            e('Restart ...');
            if ($pid = $snipe->restart()) {
                e('PID', $pid);
            } else {
                e('falied');
            }
        }

        foreach (glob('esniper.*.bug.html') as $bug) {
            unlink($bug);
        }

        break;

    // ------------------
    case 'gc':
        e('::: Garbage collection');

        $ts = time() - (isset($argv[2]) ? $argv[2] : 5) * 86400;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($config->dataDir));
        $it->rewind();

        while ($it->valid()) {
            if (!$it->isDot() && preg_match('~\.log$~', $it->key())) {
                if (filemtime($it->key()) < $ts) {
                    $log = $it->key();
                    e('Delete', $log);
#                    unlink($log);
                    $ini = str_replace('.log', '.ini', $log);
                    if (is_file($ini)) {
                        e('Delete', $ini);
#                        unlink($ini);
                    }
                    $pid = str_replace('.log', '.pid', $log);
                    if (is_file($pid)) {
                        e('Delete', $pid);
#                        unlink($pid);
                    }
                }
            }

            $it->next();
        }

        exec('find ' . $config->dataDir . ' -empty -delete');

        break;

    // ------------------
    default:
        usage(2);
}
