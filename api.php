<?php
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
            #$result = $snipe->log;

            break;

        // ---------------
        case 'delete':
            $result = $snipe->delete();

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
            'bug'  => FILTER_SANITIZE_STRING,
            'name' => FILTER_SANITIZE_STRING,
            'item' => FILTER_SANITIZE_STRING,
            'ship' => FILTER_SANITIZE_STRING
        ],
        true
    ));

    switch ($GET['api']) {
        // ---------------
        case 'bug':
            // Bug file contents
            $bug = mef\User::$dir.'/'.$GET['bug'];
            if (is_file($bug)) {
                $result = file_get_contents($bug);
            }

            break;
        // ---------------
        case 'bookmark':
            // Need user for snipe instance to have a data path
            mef\User::init($GET['token'], null);

            $name = urldecode($GET['name']);

            // Trim auction title title to max. 40 chars, don't split words
            // $name = substr($name, 0, 40);
            // while (strlen($name) && substr($name, -1) != ' ') {
            //     $name = substr($name, 0, -1);
            // }

            $snipe = new mef\Snipe(
                trim($name),
                '# Shipping: ' . urldecode($GET['ship']) . PHP_EOL .
                '# Adjust your price!' . PHP_EOL .
                $GET['item'] . ' 1.00'
            );

            $result = $snipe->save()
                    ? 'Added auction group<br><br><strong>' . $name . '</strong>'
                    : 'Failed, something went wrong :-(';

            $result = '<html><body style="padding:1rem;font-family:sans-serif">'
                    . $result
                    . '</body></html>';

            break;
    } // switch
}

die($result);
