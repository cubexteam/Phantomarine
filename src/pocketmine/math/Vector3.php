<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace pocketmine\math;

use function abs;
use function ceil;
use function floor;
use function iterator_to_array;
use function max;
use function min;
use function round;
use function sqrt;
use const PHP_ROUND_HALF_UP;

class Vector3{

	const SIDE_DOWN = 0;
	const SIDE_UP = 1;
	const SIDE_NORTH = 2;
	const SIDE_SOUTH = 3;
	const SIDE_WEST = 4;
	const SIDE_EAST = 5;

	public function __construct(
		public float|int|null $x,
		public float|int|null $y,
		public float|int|null $z
	){}

	public static function zero() : Vector3{
		return new self(0, 0, 0);
	}

	public function getX() : float|int{
		return $this->x;
	}

	public function getY() : float|int{
		return $this->y;
	}

	public function getZ() : float|int{
		return $this->z;
	}

	public function getFloorX() : int{
		return (int) floor($this->x);
	}

	public function getFloorY() : int{
		return (int) floor($this->y);
	}

	public function getFloorZ() : int{
		return (int) floor($this->z);
	}

	public function add($x, $y = 0, $z = 0){
		if($x instanceof Vector3){
			return new Vector3($this->x + $x->x, $this->y + $x->y, $this->z + $x->z);
		}else{
			return new Vector3($this->x + $x, $this->y + $y, $this->z + $z);
		}
	}

	public function addVector(Vector3 $v) : Vector3{
		return $this->add($v->x, $v->y, $v->z);
	}
	public function subtract($x, $y = 0, $z = 0){
		if($x instanceof Vector3){
			return $this->add(-$x->x, -$x->y, -$x->z);
		}else{
			return $this->add(-$x, -$y, -$z);
		}
	}

	public function subtractVector(Vector3 $v) : Vector3{
		return $this->add(-$v->x, -$v->y, -$v->z);
	}

	public function multiply(float $number) : Vector3{
		return new Vector3($this->x * $number, $this->y * $number, $this->z * $number);
	}

	public function divide(float $number) : Vector3{
		return new Vector3($this->x / $number, $this->y / $number, $this->z / $number);
	}

	public function ceil() : Vector3{
		return new Vector3((int) ceil($this->x), (int) ceil($this->y), (int) ceil($this->z));
	}

	public function floor() : Vector3{
		return new Vector3((int) floor($this->x), (int) floor($this->y), (int) floor($this->z));
	}
	public function round(int $precision = 0, int $mode = PHP_ROUND_HALF_UP) : Vector3{
		return $precision > 0 ?
			new Vector3(round($this->x, $precision, $mode), round($this->y, $precision, $mode), round($this->z, $precision, $mode)) :
			new Vector3((int) round($this->x, $precision, $mode), (int) round($this->y, $precision, $mode), (int) round($this->z, $precision, $mode));
	}

	public function abs() : Vector3{
		return new Vector3(abs($this->x), abs($this->y), abs($this->z));
	}
	public function getSide(int $side, int $step = 1){
		[$offsetX, $offsetY, $offsetZ] = Facing::OFFSET[$side] ?? [0, 0, 0];

		return $this->add($offsetX * $step, $offsetY * $step, $offsetZ * $step);
	}
	public function down(int $step = 1){
		return $this->getSide(Facing::DOWN, $step);
	}
	public function up(int $step = 1){
		return $this->getSide(Facing::UP, $step);
	}
	public function north(int $step = 1){
		return $this->getSide(Facing::NORTH, $step);
	}
	public function south(int $step = 1){
		return $this->getSide(Facing::SOUTH, $step);
	}
	public function west(int $step = 1){
		return $this->getSide(Facing::WEST, $step);
	}
	public function east(int $step = 1){
		return $this->getSide(Facing::EAST, $step);
	}
	public function sides(int $step = 1) : \Generator{
		foreach(Facing::ALL as $facing){
			yield $facing => $this->getSide($facing, $step);
		}
	}
	public function sidesArray(bool $keys = false, int $step = 1) : array{
		return iterator_to_array($this->sides($step), $keys);
	}
	public function sidesAroundAxis(int $axis, int $step = 1) : \Generator{
		foreach(Facing::ALL as $facing){
			if(Facing::axis($facing) !== $axis){
				yield $facing => $this->getSide($facing, $step);
			}
		}
	}
	public function asVector3() : Vector3{
		return new Vector3($this->x, $this->y, $this->z);
	}
	public static function getOppositeSide(int $side) : int{
		if($side >= 0 and $side <= 5){
			return $side ^ 0x01;
		}

		throw new \InvalidArgumentException("Invalid side $side given to getOppositeSide");
	}

	public function distance(Vector3 $pos) : float{
		return sqrt($this->distanceSquared($pos));
	}

	public function distanceSquared(Vector3 $pos) : float{
		return (($this->x - $pos->x) ** 2) + (($this->y - $pos->y) ** 2) + (($this->z - $pos->z) ** 2);
	}

	public function maxPlainDistance(Vector3|Vector2|float $x, float $z = 0) : float{
		if($x instanceof Vector3){
			return $this->maxPlainDistance($x->x, $x->z);
		}elseif($x instanceof Vector2){
			return $this->maxPlainDistance($x->x, $x->y);
		}else{
			return max(abs($this->x - $x), abs($this->z - $z));
		}
	}

	public function length() : float{
		return sqrt($this->lengthSquared());
	}

	public function lengthSquared() : float{
		return $this->x * $this->x + $this->y * $this->y + $this->z * $this->z;
	}

	public function normalize() : Vector3{
		$len = $this->lengthSquared();
		if($len > 0){
			return $this->divide(sqrt($len));
		}

		return new Vector3(0, 0, 0);
	}

	public function dot(Vector3 $v) : float{
		return $this->x * $v->x + $this->y * $v->y + $this->z * $v->z;
	}

	public function cross(Vector3 $v) : Vector3{
		return new Vector3(
			$this->y * $v->z - $this->z * $v->y,
			$this->z * $v->x - $this->x * $v->z,
			$this->x * $v->y - $this->y * $v->x
		);
	}

	public function equals(Vector3 $v) : bool{
		return $this->x == $v->x and $this->y == $v->y and $this->z == $v->z;
	}
	public function getIntermediateWithXValue(Vector3 $v, float $x) : ?Vector3{
		$xDiff = $v->x - $this->x;
		if(($xDiff * $xDiff) < 0.0000001){
			return null;
		}

		$f = ($x - $this->x) / $xDiff;

		if($f < 0 or $f > 1){
			return null;
		}else{
			return new Vector3($x, $this->y + ($v->y - $this->y) * $f, $this->z + ($v->z - $this->z) * $f);
		}
	}
	public function getIntermediateWithYValue(Vector3 $v, float $y) : ?Vector3{
		$yDiff = $v->y - $this->y;
		if(($yDiff * $yDiff) < 0.0000001){
			return null;
		}

		$f = ($y - $this->y) / $yDiff;

		if($f < 0 or $f > 1){
			return null;
		}else{
			return new Vector3($this->x + ($v->x - $this->x) * $f, $y, $this->z + ($v->z - $this->z) * $f);
		}
	}
	public function getIntermediateWithZValue(Vector3 $v, float $z) : ?Vector3{
		$zDiff = $v->z - $this->z;
		if(($zDiff * $zDiff) < 0.0000001){
			return null;
		}

		$f = ($z - $this->z) / $zDiff;

		if($f < 0 or $f > 1){
			return null;
		}else{
			return new Vector3($this->x + ($v->x - $this->x) * $f, $this->y + ($v->y - $this->y) * $f, $z);
		}
	}
	public function setComponents($x, $y, $z){
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		return $this;
	}

	public function __toString(){
		return "Vector3(x=" . $this->x . ",y=" . $this->y . ",z=" . $this->z . ")";
	}
	public function fromObjectAdd(Vector3 $pos, $x, $y, $z){
		$this->x = $pos->x + $x;
		$this->y = $pos->y + $y;
		$this->z = $pos->z + $z;
		return $this;
	}
	public function withComponents(float|int|null $x, float|int|null $y, float|int|null $z) : Vector3{
		if($x !== null || $y !== null || $z !== null){
			return new self($x ?? $this->x, $y ?? $this->y, $z ?? $this->z);
		}
		return $this;
	}
	public static function maxComponents(Vector3 $vector, Vector3 ...$vectors) : Vector3{
		$x = $vector->x;
		$y = $vector->y;
		$z = $vector->z;
		foreach($vectors as $position){
			$x = max($x, $position->x);
			$y = max($y, $position->y);
			$z = max($z, $position->z);
		}
		return new Vector3($x, $y, $z);
	}
	public static function minComponents(Vector3 $vector, Vector3 ...$vectors) : Vector3{
		$x = $vector->x;
		$y = $vector->y;
		$z = $vector->z;
		foreach($vectors as $position){
			$x = min($x, $position->x);
			$y = min($y, $position->y);
			$z = min($z, $position->z);
		}
		return new Vector3($x, $y, $z);
	}

	public static function sum(Vector3 ...$vector3s) : Vector3{
		$x = $y = $z = 0;
		foreach($vector3s as $vector3){
			$x += $vector3->x;
			$y += $vector3->y;
			$z += $vector3->z;
		}
		return new Vector3($x, $y, $z);
	}
}
