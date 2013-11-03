# Dispatch Extras
A set of utility function for dispatch.

## Requirements
* `apc` - if you want to use `cache()` and `cache_invalidate()`
* `mcrypt` - if you want to use `encrypt()` and `decrypt()`

## Expected Settings
* `dispatch.extras.debug_log` - messages sent to `debug()` calls will be written to this file
* `dispatch.extras.crypt_key` - encryption salt to be used by `encrypt()` and `decrypt()`

## Functions Added
* `debug($message)` - writes the string to file pointed to by `dispatch.extras.debug_log`
* `encrypt($str, $algo = MCRYPT_RIJNDAEL_256, $mode = MCRYPT_MODE_CBC)` - encrypts a string
* `decrypt($str, $algo = MCRYPT_RIJNDAEL_256, $mode = MCRYPT_MODE_CBC)` - decrypts a string
* `cache($key, $func, $ttl = 0)` - maps results of `$func` in apc, with `$ttl` ttl, with `$key`
* `cache_invalidate($key1, ..., $keyN)` - invalidates apc keys

## LICENSE
MIT: <http://noodlehaus.mit-license.org>
