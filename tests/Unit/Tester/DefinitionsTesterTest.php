<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Tester;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Callbacks\After;
use Orisai\ObjectMapper\Rules\MixedValue;
use Orisai\ObjectMapper\Rules\StringValue;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use Orisai\Utils\Dependencies\DependenciesTester;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\Definition\AttributeLessCallbackDefinition;
use Tests\Orisai\ObjectMapper\Doubles\Definition\AttributeLessModifierDefinition;
use Tests\Orisai\ObjectMapper\Doubles\Definition\AttributeLessRuleDefinition;
use Tests\Orisai\ObjectMapper\Doubles\Definition\WithAttributeIncompatibleRuleDefinition;
use Tests\Orisai\ObjectMapper\Doubles\Definition\WithExtraTargetCallbackAttributeDefinition;
use Tests\Orisai\ObjectMapper\Doubles\Definition\WithExtraTargetRuleAnnotationDefinition;
use Tests\Orisai\ObjectMapper\Doubles\Definition\WithMissingTargetCallbackAttributeDefinition;
use Tests\Orisai\ObjectMapper\Doubles\Definition\WithMissingTargetRuleAnnotationDefinition;
use Tests\Orisai\ObjectMapper\Doubles\Definition\WithNoTargetAnnotationDefinition;
use const PHP_VERSION_ID;

final class DefinitionsTesterTest extends TestCase
{

	public function testNotCallbackAnnotation(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"'Orisai\ObjectMapper\Rules\StringValue' does not implement 'Orisai\ObjectMapper\Callbacks\CallbackDefinition'.",
		);

		DefinitionTester::assertIsCallbackAnnotation(StringValue::class);
	}

	public function testNotCallbackAttribute(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"'Orisai\ObjectMapper\Rules\StringValue' does not implement 'Orisai\ObjectMapper\Callbacks\CallbackDefinition'.",
		);

		DefinitionTester::assertIsCallbackAttribute(StringValue::class);
	}

	public function testNotModifierAnnotation(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"'Orisai\ObjectMapper\Rules\StringValue' does not implement 'Orisai\ObjectMapper\Modifiers\ModifierDefinition'.",
		);

		DefinitionTester::assertIsModifierAnnotation(StringValue::class, []);
	}

	public function testNotModifierAttribute(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"'Orisai\ObjectMapper\Rules\StringValue' does not implement 'Orisai\ObjectMapper\Modifiers\ModifierDefinition'.",
		);

		DefinitionTester::assertIsModifierAttribute(StringValue::class, []);
	}

	public function testNotRuleAnnotation(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"'Orisai\ObjectMapper\Callbacks\After' does not implement 'Orisai\ObjectMapper\Rules\RuleDefinition'.",
		);

		DefinitionTester::assertIsRuleAnnotation(After::class);
	}

	public function testNotRuleAttribute(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"'Orisai\ObjectMapper\Callbacks\After' does not implement 'Orisai\ObjectMapper\Rules\RuleDefinition'.",
		);

		DefinitionTester::assertIsRuleAttribute(After::class);
	}

	public function testMissingAnnotationAtCallback(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"'Tests\Orisai\ObjectMapper\Doubles\Definition\AttributeLessCallbackDefinition' does not define annotation '@Annotation'.",
		);

		DefinitionTester::assertIsCallbackAnnotation(AttributeLessCallbackDefinition::class);
	}

	public function testMissingAttributeAtCallback(): void
	{
		if (PHP_VERSION_ID < 8_00_00) {
			self::markTestSkipped('Attributes are supported on PHP 8.0+');
		}

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"'Tests\Orisai\ObjectMapper\Doubles\Definition\AttributeLessCallbackDefinition' does not define attribute '#[Attribute]'.",
		);

		DefinitionTester::assertIsCallbackAttribute(AttributeLessCallbackDefinition::class);
	}

	public function testMissingAnnotationAtModifier(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"'Tests\Orisai\ObjectMapper\Doubles\Definition\AttributeLessModifierDefinition' does not define annotation '@Annotation'.",
		);

		DefinitionTester::assertIsModifierAnnotation(AttributeLessModifierDefinition::class, []);
	}

	public function testMissingAttributeAtModifier(): void
	{
		if (PHP_VERSION_ID < 8_00_00) {
			self::markTestSkipped('Attributes are supported on PHP 8.0+');
		}

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"'Tests\Orisai\ObjectMapper\Doubles\Definition\AttributeLessModifierDefinition' does not define attribute '#[Attribute]'.",
		);

		DefinitionTester::assertIsModifierAttribute(AttributeLessModifierDefinition::class, []);
	}

	public function testMissingAnnotationAtRule(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"'Tests\Orisai\ObjectMapper\Doubles\Definition\AttributeLessRuleDefinition' does not define annotation '@Annotation'.",
		);

		DefinitionTester::assertIsRuleAnnotation(AttributeLessRuleDefinition::class);
	}

	public function testMissingAttributeAtRule(): void
	{
		if (PHP_VERSION_ID < 8_00_00) {
			self::markTestSkipped('Attributes are supported on PHP 8.0+');
		}

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"'Tests\Orisai\ObjectMapper\Doubles\Definition\AttributeLessRuleDefinition' does not define attribute '#[Attribute]'.",
		);

		DefinitionTester::assertIsRuleAttribute(AttributeLessRuleDefinition::class);
	}

	public function testDefinitionIncompatibleWithAttributes(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"'Tests\Orisai\ObjectMapper\Doubles\Definition\WithAttributeIncompatibleRuleDefinition' does not define annotation"
			. " '@Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor'."
			. ' It is required for attributes compatibility.',
		);

		DefinitionTester::assertIsRuleAnnotation(WithAttributeIncompatibleRuleDefinition::class);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testAnnotationsAreNotInstalled(): void
	{
		DependenciesTester::addIgnoredPackages(['doctrine/annotations']);

		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage("Annotations are testable only with 'doctrine/annotations' installed.");

		DefinitionTester::assertIsRuleAnnotation(MixedValue::class);
	}

	public function testAttributesAreNotSupported(): void
	{
		if (PHP_VERSION_ID >= 8_00_00) {
			self::markTestSkipped('Attributes unavailability can be tested only with PHP < 8.0');
		}

		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage('Attributes are testable only with PHP >= 8.0');

		DefinitionTester::assertIsRuleAttribute(MixedValue::class);
	}

	public function testAnnotationWithNoTarget(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"'Tests\Orisai\ObjectMapper\Doubles\Definition\WithNoTargetAnnotationDefinition' does not define annotation"
			. " '@Doctrine\Common\Annotations\Annotation\Target'.",
		);

		DefinitionTester::assertIsRuleAnnotation(WithNoTargetAnnotationDefinition::class);
	}

	public function testRuleAnnotationWithMissingTarget(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"Annotation '@Doctrine\Common\Annotations\Annotation\Target' of class"
			. " 'Tests\Orisai\ObjectMapper\Doubles\Definition\WithMissingTargetRuleAnnotationDefinition' must define target 'PROPERTY'.",
		);

		DefinitionTester::assertIsRuleAnnotation(WithMissingTargetRuleAnnotationDefinition::class);
	}

	public function testRuleAnnotationWithExtraTarget(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"Annotation '@Doctrine\Common\Annotations\Annotation\Target' of class"
			. " 'Tests\Orisai\ObjectMapper\Doubles\Definition\WithExtraTargetRuleAnnotationDefinition'"
			. " must define only allowed targets, target 'ALL' given. Allowed are: 'ANNOTATION', 'PROPERTY'.",
		);

		DefinitionTester::assertIsRuleAnnotation(WithExtraTargetRuleAnnotationDefinition::class);
	}

	public function testCallbackAttributeWithMissingTarget(): void
	{
		if (PHP_VERSION_ID < 8_00_00) {
			self::markTestSkipped('Attributes are supported on PHP 8.0+');
		}

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"Attribute '#[Attribute]' of class 'Tests\Orisai\ObjectMapper\Doubles\Definition\WithMissingTargetCallbackAttributeDefinition'"
			. " must define target 'Attribute::TARGET_PROPERTY'.",
		);

		DefinitionTester::assertIsCallbackAttribute(WithMissingTargetCallbackAttributeDefinition::class);
	}

	public function testCallbackAttributeWithExtraTarget(): void
	{
		if (PHP_VERSION_ID < 8_00_00) {
			self::markTestSkipped('Attributes are supported on PHP 8.0+');
		}

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"Attribute '#[Attribute]' of class "
			. "'Tests\Orisai\ObjectMapper\Doubles\Definition\WithExtraTargetCallbackAttributeDefinition'"
			. " must define only allowed flags, flag 'Attribute::TARGET_ALL' given."
			. " Allowed are: 'Attribute::TARGET_CLASS', 'Attribute::TARGET_PROPERTY'.",
		);

		DefinitionTester::assertIsCallbackAttribute(WithExtraTargetCallbackAttributeDefinition::class);
	}

}
