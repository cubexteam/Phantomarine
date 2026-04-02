<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);
namespace pocketmine\math;

use function sqrt;

final class Math{
	private function __construct(){
	}
	public static function floorFloat($n) : int{
		$i = (int) $n;
		return $n >= $i ? $i : $i - 1;
	}
	public static function ceilFloat($n) : int{
		$i = (int) $n;
		return $n <= $i ? $i : $i + 1;
	}
    public static function signum($num) : int{
        if($num == 0){
            return 0;
        }
        return $num > 0 ? 1 : -1;
    }
	public static function clamp($value, $low, $high){
		return min($high, max($low, $value));
	}
	public static function solveQuadratic(float $a, float $b, float $c) : array{
		if($a === 0.0){
			throw new \InvalidArgumentException("Coefficient a cannot be 0!");
		}
		$discriminant = $b ** 2 - 4 * $a * $c;
		if($discriminant > 0){
			$sqrtDiscriminant = sqrt($discriminant);
			return [
				(-$b + $sqrtDiscriminant) / (2 * $a),
				(-$b - $sqrtDiscriminant) / (2 * $a)
			];
		}elseif($discriminant == 0){
			return [
				-$b / (2 * $a)
			];
		}else{
			return [];
		}
	}
    public static function randomGaussian() : float{
        return sqrt(-2 * log(lcg_value())) * cos(2 * M_PI * lcg_value());
    }
}
