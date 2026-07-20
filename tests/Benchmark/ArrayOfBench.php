<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Benchmark;

use Generator;
use Orisai\ObjectMapper\Exception\InvalidData;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\OutputTimeUnit;
use PhpBench\Benchmark\Metadata\Annotations\ParamProviders;
use PhpBench\Benchmark\Metadata\Annotations\RetryThreshold;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use Tests\Orisai\ObjectMapper\Doubles\ArrayOfIntVO;
use Tests\Orisai\ObjectMapper\Doubles\ArrayOfStringVO;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;
use function array_fill;

/**
 * @Revs(3)
 * @Iterations(3)
 * @RetryThreshold(3.5)
 * @OutputTimeUnit("milliseconds")
 */
final class ArrayOfBench extends ProcessingTestCase
{

	private bool $isSetUp = false;

	private function setUpOnce(): void
	{
		if ($this->isSetUp) {
			return;
		}

		$this->isSetUp = true;
		$this->setUp();
	}

	/**
	 * @param array<string> $items
	 * @throws InvalidData
	 *
	 * @ParamProviders("provideArrayOfString")
	 */
	public function benchArrayOfString(array $items): void
	{
		$this->setUpOnce();
		$data = [
			'items' => $items,
		];

		$this->processor->process($data, ArrayOfStringVO::class);
	}

	/**
	 * @param array<int> $items
	 * @throws InvalidData
	 *
	 * @ParamProviders("provideArrayOfInt")
	 */
	public function benchArrayOfInt(array $items): void
	{
		$this->setUpOnce();
		$data = [
			'items' => $items,
		];

		$this->processor->process($data, ArrayOfIntVO::class);
	}

	/**
	 * @return Generator<string, array<string>>
	 */
	public function provideArrayOfString(): Generator
	{
		yield '10k' => array_fill(0, 10_000, 'string');
		yield '100k' => array_fill(0, 100_000, 'string');
	}

	/**
	 * @return Generator<string, array<int>>
	 */
	public function provideArrayOfInt(): Generator
	{
		yield '10k' => array_fill(0, 10_000, 42);
		yield '100k' => array_fill(0, 100_000, 42);
	}

}
