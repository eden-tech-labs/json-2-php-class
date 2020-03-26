<?php


namespace EdenTechLabs\JSON2PHPClass;


use DateTime;
use Exception;

trait MadeFromJson
{
    protected static $allowNotSpecifiedProperties = true;

    /**
     * @param string $key
     * @return string
     * @throws Exception
     */
    protected static function typeByKey(string $key) : string {
        if (self::$allowNotSpecifiedProperties && !isset(self::_mapping()[$key]))
            throw new Exception('Property "' . $key . '" is not expected in class ' . self::class);

        return (string) self::_mapping()[$key] ?? 'unknown';
    }

    protected static function _mapping() : array {
        return [];
    }

    protected static function _aliases() : array {
        return [];
    }

    /**
     * @param array|string|object $data
     * @return static
     * @throws Exception
     */
    public static function make($data): self
    {
        if (is_string($data))
            $data = json_decode($data);

        if (is_object($data))
            $data = (array)$data;

        if (!is_array($data) || empty($data))
            throw new Exception('Invalid object data');

        $aliases = self::_aliases();

        $object = new self();
        foreach ($data as $key => $value) {

            $propertyKey = (isset($aliases[$key]))
                ? $aliases[$key]
                : $key;

            $parsingMethod = 'parse' . ucfirst($propertyKey) . 'Attribute';
            if (method_exists(self::class, $parsingMethod))
                $value = self::$parsingMethod($value);

            $type = self::typeByKey($propertyKey);
            switch ($type) {
                case 'int':
                    $object->$propertyKey = (int) $value;
                    break;
                case 'string':
                    $object->$propertyKey = (string) $value;
                    break;
                case 'float':
                    $object->$propertyKey = (float) $value;
                    break;
                case 'bool':
                    $object->$propertyKey = (bool) $value;
                    break;
                case 'milliseconds':
                    $object->$propertyKey = DateTime::createFromFormat('U', (int)($value/1000));
                    break;
                case 'seconds':
                    $object->$propertyKey = DateTime::createFromFormat('U', $value);
                    break;
                case 'json':
                    $object->$propertyKey = json_decode($value);
                    break;
                case 'unknown':
                    $object->$propertyKey = $value;
                    break;
                default:
                    if (class_exists($type)) {
                        if (is_callable($value))
                            $object->$propertyKey = $value();
                        elseif (is_subclass_of($type, MadeFromDataContract::class))
                            $object->$propertyKey = $type::make($value);
                        else
                            $object->$propertyKey = new $type($value);
                    } else {
                        $object->$propertyKey = $value;
                    }

                    break;
            }
        }

        return $object;
    }

    public static function generatePHPDoc()
    {
        $output = "/**\n";
        $output .= " * Class " . __CLASS__ . "\n";

        foreach (self::_mapping() as $key => $value) {
            if (class_exists($value))
                $value = "\\$value";

            if ($value == 'milliseconds' || $value == 'seconds')
                $value = '\\DateTime';

            if ($value == 'json')
                $value = 'object';

            if ($value == 'unknown')
                $value = '';

            $output .= " * @property $value $key\n";
        }

        $output .= " */\n";

        return $output;
    }
}