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
use Iterator;
use ArrayAccess;
use Countable;

/**
 * Hold an array of all snipes
 */
class Snipes implements Iterator, ArrayAccess, Countable
{
    /**
     * ebay user name
     */
    public static $user;

    /**
     * ebay password
     */
    public static $password;

    /**
     * Class constructor, reads all auction group files
     */
    public function __construct()
    {
        $this->position  = 0;
        $this->container = [];

        foreach (glob(User::$dir.'/*.ini') as $file) {
            $snipe = new Snipe();
            $this->container[] = $snipe->loadIni($file);
        }

        // DEPRECATED, will be removed in 2.0.0
        foreach (glob(User::$dir.'/*.txt') as $file) {
            $snipe = new Snipe();
            $this->container[] = $snipe->loadTxt($file);
        }
        // DEPRECATED

        $this->sort();
    }

    /**
     * Get snipe by name
     *
     * @param string $name
     * @return Snipe|null
     */
    public function get($name)
    {
        foreach ($this->container as &$snipe) {
            if ($snipe->name == $name) {
                return $snipe;
            }
        }
    }

    /**
     * Get snipe by token
     *
     * @param string $name
     * @return Snipe|null
     */
    public function find($token)
    {
        foreach ($this->container as &$snipe) {
            if ($snipe->getNameHash() == $token) {
                return $snipe;
            }
        }
    }

    /**
     * Remove one snipe from collection
     *
     * @param Snipe $snipe
     * @return void
     */
    public function remove($snipe)
    {
        foreach ($this->container as $key => $value) {
            if ($value->name == $snipe->name) {
                unset($this[$key]);
                return;
            }
        }
    }

    /**
     * Iterator interface
     *
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Iterator interface
     *
     * @return mixed
     */
    public function current()
    {
        return $this->container[$this->position];
    }

    /**
     * Iterator interface
     *
     * @return integer
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Iterator interface
     *
     * @return void
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * Iterator interface
     *
     * @return boolean
     */
    public function valid()
    {
        return isset($this->container[$this->position]);
    }

    /**
     * ArrayAccess interface
     *
     * @param integer|null $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }

        $this->sort();
    }

    /**
     * ArrayAccess interface
     *
     * @param integer $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * ArrayAccess interface
     *
     * @param integer $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
        $this->sort();
    }

    /**
     * ArrayAccess interface
     *
     * @param integer $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * Countable interface
     *
     * @return integer
     */
    public function count()
    {
        return count($this->container);
    }

    // -----------------------------------------------------------------------
    // PROTECTED
    // -----------------------------------------------------------------------

    /**
     * Array position for Iterator interface
     *
     * @var int
     */
    protected $position;

    /**
     * Array of snipes
     *
     * @var array
     */
    protected $container;

    /**
     * Sort auction groups by name
     *
     * @return void
     */
    protected function sort()
    {
        usort($this->container, function ($a, $b) {
            return strcasecmp($a->name, $b->name);
        });
    }
}
