# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/orisai/object-mapper/compare/0.2.0...v1.x)

### Added

- `ArgsFieldContext` - args validation context which adds access to the property's default value

### Changed

- Composer
	- Allow PHP 8.4
	- Allow benmorel/weakmap-polyfill:^0.5.0
- Metadata
	- Improved accuracy of metadata-related error messages
	- `ReflectorMetaSource` is marked as `@internal`
- `Rule`
	- `resolveArgs()` accepts `ArgsFieldContext` instead of `ArgsContext`

## [0.2.0](https://github.com/orisai/object-mapper/compare/0.1.0...0.2.0) - 2024-06-22

### Added

- Allow PHP 8.3
- Ensure metadata are defined in scope of `MappedObject`
- Conflicting field name exception reports error in source property instead of context property
- Conflicting field name exception reports in which class did the conflict occur
- Check that `Rule` returns expected type of `Args`

## [0.1.0](https://github.com/orisai/object-mapper/releases/tag/0.1.0) - 2023-07-07

Initial release
