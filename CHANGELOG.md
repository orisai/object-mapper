# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/orisai/object-mapper/compare/0.2.0...v1.x)

### Added

- `ArrayShapeRule` - allows processing of arrays with predefined keys
- `ArgsFieldContext` - args validation context which adds access to the property's default value
- `Processor` - `reset()` method for resetting to initial state (clearing meta cache)

### Changed

- Composer
	- Allow PHP 8.4
	- Allow benmorel/weakmap-polyfill:^0.5.0
- Metadata
	- Improved accuracy of metadata-related error messages
	- `ReflectorMetaSource` is marked as `@internal`
	- Check that `Callback` returns correct `Args` class type
- `Rule`
	- `resolveArgs()` accepts `ArgsFieldContext` instead of `ArgsContext`
	- all rules initialize `Type` lazily (performance optimization)
- `ArrayOfRule`
	- check during metadata parsing that default value is an array when `mergeDefaults` is enabled
- `FieldContext`, `MappedObjectContext`
	- initializes `Type` lazily (performance optimization)
- `ArrayShapeType`
	- `getFields()` always returns the same instances
- `DefaultProcessor`
	- creates `MappedObjectType` only when necessary (performance optimization)

### Removed

- `DefaultProcessor` - automatic meta cache reset after main `process()` call (performance optimization)
- `Skipped` modifier and the related code - it was an undocumented feature that is no longer necessary

## [0.2.0](https://github.com/orisai/object-mapper/compare/0.1.0...0.2.0) - 2024-06-22

### Added

- Allow PHP 8.3
- Ensure metadata are defined in scope of `MappedObject`
- Conflicting field name exception reports error in source property instead of context property
- Conflicting field name exception reports in which class did the conflict occur
- Check that `Rule` returns expected type of `Args`

## [0.1.0](https://github.com/orisai/object-mapper/releases/tag/0.1.0) - 2023-07-07

Initial release
