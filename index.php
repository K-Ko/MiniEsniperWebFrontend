<?php
/**
 *
 *
 * @author     Knut Kohl <github@knutkohl.de>
 * @copyright  (c) 2016 Knut Kohl
 * @licence    MIT License - http://opensource.org/licenses/MIT
 */
use App\Config;
use App\Encoder;
use App\Hook;
use App\I18N;
use App\Snipes;
use App\User;

/**
 *
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

Hook::apply('init');

// Load default config and custom config if exists
$config = Config::getInstance()->load('config.default.php')->load('config.local.php');

// Must be set via config.local.php
if ($config->debug) {
    ini_set('display_errors', 1);
    error_reporting(-1);
}

if ($config->esniper == '') {
    die('Can not find esniper binary, please help and define in <pre>config.local.php</pre>');
}

if (isset($_GET['login'])) {
    list($_SESSION['user'], $_SESSION['pass'], $_SESSION['lang']) =
        explode("\t", Encoder::decrypt($_GET['login'], $config->secret));
    $_SESSION['token'] = $_GET['login'];
    // session_write_close();
    // die(header('Location: /'));
}

if (isset($_SESSION['lang'])) {
    $config->language = $_SESSION['lang'];
}

$config->version = trim(file_get_contents('.version'));
$config->esniper_version = exec('esniper -v 2>&1 | head -n 1');

setlocale(LC_ALL, $config->locales[$config->language]);

if (!is_dir($config->dataDir) && !mkdir($config->dataDir, 0700, true)) {
    die(sprintf(
        'Unable to create data directory:<pre>%s</pre>Please check permissions.',
        $config->dataDir
    ));
}

Hook::apply('config.loaded', $config);

I18N::load(realpath('language/' . $config->language . '.php'));

if (isset($_SESSION['user'], $_SESSION['pass'])) {
    $page = 'home';

    User::init($_SESSION['user'], $_SESSION['pass']);
    $user_name=User::$name;

    // Load all auction groups
    $snipes = new Snipes();

    // Any bug reports?
    $bugs = glob(User::$dir.'/esniper.*.bug.html');
    $bugs = array_map(function ($file) {
        return basename($file);
    }, $bugs);
} else {
    $page = 'login';
}

Hook::apply('before.action', $page);

// Check for API calls
include 'api.php';

// Check for form submits
include 'post.php';

$server_name = $_SERVER['SERVER_NAME'];

foreach (['index', $page] as $tpl) {
    $cpl = 'design_cpl/' . $config->design . '.' . $tpl . '.html';
    $tpl = 'design/' . $config->design . '/' . $tpl . '.phtml';

    if ($config->debug || !is_file($cpl) || filemtime($cpl) < filemtime($tpl)) {
        // Compile template
        $html = file_get_contents($tpl);

        Hook::apply('template.compile', $html);

        // Variables - {{$var_name}}
        if (preg_match_all('~\{\{(\$.+?)\}\}~', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $html = str_replace($match[0], '<?php echo '.$match[1].' ?'.'>', $html);
            }
        }
        // Coding - {{if (true):}}
        if (preg_match_all('~\{\{(.+?)\}\}~s', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $html = str_replace($match[0], '<?php '.$match[1].' ?'.'>', $html);
            }
        }
        // Translations - {|translation_id|}
        if (preg_match_all('~\{\|(\w+)\|\}~', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $html = str_replace($match[0], '<?php echo App\I18N::_(\''.$match[1].'\') ?'.'>', $html);
            }
        }

        Hook::apply('template.compiled', $html);

        if ($config->compress) {
            $html = preg_replace('~\s+~s', ' ', $html);
            $html = preg_replace('~<!--.+?-->~', '', $html);
            $html = preg_replace('~/\*.+?\*/~s', ' ', $html);
            $html = preg_replace('~>\s+<~s', '><', $html);
            $html = str_replace(['<?php', '?'.'>'], ['<?php ', ' ?'.'>'], $html);

            Hook::apply('template.compressed', $html);
        }

        file_put_contents($cpl, $html);
    }
}

Hook::apply('before.render');

// Show
include 'design_cpl/' . $config->design . '.index.html';

Hook::apply('after.render');

unset($_SESSION['new']);

session_write_close();
