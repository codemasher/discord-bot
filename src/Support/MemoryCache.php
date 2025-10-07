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

namespace codemasher\DiscordBot\Support;

use InvalidArgumentException;
use function array_key_exists;
use function trim;

/**
 * A very rudimentary intentionally non-persistent cache class
 *
 * No PSR-16, no Promises, no nothing - just plain old "you get what you asked for"
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

		if(!$this->has($key)){
			return $default;
		}

		return $this->cache[$this->key($key)];
	}

	/**
	 * Deletes the given key/value pair.
	 */
	public function delete(string $key):MemoryCache{
		unset($this->cache[$this->key($key)]);

		return $this;
	}

	/**
	 * Checks whether an element with the given key exists
	 */
	public function has(string $key):bool{
		return array_key_exists($this->key($key), $this->cache);
	}

	/**
	 * Trims the given key and checks whether it's empty, returns the trimmed key
	 */
	private function key(string $key):string{
		$key = trim($key);

		if($key === ''){
			throw new InvalidArgumentException('invalid cache key');
		}

		return $key;
	}

}
