# Todebug \[P\]
Debug logger with over 0 million downloads.

# Requirements
* PHP 7.0+
* WordPress 3.1.0+ _(Optional. For WordPress plugin only)_

# Installation
## Standalone Plugin
1. Include main file.
2. _Optional._ Define output file with function **set_todebug_output_file**. Output file by default - `.../todebug/logs/%Y-m-d%.log`.
3. _Optional._ Define maximum amount of array items that will output in inline format with function **set_todebug_max_inline_array_length**. By default - 3 items.

```
require_once todebug/main.php;
set_todebug_output_file('/dev/null');
```

## WordPress Plugin
1. Install (upload) the plugin.
2. _Optional._ For must-use plugin, copy file **mu-plugins/todebug/todebug-mu.php** into the folder **mu-plugins/**.
3. _Optional._ Define output file in settings: **Settings > General > Todebug > Output file**. Output file by default - `.../todebug/logs/%Y-m-d%.log`.
4. _Optional._ Enable **silent debugging** (render messages in **execution log** _(see below)_, but push them to log file only on AJAX calls): **Settings > General > Todebug > Silent debugging**. Disabled by default.
5. _Optional._ If required, enable option **Skip AJAX logs** (don't push any message to log file on AJAX calls): **Settings > General > Todebug > Skip AJAX logs**. Disabled by default.
6. _Optional._ Define maximum amount of array items that will output in inline format in settings: **Settings > General > Todebug > Max inline array length**. By default - 3 items.

# Functions
There are 6 functions to convert any type of values into the string: **todebug**, **todebugs**, **todebugx** and **tostring**, **tostrings**, **tostringx**.

1. **todebug(...$vars)** - logs the message into the log file; outputs strings without quotes `""` _(but nested strings - arrays, object fields etc. - is always wrapped with quotes)_.
```
$ todebug('Offset:', 3);
> Offset: 3

$ todebug(['offset' => 3]);
> ["offset" => 3]
```
2. **todebugs(...$vars)** - _strict_ version of _todebug()_; similar to _todebug()_, but **always** outputs strings with quotes `""`.
```
$ todebugs('Hello world');
> "Hello world"

$ todebugs('Offset:', 3);
> "Offset:" 3
```
3. **todebugx($var, string $type)** - outputs the variable with a defined type.
```
$ todebug('count');
> count

$ todebugs('count');
> "count"

$ todebugx('count', 'function');
> function count($array_or_countable[, $mode]) { ... }
```
4. **tostring(...$vars)** - similar to _todebug()_, but will only build and return the message (without pushing it to the log file).
5. **tostrings(...$vars)** - similar to _todebugs()_ and _tostring()_ (strings with quotes, will not push the message to the log file).
6. **tostringx($var, string $type)** - similar to _todebugx()_ and _tostring()_ (output with a defined type, will not push the message to the log file).

## Other Functions
1. todebug_output_file(string $outputFile) - set the output file.
2. todebug_array_length(int $length) - set the maximum length of the array, which will be perceived as "inline".

## More Functions for WordPress
1. todebug_log_to_file() - ignore settings and force the plugin **to write** debug messages into a log file.
2. todebug_dont_log_to_file() - ignore settings and force the plugin **not to write** debug messages into a log file.
3. todebug_reset_options() - stop ignoring settings.

# Examples
* **Boolean**: `true`, `false`
* **Integer**: `57`
* **Float**: `0.18`, `3.142`
* **String**: `"Hello"`
* **Indexed array**: `[1, 2, 3]`
* **Associative array**: `[0 => 5, "a" => true]`
* **Date** _(\DateTime object)_: `{19 April, 2018 (2018-04-19)}`
* **Function**: `function todebug([$vars]) { ... }`
* **Method**: `just\HidePlugins::filterPluginActions($actions, $plugin) { ... }`
* **Closure**: `function ($r, $g, $b[, $a]) { ... }`
* **Null**: `null`
* **Object**:
```
final class todebug\Todebug
{
    private static $executionMessages = [];
    private static $instance = {%Instance of todebug\Todebug%};
    private $version = "18.16.1";
    protected static $outputFile;

    public static function saveMessage($message) { ... }
    protected static function log($message, $outputFile) { ... }
    public static function buildMessage($vars) { ... }
    public static function buildStrings($vars) { ... }
    public static function buildStringAs($var, $type) { ... }
    protected static function proposeOutputFile() { ... }
    private function __construct() { ... }
    private function readVersion() { ... }
    private function addActions() { ... }
    public function loadTranslations() { ... }
    public function addSettings() { ... }
    public function renderOutputFileSetting() { ... }
    public function renderSilentDebuggingSetting() { ... }
    public function loadScripts($page) { ... }
    public function addAdminBarActions($wpAdminBar) { ... }
    public function renderLogs() { ... }
    public function __clone() { ... }
    public function __wakeup() { ... }
    private function terminate($function, $message, $version) { ... }
    public static function create() { ... }
    public static function write($vars) { ... }
    public static function writeStrict($vars) { ... }
    public static function writeAs($var, $type) { ... }
    public static function clear() { ... }
    public static function outputFile() { ... }
    protected static function defaultOutputFile() { ... }
}
```

# Execution Log
**This feature available only in WordPress Plugin.**
All messages of the current execution are rendered in the _execution log_, that you can view when click button **Todebug** in the admin bar.

# License
The project is licensed under the [MIT License](https://opensource.org/licenses/MIT).
