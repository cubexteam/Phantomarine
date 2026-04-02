<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */


namespace pocketmine\event\level;

use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
class ChunkLoadEvent extends ChunkEvent{
	public static $handlerList = null;

	private $newChunk;
	public function __construct(Level $level, Chunk $chunk, bool $newChunk){
		parent::__construct($level, $chunk);
		$this->newChunk = $newChunk;
	}
	public function isNewChunk(){
		return $this->newChunk;
	}
}