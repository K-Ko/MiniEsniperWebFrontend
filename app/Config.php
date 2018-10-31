<?php
/**
 *
 *
 * @author     Knut Kohl <github@knutkohl.de>
 * @copyright  (c) 2016 Knut Kohl
 * @licence    MIT License - http://opensource.org/licenses/MIT
 */
namespace App;

/**
 * Simple configuration class
 */
class Config
{
    /**
     * Singleton pattern
     *
     * @return Instance
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Load a config file if exists
     *
     * @param string $file
     * @return instance $this for fluid interface
     */
    public function load($file)
    {
        if (is_file($file)) {
            foreach (include $file as $key => $value) {
                $this->set($key, $value);
            }
        }

        return $this;
    }

    /**
     * Get a value, return $default if not found
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        $name = strtolower($name);
        return array_key_exists($name, $this->data) ? $this->data[$name] : $default;
    }

    /**
     * Set a value
     *
     * @param string $name
     * @param mixed $value
     * @return Instance $this for fluid interface
     */
    public function set($name, $value)
    {
        $this->data[strtolower($name)] = $value;
        return $this;
    }

    /**
     * Magic get a value, return null if not found
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Magic set a value
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    // -----------------------------------------------------------------------
    // PROTECTED
    // -----------------------------------------------------------------------

    /**
     * Singleton pattern
     *
     * @var Config
     */
    protected static $instance = null;

    /**
     * Configuration data
     *
     * @var array
     */
    protected $data;

    /**
     * Hidden for singleton
     */
    protected function __construct()
    {
        $this->data = [];
    }
}
