<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

class DefaultMetaSourceManager implements MetaSourceManager
{

	/** @var array<MetaSource> */
	private array $sources;

	public function addSource(MetaSource $source): void
	{
		$this->sources[] = $source;
	}

	/**
	 * @return array<MetaSource>
	 */
	public function getAll(): array
	{
		return $this->sources;
	}

}
