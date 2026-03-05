# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**wp-bootstrap-hooks** is a WordPress plugin that automatically transforms WordPress-generated markup into Bootstrap 5 compatible HTML via WordPress filters and actions. It handles menus, pagination, forms, widgets, comments, galleries, Gutenberg blocks, and content elements. Themes opt in with `add_theme_support('bootstrap')`.

Originally by Benignware (Rafael Nowrotek), currently maintained at `git@github.com:jimboobrien/wp-bootstrap-hooks.git`.

## Development Environment

### Docker (standalone)
```bash
docker-compose up -d                    # Start WordPress + MariaDB + phpMyAdmin
# WordPress: http://localhost:8020
# phpMyAdmin: http://localhost:8021
```

### VVV (current context)
This plugin is mounted inside a VVV WordPress site at `www/theloftnew/`. No separate build step is needed for the plugin PHP itself.

### Frontend Assets (test themes only)
```bash
npm install
npm run bootstrap-classic-custom:build   # Webpack build for test theme
npm run bootstrap-classic-custom:watch   # Watch mode
```

### Composer
```bash
composer install   # Installs dev dependencies (importer, gutenberg, test data, benignware plugins)
```

### No automated tests exist
There is no PHPUnit, no linter config, and no CI/CD pipeline. Testing is manual via the three test themes in `test/themes/`.

## Architecture

### Entry Point & Loading

`bootstrap-hooks.php` is the main plugin file. It:
1. Loads all utility libraries from `lib/`
2. Loads core feature support from `features/functions.php`
3. Calls `wp_bootstrap_hooks()` which requires all feature modules from `features/`
4. Defines `wp_bootstrap_options()` — returns 170+ configurable class/markup options
5. Enqueues `assets/style.css` and `assets/script.js`

### Namespace

Most files use `namespace benignware\wp\bootstrap_hooks`. No PSR-4 autoloading — all files are loaded via `require_once`. Some legacy files (helpers, navigation walker, widgets) remain in the global namespace.

### Key Directories

- **`lib/`** — Low-level utilities: DOM parsing/serialization (`dom.php`), HTML attribute helpers, color processing, theme.json integration, math utilities
- **`features/`** — WordPress hook registrations organized by component (navigation, pagination, comments, forms, widgets, gallery, theme, header, taxonomies, thumbnails)
- **`features/blocks/`** — ~40 Gutenberg block renderers, one per block type. Each hooks into `render_block_core/{block-name}`
- **`features/content/`** — Filters on `the_content` for images, tables, embeds, buttons, alerts
- **`features/gallery/`** — Gallery shortcode/block handler with carousel modal, templates in `template/`
- **`features/theme/`** — Dynamic CSS generation, body class manipulation, theme.json integration
- **`test/themes/`** — Three test themes: `bootstrap-blocks` (FSE/block), `bootstrap-classic` (traditional), `bootstrap-classic-custom` (webpack-built variant)

### Core Patterns

**Filter-based transformation:** Features register WordPress filters that intercept rendered HTML, parse it with DOMDocument, apply Bootstrap classes, and serialize back. Content filters use very high priorities (999999999+) to run last.

**Options system:** All Bootstrap class names are configurable via the `bootstrap_options` filter. Themes can override any option:
```php
add_filter('bootstrap_options', function($options, $args) {
    return array_merge($options, ['img_class' => 'img-responsive']); // Bootstrap 3
}, 1, 2);
```

**Block renderer pattern:** Each file in `features/blocks/` follows the same structure:
1. Check `current_theme_supports('bootstrap')`
2. Parse block HTML with `parse_html()`
3. Map block attributes to Bootstrap classes
4. Serialize with `serialize_html()`
5. Register via `add_filter('render_block_core/{name}', ..., 10, 2)`

**DOM utilities (`lib/dom.php`):** Central to all HTML transformation — `parse_html()`, `serialize_html()`, `dom_query()` (XPath), `add_class()`, `remove_class()`, `add_style()`, `get_style()`.

### Theme Activation

Themes must declare support:
```php
add_theme_support('bootstrap');           // Bootstrap 5 (default)
add_theme_support('bootstrap', ['version' => 4]);  // Bootstrap 4 compat
```

Almost every feature checks `current_theme_supports('bootstrap')` before transforming markup.

### Version

Current version `1.1.22` is tracked in three places: `bootstrap-hooks.php` header, `composer.json`, and `package.json`.
