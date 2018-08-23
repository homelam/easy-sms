<?php

namespace Wincy\EasySms\Support;

use ArrayAccess;

class Config implements ArrayAccess
{
    /**
     * @var array
     */
    protected $config;

    /**
     * constructor
     * 
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Get an item from an array using "dot" natation 获取配置项
     * 
     * @param string $key
     * @param mixed $default
     * 
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $config = $this->config;

        if (is_null($key)) {
            return null;
        }

        if (isset($config[$key])) {
            return $config[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($config) || !array_key_exists($segment, $config)) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    /**
     * Whether a offset exists.
     *
     * @param mixed $offset <p>
     *
     * @return bool true on success or false on failure.
     */
    public function offsetExists($offset)
    {   
        return array_key_exists($offset, $this->config);
    }

    /**
     * Offset to retrieve.
     *
     * @param mixed $offset

     * @return mixed Can return all value types
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set.
     *
     * @param mixed $offset 
     */
    public function offsetSet($offset, $value)
    {
        if (issset($this->config[$offset])) {
            $this->config[$offset] = $value;
        }
    }

    /**
     * Offset to unset
     * 
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        if (isset($this->config[$offset])) {
            unset($this->config[$offset]);
        }
    }
}