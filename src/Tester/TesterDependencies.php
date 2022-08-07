<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Tester;

use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Meta\DefaultValueMeta;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\MetaResolver;
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

	public TypeContext $typeContext;

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
		$this->typeContext = new TypeContext($metaLoader, $ruleManager);
	}

	public function createRuleArgsContext(ReflectionProperty $property): RuleArgsContext
	{
		return new RuleArgsContext($property, $this->ruleManager, $this->metaLoader, $this->metaResolver);
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
			$options ?? new Options(),
			new MessageType('test'),
			$defaultValueMeta ?? DefaultValueMeta::fromNothing(),
			$initializeObjects,
			'test',
			'test',
		);
	}

}
