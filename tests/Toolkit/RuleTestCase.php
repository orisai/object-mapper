<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Toolkit;

use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Meta\DefaultValueMeta;
use Orisai\ObjectMapper\Options;
use Orisai\ObjectMapper\Types\MessageType;
use ReflectionClass;
use ReflectionProperty;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;

abstract class RuleTestCase extends ProcessingTestCase
{

	protected TypeContext $typeContext;

	protected function setUp(): void
	{
		parent::setUp();
		$this->typeContext = new TypeContext($this->metaLoader, $this->ruleManager);
	}

	protected function ruleArgsContext(?ReflectionProperty $property = null): RuleArgsContext
	{
		if ($property === null) {
			$class = new ReflectionClass(NoDefaultsVO::class);
			$property = $class->getProperty('string');
		} else {
			$class = $property->getDeclaringClass();
		}

		return new RuleArgsContext($class, $property, $this->ruleManager, $this->metaLoader, $this->metaResolver);
	}

	protected function fieldContext(
		?DefaultValueMeta $defaultValueMeta = null,
		?Options $options = null,
		bool $initializeObjects = false
	): FieldContext
	{
		return new FieldContext(
			$this->metaLoader,
			$this->ruleManager,
			$this->processor,
			$options ?? new Options(),
			new MessageType('test'),
			$defaultValueMeta ?? DefaultValueMeta::fromNothing(),
			$initializeObjects,
			'test',
			'test',
		);
	}

}
