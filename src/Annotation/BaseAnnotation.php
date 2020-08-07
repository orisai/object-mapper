<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation;

/**
 * Base interface for value object annotations
 *
 * @internal
 */
interface BaseAnnotation
{

	/**
	 * @phpstan-return class-string
	 */
	public function getType(): string;

	/**
	 * Return all annotation args provided by user
	 *
	 * @return array<mixed>
	 */
	public function getArgs(): array;

}
