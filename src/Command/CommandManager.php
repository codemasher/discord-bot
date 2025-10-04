<?php
/**
 * Class CommandManager
 *
 * @created      18.08.2025
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2025 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace codemasher\DiscordBot\Command;

use chillerlan\Settings\SettingsContainerInterface;
use codemasher\DiscordBot\DiscordBotOptions;
use codemasher\DiscordBot\Support\GuildConfigManager;
use codemasher\DiscordBot\Support\MemoryCache;
use DirectoryIterator;
use Discord\Discord;
use Discord\Parts\Interactions\Command\Command;
use Discord\Repository\Interaction\GlobalCommandRepository;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use function array_key_exists;
use function array_map;
use function in_array;
use function sprintf;
use function substr;

final class CommandManager{

	/** @var \codemasher\DiscordBot\Command\CommandInterface[] */
	private array $commands = [];

	public function __construct(
		private readonly SettingsContainerInterface|DiscordBotOptions $options,
		private readonly GuildConfigManager                           $guildConfig,
		private readonly MemoryCache                                  $memoryCache,
		private readonly Discord                                      $discord,
		private readonly LoggerInterface                              $logger,
	){

	}

	public function get(string $name):CommandInterface|null{

		if(array_key_exists($name, $this->commands)){
			return $this->commands[$name];
		}

		return null;
	}

	public function register():self{
		// fetch existing global commands from the API
		$this->discord->application->commands->freshen()->then($this->registerGlobalCommands(...));

		return $this;
	}

	private function registerGlobalCommands(GlobalCommandRepository $repository):void{
		// a handy $id => $name map of existing commands
		$existingCommandList = array_map(fn(Command $c):string => $c->name, $repository->toArray());

		/** @var \ReflectionClass $reflection */
		foreach($this->fetchCommands() as $reflection){
			/** @var \codemasher\DiscordBot\Command\CommandInterface $command */
			$command = $reflection->newInstanceArgs([$this->guildConfig, $this->memoryCache, $this->discord, $this->logger]);
			/** @var \Discord\Parts\Interactions\Command\Command $existingCommand */
			$existingCommand = $repository->get('name', $command::NAME);

			// exclude commands from a list
			if(in_array($command::NAME, $this->options->excludeGlobalCommands, true)){
				// delete existing excluded command
				if(in_array($command::NAME, $existingCommandList, true)){
					$command->delete($existingCommand->id, 'excluded');
				}

				continue;
			}

			// register new commands
			if($this->options->registerGlobalCommands){
				// update existing command (delete & register again)
				// I'm not sure if this is necessary, needs more observation
				// https://github.com/discord-php/DiscordPHP/discussions/1225
				if($this->options->deleteCommandOnUpdate && in_array($command::NAME, $existingCommandList, true)){
					$command->delete($existingCommand->id, 'update');
				}

				$command->register();

				$this->logger->info(sprintf('registered command: "%s"', $command::NAME));
			}

			$this->commands[$command::NAME] = $command->listen();

			$this->logger->info(sprintf('listening to command: "%s"', $command::NAME));
		}
	}

	private function fetchCommands():array{
		$classes = [];
		/** @var \SplFileInfo $file */
		foreach(new DirectoryIterator(__DIR__) as $file){

			if($file->getExtension() !== 'php'){
				continue;
			}

			$classname  = sprintf('%s\\%s', __NAMESPACE__, substr($file->getFilename(), 0, -4));
			$reflection = new ReflectionClass($classname);

			if(!$reflection->implementsInterface(CommandInterface::class) || $reflection->isAbstract()){
				continue;
			}

			$classes[] = $reflection;
		}

		return $classes;
	}

}
