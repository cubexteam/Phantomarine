<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace pocketmine\math;

use function in_array;

final class Facing{
	private function __construct(){
	}

	public const FLAG_AXIS_POSITIVE = 1;

	/* most significant 2 bits = axis, least significant bit = is positive direction */
	public const DOWN =   Axis::Y << 1;
	public const UP =    (Axis::Y << 1) | self::FLAG_AXIS_POSITIVE;
	public const NORTH =  Axis::Z << 1;
	public const SOUTH = (Axis::Z << 1) | self::FLAG_AXIS_POSITIVE;
	public const WEST =   Axis::X << 1;
	public const EAST =  (Axis::X << 1) | self::FLAG_AXIS_POSITIVE;

	public const ALL = [
		self::DOWN,
		self::UP,
		self::NORTH,
		self::SOUTH,
		self::WEST,
		self::EAST
	];

	public const HORIZONTAL = [
		self::NORTH,
		self::SOUTH,
		self::WEST,
		self::EAST
	];

	public const OFFSET = [
		self::DOWN  => [ 0, -1,  0],
		self::UP    => [ 0, +1,  0],
		self::NORTH => [ 0,  0, -1],
		self::SOUTH => [ 0,  0, +1],
		self::WEST  => [-1,  0,  0],
		self::EAST  => [+1,  0,  0]
	];

	private const CLOCKWISE = [
		Axis::Y => [
			self::NORTH => self::EAST,
			self::EAST => self::SOUTH,
			self::SOUTH => self::WEST,
			self::WEST => self::NORTH
		],
		Axis::Z => [
			self::UP => self::EAST,
			self::EAST => self::DOWN,
			self::DOWN => self::WEST,
			self::WEST => self::UP
		],
		Axis::X => [
			self::UP => self::NORTH,
			self::NORTH => self::DOWN,
			self::DOWN => self::SOUTH,
			self::SOUTH => self::UP
		]
	];
	public static function axis(int $direction) : int{
		return $direction >> 1;
	}
	public static function isPositive(int $direction) : bool{
		return ($direction & self::FLAG_AXIS_POSITIVE) === self::FLAG_AXIS_POSITIVE;
	}
	public static function opposite(int $direction) : int{
		return $direction ^ self::FLAG_AXIS_POSITIVE;
	}
	public static function rotate(int $direction, int $axis, bool $clockwise) : int{
		if(!isset(self::CLOCKWISE[$axis])){
			throw new \InvalidArgumentException("Invalid axis $axis");
		}
		if(!isset(self::CLOCKWISE[$axis][$direction])){
			throw new \InvalidArgumentException("Cannot rotate facing \"" . self::toString($direction) . "\" around axis \"" . Axis::toString($axis) . "\"");
		}

		$rotated = self::CLOCKWISE[$axis][$direction];
		return $clockwise ? $rotated : self::opposite($rotated);
	}
	public static function rotateY(int $direction, bool $clockwise) : int{
		return self::rotate($direction, Axis::Y, $clockwise);
	}
	public static function rotateZ(int $direction, bool $clockwise) : int{
		return self::rotate($direction, Axis::Z, $clockwise);
	}
	public static function rotateX(int $direction, bool $clockwise) : int{
		return self::rotate($direction, Axis::X, $clockwise);
	}
	public static function validate(int $facing) : void{
		if(!in_array($facing, self::ALL, true)){
			throw new \InvalidArgumentException("Invalid direction $facing");
		}
	}
	public static function toString(int $facing) : string{
		return match($facing){
			self::DOWN => "down",
			self::UP => "up",
			self::NORTH => "north",
			self::SOUTH => "south",
			self::WEST => "west",
			self::EAST => "east",
			default => throw new \InvalidArgumentException("Invalid facing $facing")
		};
	}
}
