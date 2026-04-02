<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace pocketmine\math;

use function floor;
use const INF;

final class VoxelRayTrace{
	private function __construct(){
	}
	public static function inDirection(Vector3 $start, Vector3 $directionVector, float $maxDistance) : \Generator{
		return self::betweenPoints($start, $start->addVector($directionVector->multiply($maxDistance)));
	}
	public static function betweenPoints(Vector3 $start, Vector3 $end) : \Generator{
		$currentBlock = $start->floor();

		$directionVector = $end->subtractVector($start)->normalize();
		if($directionVector->lengthSquared() <= 0){
			throw new \InvalidArgumentException("Start and end points are the same, giving a zero direction vector");
		}

		$radius = $start->distance($end);

		$stepX = $directionVector->x <=> 0;
		$stepY = $directionVector->y <=> 0;
		$stepZ = $directionVector->z <=> 0;

		$tMaxX = self::rayTraceDistanceToBoundary($start->x, $directionVector->x);
		$tMaxY = self::rayTraceDistanceToBoundary($start->y, $directionVector->y);
		$tMaxZ = self::rayTraceDistanceToBoundary($start->z, $directionVector->z);

		$tDeltaX = $directionVector->x == 0 ? 0 : $stepX / $directionVector->x;
		$tDeltaY = $directionVector->y == 0 ? 0 : $stepY / $directionVector->y;
		$tDeltaZ = $directionVector->z == 0 ? 0 : $stepZ / $directionVector->z;

		while(true){
			yield $currentBlock;

			if($tMaxX < $tMaxY and $tMaxX < $tMaxZ){
				if($tMaxX > $radius){
					break;
				}
				$currentBlock = $currentBlock->add($stepX, 0, 0);
				$tMaxX += $tDeltaX;
			}elseif($tMaxY < $tMaxZ){
				if($tMaxY > $radius){
					break;
				}
				$currentBlock = $currentBlock->add(0, $stepY, 0);
				$tMaxY += $tDeltaY;
			}else{
				if($tMaxZ > $radius){
					break;
				}
				$currentBlock = $currentBlock->add(0, 0, $stepZ);
				$tMaxZ += $tDeltaZ;
			}
		}
	}
	private static function rayTraceDistanceToBoundary(float $s, float $ds) : float{
		if($ds == 0){
			return INF;
		}

		if($ds < 0){
			$s = -$s;
			$ds = -$ds;

			if(floor($s) == $s){
				return 0;
			}
		}

		return (1 - ($s - floor($s))) / $ds;
	}
}
