<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\ObjectMapper\ValueObject;

interface MetaCache
{

	/**
	 * @phpstan-param class-string<ValueObject> $class
	 * @return array<mixed>|null
	 */
	public function load(string $class): ?array;

	/**
	 * @phpstan-param class-string<ValueObject> $class
	 * @param array<mixed> $meta
	 */
	public function save(string $class, array $meta): void;

}
