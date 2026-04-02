<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\metadata;

use pocketmine\plugin\Plugin;

interface Metadatable{
	public function setMetadata(string $metadataKey, MetadataValue $newMetadataValue);
	public function getMetadata(string $metadataKey);
	public function hasMetadata(string $metadataKey) : bool;
	public function removeMetadata(string $metadataKey, Plugin $owningPlugin);

}