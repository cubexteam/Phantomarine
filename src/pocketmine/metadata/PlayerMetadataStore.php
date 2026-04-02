<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\metadata;

use pocketmine\IPlayer;
use pocketmine\plugin\Plugin;
use function strtolower;

class PlayerMetadataStore extends MetadataStore{

	private function disambiguate(IPlayer $player, string $metadataKey) : string{
		return strtolower($player->getName()) . ":" . $metadataKey;
	}

	public function getMetadata(IPlayer $subject, string $metadataKey){
		return $this->getMetadataInternal($this->disambiguate($subject, $metadataKey));
	}

	public function hasMetadata(IPlayer $subject, string $metadataKey) : bool{
		return $this->hasMetadataInternal($this->disambiguate($subject, $metadataKey));
	}

	public function removeMetadata(IPlayer $subject, string $metadataKey, Plugin $owningPlugin){
		$this->removeMetadataInternal($this->disambiguate($subject, $metadataKey), $owningPlugin);
	}

	public function setMetadata(IPlayer $subject, string $metadataKey, MetadataValue $newMetadataValue){
		$this->setMetadataInternal($this->disambiguate($subject, $metadataKey), $newMetadataValue);
	}
}
