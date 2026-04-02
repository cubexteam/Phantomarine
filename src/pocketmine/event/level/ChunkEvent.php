<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */


namespace pocketmine\event\level;

use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
abstract class ChunkEvent extends LevelEvent{
	private $chunk;
	public function __construct(Level $level, Chunk $chunk){
		parent::__construct($level);
		$this->chunk = $chunk;
	}
	public function getChunk(){
		return $this->chunk;
	}
}