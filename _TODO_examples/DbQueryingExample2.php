<?php declare(strict_types = 1);

use App\Core\User\UserRepository;
use App\Core\User\User;
use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\ArrayOf;
use Orisai\ObjectMapper\Attributes\Modifiers\FieldName;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Exceptions\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Types\ArrayType;
use Orisai\ObjectMapper\Types\NoValue;
use Orisai\ObjectMapper\Types\Value;

/**
 * Initialize array of entities from array of IDs
 */
final class DbQueryingExample2 extends MappedObject
{

	private UserRepository $userRepository;

	public function __construct(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}

	/**
	 * @todo - implement passFilteredValuesAfterFailedValidation filter
	 * @var array<User>
	 * @ArrayOf(
	 *     @IntValue(),
	 *     passFilteredValuesAfterFailedValidation=true,
	 * )
	 * @After("afterIds")
	 * @FieldName("ids")
	 */
	public array $users;

	/**
	 * @param array<int> $ids
	 * @return array<User>
	 * @throws ValueDoesNotMatch
	 */
	public function afterIds(array $ids, FieldContext $context): array
	{
		$users = $this->userRepository->findByIds($ids);
		$existingIds = $users->fetchPairs(null, 'id');

		$type = $context->getType();
		assert($type instanceof ArrayType);

		foreach ($ids as $key => $id) {
			if (!in_array($id, $existingIds, true)) {
				$type->addInvalidValue(
					$key,
					ValueDoesNotMatch::createFromString("User with ID $id not found.", Value::of($key))
				);
			}
		}

		if ($type->hasInvalidPairs()) {
			throw ValueDoesNotMatch::create($type, Value::none());
		}

		return $users->fetchAll();
	}

}
