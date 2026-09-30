<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'rawMessage' => 'Property Orisai\\ObjectMapper\\Printers\\ErrorVisualPrinter<T of array<mixed>|string>::$converter (Orisai\\ObjectMapper\\Printers\\TypeToPrimitiveConverter<T of array<mixed>|string>) does not accept Orisai\\ObjectMapper\\Printers\\TypeToPrimitiveConverter<covariant T of array<mixed>|string>.',
	'identifier' => 'assign.propertyType',
	'count' => 1,
	'path' => __DIR__ . '/../src/Printers/ErrorVisualPrinter.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Method Orisai\\ObjectMapper\\Processing\\ObjectCreator::createInstance() should return T of Orisai\\ObjectMapper\\MappedObject but returns Orisai\\ObjectMapper\\MappedObject.',
	'identifier' => 'return.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/Processing/ObjectCreator.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Parameter #1 $object of method Orisai\\ObjectMapper\\Processing\\DependencyInjector<T of Orisai\\ObjectMapper\\MappedObject>::inject() expects T of Orisai\\ObjectMapper\\MappedObject, Orisai\\ObjectMapper\\MappedObject given.',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/Processing/ObjectCreator.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Unreachable statement - code above always terminates.',
	'identifier' => 'deadCode.unreachable',
	'count' => 3,
	'path' => __DIR__ . '/Unit/Processing/DefaultProcessorTest.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Call to an undefined method Orisai\\ObjectMapper\\Types\\Type::getMessage().',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/Unit/Rules/ArrayShapeRuleTest.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Call to an undefined method Orisai\\ObjectMapper\\Types\\Type::getName().',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/Unit/Rules/ArrayShapeRuleTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
