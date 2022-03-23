<?php declare(strict_types = 1);

use App\Core\User\UserRepository;
use App\Core\User\User;
use Orisai\ObjectMapper\Attributes\Expect\ArrayOf;
use Orisai\ObjectMapper\Attributes\Expect\Structure;
use Orisai\ObjectMapper\MappedObject;

/**
 * An alternative to example no.3
 *
 * Initialize array of structures and turn their IDs into entities
 */
final class DbQueryingExample4 extends MappedObject
{

	private UserRepository $userRepository;

	public function __construct(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}

	/**
	 * @var array<DbQueryingExample1>
	 * @ArrayOf(
	 *     @Structure(DbQueryingExample1::class)
	 * )
	 * @Before("beforeExamples")
	 */
	public array $examples;

	/**
	 * @param mixed $rawExamples
	 */
	public function beforeExamples($rawExamples): void
	{
		if (is_array($rawExamples)) {
			$userIds = [];
			foreach ($rawExamples as $rawExample) {
				if (is_array($rawExample)) {
					$userId = $rawExample['userId'] ?? null;
					if (is_int($userId)) {
						$userIds[] = $userId;
					}
				}
			}

			// Load users into identity map, DbQueryingExample1 will be able to get entity without query
			$this->userRepository->findByIds($userIds);
		}
	}

}
