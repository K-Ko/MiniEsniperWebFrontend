<?php
/**
 *
 */
use App\I18N;
use App\Snipe;
use App\User;

/**
 *
 *
 * @author     Knut Kohl <github@knutkohl.de>
 * @copyright  (c) 2016 Knut Kohl
 * @licence    MIT License - http://opensource.org/licenses/MIT
 */
if (empty($_POST)) {
    return;
}

extract(filter_input_array(
    INPUT_POST,
    [
        'action'    => FILTER_SANITIZE_STRING,
        'ebay_user' => FILTER_SANITIZE_STRING,
        'ebay_pass' => FILTER_SANITIZE_STRING,
        'language'  => FILTER_SANITIZE_STRING,
        'name'      => FILTER_SANITIZE_STRING,
        'name_old'  => FILTER_SANITIZE_STRING,
        'data'      => FILTER_SANITIZE_STRING,
        'bugs'      => [
            'flags' => FILTER_REQUIRE_ARRAY
        ]
    ],
    true
));

if ($page == 'login' && $action != 'login') {
    // Any other action is without login invalid
    $action = '';
}

if ($action == '') {
    return;
}

// ---------------------------------------------------------------------------
switch ($action) {
    // ---------------
    case 'login':
        if ($ebay_user == '' || $ebay_pass == '') {
            break;
        }

        session_regenerate_id();

        if (!isset($config->languages[$language])) {
            $language = 'en';
        }

        // Reload translations for messages
        I18N::load(realpath('language/' . $language . '.php'));

        if (count($config->users) == 0 || array_search(strtolower($ebay_user), $config->users) !== false) {
            $_SESSION['user'] = $ebay_user;
            $_SESSION['pass'] = $ebay_pass;
            $_SESSION['lang'] = $language;

            $_SESSION['token'] = App\Encoder::encrypt(
                $ebay_user . "\t" . $ebay_pass . "\t" . $_SESSION['lang'],
                $config->secret
            );

            $_SESSION['message'] = ['class' => 'success', 'text' => I18N::_('welcome') . '!'];
        } else {
            $_SESSION['message'] = ['class' => 'danger', 'text' => I18N::_('invalid_user') . '!'];
        }

        session_write_close();
        die(header('Location: /'));

    // ---------------
    case 'logout':
        $_SESSION = [];

        session_destroy();

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_regenerate_id();
        session_write_close();
        die(header('Location: /'));

    // ---------------
    case 'save':
        if ($name == '' || $data == '') {
            break;
        }

        $new = true;

        if ($snipe = $snipes->get($name_old)) {
            $snipe->delete();
            $new = false;
        }

        $snipe = new Snipe($name, $data);
        $snipe->save();

        if ($new) {
            $_SESSION['new'][$snipe->getHash()] = true;
        }

        session_write_close();
        die(header('Location: /'));

    // ---------------
    case 'start':
        if ($name == '' || $data == '') {
            break;
        }

        if ($snipe = $snipes->get($name_old)) {
            $snipe->delete();
        }

        $snipe = new Snipe($name, $data);
        $snipe->start();

        session_write_close();
        die(header('Location: /'));

    // ---------------
    case 'stop':
        if ($snipe = $snipes->get($name)) {
            $snipe->stop();
            unset($snipe);
        }

        break;

    // ---------------
    case 'delete':
        if ($snipe = $snipes->get($name)) {
            $snipe->delete();
            $snipes->remove($snipe);
            unset($snipe);
        }

        break;

    // ---------------
    case 'bug':
        array_map(function ($file) {
            @unlink(User::$dir.'/'.$file);
        }, $bugs);
        die(header('Location: /'));
}
