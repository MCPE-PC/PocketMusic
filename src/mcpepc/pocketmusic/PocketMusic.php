<?php

namespace mcpepc\pocketmusic;

use Closure;
use mcpepc\pocketmusic\commands\{
	PlaySoundCommand,
	PocketMusicAdminCommand,
	PocketMusicUserCommand,
	StopSoundCommand
};
use mcpepc\pocketmusic\tasks\PlaySoundTask;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\lang\BaseLang;
use pocketmine\level\Level;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\Config;
use pocketmine\utils\UUID;
use pocketmine\Player;
use function array_merge;
use function file_exists;
use function is_array;
use function is_dir;
use function is_int;
use function mkdir;
use function strtolower;

class PocketMusic extends PluginBase implements Listener {
	const SOUNDS_DIRECTORY_NAME = 'sounds';
	const TOP_SPACE = 'pocketmusic';
	const TOP_LEVEL = self::TOP_SPACE . '.';
	const MUSIC = self::TOP_LEVEL . 'music.';
	const RANDOM = self::TOP_LEVEL . 'random';

	/** @var BaseLang */
	private $language;

	/** @var PlaySoundTask[] */
	private $playSoundTaskHandlers = [];

	/** @var Config */
	private $soundsConfig;

	/** @var Config */
	private $resourcePackConfig;

	function onLoad(): void {
		$this->saveDefaultConfig();
		$this->saveResource('sounds.yml');

		if (!(file_exists($this->getSoundsPath()) && is_dir($this->getSoundsPath()))) {
			mkdir($this->getSoundsPath(), 0755, true);
		}

		$this->soundsConfig = new Config($this->getDataFolder() . 'sounds.yml');

		$this->resourcePackConfig = new Config($this->getDataFolder() . 'resource_pack.yml', Config::YAML, $this->getResourcePackConfigDefaults());

		$this->registerCommandsIf(function(Command $command) {
			return true;
		}, new PocketMusicAdminCommand($this), new PocketMusicUserCommand($this));
		$this->registerCommandsIf(function(Command $command) {
			return $this->getConfig()->getNested('features.' . $command->getName());
		}, new PlaySoundCommand($this), new StopSoundCommand($this));
	}

	function onEnable(): void {
		if ($this->getName() !== 'PocketMusic' || $this->getDescription()->getAuthors() !== ['MCPE_PC']) {
			$this->setEnabled(false);
		}

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	function onDisable() {
		$this->getResourcePackConfig()->save();
	}

	function onJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer();

		// $this->playSoundTaskHandlers[strtolower($player->getName())] = [];

		if (($soundName = $this->getAutoplaySound($player->getLevel())) !== null) {
			$this->playSound(true, true, $player, $soundName);
		}
	}

	function onEntityLevelChange(EntityLevelChangeEvent $event) {
		$player = $event->getEntity();

		if ($player instanceof Player && ($soundName = $this->getAutoplaySound($event->getTarget())) !== null) {
			$this->playSound(true, true, $player, $soundName);
		}
	}

	function registerCommandsIf(Closure $callback, Command ...$commands) {
		$map = $this->getServer()->getCommandMap();
		foreach ($commands as $command) {
			if ($callback($command)) {
				$map->register(self::TOP_SPACE, $command);
			}
		}
	}

	function getResourcePackConfig(): Config {
		return $this->resourcePackConfig;
	}

	function getResourcePackConfigDefaults(): array {
		return [
			'name' => $this->getServer()->getMotd(),
			'uuid' => [
				'addon' => UUID::fromRandom()->toString(),
				'resources' => UUID::fromRandom()->toString()
			],
			'version' => 0,
			'manifestExtras' => [
				'format_version' => 1
			],
			'compressionLevel' => 6,
			'soundsCache' => []
		];
	}

	function getSoundsConfig(): Config {
		return $this->soundsConfig;
	}

	function getSoundsPath(): string {
		return $this->getDataFolder() . self::SOUNDS_DIRECTORY_NAME . '/';
	}

	function getSoundInfo(string $soundName): ?array {
		$info = [
			'file' => $soundName . '.ogg',
			'duration' => 0,
			'settings' => $this->getConfig()->get('default-settings')
		];
		$soundConfig = $this->getSoundsConfig()->get($soundName);

		if (is_int($soundConfig) && $soundConfig > 0) {
			$info['duration'] = $soundConfig;
		} else if (is_array($soundConfig) && $soundConfig['duration'] > 0) {
			$soundConfig['settings'] = array_merge($info['settings'], $soundConfig['settings'] ?? []);
			$info = array_merge($info, $soundConfig);
		} else {
			return null;
		}

		return $info;
	}

	function getAutoplaySound(Level $world): ?string {
		$autoplayConfig = $this->getConfig()->get('autoplay');

		if ($autoplayConfig['autoplay'] && ($worldSoundName = $autoplayConfig['worlds'][$world->getFolderName()] ?? null) !== false) {
			if ($worldSoundName !== null) {
				return is_string($worldSoundName) ? $worldSoundName : $worldSoundName[array_rand($worldSoundName)];
			}

			return $autoplayConfig['default-sound'] !== false ? $autoplayConfig['default-sound'] ?? null : null;
		}

		return null;
	}

	function playSound(bool $repeatSound, bool $stopPlayingSounds, ...$args): void {
		$handler = &$this->playSoundTaskHandlers[strtolower($args[0]->getName())] ?? null;

		if ($stopPlayingSounds) {
			if ($handler instanceof TaskHandler) {
				$handler->cancel();
				$handler = null;
			}

			Sound::stop($args[0]);
		}

		if (strpos($args[1], self::TOP_LEVEL) === 0 && strpos($args[1], self::MUSIC) === false) {
			$args[1] = $this->getResourcePackConfig()->get('soundsCache');
		}

		if (is_array($args[1])) {
			$args[1] = $args[1][array_rand($args[1])];
		}

		if ($repeatSound) {
			$handler = $this->getScheduler()->scheduleRepeatingTask(new PlaySoundTask($this, ...$args), $this->getSoundInfo($args[1])['duration'] * 20);
		} else {
			$this->getScheduler()->scheduleTask(new PlaySoundTask($this, ...$args));
		}
	}
}
