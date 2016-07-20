<?php
/**
 *
 *
 * @author     Knut Kohl <github@knutkohl.de>
 * @copyright  (c) 2016 Knut Kohl
 * @licence    MIT License - http://opensource.org/licenses/MIT
 * @version    1.0.0
 */
return array(

    /**
     * Basic auth settings
     */
    'basic_auth' => array(
        'message'  => 'Restricted area',
        'user'     => null,
        'password' => null
    ),

    /**
     * Your personal application title
     */
    'title' => 'esniper@'.trim(exec('uname -n')),

    /**
     * Your esniper config file
     */
    '.esniper' => '~/.esniper',

    /**
     * TLD to use for links to auctions
     * default: ebay.com
     */
    'ebay' => 'com',

    /**
     * Design
     */
    'design' => 'default',

    /**
     * Only for development
     */
    'debug' => false,

);
