# "One Is Enough" library TYPO3 extension

[![TYPO3 V11](https://img.shields.io/badge/TYPO3-11-orange.svg)](https://get.typo3.org/version/11)
[![TYPO3 V10](https://img.shields.io/badge/TYPO3-10-orange.svg)](https://get.typo3.org/version/10)
[![License](https://poser.pugx.org/oliverklee/oelib/license.svg)](https://packagist.org/packages/oliverklee/oelib)
[![Total Downloads](https://poser.pugx.org/oliverklee/oelib/downloads.svg)](https://packagist.org/packages/oliverklee/oelib)
[![GitHub CI Status](https://github.com/oliverklee/ext-oelib/workflows/CI/badge.svg?branch=main)](https://github.com/oliverklee/ext-oelib/actions)
[![Coverage Status](https://coveralls.io/repos/github/oliverklee/ext-oelib/badge.svg?branch=main)](https://coveralls.io/github/oliverklee/ext-oelib?branch=main)

This extension provides useful stuff for extension development: helper functions for unit testing, templating and
automatic configuration checks.

Most of the documentation is in ReST format
[in the Documentation/ folder](Documentation/) and is rendered
[as part of the TYPO3 documentation](https://docs.typo3.org/typo3cms/extensions/oelib/).

## Running the tests locally

You will need to have a Git clone of the extension for this with the Composer dependencies installed.

### Running the unit tests

#### On the command line

To run all unit tests on the command line:

```bash
composer ci:tests:unit
```

To run all unit tests in a directory or file (using the directory
`Tests/Unit/Model/` as an example):

```bash
.Build/vendor/bin/phpunit -c .Build/vendor/nimut/testing-framework/res/Configuration/UnitTests.xml Tests/Unit/Model/
```

#### In PhpStorm

First, you need to configure the path to PHPUnit in the settings:

Languages & Frameworks > PHP > Test Frameworks

In this section, configure PhpStorm to use the Composer autoload and the script path `.Build/vendor/autoload.php` within
your project.

In the Run/Debug configurations for PHPUnit, use an alternative configuration file:

`.Build/vendor/nimut/testing-framework/res/Configuration/UnitTests.xml`

### Running the functional tests

You will need a local MySQL user that has the permissions to create new databases.

In the examples, the following credentials are used:

- user name: `typo3`
- password: `typo3pass`
- DB name prefix: `typo3_test` (optional)
- DB host: `localhost` (omitted as this is the default)

You will need to provide those credentials as environment variables when running the functional tests:

- `typo3DatabaseUsername`
- `typo3DatabasePassword`
- `typo3DatabaseName`

#### On the command line

To run all functional tests on the command line:

```bash
typo3DatabaseUsername=typo3 typo3DatabasePassword=typo3pass typo3DatabaseName=typo3_test composer ci:tests:functional
```

To run all functional tests in a directory or file (using the directory
`Tests/Functional/Authentication/` as an example):

```bash
typo3DatabaseUsername=typo3 typo3DatabasePassword=typo3pass typo3DatabaseName=typo3_test .Build/vendor/bin/phpunit -c .Build/vendor/nimut/testing-framework/res/Configuration/FunctionalTests.xml Tests/Functional/Authentication/
```

#### In PhpStorm

First, you need to configure the path to PHPUnit in the settings:

Languages & Frameworks > PHP > Test Frameworks

In this section, configure PhpStorm to use the Composer autoload and the script path `.Build/vendor/autoload.php` within
your project.

In the Run/Debug configurations for PHPUnit, use an alternative configuration file:

`.Build/vendor/nimut/testing-framework/res/Configuration/FunctionalTests.xml`

Also set the following environment variables in your runner configuration:

- `typo3DatabaseUsername`
- `typo3DatabasePassword`
- `typo3DatabaseName`
