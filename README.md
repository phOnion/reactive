# Onion-Reactive scheduler

## 1. Install

```shell
composer require onion/reactive
```

## 2. Use

```php
<?php
require 'vendor/autoload.php';

$source = \Rx\Observable::fromArray([1, 2, 3, 4]);
$source->subscribe(
    function ($x) {
        echo 'Next: ', $x, PHP_EOL;
    },
    function (Exception $ex) {
        echo 'Error: ', $ex->getMessage(), PHP_EOL;
    },
    function () {
        echo 'Completed', PHP_EOL;
    }
);

\Onion\Framework\Loop\scheduler()->start();
```

Where `\Onion\Framework\Loop\scheduler()->start();` is the minimal change required in order to have the loop started, everything else could be copy-pasted directly from the [RxPHP docs](https://github.com/ReactiveX/RxPHP) without any further modification necessary.
