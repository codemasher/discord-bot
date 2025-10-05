<?php
/**
 * example.php
 *
 * @created      08.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
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

	// Listen for messages.
	// Note: MESSAGE_CONTENT intent must be enabled to get the content if the bot is not mentioned/DMed.
	$discord->on(Event::MESSAGE_CREATE, function(Message $message, Discord $discord):void{
		printf("%s: %s\n", $message->author->username, $message->content);

		// If message is from a bot
		if($message->author->bot){
			// Do nothing
			return;
		}

		// If message is "ping"
		if(strtolower($message->content) === 'ping'){
			$message->reply('Pong!');
		}

	});
});

$dc->run();
