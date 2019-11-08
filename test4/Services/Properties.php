<?php

namespace Services;

class Properties
{
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function __set($name, $value)
    {
        $this->$name = $value;

        return $this;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->$name);
    }
}
