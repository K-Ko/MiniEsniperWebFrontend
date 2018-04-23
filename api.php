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

if ($snipe = $snipes->find($GET['token'])) {
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
}

header('Content-Type: application/json');

die(json_encode($result));
