<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Source;

interface MetaSourceManager
{

	/**
	 * @return array<MetaSource>
	 */
	public function getAll(): array;

}
