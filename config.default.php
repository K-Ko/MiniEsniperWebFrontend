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
     * Your personal application title
     */
    'title' => 'esniper @ ' . trim(exec('uname -n')),

    /**
     * Try to find esniper binary
     */
    'esniper' => exec('which esniper'),

    /**
     * Create data dir inside root dir. automatic
     *
     * If set, make sure the web server running user (mostly www-data)
     * can write to it!
     */
    'dataDir' => '',

    /**
     * TLD to use for links to auctions
     * here: ebay.com
     */
    'ebay' => 'com',

    /**
     * Bid ? seconds before end time, use only if not set by user "seconds = ..."
     */
    'seconds' => 10,

    /**
     * Design
     */
    'design' => 'default',

    /**
     * Site languages
     */
    'languages' => [
        'en' => 'English',
        'de' => 'Deutsch'
    ],

    /**
     * Locales
     */
    'locales' => [
        'en' => [],
        'de' => ['de_DE.UTF-8', 'de_DE', 'de', 'ge'],
    ],

    /**
     * Inital language
     */
    'language' => 'en',

    /**
     * Only for development
     */
    'debug' => false,
];
