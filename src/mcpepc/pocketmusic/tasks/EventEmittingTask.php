<?php

namespace mcpepc\pocketmusic\tasks;

use pocketmine\event\Event;
use pocketmine\plugin\Plugin;

class EventEmittingTask extends PocketMusicTask {
	private $callback;
	private $event;

	function __construct(Plugin $plugin, Event $event, ?callable $callback) {
		$this->owningPlugin = $plugin;

		$this->callback = $callback;
		$this->event = $event;
	}

	function onRun(int $currentTick) {
		$this->event->call();
	}
}
