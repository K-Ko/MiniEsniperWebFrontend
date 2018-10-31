<?php
/**
 *
 */
namespace App;

/**
 * Hold all data for a specific snipe
 */
abstract class I18N
{
    /**
     * Load translation file
     *
     * @param string $file
     * @return void
     */
    public static function load($file)
    {
        self::$data = include $file;
    }

    /**
     * Translate key
     *
     * @param string $key
     * @return void
     */
    public static function _($key)
    {
        $args = func_get_args();
        $key = array_shift($args);
        $trans =array_key_exists($key, self::$data)
             ? vsprintf(self::$data[$key], $args)
             : '?? ' . $key . ' ??';

        return preg_replace('~\s+~s', ' ', $trans);
    }

    /**
     * Translation data
     *
     * @var array
     */
    protected static $data = [];
}
