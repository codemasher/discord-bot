<?php
/**
 * Class MemoryCacheTest
 *
 * @created      02.10.2025
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2025 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace codemasher\DiscordBotTest;

use codemasher\DiscordBot\Support\MemoryCache;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MemoryCacheTest extends TestCase{

	protected MemoryCache $memoryCache;

	protected function setUp():void{
		$this->memoryCache = new MemoryCache;
	}

	#[Test]
	public function getNonExistent():void{
		$this::assertNull($this->memoryCache->get('foo'));
		$this::assertSame('default', $this->memoryCache->get('bar', 'default'));
	}

	#[Test]
	public function setGet():void{
		$this->memoryCache->set('foo', 'bar');

		$this::assertSame('bar', $this->memoryCache->get('foo'));
	}

	#[Test]
	public function delete():void{
		$this->memoryCache->set('foo', 'bar');

		$this::assertSame('bar', $this->memoryCache->get('foo'));

		$this->memoryCache->delete('foo');

		$this::assertNull($this->memoryCache->get('foo'));
	}

	#[Test]
	public function has():void{
		$this::assertFalse($this->memoryCache->has('foo'));

		$this->memoryCache->set('foo', 'bar');

		$this::assertTrue($this->memoryCache->has('foo'));
	}

	#[Test]
	public function emptyKeyException():void{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('invalid cache key');

		$this->memoryCache->set(' ', 'bar');
	}

}
