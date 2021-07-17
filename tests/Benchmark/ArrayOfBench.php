<?php declare(strict_types = 1);

namespace Orisai\Tests\Benchmark;

use Tests\Orisai\ObjectMapper\Doubles\ArrayOfIntVO;
use Tests\Orisai\ObjectMapper\Doubles\ArrayOfStringVO;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class ArrayOfBench extends ProcessingTestCase
{
	/**
	 * @Iterations(3)
	 * @ParamProviders("provideArrayOfStrings")
	 */
	public function benchArrayOfString(array $items)
	{
		$this->setup();
		$data = [
			'items' => $items,
		];

		$this->processor->process($data, ArrayOfStringVO::class);
	}

	/**
	 * @Iterations(3)
	 * @ParamProviders("provideArrayOfInts")
	 */
	public function benchArrayOfInts(array $items)
	{
		$this->setup();
		$data = [
			'items' => $items,
		];

		$this->processor->process($data, ArrayOfIntVO::class);
	}

	public function provideArrayOfStrings(): \Generator
	{
		yield '10k' => array_fill(0, 10_000, 'string');
		yield '100k' => array_fill(0, 100_000, 'string');
	}

	public function provideArrayOfInts(): \Generator
	{
		yield '10k' => array_fill(0, 10_000, 42);
		yield '100k' => array_fill(0, 100_000, 42);
	}
}
