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

define('DS', DIRECTORY_SEPARATOR);
define('ROOTDIR', dirname(__DIR__));

$config = include 'config/config.default.php';
if (is_file('config/config.php')) {
    $config = array_merge($config, include 'config/config.php');
}

if ($config['debug']) {
    ini_set('display_errors', 1);
    error_reporting(-1);
}

$config['version'] = trim(file_get_contents(__DIR__.DS.'.version'));

include 'index.inc.php';

$bugs = glob('esniper.*.bug.html', GLOB_NOSORT);

ob_start('ob_gzhandler');

// Shortcut to access configuration with $cfg_...
extract($config, EXTR_PREFIX_ALL, 'cfg');

$tpl = 'design'.DS.$cfg_design.DS.'index.html';
$cpl = 'tmp'.DS.'index.'.$cfg_design.'.html';

if (!is_file($cpl) || filemtime($cpl) < filemtime($tpl)) {
    // Compile template
    $html = file_get_contents($tpl);
    if (preg_match_all('~\{\{(\$.+?)\}\}~', $html, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $html = str_replace($match[0], '<?php echo '.$match[1].' ?>', $html);
        }
    }
    if (preg_match_all('~\{\{(.+?)\}\}~', $html, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $html = str_replace($match[0], '<?php '.$match[1].' ?>', $html);
        }
    }
    file_put_contents($cpl, $html);
}

include $cpl;

// Some statistics
printf(
    '<!-- load %d ms; peak memory %d kByte -->',
    (microtime(true)-$_start)*1000, memory_get_peak_usage()/1000
);
