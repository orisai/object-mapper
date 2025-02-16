<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Source;

interface MetaSourceManager
{

	/**
	 * @return list<MetaSource>
	 */
	public function getAll(): array;

}
