<?php
/**
 * Class CommandAbstract
 *
 * @created      11.08.2025
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2025 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace codemasher\DiscordBot\Command;

use codemasher\DiscordBot\Support\GuildConfigManager;
use codemasher\DiscordBot\Support\MemoryCache;
use Discord\Builders\CommandBuilder;
use Discord\Discord;
use Discord\Helpers\Collection;
use Discord\Helpers\RegisteredCommand;
use Discord\Http\Http;
use Discord\Parts\Interactions\ApplicationCommand;
use Discord\Parts\Interactions\Command\Command;
use Psr\Log\LoggerInterface;

abstract class CommandAbstract implements CommandInterface{

	protected Http $http;
	protected RegisteredCommand $registeredCommand;

	public function __construct(
		protected readonly GuildConfigManager $guildConfig,
		protected readonly MemoryCache        $memoryCache,
		protected readonly Discord            $discord,
		protected readonly LoggerInterface    $logger,
	){
		$this->http = $this->discord->getHttpClient();
	}

	abstract protected function execute(ApplicationCommand $interaction, Collection $params):void;

	protected function build():array{
		return (new CommandBuilder)
			->setType(Command::CHAT_INPUT)
			->setName($this::NAME)
			->setDescription($this::DESCRIPTION)
			->toArray();
	}

	public function register():static{
		// When the bot is ready, attempt to create a global slash command
		// After the command created successfully in your bot, please disable this code
		$this->discord->application->commands->save(new Command($this->discord, $this->build()));

		return $this;
	}

	public function delete(string $id, string|null $reason = null):static{
		$this->discord->application->commands->delete($id, $reason);

		return $this;
	}

	public function listen():static{
		// Respond the command with an interaction message
		$this->registeredCommand = $this->discord->listenCommand($this::NAME, $this->execute(...), $this->autocomplete(...));

		return $this;
	}

	protected function autocomplete(ApplicationCommand $interaction):array|null{
		// noop
		return null;
	}

}
