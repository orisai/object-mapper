<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Printers;

use Orisai\ObjectMapper\Printers\PrinterScope;
use PHPUnit\Framework\TestCase;

final class PrinterScopeTest extends TestCase
{

	public function testInvalidDefault(): void
	{
		$scope = PrinterScope::forInvalidScope();
		self::assertFalse($scope->shouldRenderValid());
	}

	public function testChangeState(): void
	{
		$scope = PrinterScope::forInvalidScope();

		$scope = $scope->withValidNodes();
		self::assertTrue($scope->shouldRenderValid());

		$scope = $scope->withoutValidNodes();
		self::assertFalse($scope->shouldRenderValid());
	}

	public function testLockValid(): void
	{
		$scope = PrinterScope::forInvalidScope();
		$scope = $scope->withValidNodes()->withImmutableState()->withoutValidNodes();

		self::assertTrue($scope->shouldRenderValid());
	}

	public function testLockInvalid(): void
	{
		$scope = PrinterScope::forInvalidScope();
		$scope = $scope->withImmutableState()->withValidNodes();

		self::assertFalse($scope->shouldRenderValid());
	}

}
