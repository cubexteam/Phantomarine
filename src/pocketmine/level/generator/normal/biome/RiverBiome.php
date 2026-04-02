<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level\generator\normal\biome;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\level\generator\populator\Sugarcane;
use pocketmine\level\generator\populator\TallGrass;

class RiverBiome extends NormalBiome{
	public function __construct(){
		$this->setGroundCover([
			BlockFactory::get(Block::DIRT),
			BlockFactory::get(Block::DIRT),
			BlockFactory::get(Block::DIRT),
			BlockFactory::get(Block::DIRT),
			BlockFactory::get(Block::DIRT)
		]);

		$sugarcane = new Sugarcane();
		$sugarcane->setBaseAmount(6);
		$tallGrass = new TallGrass();
		$tallGrass->setBaseAmount(5);

		$this->addPopulator($sugarcane);
		$this->addPopulator($tallGrass);

		$this->setElevation(58, 62);

		$this->temperature = 0.5;
		$this->rainfall = 0.7;
	}
	public function getName() : string{
		return "River";
	}
}
