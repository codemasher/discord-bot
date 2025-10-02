<?php
/**
 * Class Ping
 *
 * @created      11.08.2025
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2025 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace codemasher\DiscordBot\Command;

use Discord\Builders\MessageBuilder;
use Discord\Helpers\Collection;
use Discord\Parts\Interactions\ApplicationCommand;

final class Ping extends CommandAbstract{

	public const string NAME        = 'ping';
	public const string DESCRIPTION = 'pong';

	protected function execute(ApplicationCommand $interaction, Collection $params):void{
		$guild = $interaction->guild;

		if($guild !== null){
			$interaction->respondWithMessage((new MessageBuilder)->setContent('Guild Pong! ('.$guild->name.')'));

			return;
		}

		$interaction->respondWithMessage((new MessageBuilder)->setContent('Pong!'));
	}

}
