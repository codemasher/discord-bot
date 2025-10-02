<?php
/**
 * Class MemoryCache
 *
 * @created      02.10.2025
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2025 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace codemasher\DiscordBot;

use InvalidArgumentException;
use function array_key_exists;
use function trim;

/**
 * A very rudimentary intentionally non-persistent cache class
 *
 * No PSR-3, no Promises, no nothing - just plain old "you get what you asked for"
 */
final class MemoryCache{

	protected array $cache = [];

	/**
	 * Stores the given $value with the associated $key,
	 * existing values with the same key will be overwritten.
	 */
	public function set(string $key, mixed $value):MemoryCache{
		$this->cache[$this->key($key)] = $value;

		return $this;
	}

	/**
	 * Returns the stored value for the given $key,
	 * if there's no value stored, $default will be returned.
	 */
	public function get(string $key, mixed $default = null):mixed{
		$key = $this->key($key);

		if(!array_key_exists($key, $this->cache)){
			return $default;
		}

		return $this->cache[$key];
	}

	/**
	 * Deletes the given key/value pair.
	 */
	public function delete(string $key):MemoryCache{
		unset($this->cache[$this->key($key)]);

		return $this;
	}

	private function key(string $key):string{
		$key = trim($key);

		if($key === ''){
			throw new InvalidArgumentException('invalid cache key');
		}

		return $key;
	}

}
