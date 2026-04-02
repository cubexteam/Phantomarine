<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine;


use pocketmine\metadata\Metadatable;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\Plugin;

class OfflinePlayer implements IPlayer, Metadatable{
	private $name;
	private $server;
	private $namedtag = null;
	public function __construct(Server $server, string $name){
		$this->server = $server;
		$this->name = $name;
		if($this->server->hasOfflinePlayerData($this->name)){
			$this->namedtag = $this->server->getOfflinePlayerData($this->name);
		}
	}
	public function isOnline(){
		return $this->getPlayer() !== null;
	}
	public function getName() : string{
		return $this->name;
	}
	public function getServer(){
		return $this->server;
	}
	public function isOp(){
		return $this->server->isOp(strtolower($this->getName()));
	}
	public function setOp($value){
		if($value === $this->isOp()){
			return;
		}

		if($value){
			$this->server->addOp(strtolower($this->getName()));
		}else{
			$this->server->removeOp(strtolower($this->getName()));
		}
	}
	public function isBanned(){
		return $this->server->getNameBans()->isBanned(strtolower($this->getName()));
	}
	public function setBanned($value){
		if($value){
			$this->server->getNameBans()->addBan($this->getName(), null, null, null);
		}else{
			$this->server->getNameBans()->remove($this->getName());
		}
	}
	public function isWhitelisted(){
		return $this->server->isWhitelisted(strtolower($this->getName()));
	}
	public function setWhitelisted($value){
		if($value){
			$this->server->addWhitelist(strtolower($this->getName()));
		}else{
			$this->server->removeWhitelist(strtolower($this->getName()));
		}
	}
	public function getPlayer(){
		return $this->server->getPlayerExact($this->getName());
	}
	public function getFirstPlayed(){
		return $this->namedtag instanceof CompoundTag ? $this->namedtag["firstPlayed"] : null;
	}
	public function getLastPlayed(){
		return $this->namedtag instanceof CompoundTag ? $this->namedtag["lastPlayed"] : null;
	}
	public function hasPlayedBefore(){
		return $this->namedtag instanceof CompoundTag;
	}

	public function setMetadata(string $metadataKey, MetadataValue $newMetadataValue){
		$this->server->getPlayerMetadata()->setMetadata($this, $metadataKey, $newMetadataValue);
	}

	public function getMetadata(string $metadataKey){
		return $this->server->getPlayerMetadata()->getMetadata($this, $metadataKey);
	}

	public function hasMetadata(string $metadataKey) : bool{
		return $this->server->getPlayerMetadata()->hasMetadata($this, $metadataKey);
	}

	public function removeMetadata(string $metadataKey, Plugin $owningPlugin){
		$this->server->getPlayerMetadata()->removeMetadata($this, $metadataKey, $owningPlugin);
	}
}