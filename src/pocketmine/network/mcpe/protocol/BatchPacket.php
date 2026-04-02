<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

use pocketmine\utils\Binary;
use pocketmine\utils\BinaryStream;
use function get_class;
use function strlen;
use function zlib_encode;
use const ZLIB_ENCODING_DEFLATE;

#ifndef COMPILE
#endif

class BatchPacket extends DataPacket{
	const NETWORK_ID = 0xfe;
	public $payload = "";
	protected $compressionLevel = 7;

	public function canBeBatched() : bool{
		return false;
	}

	public function canBeSentBeforeLogin() : bool{
		return true;
	}

	public function decode(){
		$this->payload = $this->getRemaining();
	}

	public function encode(){
		$this->reset();
		$encoded = zlib_encode($this->payload, ZLIB_ENCODING_DEFLATE, $this->compressionLevel);
		if($encoded === false) throw new \Error("ZLIB compression failed");
		$this->put($encoded);
	}
	public function addPacket(DataPacket $packet){
		if(!$packet->canBeBatched()){
			throw new \InvalidArgumentException(get_class($packet) . " cannot be put inside a BatchPacket");
		}
		if(!$packet->isEncoded){
			$packet->encode();
		}

		$this->payload .= Binary::writeUnsignedVarInt(strlen($packet->buffer)) . $packet->buffer;
	}
	public function getPackets(){
		$stream = new BinaryStream($this->payload);
		$count = 0;
		while(!$stream->feof()){
			if($count++ >= 500){
				throw new \UnexpectedValueException("Too many packets in a single batch");
			}
			yield $stream->getString();
		}
	}

	public function getCompressionLevel() : int{
		return $this->compressionLevel;
	}
	public function setCompressionLevel(int $level){
		$this->compressionLevel = $level;
	}
	public function getName(){
		return "BatchPacket";
	}

}