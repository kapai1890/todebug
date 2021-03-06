# Todebug
Debug logger with over 0 million downloads.

# Requirements
* PHP 5.6
* WordPress 4.9

# Installation
Like any other WordPress plugin.

To use as must-use plugin just copy `todebug-mu.php` into `mu-plugins/` directory.

# Functions
1. `todebug(...$vars)` — put the values into the log; outputs root strings without quotes `""`.
```php
$ todebug('Offset:', 3);
> Offset: 3

$ todebug(['offset' => 3]);
> ["offset" => 3]
```

2. `todebugs(...$vars)` — put the values into the log in strict mode; all strings will be quoted with `""`.
```php
$ todebugs('Hello world');
> "Hello world"

$ todebugs('Offset:', 3);
> "Offset:" 3
```

3. `todebugms(string $message, ...$vars)` — same as todebugs(), but prints the **$message** without quotes `""`.
```php
$ todebugms('Offset:', 'three');
> Offset: "three"
```

4. `todebugx($var, string $type, int $maxDepth = -1)` — put the value into the log, indicating it's type manually.
```php
$ todebug('count');
> count

$ todebugs('count');
> "count"

$ todebugx('count', 'function');
> function count($array_or_countable[, $mode]) { ... }
```

5. `todebugu($var, int $maxDepth = -1, $recursiveClasses = [])` — build the message also going into the nested objects; by default all nested objects (objects in objects) have output format _"{%Instance of CLASS_NAME%}"_, this function changes the default rule.

## Control Functions
* `\todebug\clear()`  — clear all log messages in admin bar (does not clear the file).
* `\todebug\on()`     — start writing all kinds of messages into a file.
* `\todebug\off()`    — stop writing any message into a file.
* `\todebug\log()`    — start writing general messages into a file ("general" means not AJAX and not cron).
* `\todebug\nologs()` — stop writing general messages into a file.
* `\todebug\ajax()`   — start writing AJAX messages into a file.
* `\todebug\noajax()` — stop writing AJAX messages into a file.
* `\todebug\cron()`   — start writing cron messages into a file.
* `\todebug\nocron()` — stop writing cron messages into a file.
* `\todebug\reset()`  — restore settings (cancel all other contol functions).

## ToStr Functions
* `asis()`    — ignore the type of the value and print it "as is".
* `get_default_string_builder()` — returns string builder that does all the work.
* `tostr()`   — similar to **todebug()**, builds the message without logging it to the file/_execution log_.
* `tostrms()` — similar to **todebugms()**.
* `tostrs()`  — similar to **todebugs()**.
* `tostru()`  — similar to **todebugu()**.
* `tostrx()`  — similar to **todebugx()**.
* `yesno()`   — converts boolean into "yes"/"no" string.

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
* **Structure** _(skips methods and constants)_:
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
