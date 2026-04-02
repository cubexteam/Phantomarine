<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level\format\io;

use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\FullChunkDataPacket;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use function assert;
use function strlen;

class ChunkRequestTask extends AsyncTask{
	protected $levelId;
	protected $chunk;
	protected $chunkX;
	protected $chunkZ;
	private $tiles;
	protected $compressionLevel;

	public function __construct(Level $level, int $chunkX, int $chunkZ, Chunk $chunk){
		$this->levelId = $level->getId();
		$this->compressionLevel = $level->getServer()->networkCompressionLevel;

		$this->tiles = $chunk->networkSerializeTiles();

		$this->chunk = $chunk->networkSerialize($this->tiles);
		$this->chunkX = $chunkX;
		$this->chunkZ = $chunkZ;
	}

	public function onRun(){
		$pk = new FullChunkDataPacket();
		$pk->chunkX = $this->chunkX;
		$pk->chunkZ = $this->chunkZ;
		$pk->data = $this->chunk;

		$batch = new BatchPacket();
		$batch->addPacket($pk);
		$batch->setCompressionLevel($this->compressionLevel);
		$batch->encode();

		$this->setResult($batch->buffer);
	}

	public function onCompletion(Server $server){
		$level = $server->getLevel($this->levelId);
		if($level instanceof Level){
			if($this->hasResult()){
				$batch = new BatchPacket($this->getResult());
				assert(strlen($batch->buffer) > 0);
				$batch->isEncoded = true;
				$level->chunkRequestCallback($this->chunkX, $this->chunkZ, $batch);
			}else{
				$server->getLogger()->error("Chunk request for level #" . $this->levelId . ", x=" . $this->chunkX . ", z=" . $this->chunkZ . " doesn't have any result data");
			}
		}else{
			$server->getLogger()->debug("Dropped chunk task due to level not loaded");
		}
	}

}