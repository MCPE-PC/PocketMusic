<?php

namespace mcpepc\pocketmusic\tasks;

use mcpepc\pocketmusic\PocketMusic;
use mcpepc\pocketmusic\Sound;
use pocketmine\plugin\Plugin;
use function strlen;
use function strpos;
use function substr;

class PlaySoundTask extends PocketMusicTask {
	private $args = [];

	function __construct(Plugin $plugin, ...$args) {
		$this->owningPlugin = $plugin;

		$soundName = &$args[1] ?? PocketMusic::RANDOM;

		if (strpos($soundName, PocketMusic::TOP_LEVEL) !== 0) {
			$soundName = PocketMusic::MUSIC . $soundName;
		}

		$this->args = $args;
	}

	function onRun(int $currentTick) {
		if (!$this->args[0]->isOnline() || (strpos(PocketMusic::MUSIC, $this->args[1]) === 0 &&
			$this->getPlugin()->getSoundInfo(substr($this->args[1], strlen(PocketMusic::MUSIC))) !== null)) {
			return $this->getHandler()->cancel();
		}

		Sound::play(...$this->args);
	}
}
