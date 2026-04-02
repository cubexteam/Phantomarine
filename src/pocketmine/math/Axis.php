<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace pocketmine\math;

final class Axis{
	private function __construct(){
	}

	public const Y = 0;
	public const Z = 1;
	public const X = 2;
	public static function toString(int $axis) : string{
		return match($axis){
			Axis::Y => "y",
			Axis::Z => "z",
			Axis::X => "x",
			default => throw new \InvalidArgumentException("Invalid axis $axis")
		};
	}
}
