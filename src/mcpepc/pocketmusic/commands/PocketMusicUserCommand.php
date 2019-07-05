<?php

namespace mcpepc\pocketmusic\commands;

use mcpepc\pocketmusic\Sound;
use pocketmine\plugin\Plugin;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;
use function count;
use function strtolower;

class PocketMusicUserCommand extends PocketMusicCommand {
	function __construct(Plugin $plugin) {
		parent::__construct('pocketmusic', $plugin, 'PocketMusic user control command', '/pocketmusic help', 'pocketmusic.command.pocketmusic.user', ['music', '음악']);
	}

	function execute(CommandSender $sender, string $commandLabel, array $args): bool {
		if (!($this->getPlugin()->isEnabled() && $this->testPermission($sender))) {
			return false;
		}

		$exception = false;
		if (count($args) < 2) {
			$sender->sendMessage(TextFormat::RED . "Usage: $this->usageMessage");
			return false;
		}

		if ($sender instanceof ConsoleCommandSender) {
			$sender->sendMessage('Use it only in-game');
			return false;
		}

		switch (strtolower($args[0])) {
			case 'play':
			case '재생':
				if (!isset($args[1])) {
					$sender->sendMessage('No sound name');
					return false;
				}
				Sound::stop($sender);
				$this->getPlugin()->playSound($sender, $args[1]);
				$sender->sendMessage("Playing $args[1]");
				break;

			case 'stop':
			case '정지':
				Sound::stop($sender);
				$sender->sendMessage('Stopped sounds');
		}

		if ($exception) {
			$sender->sendMessage(TextFormat::RED . "Usage: $this->usageMessage");
			return false;
		} else {
			return true;
		}
	}
}