<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\block\utils\Fallable;
use pocketmine\block\utils\FallableTrait;
use pocketmine\inventory\AnvilInventory;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;

class Anvil extends Transparent implements Fallable{
	use FallableTrait;

	const NORMAL = 0;
	const SLIGHTLY_DAMAGED = 4;
	const VERY_DAMAGED = 8;

	protected $id = self::ANVIL;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function isTransparent(){
		return true;
	}
	public function getHardness(){
		return 5;
	}
	public function getResistance(){
		return 6000;
	}
	public function getName() : string{
		$names = [
			self::NORMAL => "Anvil",
			self::SLIGHTLY_DAMAGED => "Slightly Damaged Anvil",
			self::VERY_DAMAGED => "Very Damaged Anvil",
			12 => "Anvil"
		];
		return $names[$this->meta & 0x0c];
	}
	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	protected function recalculateBoundingBox(){
		$inset = 0.125;

		if($this->meta & 0x01){
			return new AxisAlignedBB(
				$this->x,
				$this->y,
				$this->z + $inset,
				$this->x + 1,
				$this->y + 1,
				$this->z + 1 - $inset
			);
		}else{
			return new AxisAlignedBB(
				$this->x + $inset,
				$this->y,
				$this->z,
				$this->x + 1 - $inset,
				$this->y + 1,
				$this->z + 1
			);
		}
	}
	public function onActivate(Item $item, Player $player = null){
		if(!$this->getLevel()->getServer()->anvilEnabled){
			return true;
		}
		if($player instanceof Player){
			if($player->isCreative() and $player->getServer()->limitedCreative){
				return true;
			}

			$player->addWindow(new AnvilInventory($this));
			$player->craftingType = Player::CRAFTING_ANVIL;
		}

		return true;
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$direction = ($player !== null ? $player->getDirection() : 0) & 0x03;
		$this->meta = ($this->meta & 0x0c) | $direction;
		$this->getLevel()->setBlock($block, $this, true, true);
		$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL);
	}
	public function getDrops(Item $item) : array{
		if($item->isPickaxe() >= 1){
			return [
				[$this->id, $this->meta & 0x0c, 1],
			];
		}else{
			return [];
		}
	}
}
