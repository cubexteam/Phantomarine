<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level\generator\normal\biome;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;

abstract class GrassyBiome extends NormalBiome{
	public function __construct(){
		$this->setGroundCover([
			BlockFactory::get(Block::GRASS, 0),
			BlockFactory::get(Block::DIRT, 0),
			BlockFactory::get(Block::DIRT, 0),
			BlockFactory::get(Block::DIRT, 0),
			BlockFactory::get(Block::DIRT, 0),
		]);
	}
}