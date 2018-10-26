# Change log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

## x.y.z (unreleased)

### Added
- Test both with the lowest and highest dependency versions (#121)
- Add a configuration for the Google Maps API key (#92, #112)

### Changed
- Move the old tests to Tests/LegacyUnit/ and Tests/LegacyFunctional/ (#123)
- Prefer stable/dist packages by default (#120)

### Deprecated

### Removed
- Drop roave/security-advisories from the dev dependencies (#118)

### Fixed
- Synchronize the versions of the test extensions (#122)
- Add a conflict with a PHP-7.0-incompatible static_info_tables version (#119)
- Add required PHP extension to the composer.json (#88, #110)

## 2.1.0

### Added
- Auto-release to the TER (#108)
- Log more details for geocoding failures (#105)
- Add a configuration for the geocoding API key (#91, #98)
- Add the possibility to set a reply-to address for a mail object (#86)

### Removed
- Remove the __destruct methods (#97)
- Remove no longer necessary destruct method from mail object (#86)

### Fixed
- Update the Google Maps URL (#103)
- Remove the "sensor" parameter from the Google geocoding (#100)
- Rework the geocoding throttling (#87, #95)
- Update the composer package name of static-info-tables (#85)

## 2.0.1

### Fixed
- Work around the PHAR inclusion problem (#84)
- Stop PHP-linting the removed Migrations/ folder (#82) 
- Hide the test tables from BE user table permission lists (#81)
- Fix more deprecation warnings (#80)
- Stop using the deprecated NullTimeTracker in 8.7 (#79)

## 2.0.0

### Added
- Add support for TYPO3 8.7 (#69)
- Add support for PHP 7.1 and 7.2 (#62)

### Changed
- Use Emogrifier from an extension instead of packaging it (#72)
- Suggest static_info_tables >= 6.4.0 (#68)
- Update to PHPUnit 5.3.5 (#59)

### Deprecated
- Deprecate the `$useHtmlSpecialChars` parameter of `translate` (#76)

### Removed
- Drop the class alias map (#71)
- Remove the deprecated ConfigCheck::checkCssStyledContent (#67)
- Drop the deprecated TestingFramework::getTcaForTable (#66)
- Remove Template::getPrefixedMarkers and ::setCss (#65)
- Remove the deprecated Double3Validator (#64) 
- Drop deprecated mailer functions (#61)
- Require TYPO3 7.6 and drop support for TYPO3 6.2 (#60)
- Drop support for PHP 5.5 (#58)

### Fixed
- Indent XLF and CSS with spaces instead of tabs (#77)
- Fix deprecation warnings in TYPO3 8.7 (#75)
- Update the TCA for TYPO3 8.7 (#74)
- Use the new PHPUnit test runner on TYPO3 8.7 (#70)
- Make the unit tests not depend on the current time of day (#57)

## 1.4.0

### Added
- Log the reason for geocoding failures (#50)
- Document in the code when deprecated things will be removed (#36)
- Add an SVG extension icon (#25)
- run the unit tests on TravisCI (#10)
- Composer script for PHP linting (#4)
- add TravisCI builds

### Changed
- Fix lots of PhpStorm code inspection warnings (#55)
- Always use a backslash for full class names (#42)
- Use more semantic PHPUnit functions (#35)
- Always use ::class (#34)
- Move the PHP CS Fixer configuration directly into the Configuration/ folder (#29)
- Update the PHP CS Fixer configuration (#26)
- Update Emogrifier to 2.0.0 and move it to Resources/Private/Php (#20)
- move the extension to GitHub

### Removed
- Directly bail from the geocoding for invalid addresses (#44)
- Drop the incorrect TYPO3 Core license headers (#38)
- remove obsolete TypoScript files (#8)

### Fixed
- Drop the Composer dependency on emogrifier (#49)
- Increase the delay when over the geocoding query limit (#47)
- Allow serialization of FE plugins for mkforms caching (#43)
- Make the geocoding compatible with TYPO3 8LTS (#39)
- Provide cli_dispatch.phpsh for 8.7 on Travis (#37)
- Fix the DataMapper tests in TYPO3 8.7 (#32)
- Require typo3-minimal for installing TYPO3 on Travis (#28)
- Use multiple attempts for failed geocoding (#22)
- Do not consider anonymous FE sessions as "logged in" (#17)
- Do not allow creation of test records in the BE (#21)
- Use $GLOBALS['TYPO3_CONF_VARS'] instead of $TYPO3_CONF_VARS (#16)
- require static_info_tables for dev (#14)
- skip tests that require static_info_tables if the extension is not installed (#11, #12, #13)
- fix autoloading when running the tests in the BE module in non-composer mode (#9)
- fix the "replace" section in the composer.json of the test extensions
- provide null page cache in the testing framework
- test failure about the framework hook in 8.7
- Db::enableFields should be able to find expired records

## 1.3.0

The [change log up to version 1.3.0](Documentation/changelog-archive.txt)
has been archived.
