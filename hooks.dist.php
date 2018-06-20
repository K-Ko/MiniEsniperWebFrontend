<?php
/**
 * Example hooks
 *
 * @author     Knut Kohl <github@knutkohl.de>
 * @copyright  (c) 2016 Knut Kohl
 * @licence    MIT License - http://opensource.org/licenses/MIT
 */
 // phpcs:disable Generic.Files.LineLength.TooLong

/**
 * Custom styles for esniper logs
 */
mef\Hook::register(
    'head.style',
    function () {
        return '.currently { font-weight: bold; color: blue;  }
                .highbid   { font-weight: bold; color: green; }
                .outbid    { font-weight: bold; color: red;   }';
    }
);

/**
 * Highligt some log lines for better overview
 */
mef\Hook::register(
    'snipe.loaded',
    function ($snipe) {
        $expr = [
            // Remove some uninteresting information
            '~Latency: \d seconds\s*~s'     => '',
            '~\(autom. bids included\)~'    => '',
            '~Sorting auctions.*~'          => '',

            // Highligt log lines for better readability
            // Auction title
            '~^Auction (\d+): *(.*)$~m'     =>
                '<a href="https://www.ebay.'.mef\Config::getInstance()->ebay.'/itm/$1" class="h6 btn-link" target="_blank"><strong>$2</strong></a>',
            // Current price
            '~^Currently:.*$~m'             => '<span class="currently">$0</span>',
            // High bidder NOT yourself
            '~^High bidder.*\(NOT.*$~m'     => '<span class="outbid">$0</span>',
            // High bidder yourself
            '~^High bidder.*!$~m'           => '<span class="highbid">$0</span>',
            '~^won 0 item.*$~m'             => '<span class="outbid h6">$0</span>',
            '~^won [1-9]\d* item.*$~m'      => '<span class="highbid h6">$0</span>',
        ];

        foreach ($expr as $regex => $replace) {
            $snipe->log = preg_replace($regex, $replace, $snipe->log);
        }

        // Trim empty lines
        $snipe->log = preg_replace('~[\r\n]{3,}~', "\n\n", $snipe->log);
    }
);

/**
 * Translate logs to DE
 */
mef\Hook::register(
    'snipe.loaded',
    function ($snipe) {
        if (mef\Config::getInstance()->language != 'de') {
            return;
        }

        $trans = [
            '~Need to win (\d+) item\(s\), (\d+) auction\(s\) remain~' =>
                'Du willst $1 Artikel gewinnen, übrig bleiben $2 Auktion(en)',
            '~You have already won (\d+) item\(s\)~'    => 'Du hast bereits $1 Auktion(en) gewonnen',
            '~Quantity reduced to (\d+) item\(s\)~'     => 'Menge auf $1 reduziert',
            '~Bid price less than minimum bid price~'   => 'Gebotspreis unter dem Mindestgebotspreis',
            '~You paid (\d+)% of your maximum bid~'     => 'Du hast $1% Deines Höchstgebots bezahlt',
            '~Time remaining~'                          => 'Restzeit      ',
            '~days~'                                    => 'Tage',
            // Time remaining
            '~ hours~'                                  => ' Stunden',
            '~mins~'                                    => 'Minuten',
            '~secs~'                                    => 'Sekunden',
            // Sleeping
            '~ minutes~'                                => ' Minuten',
            '~ seconds~'                                => ' Sekunden',
            '~Auction~'                                 => 'Auktion       ',
            '~End time~'                                => 'Endzeit       ',
            '~Currently~'                               => 'Aktuell       ',
            '~your maximum bid~'                        => 'Dein Höchstgebot',
            '~# of bids~'                               => 'Gebote bisher ',
            '~Bidding~'                                 => 'Biete',
            '~Waiting (\d+) Sekunden for auction to complete~' => 'Warte $1 Sekunden auf das Auktionsende',
            '~You have been outbid~'                    => 'Du wurdest überboten',
            '~High bidder~'                             => 'Höchstbieter  ',
            '~\(NOT~'                                   => '(NICHT',
            '~^(.*)Sleeping for a day~m'                => PHP_EOL . '$1Schlafe für einen Tag',
            '~^(.*)Sleeping for~m'                      => PHP_EOL . '$1Schlafe für',
            '~won ([1-9]\d*) item\(s\)~'                => '$1 Artikel gewonnen',
            '~won .* item\(s\)~'                        => 'Nicht gewonnen',
        ];

        foreach ($trans as $regex => $replace) {
            $snipe->log = preg_replace($regex, $replace, $snipe->log);
        }
    },
    100 // As latest!
);

/**
 *
 */
mef\Hook::register(
    'after.render',
    function () {
        // Some statistics
        printf(
            "\n<!-- build in %d ms; peak memory %d kByte -->",
            (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000,
            memory_get_peak_usage()/1000
        );
    }
);
