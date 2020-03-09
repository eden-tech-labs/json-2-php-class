<?php
/**
 * Created by PhpStorm.
 * User: lachezartodorov
 * Date: 2019-04-10
 * Time: 13:35
 */

namespace EdenTechLabs\Common;


use InvalidArgumentException;
use ReflectionClass;

abstract class Enum
{
    protected $value;

    /**
     * MyEnum constructor.
     * @param $value
     * @throws \ReflectionException|InvalidArgumentException
     */
    final public function __construct($value)
    {
        $c = new ReflectionClass($this);
        if(!in_array($value, $c->getConstants())) {
            throw new InvalidArgumentException();
        }
        $this->value = $value;
    }

    final public function __toString()
    {
        return (string)$this->value;
    }

    public function equal(Enum $other): bool
    {
        return (string)$this == (string)$other;
    }

    /**
     * @param static $a
     * @param static $b
     * @return mixed
     */
    public static function compare($a, $b)
    {
        return strcmp((string)$a, (string)$b);
    }


    public function toInt()
    {
        return (int) $this->value;
    }
}