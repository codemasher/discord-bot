<?php
/**
 * Class Roll
 *
 * @created      24.08.2025
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2025 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace codemasher\DiscordBot\Command;

use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Helpers\Collection;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use function array_sum;
use function implode;
use function random_int;
use function sprintf;

class Roll extends CommandAbstract{

	public const string NAME        = 'roll';
	public const string DESCRIPTION = 'rolls one or more n-sided dice';

	protected function build():array{

		$sides = new Option($this->discord)
			->setName('sides')
			->setDescription('sides on the die')
			->setType(Option::INTEGER);

		$amount = new Option($this->discord)
			->setName('amount')
			->setDescription('amount of dice')
			->setType(Option::INTEGER)
			->setMinValue(1)
			->setMaxValue(100);

		return (new CommandBuilder)
			->setType(Command::CHAT_INPUT)
			->setName($this::NAME)
			->setDescription($this::DESCRIPTION)
			->addOption($sides)
			->addOption($amount)
			->toArray();
	}

	protected function execute(Interaction $interaction, Collection $params):void{
		$sides  = ($interaction->data->options->offsetGet('sides')?->value ?? 20);
		$amount = ($interaction->data->options->offsetGet('amount')?->value ?? 1);

		$message = match(true){
			$sides < 1   => sprintf('%s this shape is not available in your current dimension', $interaction->user),
			$sides === 1 => sprintf('%s one side? are you kidding me???', $interaction->user),
			$sides === 2 => sprintf('%s this is not a fucking coin flip', $interaction->user),
			$sides === 3 => sprintf('%s I\'m calling the geometry police', $interaction->user),
			$sides > 100 => sprintf('%s this is a sphere', $interaction->user),
			default      => $this->roll($interaction, $amount, $sides),
		};

		$interaction->respondWithMessage((new MessageBuilder)->setContent($message));
	}

	private function roll(Interaction $interaction, int $amount, int $sides):string{
		$rolls = [];

		for($i = 0; $i < $amount; $i++){
			$rolls[] = random_int(1, $sides);
		}

		$sum = array_sum($rolls);

		return ($amount === 1)
			? sprintf('%s rolled %s with a %s-sided die', $interaction->user, $sum, $sides)
			: sprintf('%s rolled %s (%s) with %s %s-sided dice', $interaction->user, $sum, implode('+', $rolls), $amount, $sides);
	}

}
