<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\metadata;

use pocketmine\plugin\Plugin;

abstract class MetadataStore{
	private $metadataMap;
	protected function setMetadataInternal(string $key, MetadataValue $newMetadataValue){
		$owningPlugin = $newMetadataValue->getOwningPlugin();

		if(!isset($this->metadataMap[$key])){
			$entry = new \SplObjectStorage();
			$this->metadataMap[$key] = $entry;
		}else{
			$entry = $this->metadataMap[$key];
		}
		$entry[$owningPlugin] = $newMetadataValue;
	}
	protected function getMetadataInternal(string $key){
		if(isset($this->metadataMap[$key])){
			return $this->metadataMap[$key];
		}else{
			return [];
		}
	}
	protected function hasMetadataInternal(string $key) : bool{
		return isset($this->metadataMap[$key]);
	}
	protected function removeMetadataInternal(string $key, Plugin $owningPlugin){
		if(isset($this->metadataMap[$key])){
			unset($this->metadataMap[$key][$owningPlugin]);
			if($this->metadataMap[$key]->count() === 0){
				unset($this->metadataMap[$key]);
			}
		}
	}
	public function invalidateAll(Plugin $owningPlugin){
		foreach($this->metadataMap as $values){
			if(isset($values[$owningPlugin])){
				$values[$owningPlugin]->invalidate();
			}
		}
	}
}