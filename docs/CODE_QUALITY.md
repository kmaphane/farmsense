# Code Quality Control

This document outlines the code quality tools and processes used in the FarmSense application.

## Tools Overview

| Tool | Purpose | Command |
|------|---------|---------|
| **Laravel Pint** | PHP code style fixer (PSR-12 + Laravel preset) | `composer lint` |
| **Rector** | Automated code refactoring and modernization | `composer rector` |
| **ESLint** | JavaScript/TypeScript linting | `npm run lint` |
| **Prettier** | JavaScript/TypeScript formatting | `npm run format` |
| **Pest** | PHP testing framework | `composer test` |

## PHP Quality Control

### Laravel Pint

Pint is Laravel's official PHP code style fixer, built on top of PHP-CS-Fixer.

```bash
# Fix all files
composer lint

# Fix only changed files (recommended for commits)
composer lint:dirty

# Check without fixing (CI mode)
composer lint:test
```

### Rector PHP

[Rector](https://github.com/driftingly/rector-laravel) is an automated refactoring tool that applies Laravel-specific rules to modernize and improve code quality.

```bash
# Apply all rector rules
composer rector

# Preview changes without applying (recommended first)
composer rector:dry
```

#### Enabled Rule Sets

The `rector.php` configuration includes:

| Rule Set | Description |
|----------|-------------|
| `UP_TO_LARAVEL_110` | Laravel 11+ upgrade rules (includes all lower versions) |
| `LARAVEL_CODE_QUALITY` | General code quality improvements |
| `LARAVEL_COLLECTION` | Improves Collection method usage |
| `LARAVEL_ARRAY_STR_FUNCTION_TO_STATIC_CALL` | Converts helpers to `Str::` and `Arr::` facades |
| `LARAVEL_CONTAINER_STRING_TO_FULLY_QUALIFIED_NAME` | Uses FQCN in container bindings |
| `LARAVEL_FACADE_ALIASES_TO_FULL_NAMES` | Replaces facade aliases with full names |
| `LARAVEL_IF_HELPERS` | Converts conditionals to `abort_if()`, `throw_if()`, etc. |
| `LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER` | Converts magic Eloquent calls to explicit query builder |
| `LARAVEL_TESTING` | Testing improvements and assertions |
| `LARAVEL_TYPE_DECLARATIONS` | Adds type hints for better type safety |

#### Debug Code Removal

Rector is configured to remove debug statements:
- `dd()`
- `dump()`
- `var_dump()`
- `print_r()`
- `ray()`

### Combined Quality Commands

```bash
# Run all quality checks (lint + rector dry-run)
composer quality

# Fix all issues automatically
composer fix
```

## JavaScript/TypeScript Quality Control

```bash
# Lint JavaScript/TypeScript files
npm run lint

# Fix linting issues
npm run lint:fix

# Format with Prettier
npm run format
```

## Pre-Commit Workflow

Before committing code, run:

```bash
# 1. PHP quality checks
composer quality

# 2. If issues found, fix them
composer fix

# 3. JavaScript/TypeScript checks
npm run lint

# 4. Run tests
composer test
```

## CI/CD Integration

For CI pipelines, use these commands:

```bash
# PHP style check (fails if issues found)
composer lint:test

# Rector dry-run (shows what would change)
composer rector:dry

# JavaScript lint
npm run lint

# Full test suite
composer test
```

## Configuration Files

| File | Purpose |
|------|---------|
| `rector.php` | Rector PHP configuration |
| `pint.json` | Laravel Pint configuration (if customized) |
| `eslint.config.js` | ESLint configuration |
| `.prettierrc` | Prettier configuration |
| `phpunit.xml` | PHPUnit/Pest configuration |

## Scanned Directories

Rector scans the following directories:
- `app/` - Application code
- `domains/` - Domain-driven design modules
- `config/` - Configuration files
- `database/` - Migrations, seeders, factories
- `routes/` - Route definitions
- `tests/` - Test files

Excluded:
- `vendor/` - Composer dependencies
- `bootstrap/cache/` - Cached files
- `storage/` - Storage files

## Adding Custom Rules

To add additional Rector rules, edit `rector.php`:

```php
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withSets([
        // Add more sets
        LaravelSetList::LARAVEL_FACTORIES,
    ])
    ->withRules([
        // Add individual rules
        SomeRule::class,
    ]);
```

## Resources

- [Rector Laravel Documentation](https://github.com/driftingly/rector-laravel)
- [All Available Rules](https://github.com/driftingly/rector-laravel/blob/main/docs/rector_rules_overview.md)
- [Laravel Pint Documentation](https://laravel.com/docs/pint)
- [ESLint Documentation](https://eslint.org/docs/latest/)
