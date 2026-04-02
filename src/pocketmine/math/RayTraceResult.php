<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace pocketmine\math;
class RayTraceResult{
	public function __construct(
		public AxisAlignedBB $bb,
		public int $hitFace,
		public Vector3 $hitVector
	){}

	public function getBoundingBox() : AxisAlignedBB{
		return $this->bb;
	}

	public function getHitFace() : int{
		return $this->hitFace;
	}

	public function getHitVector() : Vector3{
		return $this->hitVector;
	}
}
