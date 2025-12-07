<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorLaravel\Rector\FuncCall\RemoveDumpDataDeadCodeRector;
use RectorLaravel\Set\LaravelLevelSetList;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/domains',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withSkip([
        // Skip vendor directory
        __DIR__.'/vendor',
        // Skip bootstrap cache
        __DIR__.'/bootstrap/cache',
        // Skip storage
        __DIR__.'/storage',
    ])
    ->withSets([
        // Laravel 11+ upgrade rules (includes all lower versions)
        LaravelLevelSetList::UP_TO_LARAVEL_110,

        // Code quality improvements
        LaravelSetList::LARAVEL_CODE_QUALITY,

        // Collection method improvements
        LaravelSetList::LARAVEL_COLLECTION,

        // Use Str and Arr facades instead of helpers
        LaravelSetList::LARAVEL_ARRAY_STR_FUNCTION_TO_STATIC_CALL,

        // Container string to fully qualified name
        LaravelSetList::LARAVEL_CONTAINER_STRING_TO_FULLY_QUALIFIED_NAME,

        // Facade aliases to full names
        LaravelSetList::LARAVEL_FACADE_ALIASES_TO_FULL_NAMES,

        // Replace abort/report/throw in conditions with *_if helpers
        LaravelSetList::LARAVEL_IF_HELPERS,

        // Eloquent magic methods to query builder
        LaravelSetList::LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER,

        // Testing improvements
        LaravelSetList::LARAVEL_TESTING,

        // Type declarations for better type safety
        LaravelSetList::LARAVEL_TYPE_DECLARATIONS,
    ])
    ->withConfiguredRule(RemoveDumpDataDeadCodeRector::class, [
        'dd',
        'dump',
        'var_dump',
        'print_r',
        'ray',
    ])
    ->withImportNames()
    ->withParallel();
