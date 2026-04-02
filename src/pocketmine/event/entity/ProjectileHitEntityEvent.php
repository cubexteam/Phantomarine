<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */



namespace pocketmine\event\entity;

use pocketmine\entity\Entity;
use pocketmine\entity\Projectile;
use pocketmine\math\RayTraceResult;

class ProjectileHitEntityEvent extends ProjectileHitEvent{
	public static $handlerList = null;
	private $entityHit;

	public function __construct(Projectile $entity, RayTraceResult $rayTraceResult, Entity $entityHit){
		parent::__construct($entity, $rayTraceResult);
		$this->entityHit = $entityHit;
	}
	public function getEntityHit() : Entity{
		return $this->entityHit;
	}
}