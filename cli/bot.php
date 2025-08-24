<?php
/**
 * bot.php
 *
 * @created      11.08.2025
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2025 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\DotEnv\DotEnv;
use codemasher\DiscordBot\DiscordBot;
use codemasher\DiscordBot\DiscordBotOptions;
use Psr\Log\LogLevel;

ini_set('memory_limit', -1);

require_once __DIR__.'/../vendor/autoload.php';

$dotEnv  = new DotEnv(__DIR__.'/../.config', '.env', false)->load();
$options = new DiscordBotOptions;

$options->botToken               = $dotEnv->get('DISCORD_BOT_TOKEN');
$options->logLevel               = LogLevel::DEBUG;
#$options->registerGlobalCommands = true;
#$options->deleteCommandOnUpdate  = true;

$dc = new DiscordBot($options);
$dc->run();
