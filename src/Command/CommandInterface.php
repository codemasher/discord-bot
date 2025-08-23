<?php
/**
 * Interface CommandInterface
 *
 * @created      11.08.2025
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2025 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace codemasher\DiscordBot\Command;

/**
 * @link https://discord.com/blog/welcome-to-the-new-era-of-discord-apps?ref=badge
 */
interface CommandInterface{

	public const string NAME        = '';
	public const string DESCRIPTION = '';

	public function register():static;
	public function delete(string $id, string|null $reason = null):static;
	public function listen():static;

#	public function registerInGuild(Guild $guild):static;
#	public function deleteInGuild(Guild $guild, string $id, string|null $reason = null):static;

}
