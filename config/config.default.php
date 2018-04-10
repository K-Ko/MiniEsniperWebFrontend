<?php
/**
 * Default config file, copy to config.php and adjust
 *
 * @author     Knut Kohl <github@knutkohl.de>
 * @copyright  (c) 2016 Knut Kohl
 * @licence    MIT License - http://opensource.org/licenses/MIT
 */
return [
    /**
     * Basic auth settings
     *
     * WITHOUT LOGIN:
     * The file defined for username "null" (~/.esniper) must exist!
     */
    'basic_auth' => [
        'message'  => 'Forbidden! Restricted area!',
        'user'     => [
            // user name => [ password, esniper config file ]
            null => [ null, '~/.esniper' ],
            // ...
        ]
    ],

    /**
     * Your personal application title
     */
    'title' => 'esniper @ ' . trim(exec('uname -n')),

    /**
     * TLD to use for links to auctions
     * here: ebay.com
     */
    'ebay' => 'com',

    /**
     * Design
     */
    'design' => 'default',

    /**
     * Site language (en|de)
     */
    'language'  => 'en',

    /**
     * Only for development
     */
    'debug' => false,
];
