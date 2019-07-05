<?php

namespace mcpepc\pocketmusic\commands;

use mcpepc\pocketmusic\Sound;
use pocketmine\plugin\Plugin;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;
use function count;

class PlaySoundCommand extends PocketMusicCommand {
	function __construct(Plugin $plugin) {
		parent::__construct('playsound', $plugin, 'Plays a sound.', '/playsound <sound> [player] [x] [y] [z] [volume] [pitch] [minimumVolume]', 'pocketmusic.command.playsound');
	}

	function execute(CommandSender $sender, string $commandLabel, array $args): bool {
		if (!($this->getPlugin()->isEnabled() && $this->testPermission($sender))) {
			return false;
		}
		if (count($args) < 2) {
			$sender->sendMessage(TextFormat::RED . 'Usage: ' . $this->usageMessage);
			return false;
		}

		$player = $sender instanceof ConsoleCommandSender ? null : $sender;
		if (isset($args[1])) {
			$player = $this->getPlugin()->getServer()->getPlayerExact($args[1]);
		}
		if ($player === null) {
			$sender->sendMessage('Player not found');
			return false;
		}

		Sound::play($player, $args[0], (int) ($args[5] ?? 100), (int) ($args[6] ?? 1));
		return true;
	}
}
