<?php
/**
 * Class GuildConfig
 *
 * @created      17.08.2025
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2025 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace codemasher\DiscordBot\Support;

use chillerlan\Settings\SettingsContainerInterface;
use chillerlan\Utilities\File;
use codemasher\DiscordBot\DiscordBotOptions;
use Discord\Parts\Guild\Guild;
use function array_key_exists;
use function is_array;
use function preg_match;
use function sprintf;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

final class GuildConfig{

	private const array rolesElements = ['config_id', 'header', 'message', 'color', 'max_values', 'options'];

	private string|null $filename = null;
	private array       $config;

	private(set) bool $isValid = false {
		get{
			return $this->isValid;
		}
	}

	public function __construct(
		private readonly SettingsContainerInterface|DiscordBotOptions $options,
		private readonly Guild                                        $guild,
	){
		$config = sprintf('%s/guilds/%s/config.json', $this->options->configDir, $this->guild->id);

		if(File::exists($config)){
			$this->filename = File::realpath($config);
		}

	}

	public function load():bool{
		$this->isValid = false;

		if($this->filename === null){
			return false;
		}

		$config = File::loadJSON($this->filename, true);

		if(!$this->validateConfig($config)){
			return false;
		}

		$this->config  = $config;
		$this->isValid = true;

		return true;
	}

	public function save():bool{

		if($this->filename === null){
			return false;
		}

		return (bool)File::saveJSON($this->filename, $this->config, (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
	}

	public function getRoleConfig():array{
		return $this->config['roles'];
	}

	private function validateConfig(mixed $config):bool{

		if(!is_array($config) || !isset($config['roles']) || !is_array($config['roles'])){
			return false;
		}

		foreach($config['roles'] as $cfg){

			if(array_any(self::rolesElements, fn(string $key):bool => !array_key_exists($key, $cfg))){
				return false;
			}

			if(!is_array($cfg['options'])){
				return false;
			}

			foreach($cfg['options'] as $role){
				if(!isset($role['role_id']/*, $role['emoji']*/)){
					return false;
				}

				if(!preg_match('/\d{19}/', (string)$role['role_id'])){
					return false;
				}
			}

		}

		return true;
	}

}
