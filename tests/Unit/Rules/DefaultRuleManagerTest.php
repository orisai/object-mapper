<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Rules\BackedEnumRule;
use Orisai\ObjectMapper\Rules\DefaultRuleManager;
use Orisai\ObjectMapper\Rules\RuleManager;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\Rules\AlwaysInvalidRule;
use function get_class;
use function sprintf;
use const PHP_VERSION_ID;

final class DefaultRuleManagerTest extends TestCase
{

	private DefaultRuleManager $ruleManager;

	protected function setUp(): void
	{
		parent::setUp();
		$this->ruleManager = new DefaultRuleManager();
	}

	public function testDefaultRules(): void
	{
		foreach (RuleManager::DefaultRules as $rule) {
			if (PHP_VERSION_ID < 8_01_00 && $rule === BackedEnumRule::class) {
				continue;
			}

			self::assertSame($rule, get_class($this->ruleManager->getRule($rule)));
		}
	}

	public function testMissingRule(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(sprintf(
			'Rule `%s` does not exist.',
			AlwaysInvalidRule::class,
		));

		$this->ruleManager->getRule(AlwaysInvalidRule::class);
	}

	public function testAddedRule(): void
	{
		$this->ruleManager->addRule(new AlwaysInvalidRule());
		/** @phpstan-ignore-next-line */
		self::assertInstanceOf(AlwaysInvalidRule::class, $this->ruleManager->getRule(AlwaysInvalidRule::class));
	}

}
