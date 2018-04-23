<?php
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
            'filter'    => FILTER_VALIDATE_INT,
            'flags'     => FILTER_REQUIRE_ARRAY
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

        $_SESSION['user'] = $ebay_user;
        $_SESSION['pass'] = $ebay_pass;
        $_SESSION['lang'] = isset($config->languages[$language]) ? $language : 'en';

        $_SESSION['message'] = ['class' => 'success', 'text' => mef\I18N::_('welcome') . '!'];

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

        if ($snipe = $snipes->get($name_old)) {
            $snipe->delete();
        }

        $snipe = new mef\Snipe($name, $data);
        $snipe->save();

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

        $snipe = new mef\Snipe($name, $data);
        $snipe->start();

        session_write_close();
        die(header('Location: /'));

    // ---------------
    case 'stop':
        $snipe = $snipes->get($name);

        if ($snipe) {
            $snipe->stop();
            unset($snipe);
        }

        break;

    // ---------------
    case 'delete':
        $snipe = $snipes->get($name);

        if ($snipe) {
            $snipe->delete();
            $snipes->remove($snipe);
            unset($snipe);
        }

        break;

    // ---------------
    case 'bug':
        foreach ((array) $bugs as $file) {
            @unlink($file);
        }

        break;
}
