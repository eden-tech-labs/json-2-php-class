# json-2-php-class
Creates object of native classes from JSON string or object
**[json-2-php-class](https://github.com/eden-tech-labs/json-2-php-class)** is a library for converting json objects to native classes.

## Requirements
- PHP 7.2 and above. (Should be checked!)

## Installation

### 1. Using Composer
You can install the library via [Composer](https://getcomposer.org/). If you don't already have Composer installed, first install it [from here](https://getcomposer.org/download/).

After composer is installed, Then run the following command to install the json-2-php-class library:

```
composer require eden-tech-labs/json-2-php-class
```

### 2. Manually

**Warning:** You have to `require` the used classes or make an autoload function to do this.  
If you're not using Composer, you can also clone `eden-tech-labs/json-2-php-class` repository into a directory in your project:

```
git clone https://github.com/eden-tech-labs/json-2-php-class
```

However, using Composer is recommended as you can easily keep the library up-to-date and deal with autoloading.

## Usage
After you installed `json-2-php-class` library, in your classes you should implement the `interface` and `use` the trait and that satisfies the `interface` requirements.
After that you should override the `_mapping()` method. You can check the implementation and documentation below.
You can define your properties in the class or in the `PHPDoc` as in the example. After you define your properties rules you can export `PHPDoc`. Example below.(TODO: put link here)

### Sample Class

```
use DateTime;
use EdenTechLabs\JSON2PHPClass\MadeFromDataContract;
use EdenTechLabs\JSON2PHPClass\MadeFromJson;
use EdenTechLabs\Common\Enum;

/**
 * Class MyApp\SampleClass
 * @property string applicationId
 * @property SampleKind kind
 * @property DateTime purchaseTime
 * @property object developerPayload
 * @property int quantity
 * @property bool acknowledged
 * @property SamplePayload samplePayload
 */
class SampleClass implements MadeFromDataContract
{
    use MadeFromJson;

    protected static function _mapping(): array
    {
        return [
            'applicationId' => 'string',
            'kind' => SampleKind::class,
            'purchaseTime' => 'milliseconds',
            'developerPayload' => 'json',
            'quantity' => 'int',
            'acknowledged' => 'bool',
            'samplePayload' => SamplePayload::class,
        ];
    }
}

/**
 * Class MyApp\SamplePayload
 * @property string stupidId
 * @property bool autoRenewing
 * @property \DateTime purchaseTime
 */
class SamplePayload implements MadeFromDataContract
{
    use MadeFromJson;

    protected static function _mapping() : array {
        return [
            'stupidId' => 'string',
            'autoRenewing' => 'bool',
            'purchaseTime' => 'seconds'
        ];
    }

}

class SampleKind extends Enum
{
    const KIND_ZERO = '0';
    const KIND_ONE = '1';
    const KIND_TWO = '2';
    const KIND_TREE = '3';
}
```

### Sample Usage

```
$json = '
{
  "applicationId": "app-id-1",
  "kind": 2,
  "purchaseTime": "1584805754123",
  "developerPayload": "{\"foo\":\"bar\"}",
  "quantity": 34,
  "acknowledged": true,
  "samplePayload": {
    "stupidId": "foo-bar-45",
    "autoRenewing": false,
    "purchaseTime": 1584805754
  }
}
';

$myClass = SampleClass::make($json);
var_dump($myClass);
```

Output:
```
object(SampleClass)#1 (7) {
  ["applicationId"]=>
  string(8) "app-id-1"
  ["kind"]=>
  object(SampleKind)#3 (1) {
    ["value":protected]=>
    int(2)
  }
  ["purchaseTime"]=>
  object(DateTime)#4 (3) {
    ["date"]=>
    string(26) "2020-03-21 15:49:14.000000"
    ["timezone_type"]=>
    int(1)
    ["timezone"]=>
    string(6) "+00:00"
  }
  ["developerPayload"]=>
  object(stdClass)#5 (1) {
    ["foo"]=>
    string(3) "bar"
  }
  ["quantity"]=>
  int(34)
  ["acknowledged"]=>
  bool(true)
  ["samplePayload"]=>
  object(SamplePayload)#6 (3) {
    ["stupidId"]=>
    string(10) "foo-bar-45"
    ["autoRenewing"]=>
    bool(false)
    ["purchaseTime"]=>
    object(DateTime)#7 (3) {
      ["date"]=>
      string(26) "2020-03-21 15:49:14.000000"
      ["timezone_type"]=>
      int(1)
      ["timezone"]=>
      string(6) "+00:00"
    }
  }
}
```

As a property `mapping` you can define this types:
- `int` Integer
- `string` String
- `float` Any Number that has fraction part
- `bool` Boolean
- `milliseconds` PHP DateTime object generated from string or int containing timestamp in milliseconds
- `seconds` PHP DateTime object generated from string or int containing timestamp in seconds
- `json` Standard PHP object generated trough `json_decode`
- `unknown` The value is assigned to the property without any transformations.
- `AnyClass::class`
  - If `implements MadeFromDataContract` an object will be created using `make` function
  - Any other class will be created by `__construct` passing the `value` as first and only parameter.
- `Closure` To be defined.
