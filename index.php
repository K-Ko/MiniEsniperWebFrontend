<?php
/**
 *
 *
 * @author     Knut Kohl <github@knutkohl.de>
 * @copyright  (c) 2016 Knut Kohl
 * @licence    MIT License - http://opensource.org/licenses/MIT
 */
if (!isset($_SERVER['REQUEST_TIME_FLOAT'])) {
    // Make sure, $_SERVER['REQUEST_TIME_FLOAT'] exists
    $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);
}

session_start();
setcookie(session_name(), session_id(), time() + 86400, '/');

require 'vendor/autoload.php';

// May not exist
@include 'hooks.php';

mef\Hook::apply('init');

// Load default config and custom config if exists
$config = mef\Config::getInstance()->load('config.default.php')->load('config.local.php');

if ($config->debug) {
    ini_set('display_errors', 1);
    error_reporting(-1);
}

if ($config->esniper == '') {
    die('Can not find esniper binary, please help and define in <pre>config.php</pre>');
}

$config->version = trim(file_get_contents('.version'));
$config->esniper_version = exec('esniper -v 2>&1 | head -n 1');

if (isset($_SESSION['lang'])) {
    $config->language = $_SESSION['lang'];
}

setlocale(LC_ALL, $config->locales[$config->language]);

if (!is_dir($config->dataDir) && !mkdir($config->dataDir, 0700, true)) {
    die(sprintf(
        'Unable to create data directory:<pre>%s</pre>Please check permissions.',
        $config->dataDir
    ));
}

mef\Hook::apply('config.loaded', $config);

mef\I18N::load(realpath('language/' . $config->language . '.php'));

if (isset($_SESSION['user'], $_SESSION['pass'])) {
    $page = 'home';

    mef\User::init($_SESSION['user'], $_SESSION['pass']);

    // Load all auction groups
    $snipes = new mef\Snipes();

    // Any bug reports?
    $bugs = glob('esniper.*.bug.html', GLOB_NOSORT);
} else {
    $page = 'login';
}

mef\Hook::apply('before.action', $page);

// Check for API calls
include 'api.php';

// Check for form submits
include 'post.php';

foreach (['index', $page] as $tpl) {
    $cpl = 'design_cpl/' . $config->design . '.' . $tpl . '.html';
    $tpl = 'design/' . $config->design . '/' . $tpl . '.html';

    if ($config->debug || !is_file($cpl) || filemtime($cpl) < filemtime($tpl)) {
        // Compile template
        $html = file_get_contents($tpl);

        mef\Hook::apply('template.compile', $html);

        // Variables - {{$var_name}}
        if (preg_match_all('~\{\{(\$.+?)\}\}~', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $html = str_replace($match[0], '<?php echo '.$match[1].' ?'.'>', $html);
            }
        }
        // Coding - {{if (true):}}
        if (preg_match_all('~\{\{(.+?)\}\}~', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $html = str_replace($match[0], '<?php '.$match[1].' ?'.'>', $html);
            }
        }
        // Translations - {|translation_id|}
        if (preg_match_all('~\{\|(\w+)\|\}~', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $html = str_replace($match[0], '<?php echo mef\I18N::_(\''.$match[1].'\') ?'.'>', $html);
            }
        }

        mef\Hook::apply('template.compiled', $html);

        if (!$config->debug) {
            $html = preg_replace('~/\*.+?\*/~s', '', $html);
            $html = preg_replace('~\s*$\s*~m', '', $html);
            $html = str_replace(['<?php', '?'.'>'], ['<?php ', ' ?'.'>'], $html);

            mef\Hook::apply('template.compressed', $html);
        }

        file_put_contents($cpl, $html);
    }
}

mef\Hook::apply('before.render');

// Show
include 'design_cpl/' . $config->design . '.index.html';

mef\Hook::apply('after.render');
