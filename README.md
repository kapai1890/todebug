# Todebug
Debug logger with over 0 million downloads.

# Requirements
* PHP 5.6+
* WordPress 4.9+

# Installation
Like any other WordPress plugin.

To use as must-use plugin just copy `todebug-mu.php` into `mu-plugins/` directory.

# Functions
1. **todebug(...$vars)** - put the values into the log; outputs root strings without quotes "".
```php
$ todebug('Offset:', 3);
> Offset: 3

$ todebug(['offset' => 3]);
> ["offset" => 3]
```

2. **todebugs(...$vars)** - put the values into the log in strict mode; all strings will be quoted with "".
```php
$ todebugs('Hello world');
> "Hello world"

$ todebugs('Offset:', 3);
> "Offset:" 3
```

3. **todebugms($message, ...$vars)** - same as todebugs(), but prints the $message without quotes "".
```php
$ todebugms('Offset:', 'three');
> Offset: "three"
```

4. **todebugx($var,** string **$type, $maxDepth** _= "auto"_**)** - put the value into the log, indicating it's value manually.
```php
$ todebug('count');
> count

$ todebugs('count');
> "count"

$ todebugx('count', 'function');
> function count($array_or_countable[, $mode]) { ... }
```

4. **todebugu($var, $maxDepth** _= "auto"_**)** - build the message, also converting all nested objects; by default all nested objects (objects in objects) have output format _"{%Instance of CLASS_NAME%}"_, this function changes the default rule.

## Control Functions
* todebug\clear()  - clear all log messages in admin bar (does not clear the file).
* todebug\on()     - start writing all kinds of messages into a file.
* todebug\off()    - stop writing any message into a file.
* todebug\log()    - start writing general messages into a file ("general" means not AJAX and not cron).
* todebug\nologs() - stop writing general messages into a file.
* todebug\ajax()   - start writing AJAX messages into a file.
* todebug\noajax() - stop writing AJAX messages into a file.
* todebug\cron()   - start writing cron messages into a file.
* todebug\nocron() - stop writing cron messages into a file.
* todebug\reset()  - restore all settings.

# Examples
* **Boolean**: `true`, `false`.
* **Boolean** _(yes/no format)_: `yes`, `no`.
* **Integer**: `57`, `-273`.
* **Float**: `3.14159`, `0.018`, `100.01`.
* **String**: `"Hello"`, `"89"`.
* **Indexed array**: `[1, 2, 3]`.
* **Associative array**: `[1 => "Aa", 2 => "Bb", 26 => "Zz"]`.
* **Iterable object**: `{1, 2, 3}`.
* **Date** _(DateTime object)_: `{18 April, 2019 (08:10:13)}`.
* **Null**: `null`.
* **As is**: `I'm a string without quotes :P`.
* **Closure**: `function ($r, $g, $b[, $a]) { ... }`.
* **Function**: `function todebugx($var, $type[, $maxDepth]) { ... }`.
* **Callback**: `Exception::getMessage() { ... }`.
* **Object**:
```php
class Exception implements Throwable
{
    protected $message = "Test exception";
    private $string = "";
    protected $code = 0;
    protected $file = ".../todebug/temp.php";
    protected $line = 15;
    private $trace = [["file" => ".../Test.php", "line" => 14, "function" => "require"]];
    private $previous;

    final private function __clone() { ... }
    public function __construct() { ... }
    public function __wakeup() { ... }
    final public function getMessage() { ... }
    final public function getCode() { ... }
    final public function getFile() { ... }
    final public function getLine() { ... }
    final public function getTrace() { ... }
    final public function getPrevious() { ... }
    final public function getTraceAsString() { ... }
    public function __toString() { ... }
}
```
* **Structure** _(skips methods)_:
```php
class Exception implements Throwable
{
    protected $message = "Test exception";
    private $string = "";
    protected $code = 0;
    protected $file = ".../todebug/temp.php";
    protected $line = 15;
    private $trace = [["file" => ".../Test.php", "line" => 14, "function" => "require"]];
    private $previous;
}
```

# License
The project is licensed under the [MIT License](https://opensource.org/licenses/MIT).

# Credits
* **ToStr**, <https://github.com/biliavskyi.yevhen/tostr>, Copyright (c) 2019 Biliavskyi Yevhen, MIT License.
* **WordPress Settings Fields**, <https://github.com/biliavskyi.yevhen/wp-settings-fields>, Copyright (c) 2019 Biliavskyi Yevhen, MIT License.
