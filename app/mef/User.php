<?php
/**
 *
 *
 * @author     Knut Kohl <github@knutkohl.de>
 * @copyright  (c) 2016 Knut Kohl
 * @licence    MIT License - http://opensource.org/licenses/MIT
 */
namespace mef;

/**
 * Hold all data for logged in user
 */
abstract class User
{
    /**
     * Ebay user name
     *
     * @var string
     */
    public static $name = '';

    /**
     * Ebay password
     *
     * @var string
     */
    public static $password = '';

    /**
     * User data directory
     *
     * @var string
     */
    public static $dir = '';

    /**
     * Init user data
     *
     * @param string $name
     * @param string $password
     * @return void
     */
    public static function init($name, $password)
    {
        self::$name     = $name;
        self::$password = $password;
        self::$dir      = realpath(Config::getInstance()->dataDir) . '/.' . substr(md5(strtolower(self::$name)), -12);

        if (!is_dir(self::$dir)) {
            mkdir(self::$dir, 0700, true);
        }
    }
}
