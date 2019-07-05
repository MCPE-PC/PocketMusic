<?php

namespace mcpepc\pocketmusic\tasks;

use pocketmine\plugin\Plugin;
use pocketmine\scheduler\Task;

class PlaySoundTask extends Task {
	private $args = [];
	private $owningPlugin;

	function __construct(Plugin $plugin, ...$args) {
		$this->owningPlugin = $plugin;
	}

	function onRun(int $currentTick) {
		$this->owningPlugin->playSound(...$this->args);
	}
}
