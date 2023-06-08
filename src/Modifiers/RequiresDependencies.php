<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Modifiers;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\DependencyInjector;

/**
 * @template-covariant T of MappedObject
 *
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class RequiresDependencies implements ModifierDefinition
{

	/** @var class-string<DependencyInjector<T>> */
	private string $injector;

	/**
	 * @param class-string<DependencyInjector<T>> $injector
	 */
	public function __construct(string $injector)
	{
		$this->injector = $injector;
	}

	public function getType(): string
	{
		return RequiresDependenciesModifier::class;
	}

	public function getArgs(): array
	{
		return [
			'injector' => $this->injector,
		];
	}

}
