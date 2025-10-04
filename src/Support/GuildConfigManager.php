<?php
/**
 * Class GuildConfigManager
 *
 * @created      21.08.2025
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2025 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace codemasher\DiscordBot\Support;

use chillerlan\Settings\SettingsContainerInterface;
use codemasher\DiscordBot\DiscordBotOptions;
use Discord\Discord;
use Psr\Log\LoggerInterface;
use function array_key_exists;
use function sprintf;

final class GuildConfigManager{

	/** @var \codemasher\DiscordBot\Support\GuildConfig[] */
	private array $config = [];

	public function __construct(
		private readonly SettingsContainerInterface|DiscordBotOptions $options,
		private readonly Discord                                      $discord,
		private readonly LoggerInterface                              $logger,
	){}

	public function get(int|string $guildID):GuildConfig|null{

		if(array_key_exists((int)$guildID, $this->config)){
			return $this->config[(int)$guildID];
		}

		return null;
	}

	public function load():self{
		/** @var \Discord\Parts\Guild\Guild $guild */
		foreach($this->discord->guilds as $guild){
			$this->config[(int)$guild->id] = new GuildConfig($this->options, $guild);

			($this->config[(int)$guild->id]->load() !== false)
				? $this->logger->info(sprintf('guild config loaded for guild %s (%s)', $guild->id, $guild->name))
				: $this->logger->warning(sprintf('could not load config for guild %s (%s)', $guild->id, $guild->name));
		}

		return $this;
	}

}
