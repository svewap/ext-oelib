<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * This file represents the configuration for Code Sniffing PSR-2-related
 * automatic checks of coding guidelines
 * Install @fabpot's great php-cs-fixer tool via
 *
 *  $ composer global require fabpot/php-cs-fixer
 *
 * And then simply run
 *
 *  $ php-cs-fixer fix --config-file Configuration/PhpCsFixer/FixerConfiguration.php .
 *
 * inside the directory. Warning: This may take some time.
 *
 * For more information read:
 * http://www.php-fig.org/psr/psr-2/
 * http://cs.sensiolabs.org
 */

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}

// Return a Code Sniffing configuration using
// all sniffers needed for PSR-2
// and additionally:
//  - Remove leading slashes in use clauses.
//  - PHP single-line arrays should not have trailing comma.
//  - Single-line whitespace before closing semicolon are prohibited.
//  - Remove unused use statements in the PHP source code
//  - Ensure Concatenation to have at least one whitespace around
//  - Remove trailing whitespace at the end of blank lines.
return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers([
        'concat_with_spaces',
        'duplicate_semicolon',
        'extra_empty_lines',
        'no_empty_lines_after_phpdocs',
        'ordered_use',
        'phpdoc_no_package',
        'phpdoc_scalar',
        'remove_leading_slash_use',
        'single_array_no_trailing_comma',
        'single_quote',
        'spaces_before_semicolon',
        'unused_use',
        'whitespacy_lines',

        'array_element_no_space_before_comma',
        'function_typehint_space',
        'include',
        'list_commas',
        'multiline_array_trailing_comma',
        'namespace_no_leading_whitespace',
        'new_with_braces',
        'no_blank_lines_after_class_opening',
        'operators_spaces',
        'phpdoc_indent',
        'phpdoc_separation',
        'phpdoc_to_comment',
        'phpdoc_type_to_var',
        'phpdoc_types',
        'remove_lines_between_uses',
        'standardize_not_equal',

        'short_array_syntax',
    ]);