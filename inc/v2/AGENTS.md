# Optimole WordPress Plugin v2 - Agent Instructions

## Dev environment tips
- Use `phpunit tests/test-srcset.php` to run srcset functionality tests.
- Use `php -l inc/v2/PageProfiler/Profile.php` to check PHP syntax before committing.
- Run `phpunit --filter="should_skip_sizes"` to test specific functionality.
- Check the namespace structure: `OptimoleWP\PageProfiler\`, `OptimoleWP\BgOptimizer\`, etc.
- Use `OPTML_DEBUG` constant for debug logging: `do_action( 'optml_log', $message )`.

## Code standards
- **CRITICAL**: All functions, methods, and classes MUST have PHPStan-compatible PHPDoc annotations.
- Always use full namespace paths: `OptimoleWP\PageProfiler\Profile`
- Use constants for device types: `Profile::DEVICE_TYPE_MOBILE` (never hardcode `1`)
- Return `WP_Error` for WordPress-compatible errors, `false` for simple failures.
- Use type hints: `string`, `int`, `bool`, `array`, `object`, union types like `string|false`.

## Testing instructions
- Find the test files in the `tests/` directory.
- Use `phpunit --filter="test_name"` to run specific tests.
- Run `php -l filename.php` to check PHP syntax before committing.
- Fix any test or syntax errors until the whole suite is green.
- Add or update tests for the code you change, even if nobody asked.

## Key components quick reference
- **PageProfiler**: `OptimoleWP\PageProfiler\Profile` - handles page profiling and device data
- **BgOptimizer**: `OptimoleWP\BgOptimizer\Lazyload` - background image lazy loading
- **Offload**: `OptimoleWP\Offload\ImageEditor` - prevents local editing of remote images
- **Preload**: `OptimoleWP\Preload\Links` - manages resource preloading

## Common patterns
- Device types: `Profile::DEVICE_TYPE_MOBILE`, `Profile::DEVICE_TYPE_DESKTOP`, `Profile::DEVICE_TYPE_GLOBAL`
- Storage: Extend `Storage\Base` and implement `store()`, `get()`, `delete()`, `delete_all()`
- Error handling: Return `WP_Error` for WordPress errors, `false` for simple failures
- Debug logging: `do_action( 'optml_log', $message )` when `OPTML_DEBUG` is true

## PR instructions
- Always run `phpunit` and `php -l` before committing
- Ensure all new code has PHPStan-compatible PHPDoc annotations
- Use full namespace paths, never hardcode device type numbers
