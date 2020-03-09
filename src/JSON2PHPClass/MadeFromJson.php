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

        $object = new self();
        foreach ($data as $key => $value) {

            $parsingMethod = 'parse' . ucfirst($key) . 'Attribute';
            if (method_exists(self::class, $parsingMethod))
                $value = self::$parsingMethod($value);

            $type = self::typeByKey($key);
            switch ($type) {
                case 'int':
                    $object->$key = (int) $value;
                    break;
                case 'string':
                    $object->$key = (string) $value;
                    break;
                case 'float':
                    $object->$key = (float) $value;
                    break;
                case 'bool':
                    $object->$key = (bool) $value;
                    break;
                case 'milliseconds':
                    $object->$key = DateTime::createFromFormat('U', (int)($value/1000));
                    break;
                case 'seconds':
                    $object->$key = DateTime::createFromFormat('U', $value);
                    break;
                case 'json':
                    $object->$key = json_decode($value);
                    break;
                case 'unknown':
                    $object->$key = $value;
                    break;
                default:
                    if (class_exists($type)) {
                        if (is_callable($value))
                            $object->$key = $value();
                        elseif (is_subclass_of($type, MadeFromDataContract::class))
                            $object->$key = $type::make($value);
                        else
                            $object->$key = new $type($value);
                    } else {
                        $object->$key = $value;
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