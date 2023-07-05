<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\Exceptions\Logic\NotImplemented;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\ObjectMapper\Rules\MultiValueEfficientRuleAdapter;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class MultiValueEfficientRuleAdapterTest extends ProcessingTestCase
{

	private MultiValueEfficientRuleAdapter $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new MultiValueEfficientRuleAdapter(new MixedRule());
	}

	public function testResolveArgs(): void
	{
		$this->expectException(NotImplemented::class);
		$this->expectExceptionMessage(
			"Method 'resolveArgs()' should never be called, adapter is used internally at runtime for phased processing.",
		);

		$this->rule->resolveArgs([], $this->argsContext());
	}

	public function testGetArgsType(): void
	{
		$this->expectException(NotImplemented::class);
		$this->expectExceptionMessage(
			"Method 'getArgsType()' should never be called, adapter is used internally at runtime for phased processing.",
		);

		$this->rule->getArgsType();
	}

	public function testProcessValue(): void
	{
		$this->expectException(NotImplemented::class);
		$this->expectExceptionMessage(
			"Method 'processValue()' should never be called, adapter is used internally at runtime for phased processing.",
		);

		$this->rule->processValue('value', new EmptyArgs(), $this->fieldContext());
	}

	public function testType(): void
	{
		$args = new EmptyArgs();

		$type = $this->rule->createType($args, $this->createTypeContext());

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);
		self::assertInstanceOf(SimpleValueType::class, $type);
		self::assertSame('mixed', $type->getName());
	}

}
