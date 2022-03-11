<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation;

/**
 * Base interface for mapped object annotations
 *
 * @internal
 */
interface BaseAnnotation
{

	/**
	 * @return class-string
	 */
	public function getType(): string;

	/**
	 * Return all annotation args provided by user
	 *
	 * @return array<mixed>
	 */
	public function getArgs(): array;

}
