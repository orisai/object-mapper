<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Modifiers;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\ResolverArgsContext;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\DependencyInjector;
use function is_string;
use function is_subclass_of;
use function sprintf;

/**
 * @implements Modifier<RequiresDependenciesArgs>
 */
final class RequiresDependenciesModifier implements Modifier
{

	private const Injector = 'injector';

	/**
	 * @return RequiresDependenciesArgs<MappedObject>
	 */
	public static function resolveArgs(array $args, ResolverArgsContext $context): RequiresDependenciesArgs
	{
		$checker = new ArgsChecker($args, self::class);

		$checker->checkAllowedArgs([self::Injector]);

		$checker->checkRequiredArg(self::Injector);
		$injector = $args[self::Injector];

		if (!is_string($injector) || !is_subclass_of($injector, DependencyInjector::class)) {
			throw InvalidArgument::create()
				->withMessage($checker->formatMessage(
					sprintf(
						'subclass of %s',
						DependencyInjector::class,
					),
					self::Injector,
					$injector,
				));
		}

		return new RequiresDependenciesArgs($injector);
	}

}
