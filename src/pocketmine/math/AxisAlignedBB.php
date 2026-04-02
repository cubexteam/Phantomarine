<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace pocketmine\math;

use function abs;
use const PHP_INT_MAX;

final class AxisAlignedBB{

	public float $minX;
	public float $minY;
	public float $minZ;
	public float $maxX;
	public float $maxY;
	public float $maxZ;

	public function __construct(float $minX, float $minY, float $minZ, float $maxX, float $maxY, float $maxZ){
		if($minX > $maxX){
			throw new \InvalidArgumentException("minX $minX is larger than maxX $maxX");
		}
		if($minY > $maxY){
			throw new \InvalidArgumentException("minY $minY is larger than maxY $maxY");
		}
		if($minZ > $maxZ){
			throw new \InvalidArgumentException("minZ $minZ is larger than maxZ $maxZ");
		}
		$this->minX = $minX;
		$this->minY = $minY;
		$this->minZ = $minZ;
		$this->maxX = $maxX;
		$this->maxY = $maxY;
		$this->maxZ = $maxZ;
	}
	public function setBounds(float $minX, float $minY, float $minZ, float $maxX, float $maxY, float $maxZ){
		$this->minX = $minX;
		$this->minY = $minY;
		$this->minZ = $minZ;
		$this->maxX = $maxX;
		$this->maxY = $maxY;
		$this->maxZ = $maxZ;

		return $this;
	}
	public function addCoord(float $x, float $y, float $z) : AxisAlignedBB{
		$minX = $this->minX;
		$minY = $this->minY;
		$minZ = $this->minZ;
		$maxX = $this->maxX;
		$maxY = $this->maxY;
		$maxZ = $this->maxZ;

		if($x < 0){
			$minX += $x;
		}elseif($x > 0){
			$maxX += $x;
		}

		if($y < 0){
			$minY += $y;
		}elseif($y > 0){
			$maxY += $y;
		}

		if($z < 0){
			$minZ += $z;
		}elseif($z > 0){
			$maxZ += $z;
		}

		return new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ);
	}
	public function expand(float $x, float $y, float $z){
		$this->minX -= $x;
		$this->minY -= $y;
		$this->minZ -= $z;
		$this->maxX += $x;
		$this->maxY += $y;
		$this->maxZ += $z;

		return $this;
	}
	public function expandedCopy(float $x, float $y, float $z) : AxisAlignedBB{
		return (clone $this)->expand($x, $y, $z);
	}
	public function offset(float $x, float $y, float $z) : AxisAlignedBB{
		$this->minX += $x;
		$this->minY += $y;
		$this->minZ += $z;
		$this->maxX += $x;
		$this->maxY += $y;
		$this->maxZ += $z;

		return $this;
	}
	public function offsetCopy(float $x, float $y, float $z) : AxisAlignedBB{
		return (clone $this)->offset($x, $y, $z);
	}
	public function offsetTowards(int $face, float $distance) : AxisAlignedBB{
		[$offsetX, $offsetY, $offsetZ] = Facing::OFFSET[$face] ?? throw new \InvalidArgumentException("Invalid Facing $face");

		return $this->offset($offsetX * $distance, $offsetY * $distance, $offsetZ * $distance);
	}
	public function offsetTowardsCopy(int $face, float $distance) : AxisAlignedBB{
		return (clone $this)->offsetTowards($face, $distance);
	}
	public function contract(float $x, float $y, float $z) : AxisAlignedBB{
		$this->minX += $x;
		$this->minY += $y;
		$this->minZ += $z;
		$this->maxX -= $x;
		$this->maxY -= $y;
		$this->maxZ -= $z;

		return $this;
	}
	public function contractedCopy(float $x, float $y, float $z) : AxisAlignedBB{
		return (clone $this)->contract($x, $y, $z);
	}
	public function extend(int $face, float $distance) : AxisAlignedBB{
		match($face){
			Facing::DOWN  => $this->minY -= $distance,
			Facing::UP    => $this->maxY += $distance,
			Facing::NORTH => $this->minZ -= $distance,
			Facing::SOUTH => $this->maxZ += $distance,
			Facing::WEST  => $this->minX -= $distance,
			Facing::EAST  => $this->maxX += $distance,
			default => throw new \InvalidArgumentException("Invalid face $face"),
		};

		return $this;
	}
	public function extendedCopy(int $face, float $distance) : AxisAlignedBB{
		return (clone $this)->extend($face, $distance);
	}
	public function trim(int $face, float $distance) : AxisAlignedBB{
		return $this->extend($face, -$distance);
	}
	public function trimmedCopy(int $face, float $distance) : AxisAlignedBB{
		return $this->extendedCopy($face, -$distance);
	}
	public function stretch(int $axis, float $distance) : AxisAlignedBB{
		if($axis === Axis::Y){
			$this->minY -= $distance;
			$this->maxY += $distance;
		}elseif($axis === Axis::Z){
			$this->minZ -= $distance;
			$this->maxZ += $distance;
		}elseif($axis === Axis::X){
			$this->minX -= $distance;
			$this->maxX += $distance;
		}else{
			throw new \InvalidArgumentException("Invalid axis $axis");
		}
		return $this;
	}
	public function stretchedCopy(int $axis, float $distance) : AxisAlignedBB{
		return (clone $this)->stretch($axis, $distance);
	}
	public function squash(int $axis, float $distance) : AxisAlignedBB{
		return $this->stretch($axis, -$distance);
	}
	public function squashedCopy(int $axis, float $distance) : AxisAlignedBB{
		return $this->stretchedCopy($axis, -$distance);
	}

	public function calculateXOffset(AxisAlignedBB $bb, float $x) : float{
		if($bb->maxY <= $this->minY or $bb->minY >= $this->maxY){
			return $x;
		}
		if($bb->maxZ <= $this->minZ or $bb->minZ >= $this->maxZ){
			return $x;
		}
		if($x > 0 and $bb->maxX <= $this->minX){
			$x1 = $this->minX - $bb->maxX;
			if($x1 < $x){
				$x = $x1;
			}
		}elseif($x < 0 and $bb->minX >= $this->maxX){
			$x2 = $this->maxX - $bb->minX;
			if($x2 > $x){
				$x = $x2;
			}
		}

		return $x;
	}

	public function calculateYOffset(AxisAlignedBB $bb, float $y) : float{
		if($bb->maxX <= $this->minX or $bb->minX >= $this->maxX){
			return $y;
		}
		if($bb->maxZ <= $this->minZ or $bb->minZ >= $this->maxZ){
			return $y;
		}
		if($y > 0 and $bb->maxY <= $this->minY){
			$y1 = $this->minY - $bb->maxY;
			if($y1 < $y){
				$y = $y1;
			}
		}elseif($y < 0 and $bb->minY >= $this->maxY){
			$y2 = $this->maxY - $bb->minY;
			if($y2 > $y){
				$y = $y2;
			}
		}

		return $y;
	}

	public function calculateZOffset(AxisAlignedBB $bb, float $z) : float{
		if($bb->maxX <= $this->minX or $bb->minX >= $this->maxX){
			return $z;
		}
		if($bb->maxY <= $this->minY or $bb->minY >= $this->maxY){
			return $z;
		}
		if($z > 0 and $bb->maxZ <= $this->minZ){
			$z1 = $this->minZ - $bb->maxZ;
			if($z1 < $z){
				$z = $z1;
			}
		}elseif($z < 0 and $bb->minZ >= $this->maxZ){
			$z2 = $this->maxZ - $bb->minZ;
			if($z2 > $z){
				$z = $z2;
			}
		}

		return $z;
	}
	public function intersectsWith(AxisAlignedBB $bb, float $epsilon = 0.00001) : bool{
		if($bb->maxX - $this->minX > $epsilon and $this->maxX - $bb->minX > $epsilon){
			if($bb->maxY - $this->minY > $epsilon and $this->maxY - $bb->minY > $epsilon){
				return $bb->maxZ - $this->minZ > $epsilon and $this->maxZ - $bb->minZ > $epsilon;
			}
		}

		return false;
	}
	public function isVectorInside(Vector3 $vector) : bool{
		if($vector->x <= $this->minX or $vector->x >= $this->maxX){
			return false;
		}
		if($vector->y <= $this->minY or $vector->y >= $this->maxY){
			return false;
		}

		return $vector->z > $this->minZ and $vector->z < $this->maxZ;
	}
	public function getAverageEdgeLength() : float{
		return ($this->maxX - $this->minX + $this->maxY - $this->minY + $this->maxZ - $this->minZ) / 3;
	}

	public function getXLength() : float{ return $this->maxX - $this->minX; }

	public function getYLength() : float{ return $this->maxY - $this->minY; }

	public function getZLength() : float{ return $this->maxZ - $this->minZ; }

	public function isCube(float $epsilon = 0.000001) : bool{
		[$xLen, $yLen, $zLen] = [$this->getXLength(), $this->getYLength(), $this->getZLength()];
		return abs($xLen - $yLen) < $epsilon && abs($yLen - $zLen) < $epsilon;
	}
	public function getVolume() : float{
		return ($this->maxX - $this->minX) * ($this->maxY - $this->minY) * ($this->maxZ - $this->minZ);
	}
	public function isVectorInYZ(Vector3 $vector) : bool{
		return $vector->y >= $this->minY and $vector->y <= $this->maxY and $vector->z >= $this->minZ and $vector->z <= $this->maxZ;
	}
	public function isVectorInXZ(Vector3 $vector) : bool{
		return $vector->x >= $this->minX and $vector->x <= $this->maxX and $vector->z >= $this->minZ and $vector->z <= $this->maxZ;
	}
	public function isVectorInXY(Vector3 $vector) : bool{
		return $vector->x >= $this->minX and $vector->x <= $this->maxX and $vector->y >= $this->minY and $vector->y <= $this->maxY;
	}
	public function calculateIntercept(Vector3 $pos1, Vector3 $pos2) : ?RayTraceResult{
		$v1 = $pos1->getIntermediateWithXValue($pos2, $this->minX);
		$v2 = $pos1->getIntermediateWithXValue($pos2, $this->maxX);
		$v3 = $pos1->getIntermediateWithYValue($pos2, $this->minY);
		$v4 = $pos1->getIntermediateWithYValue($pos2, $this->maxY);
		$v5 = $pos1->getIntermediateWithZValue($pos2, $this->minZ);
		$v6 = $pos1->getIntermediateWithZValue($pos2, $this->maxZ);

		if($v1 !== null and !$this->isVectorInYZ($v1)){
			$v1 = null;
		}

		if($v2 !== null and !$this->isVectorInYZ($v2)){
			$v2 = null;
		}

		if($v3 !== null and !$this->isVectorInXZ($v3)){
			$v3 = null;
		}

		if($v4 !== null and !$this->isVectorInXZ($v4)){
			$v4 = null;
		}

		if($v5 !== null and !$this->isVectorInXY($v5)){
			$v5 = null;
		}

		if($v6 !== null and !$this->isVectorInXY($v6)){
			$v6 = null;
		}

		$vector = null;
		$distance = PHP_INT_MAX;
		$face = -1;

		foreach([
			Facing::WEST => $v1,
			Facing::EAST => $v2,
			Facing::DOWN => $v3,
			Facing::UP => $v4,
			Facing::NORTH => $v5,
			Facing::SOUTH => $v6
		] as $f => $v){
			if($v !== null and ($d = $pos1->distanceSquared($v)) < $distance){
				$vector = $v;
				$distance = $d;
				$face = $f;
			}
		}

		if($vector === null){
			return null;
		}

		return new RayTraceResult($this, $face, $vector);
	}

	public function __toString() : string{
		return "AxisAlignedBB({$this->minX}, {$this->minY}, {$this->minZ}, {$this->maxX}, {$this->maxY}, {$this->maxZ})";
	}
	public static function one() : AxisAlignedBB{
		return new AxisAlignedBB(0, 0, 0, 1, 1, 1);
	}
}
