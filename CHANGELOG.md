# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/orisai/object-mapper/compare/0.2.0...v1.x)

### Added

- Composer
	- Allow PHP 8.4
	- Allow benmorel/weakmap-polyfill:^0.5.0
- Rules
	- `ArrayShapeRule` - allows processing of arrays with predefined keys
- Processing
	- `Processor` - `reset()` method for resetting to initial state (clearing meta cache)

### Changed

- Metadata
	- Improved accuracy of metadata-related error messages
	- `ReflectorMetaSource` is marked as `@internal`
	- Check that `Callback` returns correct `Args` class type
	- Callbacks in `NodeRuntimeMeta` are grouped by type (performance optimization)
- Callbacks
	- receive `FieldContext` (callback-specific) instead of `FieldContext` and `ObjectContext` instead of
	  `MappedObjectContext`
	- are not called via closure binding when public
- Contexts
	- `TypeContext`, `MappedObjectContext` and `FieldContext` replaced with `ServicesContext`, `DynamicContext`,
	  `PropertyContext`, `FieldContext` (for callbacks) and `ObjectContext` (callback-specific)
		- performance and memory optimization to significantly reduce number of created objects
	- `ArgsContext` renamed to `MetaContext`
		- added child class `MetaFieldContext` with access to property's default value
- Rules
	- `Rule`
		- accepts `ServicesContext`, `DynamicContext` and `PropertyContext` instead of `TypeContext` and `FieldContext`
		- accepts `MetaFieldContext` instead of `ArgsContext`
		- all rules initialize `Type` lazily (performance optimization)
	- `ArrayOfRule`
		- check during metadata parsing that default value is an array when `mergeDefaults` is enabled
	- `ArrayOfRule`, `ListOfRule`
		- skip 2nd and 3rd validation phase for item rules that don't require it
		- pass values with keys included to phase 2 (previously keys were stripped)
	- `MultiValueEfficientRule`
		- renamed to `PhasedRule`
	- `MultiValueEfficientRuleAdapter`
		- renamed to `PhasedRuleAdapter`
		- marked as internal
- Types
	- `ArrayShapeType`
		- `getFields()` always returns the same instances
- Processing
	- `DefaultProcessor`
		- creates `MappedObjectType` only when necessary (performance optimization)
		- automatic meta cache reset after main `process()` call (performance optimization) replaced with `reset()`
		  method
		- properties are not set/unset via closure binding when public

### Removed

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
