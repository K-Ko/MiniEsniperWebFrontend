<?php
/**
 *
 *
 * @author     Knut Kohl <github@knutkohl.de>
 * @copyright  (c) 2016 Knut Kohl
 * @licence    MIT License - http://opensource.org/licenses/MIT
 * @version    1.0.0
 */

$_start = microtime(true);

ini_set('display_errors', 1);
error_reporting(-1);

define('DS', DIRECTORY_SEPARATOR);
define('ROOTDIR', dirname(__DIR__));

$config = include 'config.default.php';
file_exists('config.php') && $config = array_merge($config, include 'config.php');

include 'index.inc.php';

ob_start('ob_gzhandler');

include 'design'.DS.$config['design'].DS.'index.html';

// Some statistics
printf('<!-- load %.0fms; peak memory %d Bytes -->', (microtime(true)-$_start)*1000, memory_get_peak_usage());
