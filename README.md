# PHP Overloadable

PHP Library that provides Object Oriented method overloading to PHP class definition. It uses the power of PHP reflexion API to find and call overloaded methods.

**Note**
The implementation is experimental as it might not be fully optimize for every use case, and may cause performance heck to your application when multiple overload method is provided.

## Installation

The recommended way to include the package into your project is by using PHP composer package manager.

In a composer.json at your project or library root folder add the following:

```json
{
    // ...
    "require": {
        "drewlabs/overloadable": "^0.1.0"
    }
    // ...
}
```

## Usage

* Inline method overloading:

```php
// ...
use Drewlabs\Overloadable\Overloadable;

class TestClass
{
    use Overloadable;

    public function log(...$args)
    {
        return $this->overload($args, [
            static function (ConsoleLogger $logger) {
                return $logger->log();
            },
            static function (FileLogger $logger, ?string $prefix = null) {
                return $logger->log($prefix ?? 'ERROR024');
            },
        ]);
    }
}
```

* Class method overload

```php
// ...
use Drewlabs\Overloadable\Overloadable;

class TestClass
{
    use Overloadable;

    public function log(...$args)
    {
        return $this->overload($args, [
            'log1',
            'log2'
        ]);
    }

    private function log1(ConsoleLogger $logger) 
    {
        return $logger->log();
    }

    private function log2(FileLogger $logger, ?string $prefix = null) 
    {
        return $logger->log($prefix ?? 'ERROR024');
    }
}
```

After library installation is completed, in order to use the overloadable implementations, include the composer autoload file in your project entry script. The example below assume your project entry script is `index.php` :

```php
// index.php
// Load composer autoloaded php scripts using PSR4 implementation
require_once __DIR__ 'vendor/autoload.php';

// Calling the overloaded methods
$object = new TestClass();

$object->log(new FileLogger); // Call the overload that accept an instance of FileLogger as 1st parameter
$object->log(new ConsoleLogger); // Call the overload that accept an instance of ConsoleLogger as 1st parameter

$object->log(new ConsoleLogger, 'ERROR CODE 2308'); // Throws execption as there is no overloaded function that matches
```
