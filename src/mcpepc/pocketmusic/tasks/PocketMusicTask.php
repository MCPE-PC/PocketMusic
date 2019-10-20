<?php

namespace mcpepc\pocketmusic\tasks;

use pocketmine\scheduler\Task;
use pocketmine\plugin\Plugin;

abstract class PocketMusicTask extends Task {
	protected $owningPlugin;

	final function getPlugin(): Plugin {
		return $this->owningPlugin;
	}
}
