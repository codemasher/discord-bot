<?php
/**
 * Class DiscordBot
 *
 * @created      11.08.2025
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2025 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace codemasher\DiscordBot;

use chillerlan\Settings\SettingsContainerInterface;
use codemasher\DiscordBot\Command\CommandManager;
use Discord\Discord;
use Discord\WebSockets\Intents;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use function sprintf;

class DiscordBot{

	protected readonly LoggerInterface $logger;
	protected readonly Discord         $discord;

	protected CommandManager           $commandManager;
	protected GuildConfigManager       $guildConfig;

	public function __construct(
		protected readonly SettingsContainerInterface|DiscordBotOptions $options,
	){
		$this->logger  = $this->initLogger();
		$this->discord = $this->initDiscord();
	}

	protected function initLogger():LoggerInterface{
		$logger = new Logger('log', [new NullHandler]);

		if($this->options->logLevel !== null){
			$formatter  = new LineFormatter(null, 'Y-m-d H:i:s', true, true)->setJsonPrettyPrint(true);
			$logHandler = new StreamHandler('php://stdout', $this->options->logLevel)->setFormatter($formatter);

			$logger->pushHandler($logHandler);
		}

		return $logger;
	}

	protected function initDiscord():Discord{
		return new Discord([
			'token'   => $this->options->botToken,
			'intents' => (Intents::getDefaultIntents() | $this->options->intents),
			'logger'  => $this->logger,
		]);
	}

	protected function initGuildConfig(Discord $discord):static{
		$this->guildConfig = new GuildConfigManager($this->options, $discord, $this->logger)->load();

		return $this;
	}

	protected function initCommandManager(GuildConfigManager $guildConfig, Discord $discord):static{
		$this->commandManager = new CommandManager($this->options, $guildConfig, $discord, $this->logger)->register();

		return $this;
	}

	public function run():void{
		$this->discord->on('init', function(Discord $discord):void{
			$this->logger->info(sprintf('logged in as %s (%s)', $discord->username, $discord->user));

			$this
				->initGuildConfig($discord)
				->initCommandManager($this->guildConfig, $discord)
			;

#			$dc->on(Event::MESSAGE_CREATE, function(Message $message):void{});

		});

		$this->discord->run();
	}

}
