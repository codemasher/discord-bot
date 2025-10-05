<?php
/**
 * example.php
 *
 * @created      05.10.2025
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Helpers\Collection;
use Discord\Parts\Interactions\ApplicationCommand;
use Discord\Parts\Interactions\ApplicationCommandAutocomplete;
use Discord\Parts\Interactions\Command\Choice;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\WebSockets\Intents;

require_once __DIR__.'/../vendor/autoload.php';

ini_set('memory_limit', -1);

$dc = new Discord([
	// https://discord.com/developers/applications/<APP_ID>>/bot
	'token'   => 'YOUR_DISCORD_BOT_TOKEN',
	// Note: MESSAGE_CONTENT, GUILD_MEMBERS and GUILD_PRESENCES are privileged, see https://dis.gd/mcfaq
	'intents' => (Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT),
]);

$dc->on('init', function(Discord $discord):void{
	echo "Bot is ready!\n";

	// we're going to roll dice
	$commandName = 'roll';

	// the command "roll"
	$commandBuilder = (new CommandBuilder)
		->setType(Command::CHAT_INPUT)
		->setName($commandName)
		->setDescription('rolls an n-sided die')
	;

	// an option "sides"
	$sides = new Option($discord)
		->setType(Option::INTEGER)
		->setName('sides')
		->setDescription('sides on the die')
		->setAutoComplete(true)
	;

	$commandBuilder->addOption($sides);

	// attempt to create a global slash command
	// after the command was created successfully, you should disable this code
	$discord->application->commands->save(new Command($discord, $commandBuilder->toArray()));

	// respond to the command with an interaction message
	$discord->listenCommand(
		// the command name to listen to
		$commandName,

		// the command callback
		function(ApplicationCommand $interaction, Collection $params):void{
			$sides = ($interaction->data->options->offsetGet('sides')?->value ?? 20);

			// sanity check
			if(!in_array($sides, [4, 6, 8, 10, 12, 20], true)){
				$sides = 20;
			}

			$message = sprintf('%s rolled %s with a %s-sided die', $interaction->user, random_int(1, $sides), $sides);

			$interaction->respondWithMessage((new MessageBuilder)->setContent($message));
		},

		// the autocomplete callback (must return array to trigger a response)
		function(ApplicationCommandAutocomplete $interaction) use($discord):array|null{

			// respond if the desired option is focused
			/** @see \Discord\Parts\Interactions\Request\Option */
			if($interaction->data->options->offsetGet('sides')->focused){
				// the dataset, e.g. fetched from a database (25 results max)
				$dataset = [4, 6, 8, 10, 12, 20];
				$choices = [];

				foreach($dataset as $sides){
					$choices[] = new Choice($discord, ['name' => sprintf('%s-sided', $sides), 'value' => $sides]);
				}

				return $choices;
			}

			return null;
		},
	);

});

$dc->run();
