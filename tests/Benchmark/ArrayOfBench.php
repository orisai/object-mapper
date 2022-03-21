<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Benchmark;

use Generator;
use Orisai\ObjectMapper\Exceptions\InvalidData;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\ParamProviders;
use Tests\Orisai\ObjectMapper\Doubles\ArrayOfIntVO;
use Tests\Orisai\ObjectMapper\Doubles\ArrayOfStringVO;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;
use function array_fill;

final class ArrayOfBench extends ProcessingTestCase
{

	/**
	 * @param array<string> $items
	 * @throws InvalidData
	 *
	 * @Iterations(3)
	 * @ParamProviders("provideArrayOfString")
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
	 * @param array<int> $items
	 * @throws InvalidData
	 *
	 * @Iterations(3)
	 * @ParamProviders("provideArrayOfInt")
	 */
	public function benchArrayOfInt(array $items): void
	{
		$this->setUp();
		$data = [
			'items' => $items,
		];

		$this->processor->process($data, ArrayOfIntVO::class);
	}

	public function provideArrayOfString(): Generator
	{
		yield '10k' => array_fill(0, 10_000, 'string');
		yield '100k' => array_fill(0, 100_000, 'string');
	}

	public function provideArrayOfInt(): Generator
	{
		yield '10k' => array_fill(0, 10_000, 42);
		yield '100k' => array_fill(0, 100_000, 42);
	}

}
