<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

interface MetaSourceManager
{

	/**
	 * @return array<MetaSource>
	 */
	public function getAll(): array;

}
