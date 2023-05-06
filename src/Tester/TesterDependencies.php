<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Tester;

use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\MetaResolver;
use Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta;
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Processing\Processor;
use Orisai\ObjectMapper\Rules\DefaultRuleManager;
use Orisai\ObjectMapper\Types\MessageType;
use ReflectionProperty;

final class TesterDependencies
{

	public MetaLoader $metaLoader;

	public MetaResolver $metaResolver;

	public DefaultRuleManager $ruleManager;

	public Processor $processor;

	/**
	 * @internal
	 * @see ObjectMapperTester::buildDependencies()
	 */
	public function __construct(
		MetaLoader $metaLoader,
		MetaResolver $metaResolver,
		DefaultRuleManager $ruleManager,
		Processor $processor
	)
	{
		$this->metaLoader = $metaLoader;
		$this->metaResolver = $metaResolver;
		$this->ruleManager = $ruleManager;
		$this->processor = $processor;
	}

	public function createRuleArgsContext(ReflectionProperty $property): RuleArgsContext
	{
		return new RuleArgsContext($property, $this->ruleManager, $this->metaLoader, $this->metaResolver);
	}

	public function createTypeContext(?Options $options = null): TypeContext
	{
		return new TypeContext(
			$this->metaLoader,
			$this->ruleManager,
			$options !== null ? $options->createClone() : new Options(),
		);
	}

	public function createFieldContext(
		?DefaultValueMeta $defaultValueMeta = null,
		?Options $options = null,
		bool $initializeObjects = false
	): FieldContext
	{
		return new FieldContext(
			$this->metaLoader,
			$this->ruleManager,
			$this->processor,
			$options !== null ? $options->createClone() : new Options(),
			new MessageType('test'),
			$defaultValueMeta ?? DefaultValueMeta::fromNothing(),
			$initializeObjects,
			'test',
			new ReflectionProperty(self::class, 'processor'),
		);
	}

}
