# codemasher/discord-bot

[![License][license-badge]][license]
[![Continuous Integration][gh-action-badge]][gh-action]

[license-badge]: https://img.shields.io/github/license/codemasher/discord-bot
[license]: https://github.com/codemasher/discord-bot/blob/main/LICENSE
[gh-action-badge]: https://img.shields.io/github/actions/workflow/status/codemasher/discord-bot/ci.yml?branch=main&logo=github&logoColor=fff
[gh-action]: https://github.com/codemasher/discord-bot/actions/workflows/ci.yml?query=branch%3Amain

## Overview

### Features

Yes.

### Requirements

- PHP 8.4+
  - ext-curl
  - ext-event ([via pecl](https://pecl.php.net/package/event))
  - ext-mbstring
  - ext-sodium
  - ext-zlib
  - from dependencies:
    - ext-fileinfo
    - ext-intl
    - ext-json
    - ext-simplexml

## Installation

Clone this repo:
```shell
git clone https://github.com/codemasher/discord-bot.git /path/to/bot
cd /path/to/bot
composer install --no-dev
cp .config/.env_example .config/.env
cp cli/bot.php cli/mybot.php
```

Create a new discord application (or use an existing one) here https://discord.com/developers/applications - go to "bot" settings and create an access token.
Leave the setting "public bot" enabled for now and adjust the desired "privileged gateway intents".
Go to the "installation" settings and scroll down to the scopes, select bot and apply the desired scopes.
Copy the invite link so that you can invite the bot to your servers.

Edit the `.config/.env` and add your bot token:
```
DISCORD_BOT_TOKEN=<token from discord app settings>
```

Edit the `cli/mybot.php` and set the `$registerGlobalCommands` option to `true`.
Create a service e.g. `/etc/systemd/system/discordbot.service` for the bot (might require root/sudo permissions):

```ini
[Unit]
Description=Discord Bot
After=multi-user.target
After=network-online.target
Wants=network-online.target

[Service]
ExecStart=/usr/bin/php -f /path/to/bot/cli/mybot.php
User=discordbot
Group=discordbot
Type=idle
Restart=always
RestartSec=15
RestartPreventExitStatus=0
TimeoutStopSec=10

[Install]
WantedBy=multi-user.target
```

Enable the bot service:

```shell
systemctl daemon-reload
systemctl enable discordbot
systemctl start discordbot
systemctl status discordbot
```

Use the invite link while the bot is running:
```
https://discord.com/oauth2/authorize?client_id=<application_id>
```

The bot should now show up in your server's member list and in the server settings under apps/integrations.
Now set the `$registerGlobalCommands` option in `cli/mybot.php` to `false` again (or outcomment the line) and restart the bot `systemctl restart discordbot`.

To update the bot you can simply run the following in the bot's root directory `/path/to/bot/`:
```shell
git pull
composer install --no-dev
systemctl restart discordbot
```

Profit!


### Configure roles

The bot has a `/roles` command that allows members to self-assign roles. Create a new config under `.config/<server_id>/config.json`, which will be loaded at startup:

```json
{
	"roles": [
		{
			"config_id": "some-roles",
			"header": "Roles",
			"message": "here are some roles to choose from",
			"color": "#B00B69",
			"max_values": null,
			"options": [
				{
					"role_id": "1234567890123456789",
					"emoji": "(currently unused)"
				},
				{
					"role_id": "2345678901234567891",
					"emoji": ""
				}
			]
		},
		{
			"config_id": "more-roles",
			"header": "More roles",
			"message": "here are some more roles to choose from",
			"color": "#420690",
			"max_values": 1,
			"options": [
				{
					"role_id": "1234567890123456789",
					"emoji": ""
				},
				{
					"role_id": "2345678901234567891",
					"emoji": ""
				},
				{
					"role_id": "2345678901234567891",
					"emoji": ""
				}
			]
		}
	]
}
```

Each section in `roles` will create a container element with a select and the given message and the given set of `options` to pick from.
All messages are sent as ephmeral messages (bot messages only visible to the user), so that you don't need a separate channel for role self-assignment.

## Disclaimer

Use at your own risk!
