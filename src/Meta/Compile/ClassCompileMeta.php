<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

final class ClassCompileMeta extends NodeCompileMeta
{

	public function hasAnyAttributes(): bool
	{
		return $this->getCallbacks() !== []
			|| $this->getDocs() !== []
			|| $this->getModifiers() !== [];
	}

}
