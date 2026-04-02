<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\permission;

class BanEntry{
	public static $format = "Y-m-d H:i:s O";

	private $name;
	private $creationDate;
	private $source = "(Unknown)";
	private $expirationDate = null;
	private $reason = "Banned by an operator.";
	public function __construct($name){
		$this->name = strtolower($name);
		$this->creationDate = new \DateTime();
	}
	public function getName() : string{
		return $this->name;
	}
	public function getCreated(){
		return $this->creationDate;
	}
	public function setCreated(\DateTime $date){
		self::validateDate($date);
		$this->creationDate = $date;
	}
	public function getSource(){
		return $this->source;
	}
	public function setSource($source){
		$this->source = $source;
	}
	public function getExpires(){
		return $this->expirationDate;
	}
	public function setExpires(\DateTime $date = null){
		if($date !== null){
			self::validateDate($date);
		}
		$this->expirationDate = $date;
	}
	public function hasExpired(){
		$now = new \DateTime();

		return $this->expirationDate === null ? false : $this->expirationDate < $now;
	}
	public function getReason(){
		return $this->reason;
	}
	public function setReason($reason){
		$this->reason = $reason;
	}
	public function getString(){
		$str = "";
		$str .= $this->getName();
		$str .= "|";
		$str .= $this->getCreated()->format(self::$format);
		$str .= "|";
		$str .= $this->getSource();
		$str .= "|";
		$str .= $this->getExpires() === null ? "Forever" : $this->getExpires()->format(self::$format);
		$str .= "|";
		$str .= $this->getReason();

		return $str;
	}
	private static function validateDate(\DateTime $dateTime) : void{
		self::parseDate($dateTime->format(self::$format));
	}
	private static function parseDate(string $date) : \DateTime{
		$datetime = \DateTime::createFromFormat(self::$format, $date);
		if(!($datetime instanceof \DateTime)){
			throw new \RuntimeException("Error parsing date for BanEntry: " . implode(", ", \DateTime::getLastErrors()["errors"]));
		}

		return $datetime;
	}
	public static function fromString(string $str) : ?BanEntry{
		if(strlen($str) < 2){
			return null;
		}else{
			$str = explode("|", trim($str));
			$entry = new BanEntry(trim(array_shift($str)));

			if(count($str) === 0){
				return $entry;
			}

			$entry->setCreated(self::parseDate(array_shift($str)));
			if(count($str) === 0){
				return $entry;
			}

			$entry->setSource(trim(array_shift($str)));
			if(count($str) === 0){
				return $entry;
			}

			$expire = trim(array_shift($str));
			if($expire !== "" and strtolower($expire) !== "forever"){
				$entry->setExpires(self::parseDate($expire));
			}
			if(count($str) === 0){
				return $entry;
			}
			$entry->setReason(trim(array_shift($str)));
			return $entry;
		}
	}
}