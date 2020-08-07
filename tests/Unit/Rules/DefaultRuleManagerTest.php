<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Rules\DefaultRuleManager;
use Orisai\ObjectMapper\Rules\RuleManager;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Fixtures\AlwaysInvalidRule;
use function sprintf;

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
		foreach (RuleManager::DEFAULT_RULES as $rule) {
			self::assertInstanceOf($rule, $this->ruleManager->getRule($rule));
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
		$this->ruleManager->addRule(AlwaysInvalidRule::class, new AlwaysInvalidRule());
		self::assertInstanceOf(AlwaysInvalidRule::class, $this->ruleManager->getRule(AlwaysInvalidRule::class));
	}

}
