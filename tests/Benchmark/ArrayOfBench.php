<?php declare(strict_types = 1);

namespace Orisai\Tests\Benchmark;

use Generator;
use Orisai\ObjectMapper\Exception\InvalidData;
use Tests\Orisai\ObjectMapper\Doubles\ArrayOfIntVO;
use Tests\Orisai\ObjectMapper\Doubles\ArrayOfStringVO;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;
use function array_fill;

final class ArrayOfBench extends ProcessingTestCase
{

	/**
	 * @Iterations(3)
	 * @ParamProviders("provideArrayOfStrings")
	 * @param array<string> $items
	 * @throws InvalidData
	 */
	public function benchArrayOfString(array $items): void
	{
		$this->setUp();
		$data = [
			'items' => $items,
		];

		$this->processor->process($data, ArrayOfStringVO::class);
	}

	/**
	 * @Iterations(3)
	 * @ParamProviders("provideArrayOfInts")
	 * @param array<int> $items
	 * @throws InvalidData
	 */
	public function benchArrayOfInts(array $items): void
	{
		$this->setUp();
		$data = [
			'items' => $items,
		];

		$this->processor->process($data, ArrayOfIntVO::class);
	}

	public function provideArrayOfStrings(): Generator
	{
		yield '10k' => array_fill(0, 10_000, 'string');
		yield '100k' => array_fill(0, 100_000, 'string');
	}

	public function provideArrayOfInts(): Generator
	{
		yield '10k' => array_fill(0, 10_000, 42);
		yield '100k' => array_fill(0, 100_000, 42);
	}

}
