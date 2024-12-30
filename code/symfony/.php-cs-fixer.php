<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests'
    ])
    ->exclude('vendor')
;

$config = new PhpCsFixer\Config();

return $config
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache')
    ->setRiskyAllowed(true)
    ->setRules([
        '@PHP80Migration:risky' => true,
        '@PHP81Migration' => true,
        '@PSR12' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'declare_strict_types' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'no_trailing_whitespace' => true,
        'single_quote' => true,
        'no_empty_comment' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => true,
        'no_blank_lines_after_class_opening' => true,
        'include' => true,
        'no_leading_namespace_whitespace' => true,
        'no_blank_lines_after_phpdoc' => true,
        'phpdoc_scalar' => true,
        'phpdoc_summary' => true,
        'self_accessor' => true,
        'no_trailing_comma_in_singleline' => true,
        'single_blank_line_before_namespace' => true,
        'single_line_after_imports' => true,
        'phpdoc_order' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'phpdoc_var_without_name' => true,
        'no_leading_import_slash' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'extra',
                'throw',
                'use',
                'use_trait',
            ]
        ],
        'fully_qualified_strict_types' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'trailing_comma_in_multiline' => true,
    ])
    ->setFinder($finder)
    ;
