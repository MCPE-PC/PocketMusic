<?php

namespace mcpepc\pocketmusic;

use ArrayObject;
use mcpepc\pocketmusic\commands\{
	PlaySoundCommand,
	PocketMusicAdminCommand,
	PocketMusicUserCommand,
	StopSoundCommand
};
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\UUID;
use function file_exists;
use function is_dir;
use function mkdir;
use const DIRECTORY_SEPARATOR;

class PocketMusic extends PluginBase implements Listener {
	const SOUNDS_DIRECTORY_NAME = 'sounds';
	const TOP_SPACE = 'pocketmusic';
	const TOP_LEVEL = self::TOP_SPACE . '.';
	const MUSIC = self::TOP_LEVEL . 'music.';
	const RANDOM = self::TOP_LEVEL . 'random';

	private $manifestConfig;
	private $soundsConfig;

	function onLoad(): void {
		$this->saveDefaultConfig();
		$this->saveResource('sounds.yml');
		if (!(file_exists($this->getSoundsPath()) && is_dir($this->getSoundsPath()))) {
			mkdir($this->getSoundsPath(), 0755, true);
		}

		$this->manifestConfig = new Config($this->getDataFolder() . 'manifest.yml', Config::YAML, $this->getManifestConfigDefaults());
		$this->manifestConfig->save();

		$this->soundsConfig = new Config($this->getDataFolder() . 'sounds.yml');

		$map = $this->getServer()->getCommandMap();
		$map->register('pocketmusic', new PocketMusicAdminCommand($this));
		$map->register('pocketmusic', new PocketMusicUserCommand($this));
		$map->register('pocketmusic', new PlaySoundCommand($this));
		$map->register('pocketmusic', new StopSoundCommand($this));
	}
	function onEnable(): void {
		if ($this->getName() !== 'PocketMusic' || $this->getDescription()->getAuthors() !== ['MCPE_PC']) {
			$this->setEnabled(false);
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	function onDisable() {
		$this->getManifestConfig()->save();
	}

	function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
		switch($command->getName()) {
			default:
				return false;
		}
	}

	function getManifestConfig(): Config {
		return $this->manifestConfig;
	}
	function getManifestConfigDefaults(): array {
		return [
			'name' => $this->getServer()->getMotd(),
			'addonUUID' => UUID::fromRandom()->toString(),
			'resourcesUUID' => UUID::fromRandom()->toString(),
			'version' => 1,
			'extras' => [
				'format_version' => 1
			]
		];
	}
	function getSoundsConfig(): Config {
		return $this->soundsConfig;
	}
	function getSoundsPath(): string {
		return $this->getDataFolder() . self::SOUNDS_DIRECTORY_NAME . DIRECTORY_SEPARATOR;
	}

	function playSound(Player $player, string $soundName, int $volume = 100, int $pitch = 1, bool $raw = false, ?Vector3 $position = null): bool {
		if (!$raw && strpos($soundName, self::TOP_LEVEL) !== 0) {
			$soundName = self::MUSIC . $soundName;
		}

		return Sound::play($player, $soundName, $volume, $pitch, $position);
	}
}
