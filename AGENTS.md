# Agent Workflow

## What This Is

Optimole WordPress plugin — cloud-based image optimization with CDN delivery, lazy loading, WebP/AVIF conversion, media offloading, and a Digital Asset Management (DAM) interface. Version 4.2.1, requires PHP 7.4+, WordPress 5.5+.

## Build & Dev Commands

```bash
# Install dependencies
npm install && composer install

# Build all assets (production)
npm run build

# Development with HMR (all targets in parallel)
npm run dev

# Build individual targets
npm run build:dashboard    # Admin settings UI
npm run build:widget       # Dashboard widget
npm run build:media        # Media library integration
npm run build:optimizer    # Frontend lazy loading script
npm run build:video-player-editor
npm run build:video-player-frontend
```

## Testing

```bash
# PHP unit tests (requires WP test suite)
composer phpunit
composer install-wp-tests          # First-time setup

# Run a specific PHP test file
phpunit tests/test-replacer.php

# Run a specific test method
phpunit --filter="test_method_name"

# JavaScript tests
npm test
npm run test:watch
npm run test:coverage

# E2E tests (Playwright, base URL: http://testing.optimole.com)
npm run e2e:run
npm run e2e:open                   # Interactive UI mode
```

## Linting & Static Analysis

```bash
# JavaScript
npm run lint                       # ESLint
npm run format                     # ESLint --fix

# PHP
composer phpcs                     # PHP CodeSniffer (WordPress-Core standard)
composer format                    # Auto-fix with phpcbf
composer phpstan                   # PHPStan level 6
```

## Architecture

### Two-era codebase: v1 (legacy) and v2 (modern)

**Legacy code (`inc/*.php`)** — Class prefix `Optml_*`, loaded by custom autoloader in `optimole-wp.php`. This is the bulk of the plugin.

**Modern code (`inc/v2/`)** — PSR-4 autoloaded under `OptimoleWP\` namespace via Composer. New features should go here.

### Plugin initialization flow

```
optimole-wp.php → optml() → Optml_Main::instance() (singleton)
  ├── Optml_Manager      — Orchestrates replacers and loads compatibilities
  ├── Optml_Admin         — Admin dashboard UI, conflict detection
  ├── Optml_Rest          — REST API endpoints for the dashboard
  ├── Optml_Media_Offload — Cloud offloading/restoration of media
  ├── Optml_Dam           — Digital Asset Management UI
  ├── Optml_Video_Player  — Video player blocks
  └── Optml_Cli           — WP-CLI commands (when available)
```

### Core subsystems

- **URL/Tag replacement** (`tag_replacer.php`, `url_replacer.php`, `app_replacer.php`) — Parses HTML output, rewrites image URLs to Optimole CDN URLs with optimization parameters.
- **Lazy loading** (`lazyload_replacer.php`, `inc/v2/BgOptimizer/`) — Viewport-based and background-image lazy loading. Frontend JS in `assets/js/optimizer.js`.
- **Media offloading** (`media_offload.php`) — Moves media to/from Optimole cloud storage, handles bulk operations.
- **Settings** (`settings.php`) — Central settings schema and management, stores in `wp_options`.
- **Compatibility layer** (`inc/compatibilities/`) — 49+ integrations for page builders (Elementor, Divi, Beaver Builder), caching plugins (WP Rocket, LiteSpeed), WooCommerce, etc. Each extends `Optml_compatibility`.

### Frontend assets (`assets/src/`)

React-based, built with `@wordpress/scripts`:
- `dashboard/` — Main admin settings page (React + @wordpress/components)
- `widget/` — Admin dashboard widget
- `media/` — Media library DAM integration
- `video-player/` — Editor and frontend video player blocks

Built output goes to `assets/build/`. The `assets/js/optimizer.js` is the frontend lazy-loading script.

## Task Routing (Where to Start)

- Bootstrap/hooks: `optimole-wp.php`, `inc/main.php`, `inc/manager.php`, `inc/filters.php`.
- URL/CDN rewriting: `inc/tag_replacer.php`, `inc/url_replacer.php`, `inc/app_replacer.php` + `tests/test-replacer.php`.
- Lazyload: `inc/lazyload_replacer.php`, `inc/v2/BgOptimizer/Lazyload.php`, `assets/js/optimizer.js` + `tests/test-lazyload.php`.
- Admin/REST/settings: `inc/admin.php`, `inc/rest.php`, `inc/settings.php`, `assets/src/dashboard/`.
- Media/DAM: `inc/media_offload.php`, `inc/dam.php`, `assets/src/media/`.
- Compat/conflicts: `inc/compatibilities/`, `inc/conflicts/`, related `tests/e2e/*.spec.ts`.
- Video player: `inc/video_player.php`, `assets/src/video-player/`, `tests/test-video.php`.

## Security Checklist (WordPress-Specific)

- State-changing actions: capability check + nonce verification.
- Input/output: sanitize on input, escape on output.
- REST: always set strict `permission_callback` for write routes.
- SQL: use `$wpdb->prepare()` for dynamic queries.
- Safety: do not leak secrets/tokens; validate remote and file/media operations before mutate/delete.

## Agent Execution Policy

- Keep changes surgical; avoid unrelated refactors/cleanups.
- Prefer `inc/v2/` for new logic unless legacy coupling forces `inc/*.php`.
- Trace hooks and callsites before editing behavior.
- Add/update the smallest relevant test and run targeted checks first.
- Build only touched frontend targets; call out intentional build artifacts.

## Code Standards

- **PHP**: WordPress-Core via PHPCS. Text domain: `optimole-wp`.
- **JS**: WordPress ESLint config. Tabs, single quotes, semicolons.
- **v2 PHP code** requires PHPStan-compatible PHPDoc annotations, full namespace paths, type hints, and constants for magic values (e.g., `Profile::DEVICE_TYPE_MOBILE` not `1`).
- Error handling: return `WP_Error` for WordPress-compatible errors, `false` for simple failures.
- Debug logging: `do_action( 'optml_log', $message )` when `OPTML_DEBUG` is true.

## Key Constants

Defined in `optimole-wp.php`: `OPTML_URL`, `OPTML_PATH`, `OPTML_VERSION`, `OPTML_NAMESPACE` (`'optml'`), `OPTML_BASEFILE`, `OPTML_DEBUG`, `OPTML_DEBUG_MEDIA`.
