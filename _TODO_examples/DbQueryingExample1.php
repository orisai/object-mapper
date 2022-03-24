<?php declare(strict_types = 1);

use App\Core\User\UserRepository;
use App\Core\User\User;
use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\IntValue;
use Orisai\ObjectMapper\Attributes\Modifiers\FieldName;
use Orisai\ObjectMapper\Exceptions\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Types\Value;

/**
 * Initialize single entity from ID
 */
final class DbQueryingExample1 extends MappedObject
{

	private UserRepository $userRepository;

	public function __construct(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}

	/**
	 * @IntValue()
	 * @After("afterId")
	 * @FieldName("userId")
	 */
	public User $user;

	/**
	 * @throws ValueDoesNotMatch
	 */
	public function afterId(int $id): User
	{
		$user = $this->userRepository->getById($id);

		if ($user === null) {
			throw ValueDoesNotMatch::createFromString("User with ID $id not found.", Value::of($id));
		}

		return $user;
	}

}
