<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\metadata\Metadatable;
use pocketmine\metadata\MetadataValue;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class Block extends Position implements BlockIds, Metadatable{
	protected $id;
	protected $meta = 0;
	public $boundingBox = null;
	protected $collisionBoxes = null;
	public static function get(int $id, int $meta = 0, Position $pos = null) : Block{
		return BlockFactory::get($id, $meta, $pos);
	}
	public function __construct($id, $meta = 0){
		$this->id = (int) $id;
		$this->meta = (int) $meta;
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		return $this->getLevel()->setBlock($this, $this, true, true);
	}
	public function isBreakable(Item $item){
		return true;
	}
	public function onBreak(Item $item, Player $player = null) : bool{
		return $this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), true, true);
	}
	public function onRandomTick() : void{

	}
	public function onScheduledUpdate() : void{

	}
	public function onActivate(Item $item, Player $player = null){
		return false;
	}
	public function getHardness(){
		return 10;
	}
	public function getResistance(){
		return $this->getHardness() * 5;
	}
	public function getBlastResistance(){
		return 0.0;
	}

	public function isTopFacingSurfaceSolid(){
		if($this->isSolid()){
			return true;
		}else{
			if($this instanceof Stair and ($this->getDamage() & 4) == 4){
				return true;
			}elseif($this instanceof Slab and ($this->getDamage() & 8) == 8){
				return true;
			}elseif($this instanceof SnowLayer and ($this->getDamage() & 7) == 7){
				return true;
			}
		}
		return false;
	}

	public function isLightedByAround(){

	}

	public function lightAround(){

	}

	public function turnOn(){

	}

	public function turnOff(){

	}
	public function getToolType(){
		return Tool::TYPE_NONE;
	}
	public function getFrictionFactor(){
		return 0.6;
	}
	public function getLightLevel(){
		return 0;
	}
	public function getLightFilter() : int{
		return 15;
	}
	public function diffusesSkyLight() : bool{
		return false;
	}
	public function ticksRandomly() : bool{
		return false;
	}
	public function canBePlaced(){
		return true;
	}

	public function isPlaceable(){
		return $this->canBePlaced();
	}
	public function canBeReplaced(){
		return false;
	}

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool{
		return $blockReplace->canBeReplaced();
	}
	public function isTransparent(){
		return false;
	}

	public function isSolid(){
		return true;
	}
	public function canBeFlowedInto(){
		return false;
	}

	public function activate(){
		return false;
	}

	public function deactivate(){
		return false;
	}

	public function isActivated(Block $from = null){
		return false;
	}

	public function hasEntityCollision(){
		return false;
	}

	public function canClimb() : bool{
		return false;
	}
	public function getName(){
		return "Unknown";
	}
	final public function getId(){
		return $this->id;
	}
	public function isSameState(Block $other) : bool{
		return $this->getId() === $other->getId() and $this->getDamage() === $other->getDamage();
	}

	public function addVelocityToEntity(Entity $entity) : ?Vector3{
		return null;
	}
	final public function getDamage(){
		return $this->meta;
	}
	final public function setDamage($meta){
		$this->meta = $meta & 0x0f;
	}
	public function getVariantBitmask() : int{
		return -1;
	}
	public function getVariant() : int{
		return $this->meta & $this->getVariantBitmask();
	}
    public function isSameType(Block $other) : bool{
        return $this->getId() === $other->getId() and $this->getVariant() === $other->getVariant();
    }
	final public function position(Position $v){
		$this->x = (int) $v->x;
		$this->y = (int) $v->y;
		$this->z = (int) $v->z;
		$this->level = $v->level;
		$this->updateState();
	}
	public function getDrops(Item $item) : array{
		if(!isset(BlockFactory::$list[$this->getId()])){
			return [];
		}else{
			return [
				[$this->getId(), $this->getVariant(), 1]
			];
		}
	}
	public function hasSameTypeId(Block $other) : bool{
		return $this->getId() === $other->getId();
	}
	public function getBreakTime(Item $item){
		$base = $this->getHardness() * 1.5;
		if($this->canBeBrokenWith($item)){
			if($this->getToolType() === Tool::TYPE_SHEARS and $item->isShears()){
				$base /= 15;
			}elseif(
				($this->getToolType() === Tool::TYPE_PICKAXE and ($tier = $item->isPickaxe()) !== false) or
				($this->getToolType() === Tool::TYPE_AXE and ($tier = $item->isAxe()) !== false) or
				($this->getToolType() === Tool::TYPE_SHOVEL and ($tier = $item->isShovel()) !== false)
			){
				switch($tier){
					case Tool::TIER_WOODEN:
						$base /= 2;
						break;
					case Tool::TIER_STONE:
						$base /= 4;
						break;
					case Tool::TIER_IRON:
						$base /= 6;
						break;
					case Tool::TIER_DIAMOND:
						$base /= 8;
						break;
					case Tool::TIER_GOLD:
						$base /= 12;
						break;
				}
			}
		}else{
			$base *= 3.33;
		}

		if($item->isSword()){
			$base /= 1.5;
		}

		return $base;
	}
	public function onNearbyBlockChange() : void{

	}

	public function canBeBrokenWith(Item $item){
		return $this->getHardness() !== -1;
	}
	public function burnsForever() : bool{
		return false;
	}
	public function getBurnChance() : int{
		return 0;
	}
	public function getBurnAbility() : int{
		return 0;
	}
	public function isFlammable() : bool{
		return $this->getBurnAbility() > 0;
	}
	public function onIncinerate() : void{

	}
	public function getSide(int $side, int $step = 1){
		if($this->isValid()){
			return $this->getLevel()->getBlock(Vector3::getSide($side, $step));
		}

		return BlockFactory::get(Item::AIR, 0, Position::fromObject(Vector3::getSide($side, $step)));
	}
	public function getHorizontalSides() : array{
		return [
			$this->getSide(Vector3::SIDE_NORTH),
			$this->getSide(Vector3::SIDE_SOUTH),
			$this->getSide(Vector3::SIDE_WEST),
			$this->getSide(Vector3::SIDE_EAST)
		];
	}
	public function getAllSides() : array{
		return array_merge(
			[
				$this->getSide(Vector3::SIDE_DOWN),
				$this->getSide(Vector3::SIDE_UP)
			],
			$this->getHorizontalSides()
		);
	}
	public function getAffectedBlocks() : array{
		return [$this];
	}
	public function __toString(){
		return "Block[" . $this->getName() . "] (" . $this->getId() . ":" . $this->getDamage() . ")";
	}
	public function collidesWithBB(AxisAlignedBB $bb){
		foreach($this->getCollisionBoxes() as $bb2){
			if($bb->intersectsWith($bb2)){
				return true;
			}
		}

		return false;
	}
	public function onEntityInside(Entity $entity) : bool{
		return true;
	}
	public function getCollisionBoxes() : array{
		if($this->collisionBoxes === null){
			$this->collisionBoxes = $this->recalculateCollisionBoxes();
		}

		return $this->collisionBoxes;
	}
	protected function recalculateCollisionBoxes() : array{
		if(($bb = $this->recalculateBoundingBox()) !== null){
			return [$bb];
		}

		return [];
	}
	public function getBoundingBox(){
		if($this->boundingBox === null){
			$this->boundingBox = $this->recalculateBoundingBox();
		}
		return $this->boundingBox;
	}
	protected function recalculateBoundingBox(){
		return new AxisAlignedBB(
			$this->x,
			$this->y,
			$this->z,
			$this->x + 1,
			$this->y + 1,
			$this->z + 1
		);
	}
	public function updateState() : void{
		$this->boundingBox = null;
		$this->collisionBoxes = null;
	}
	public function calculateIntercept(Vector3 $pos1, Vector3 $pos2) : ?RayTraceResult{
		$bbs = $this->getCollisionBoxes();
		if(count($bbs) === 0){
			return null;
		}
		$currentHit = null;
		$currentDistance = PHP_INT_MAX;

		foreach($bbs as $bb){
			$nextHit = $bb->calculateIntercept($pos1, $pos2);
			if($nextHit === null){
				continue;
			}

			$nextDistance = $nextHit->hitVector->distanceSquared($pos1);
			if($nextDistance < $currentDistance){
				$currentHit = $nextHit;
				$currentDistance = $nextDistance;
			}
		}

		return $currentHit;
	}

	public function setMetadata(string $metadataKey, MetadataValue $newMetadataValue){
		if($this->getLevel() instanceof Level){
			$this->getLevel()->getBlockMetadata()->setMetadata($this, $metadataKey, $newMetadataValue);
		}
	}

	public function getMetadata(string $metadataKey){
		if($this->getLevel() instanceof Level){
			return $this->getLevel()->getBlockMetadata()->getMetadata($this, $metadataKey);
		}

		return null;
	}

	public function hasMetadata(string $metadataKey) : bool{
		if($this->getLevel() instanceof Level){
			return $this->getLevel()->getBlockMetadata()->hasMetadata($this, $metadataKey);
		}

		return false;
	}

	public function removeMetadata(string $metadataKey, Plugin $owningPlugin){
		if($this->getLevel() instanceof Level){
			$this->getLevel()->getBlockMetadata()->removeMetadata($this, $metadataKey, $owningPlugin);
		}
	}
}