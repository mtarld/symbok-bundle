# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
