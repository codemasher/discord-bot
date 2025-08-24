<?php
/**
 * Class Roles
 *
 * @created      21.08.2025
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2025 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace codemasher\DiscordBot\Command;

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Container;
use Discord\Builders\Components\Option;
use Discord\Builders\Components\StringSelect;
use Discord\Builders\Components\TextDisplay;
use Discord\Builders\MessageBuilder;
use Discord\Helpers\Collection;
use Discord\Http\Endpoint;
use Discord\Parts\Guild\Role;
use Discord\Parts\Interactions\Interaction;
use Discord\Repository\Guild\RoleRepository;
use function array_column;
use function array_diff;
use function array_key_exists;
use function array_map;
use function implode;
use function in_array;
use function sprintf;

final class Roles extends CommandAbstract{

	public const string NAME        = 'roles';
	public const string DESCRIPTION = 'Manage self-assignable user roles';

	protected function execute(Interaction $interaction, Collection $params):void{

		if($interaction->guild === null){
			$message = (new MessageBuilder)->setContent('Error: this command cannot be used in direct messages.');

			$interaction->respondWithMessage($message, true);

			return;
		}

		$guildConfig = $this->guildConfig->get($interaction->guild->id);

		if($guildConfig === null || !$guildConfig->isValid){
			$interaction->respondWithMessage((new MessageBuilder)->setContent('Error: invalid guild configuration.'), true);

			return;
		}

		// refresh the guild roles before proceeding
		$interaction->guild->roles->freshen()
			->then(function(RoleRepository $repository) use ($interaction, $guildConfig):void{
				// map existing roles
				$existingRoles  = array_map(fn(Role $r):string => $r->name, $repository->toArray());
				$messageBuilder = new MessageBuilder;
				// create a container with role select for each set of roles
				foreach($guildConfig->getRoleConfig() as $config){
					$messageBuilder->addComponent($this->createSelectContainer($config, $existingRoles));
				}

				$interaction->respondWithMessage($messageBuilder, true);
			})
			// idk whatever
			->catch(function() use ($interaction):void{
				$msg = 'something went horribly wrong';
				$interaction->respondWithMessage((new MessageBuilder)->setContent($msg), true);
				$this->logger->error($msg);
			})
		;

	}

	private function createSelectContainer(array $config, array $existingRoles):Container{
		return (new Container)
			->setAccentColor($config['color'])
			->addComponent((new TextDisplay)->setContent(sprintf("# %s\n%s", $config['header'], $config['message'])))
			->addComponent((new ActionRow)->addComponent($this->createSelect($config, $existingRoles)))
		;
	}

	private function createSelect(array $config, array $existingRoles):StringSelect{
		// populate the select
		$select = new StringSelect($config['config_id'])
			->setMinValues(0) // zero allows the user to remove all roles
			->setMaxValues(($config['max_values'] ?? count($config['options'])))
		;

		foreach($config['options'] as $roleOption){
			$id = (int)$roleOption['role_id'];

			if(!array_key_exists($id, $existingRoles)){
				continue;
			}

			$select->addOption(new Option('@'.$existingRoles[$id], $roleOption['role_id']));
		}

		$select->setListener(function(Interaction $interaction, Collection $options) use ($config):void{
			$interaction->respondWithMessage($this->selectResponse($interaction, $options, $config), true);
		}, $this->discord);

		return $select;
	}

	private function selectResponse(Interaction $interaction, Collection $options, array $config):MessageBuilder{
		$allowedRoles = array_column($config['options'], 'role_id');
		$currentRoles = array_map(fn(Role $r):string => $r->id, $interaction->member->roles->toArray());
		$added        = [];
		// check & add the selected roles
		foreach($options as $option){
			$role_id = $option->getValue();
			// idk how secure discord forms are, but make sure we only process roles that we configured
			if(!in_array($role_id, $allowedRoles, true)){
				continue;
			}

			$added[] = $role_id;
		}

		return $this->selectResponseMessage(
			$config,
			$this->addRoles($interaction, $added, $currentRoles),
			$this->removeRoles($interaction, array_diff($allowedRoles, $added), $currentRoles),
		);
	}

	private function selectResponseMessage(array $config, array $added, array $removed):MessageBuilder{
		// compose the response message
		$display = fn(string $r):string => sprintf('<@&%s>', $r);
		$message = sprintf('### %s changes', $config['header']);

		if($added === [] && $removed === []){
			$message .= "\n- no role changes";
		}

		if($added !== []){
			$message .= "\n- added: ".implode(', ', array_map($display, $added));
		}

		if($removed !== []){
			$message .= "\n- removed: ".implode(', ', array_map($display, $removed));
		}

		$container = (new Container)
			->setAccentColor($config['color'])
			->addComponent((new TextDisplay)->setContent($message))
		;

		return (new MessageBuilder)->addComponent($container);
	}

	private function addRoles(Interaction $interaction, array $rolesToAdd, array $currentRoles):array{
		$added = [];

		foreach($rolesToAdd as $role){
			// skip existing role
			if(in_array($role, $currentRoles, true)){
				continue;
			}
			/** @phan-suppress-next-line PhanTypeMismatchArgument */
			$endpoint = Endpoint::bind(Endpoint::GUILD_MEMBER_ROLE, $interaction->guild->id, $interaction->user->id, $role);

			$this->http->put($endpoint);
			$added[] = $role;
		}

		$this->logger->debug('added roles', $added);

		return $added;
	}

	private function removeRoles(Interaction $interaction, array $rolesToRemove, array $currentRoles):array{
		$removed = [];

		foreach($rolesToRemove as $role){
			// skip non-existing role
			if(!in_array($role, $currentRoles, true)){
				continue;
			}
			/** @phan-suppress-next-line PhanTypeMismatchArgument */
			$endpoint = Endpoint::bind(Endpoint::GUILD_MEMBER_ROLE, $interaction->guild->id, $interaction->user->id, $role);

			$this->http->delete($endpoint);
			$removed[] = $role;
		}

		$this->logger->debug('removed roles', $removed);

		return $removed;
	}

}
