<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\resourcepacks;

class ResourcePackInfoEntry{
	protected $packId;
	protected $version;
	protected $packSize;

	public function __construct(string $packId, string $version, int $packSize = 0){
		$this->packId = $packId;
		$this->version = $version;
		$this->packSize = $packSize;
	}

	public function getPackId() : string{
		return $this->packId;
	}

	public function getVersion() : string{
		return $this->version;
	}

	public function getPackSize() : int{
		return $this->packSize;
	}
}