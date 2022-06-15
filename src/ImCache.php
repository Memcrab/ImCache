<?php

declare(strict_types=1);

namespace Memcrab\ImCache;

class ImCache
{
    /**
     * @var mixed
     */
    protected static $instance;

    /**
     * @var array
     */
    private $noData = [];
    /**
     * @var mixed
     */
    private $data;
    /**
     * @var mixed
     */
    private $timer;

    public function __construct()
    {
    }
    public function __clone()
    {
    }
    public function __wakeup()
    {
    }

    public static function obj()
    {
        if (!isset(self::$instance) || !(self::$instance instanceof self)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $key
     * @param $value
     * @return mixed
     */
    public function set(string $key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @param $value
     * @param $seconds
     * @return mixed
     */
    public function setEx(string $key, $value, $seconds)
    {
        $this->data[$key] = $value;
        $this->timer[$key] = time() + (int) $seconds;

        return $this;
    }

    /**
     * @param string $key
     */
    public function exists(string $key)
    {
        if (
            (isset($this->data[$key])
                && isset($this->timer[$key])
                && $this->timer[$key] > time()
            ) || (isset($this->data[$key])
                && !isset($this->timer[$key])
            )
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        if (isset($this->timer[$key]) && $this->timer[$key] <= time()) {
            $this->del($key);
        }

        if (isset($this->data[$key])) {
            return $this->data[$key];
        } else {
            return null;
        }
    }

    public function &getAddress(string $key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        } else {
            return $this->noData;
        }
    }

    /**
     * @param string $key
     * @return int
     */
    public function getCacheTime(string $key)
    {
        if (isset($this->timer[$key]) && $this->timer[$key] > time()) {
            return ($this->timer[$key] - time());
        } else {
            return 0;
        }
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function del(string $key)
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }

        if (isset($this->timer[$key])) {
            unset($this->timer[$key]);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAllKeysWithSize(): ?array
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getAllKeysWithCount(): ?array
    {
        $result = [];
        foreach ($this->data as $key => $datum) {
            $result[$key] = is_array($datum) ? count($datum) : 1;
        }
        ksort($result);

        return $result;
    }
}
