<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Dependencies;

use Orisai\ObjectMapper\Modifiers\RequiresDependencies;
use stdClass;

/**
 * @RequiresDependencies(injector=DependentChildVoInjector1::class)
 * @RequiresDependencies(injector=DependentChildVoInjector2::class)
 */
final class DependentChildVO extends DependentBaseVO
{

	public string $child1;

	public int $child2;

	public function __construct(stdClass $base1, string $child1, int $child2)
	{
		parent::__construct($base1);
		$this->child1 = $child1;
		$this->child2 = $child2;
	}

}
