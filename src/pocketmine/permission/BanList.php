<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\permission;

use pocketmine\utils\MainLogger;
use function fclose;
use function fgets;
use function fopen;
use function fwrite;
use function is_resource;
use function strtolower;

class BanList{
	private $list = [];
	private $file;
	private $enabled = true;
	public function __construct($file){
		$this->file = $file;
	}
	public function isEnabled(){
		return $this->enabled === true;
	}
	public function setEnabled($flag){
		$this->enabled = (bool) $flag;
	}
	public function getEntry(string $name) : ?BanEntry{
		$this->removeExpired();

		return $this->list[strtolower($name)] ?? null;
	}
	public function getEntries(){
		$this->removeExpired();

		return $this->list;
	}
	public function isBanned($name){
		$name = strtolower($name);
		if(!$this->isEnabled()){
			return false;
		}else{
			$this->removeExpired();

			return isset($this->list[$name]);
		}
	}
	public function add(BanEntry $entry){
		$this->list[$entry->getName()] = $entry;
		$this->save();
	}
	public function addBan($target, $reason = null, $expires = null, $source = null){
		$entry = new BanEntry($target);
		$entry->setSource($source != null ? $source : $entry->getSource());
		$entry->setExpires($expires);
		$entry->setReason($reason != null ? $reason : $entry->getReason());

		$this->list[$entry->getName()] = $entry;
		$this->save();

		return $entry;
	}
	public function remove($name){
		$name = strtolower($name);
		if(isset($this->list[$name])){
			unset($this->list[$name]);
			$this->save();
		}
	}

	public function removeExpired(){
		foreach($this->list as $name => $entry){
			if($entry->hasExpired()){
				unset($this->list[$name]);
			}
		}
	}

	public function load(){
		$this->list = [];
		$fp = @fopen($this->file, "r");
		if(is_resource($fp)){
			while(($line = fgets($fp)) !== false){
				if($line[0] !== "#"){
					try{
						$entry = BanEntry::fromString($line);
						if($entry instanceof BanEntry){
							$this->list[$entry->getName()] = $entry;
						}
					}catch(\Throwable $e){
						$logger = MainLogger::getLogger();
						$logger->critical("Failed to parse ban entry from string \"$line\": " . $e->getMessage());
						$logger->logException($e);
					}
				}
			}
			fclose($fp);
		}else{
			MainLogger::getLogger()->error("Could not load ban list");
		}
	}
	public function save($flag = true){
		$this->removeExpired();
		$fp = @fopen($this->file, "w");
		if(is_resource($fp)){
			if($flag === true){
				fwrite($fp, "# victim name | ban date | banned by | banned until | reason\n\n");
			}

			foreach($this->list as $entry){
				fwrite($fp, $entry->getString() . "\n");
			}
			fclose($fp);
		}else{
			MainLogger::getLogger()->error("Could not save ban list");
		}
	}

}