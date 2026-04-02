<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\utils;

class UUID{

	private $parts;
	private $version;
	public function __construct($part1 = 0, $part2 = 0, $part3 = 0, $part4 = 0, $version = null){
		$this->parts[0] = (int) $part1;
		$this->parts[1] = (int) $part2;
		$this->parts[2] = (int) $part3;
		$this->parts[3] = (int) $part4;

		$this->version = $version === null ? ($this->parts[1] & 0xf000) >> 12 : (int) $version;
	}
	public function getVersion(){
		return $this->version;
	}
	public function equals(UUID $uuid){
		return $uuid->parts === $this->parts;
	}
	public static function fromString($uuid, $version = null){
		$binary = @hex2bin(str_replace("-", "", trim($uuid)));
		if($binary === false){
			throw new \InvalidArgumentException("Invalid hex string UUID representation");
		}
		return self::fromBinary($binary, $version);
	}
	public static function fromBinary($uuid, $version = null){
		if(strlen($uuid) !== 16){
			throw new \InvalidArgumentException("Must have exactly 16 bytes");
		}

		return new UUID(Binary::readInt(substr($uuid, 0, 4)), Binary::readInt(substr($uuid, 4, 4)), Binary::readInt(substr($uuid, 8, 4)), Binary::readInt(substr($uuid, 12, 4)), $version);
	}
	public static function fromData(...$data){
		$hash = hash("md5", implode($data), true);

		return self::fromBinary($hash, 3);
	}
	public static function fromRandom(){
		return self::fromData(Binary::writeInt(time()), Binary::writeShort(getmypid()), Binary::writeShort(getmyuid()), Binary::writeInt(mt_rand(-0x7fffffff, 0x7fffffff)), Binary::writeInt(mt_rand(-0x7fffffff, 0x7fffffff)));
	}
	public function toBinary(){
		return Binary::writeInt($this->parts[0]) . Binary::writeInt($this->parts[1]) . Binary::writeInt($this->parts[2]) . Binary::writeInt($this->parts[3]);
	}
	public function toString(){
		$hex = bin2hex($this->toBinary());

		return substr($hex, 0, 8) . "-" . substr($hex, 8, 4) . "-" . substr($hex, 12, 4) . "-" . substr($hex, 16, 4) . "-" . substr($hex, 20, 12);
	}
	public function __toString(){
		return $this->toString();
	}
	public function getPart(int $partNumber) : int{
		if($partNumber < 0 or $partNumber > 3){
			throw new \InvalidArgumentException("Invalid UUID part index $partNumber");
		}
		return $this->parts[$partNumber];
	}
	public function getParts() : array{
		return $this->parts;
	}
}