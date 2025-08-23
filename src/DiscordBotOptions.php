<?php
/**
 * Class DiscordBotOptions
 *
 * @created      11.08.2025
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2025 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace codemasher\DiscordBot;

use chillerlan\Settings\SettingsContainerAbstract;
use Discord\WebSockets\Intents;
use Psr\Log\LogLevel;

class DiscordBotOptions extends SettingsContainerAbstract{

	protected string      $configDir              = __DIR__.'/../.config';
	protected string      $botToken               = '';
	protected string|null $logLevel               = LogLevel::INFO;
	// Note: MESSAGE_CONTENT, GUILD_MEMBERS and GUILD_PRESENCES are privileged, see https://dis.gd/mcfaq
	protected int         $intents                = Intents::MESSAGE_CONTENT;
	protected bool        $registerGlobalCommands = false;
	protected bool        $deleteCommandOnUpdate  = false;
	protected array       $excludeGlobalCommands  = ['ping'];

}
