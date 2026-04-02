<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\entity;

use pocketmine\entity\Projectile;
use pocketmine\math\RayTraceResult;

abstract class ProjectileHitEvent extends EntityEvent{
	public static $handlerList = null;
	private $rayTraceResult;
	public function __construct(Projectile $entity, RayTraceResult $rayTraceResult){
		$this->entity = $entity;

		$this->rayTraceResult = $rayTraceResult;
	}
	public function getEntity(){
		return $this->entity;
	}
	public function getRayTraceResult() : RayTraceResult{
		return $this->rayTraceResult;
	}
}