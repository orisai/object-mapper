<?php declare(strict_types = 1);

use App\Core\User\UserRepository;
use App\Core\User\User;
use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\ArrayOf;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Exceptions\InvalidData;
use Orisai\ObjectMapper\Exceptions\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Types\ArrayType;
use Orisai\ObjectMapper\Types\NoValue;
use Orisai\ObjectMapper\Types\Value;

/**
 * Initialize array of structures and turn their IDs into entities
 */
final class DbQueryingExample3 extends MappedObject
{

	private UserRepository $userRepository;

	public function __construct(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}

	/**
	 * @todo - implement passFilteredValuesAfterFailedValidation filter
	 * @todo - implement ArrayShape
	 *       - passFilteredValuesAfterFailedValidation - true|false|Rule
	 * @var array<DbQueryingExample1>
	 * @ArrayOf(
	 *     @ArrayShape(
	 *         {
	 *             userId: @IntValue()
	 *         },
	 *         otherProperties: true
	 *     ),
	 *     passFilteredValuesAfterFailedValidation=true,
	 * )
	 * @After("afterExamples")
	 */
	public array $examples;

	/**
	 * @param array<array{userId: int}> $rawExamples
	 * @return array<DbQueryingExample1>
	 * @throws ValueDoesNotMatch
	 */
	public function afterExamples(array $rawExamples, FieldContext $context): array
	{
		$userIds = array_column($rawExamples, 'userId');
		// Load users into identity map, DbQueryingExample1 will be able to get entity without query
		$this->userRepository->findByIds($userIds);

		$type = $context->getType();
		assert($type instanceof ArrayType);

		$processor = $context->getProcessor();

		$examples = [];
		foreach ($rawExamples as $key => $rawExample) {
			try {
				$examples[] = $processor->process($rawExample, DbQueryingExample1::class);
			} catch (InvalidData $exception) {
				$type->addInvalidValue(
					$key,
					$exception
				);
			}
		}

		if ($type->hasInvalidPairs()) {
			throw ValueDoesNotMatch::create($type, Value::none());
		}

		return $examples;
	}

}
