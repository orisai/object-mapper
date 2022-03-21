<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Printers;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Printers\PrinterScopes;
use PHPUnit\Framework\TestCase;

final class PrinterScopesTest extends TestCase
{

	private PrinterScopes $scopes;

	protected function setUp(): void
	{
		parent::setUp();
		$this->scopes = new PrinterScopes();
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function testOpenClose(): void
	{
		$this->scopes->open();

		$this->scopes->openScope(false);
		$this->scopes->closeScope();

		$this->scopes->openScope(false);
		$this->scopes->openScope(false);
		$this->scopes->closeScope();
		$this->scopes->closeScope();

		$this->scopes->openScope(false);
		$this->scopes->closeScope();

		$this->scopes->close();
	}

	public function testShouldRender(): void
	{
		$this->scopes->open();

		self::assertFalse($this->scopes->shouldRenderValid());

		$this->scopes->openScope(false);
		self::assertTrue($this->scopes->shouldRenderValid());

		$this->scopes->openScope(true);
		self::assertFalse($this->scopes->shouldRenderValid());

		$this->scopes->openScope(false);
		self::assertTrue($this->scopes->shouldRenderValid());
	}

	public function testShouldRenderImmutable(): void
	{
		$this->scopes->open();

		self::assertFalse($this->scopes->shouldRenderValid());

		$this->scopes->openScope(false);
		self::assertTrue($this->scopes->shouldRenderValid());

		// All following should have some result, until this scope is closed
		$this->scopes->openScope(true, true);
		self::assertFalse($this->scopes->shouldRenderValid());

		$this->scopes->openScope(false);
		self::assertFalse($this->scopes->shouldRenderValid());

		$this->scopes->openScope(false, true);
		self::assertFalse($this->scopes->shouldRenderValid());

		// Close all scopes except first (closes all immutable)
		$this->scopes->closeScope();
		$this->scopes->closeScope();
		$this->scopes->closeScope();
		self::assertTrue($this->scopes->shouldRenderValid());

		// New scopes are not in immutable one
		$this->scopes->openScope(false);
		self::assertTrue($this->scopes->shouldRenderValid());

		$this->scopes->openScope(true);
		self::assertFalse($this->scopes->shouldRenderValid());
	}

	public function testOpenTwiceWithoutClose(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage('Can\'t open scopes, previous were never closed');

		$this->scopes->open();
		$this->scopes->open();
	}

	public function testCloseNotOpened(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage('Can\'t close scopes, they were never opened');

		$this->scopes->close();
	}

	public function testCloseNotEmpty(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage('Can\'t close all scopes, some individual scopes are not closed');

		$this->scopes->open();
		$this->scopes->openScope(false);
		$this->scopes->close();
	}

	public function testCloseNotOpenedScope(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage('Can\'t close scope which was never opened');

		$this->scopes->open();
		$this->scopes->closeScope();
	}

}
