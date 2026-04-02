<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */



namespace pocketmine\event\entity;

use pocketmine\block\Block;
use pocketmine\entity\Projectile;
use pocketmine\math\RayTraceResult;

class ProjectileHitBlockEvent extends ProjectileHitEvent{
	public static $handlerList = null;
	private $blockHit;

	public function __construct(Projectile $entity, RayTraceResult $rayTraceResult, Block $blockHit){
		parent::__construct($entity, $rayTraceResult);
		$this->blockHit = $blockHit;
	}
	public function getBlockHit() : Block{
		return $this->blockHit;
	}
}