<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Source;

final class DefaultMetaSourceManager implements MetaSourceManager
{

	/** @var list<MetaSource> */
	private array $sources;

	public function addSource(MetaSource $source): void
	{
		$this->sources[] = $source;
	}

	public function getAll(): array
	{
		return $this->sources;
	}

}
