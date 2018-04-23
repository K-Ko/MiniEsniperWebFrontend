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
 *
 */
abstract class Hook
{
    /**
     * Register hook callback
     *
     * @param string $name
     * @param callable $callback
     * @param integer $position
     * @return void
     */
    public static function register($name, callable $callback, $position = 0)
    {
        if (!isset(self::$hooks[$name])) {
            self::$hooks[$name] = [];
        }
        while (isset(self::$hooks[$name][$position])) {
            $position++;
        }
        self::$hooks[$name][$position] = $callback;
    }

    /**
     * Process hook code
     *
     * @param string $name
     * @param mixed $param
     * @return mixed
     */
    public static function apply($name, &$param = null)
    {
        $result = null;
        if (isset(self::$hooks[$name])) {
            foreach (self::$hooks[$name] as $callback) {
                $result = $callback($param);
            }
        }
        return $result;
    }

    // -----------------------------------------------------------------------
    // PROTECTED
    // -----------------------------------------------------------------------

    /**
     * Registered hooks
     *
     * @var array
     */
    protected static $hooks = [];
}
