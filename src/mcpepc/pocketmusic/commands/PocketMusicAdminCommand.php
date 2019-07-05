<?php

namespace mcpepc\pocketmusic\commands;

use mcpepc\pocketmusic\tasks\ResourcePackCreationTask;
use pocketmine\plugin\Plugin;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use function count;
use function strtolower;

class PocketMusicAdminCommand extends PocketMusicCommand {
	function __construct(Plugin $plugin) {
		parent::__construct('pocketmusicadmin', $plugin, 'PocketMusic admin command', '/pocketmusicadmin help', 'pocketmusic.command.pocketmusic.admin', ['musicop', '음악관리자']);
	}

	function execute(CommandSender $sender, string $commandLabel, array $args) {
		if (!($this->getPlugin()->isEnabled() && $this->testPermission($sender))) {
			return false;
		}
		if (count($args) < 2) {
			$sender->sendMessage(TextFormat::RED . 'Usage: ' . $this->usageMessage);
			return false;
		}

		switch (strtolower($args[0])) {
			case 'resourcepack':
			case '리소스팩':
				switch (strtolower($args[1])) {
					case 'create':
					case '생성':
						$this->getPlugin()->getScheduler()->scheduleTask(new ResourcePackCreationTask($this->getPlugin()));
						$sender->sendMessage('Started creation, messages will be on the console!');
				}
				break;
		}
	}
}
