<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\metadata;

use pocketmine\level\Level;
use pocketmine\plugin\Plugin;
use function strtolower;

class LevelMetadataStore extends MetadataStore{

	private function disambiguate(Level $level, string $metadataKey) : string{
		return strtolower($level->getName()) . ":" . $metadataKey;
	}

	public function getMetadata(Level $subject, string $metadataKey){
		return $this->getMetadataInternal($this->disambiguate($subject, $metadataKey));
	}

	public function hasMetadata(Level $subject, string $metadataKey) : bool{
		return $this->hasMetadataInternal($this->disambiguate($subject, $metadataKey));
	}

	public function removeMetadata(Level $subject, string $metadataKey, Plugin $owningPlugin){
		$this->removeMetadataInternal($this->disambiguate($subject, $metadataKey), $owningPlugin);
	}

	public function setMetadata(Level $subject, string $metadataKey, MetadataValue $newMetadataValue){
		$this->setMetadataInternal($this->disambiguate($subject, $metadataKey), $newMetadataValue);
	}
}