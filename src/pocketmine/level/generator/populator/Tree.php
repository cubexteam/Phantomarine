<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level\generator\populator;

use pocketmine\block\Block;
use pocketmine\block\Sapling;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\object\Tree as ObjectTree;
use pocketmine\utils\Random;

class Tree extends Populator{
	private $randomAmount = 1;
	private $baseAmount = 0;

	private $type;
	public function __construct($type = Sapling::OAK){
		$this->type = $type;
	}
	public function setRandomAmount($amount){
		$this->randomAmount = $amount;
	}
	public function setBaseAmount($amount){
		$this->baseAmount = $amount;
	}
	public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random){
		$amount = $random->nextRange(0, $this->randomAmount) + $this->baseAmount;
		for($i = 0; $i < $amount; ++$i){
			$x = $random->nextRange($chunkX << 4, ($chunkX << 4) + 15);
			$z = $random->nextRange($chunkZ << 4, ($chunkZ << 4) + 15);
			$y = $this->getHighestWorkableBlock($level, $x, $z);
			if($y === -1){
				continue;
			}
			ObjectTree::growTree($level, $x, $y, $z, $random, $this->type);
		}
	}

	private function getHighestWorkableBlock(ChunkManager $level, int $x, int $z) : int{
		for($y = 127; $y >= 0; --$y){
			$b = $level->getBlockIdAt($x, $y, $z);
			if($b === Block::DIRT or $b === Block::GRASS){
				return $y + 1;
			}elseif($b !== 0 and $b !== Block::SNOW_LAYER){
				return -1;
			}
		}

		return -1;
	}
}