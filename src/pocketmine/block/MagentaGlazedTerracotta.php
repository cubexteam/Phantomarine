<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\Player;

class MagentaGlazedTerracotta extends Solid{

	protected $id = self::MAGENTA_GLAZED_TERRACOTTA;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getHardness(){
		return 1.4;
	}
	public function getName(){
		return "Magenta Glazed Terracotta";
	}
	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$faces = [
			0 => 4,
			1 => 2,
			2 => 5,
			3 => 3,
		];
		$this->meta = $faces[$player instanceof Player ? $player->getDirection() : 0];
		$this->getLevel()->setBlock($block, $this, true, true);
		return true;
	}
}