<?php
/**
 *
 */
use App\I18n;
use App\Snipe;
use App\User;

/**
 *
 *
 * @author     Knut Kohl <github@knutkohl.de>
 * @copyright  (c) 2016 Knut Kohl
 * @licence    MIT License - http://opensource.org/licenses/MIT
 */
if (empty($_GET['api'])) {
    return;
}

$GET = filter_input_array(
    INPUT_GET,
    [
        'api'   => FILTER_SANITIZE_STRING,
        'token' => FILTER_SANITIZE_STRING
    ],
    true
);

$result = '';

if (isset($snipes) && $snipe = $snipes->find($GET['token'])) {
    switch ($GET['api']) {
        // ---------------
        case 'edit':
            $snipe->stop();
            $result = ['name' => $snipe->name, 'data' => $snipe->data ];
            break;

        // ---------------
        case 'stop':
            $result = $snipe->stop();
            break;

        // ---------------
        case 'delete':
            $result = $snipe->delete();
            break;

        // ---------------
        case 'log':
            $result = $snipe->log;
            break;
    } // switch

    // Auction group related actions return always JSON content
    header('Content-Type: application/json');
    $result = json_encode($result);
} else {
    // Merge add. parameters in
    $GET = array_merge($GET, filter_input_array(
        INPUT_GET,
        [
            'bug'   => FILTER_SANITIZE_STRING,
            'name'  => FILTER_SANITIZE_STRING,
            'item'  => FILTER_SANITIZE_STRING,
            'price' => FILTER_SANITIZE_STRING,
            'ship'  => FILTER_SANITIZE_STRING,
            'bid'   => FILTER_SANITIZE_STRING
        ],
        true
    ));

    switch ($GET['api']) {
        // ---------------
        case 'bug':
            // Bug file contents
            $bug = User::$dir.'/'.$GET['bug'];
            if (is_file($bug)) {
                $result = file_get_contents($bug);
            }

            break;
        // ---------------
        case 'bookmark':
            // Need user for snipe instance to have a data path
            User::init($GET['token'], null);

            // Ebay uses &#34; as quotes in auction names
            $name = str_replace('&#34;', '"', urldecode($GET['name']));

            $snipe = new Snipe(
                trim($name),
                '# ' . I18N::_('actual_bid') . ': ' . urldecode($GET['price']) . PHP_EOL .
                '# ' . I18N::_('shipping_costs') . ': ' . urldecode($GET['ship']) . PHP_EOL .
                $GET['item'] . ' ' . $GET['bid']
            );

            if ($snipe->save()) {
                $msg = I18N::_('group_added', $name);
                $_SESSION['new'][$snipe->getHash()] = true;
            } else {
                $msg = I18N::_('group_add_failed');
            }

            $result = '<html><body style="padding:1rem;font-family:sans-serif">'
                    . $msg
                    . '</body></html>';

            break;
    } // switch
}

die($result);
