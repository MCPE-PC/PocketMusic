<?php

namespace mcpepc\pocketmusic;

use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\{
	PlaySoundPacket,
	StopSoundPacket
};

class Sound {
	static function play(Player $player, string $soundName, int $volume = 100, int $pitch = 1, ?Vector3 $position = null): bool {
		if ($position === null) {
			$position = $player->getPosition();
		}

		$pk = new PlaySoundPacket();
		$pk->soundName = $soundName;
		$pk->x = $position->getX();
		$pk->y = $position->getY();
		$pk->z = $position->getZ();
		$pk->volume = $volume;
		$pk->pitch = $pitch;

		return $player->dataPacket($pk);
	}

	static function stop(Player $player, string $soundName = ''): bool {
		$pk = new StopSoundPacket();
		$pk->soundName = $soundName;
		$pk->stopAll = $soundName === '';

		return $player->dataPacket($pk);
	}
}
