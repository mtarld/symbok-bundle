# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.1.2]

### Added

- Added PHP7.4 typed properties support

### Changed

- Improved services definition
- Moved tests to `tests` folder
- Changed doctrine inflector by symfony inflector
- Fixed `PhpParser\Comment` deprecations
- Improved logs
- Fixed nullable method documentation return type

### Removed

- Removed PassConfig (replaced by tagged iterator)

## [2.1.1] - 2020-04-04

### Added

- Added `@final` and `@internal` class tags
- Symfony 5 compatibility
- Symfony `Symfony\Component\ErrorHandler\DebugClassLoader` compatibility
- Parsed every `phpDocumentor\Reflection\Type` and `PhpParser\Node` type nodes
- Parsed every Doctrine DBAL's 2.6+ and 2.6- types

### Changed

- Improved commands output
- Improved self composer dependencies
- Changed `RuntimeException` and `LogicException` to more accurate ones
- Improved error handling
- Replaced PHPStan by Psalm

### Removed

- `composer.lock` file
- Codecov coverage analysis

## [2.0.2] - 2019-02-04

### Changed

- Restricted to Symfony 4 only

## [2.0.1] - 2019-14-01

### Changed

- Made Doctrine DocParser check ignoredAnnotations

### Removed

- `composer.lock` file

## [2.0.0] - 2019-12-06

### Added

- Command to update original files
- Command to preview changes made by compilation (for runtime and for files)
- Handle native array property types
- `hasPrefix` property in Getter annotation
- `updateOtherSide` property in Setter and Data annotations
- Logs of key steps
- Symfony5 support
- Add `__construct` method when *ToMany relations which init each collection properties
  with `ArrayCollection`

### Changed

- Replaced legacy cache by resource cache
- Improved Doctrine other side updates
- Improved Doctrine other side property name finding method
- Set getter nullable when ManyToOne relation (after Getter nullable and before
  property nullable)
- Replaced noAdd by add (with default true) for Setter and Data annotations
- Replaced noRemove by remove (with default true) for Setter and Data annotations
- Improved ConfigTree structure
- Fix annotations from another namespaces deletion
- Made nullable (AllArgsConstructor annotation) and constructorNullable (Data
  annotation) with the highest priority for AllArgsConstructor nullable behavior

### Removed

- Original on-the-fly file rewriting

## [1.0.1] - 2019-12-04

### Changed

- Restrict compatibility from Symfony 3.0 to Symfony 4.3  [#24](https://github.com/mtarld/symbok-bundle/pull/24)
