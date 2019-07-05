<?php

namespace mcpepc\pocketmusic\commands;

use mcpepc\pocketmusic\PocketMusic;
use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;

abstract class PocketMusicCommand extends Command implements PluginIdentifiableCommand {
	private $owningPlugin;

	function __construct(string $name, PocketMusic $plugin, ?string $description = null, ?string $usageMessage = null, string $permission = 'pocketmusic.command', array $aliases = []) {
		$this->owningPlugin = $plugin;
		parent::__construct($name, $description ?? "{$plugin->getName()} command", $usageMessage ?? "/$name", $aliases);
		$this->setPermission($permission);
	}

	final function getPlugin(): Plugin {
		return $this->owningPlugin;
	}
}
