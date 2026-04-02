<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event;
abstract class EventPriority{
	public const ALL = [
		self::LOWEST,
		self::LOW,
		self::NORMAL,
		self::HIGH,
		self::HIGHEST,
		self::MONITOR
	];
	const LOWEST = 5;
	const LOW = 4;
	const NORMAL = 3;
	const HIGH = 2;
	const HIGHEST = 1;
	const MONITOR = 0;
	public static function fromString(string $name) : int{
		$name = strtoupper($name);
		$const = self::class . "::" . $name;
		if($name !== "ALL" and \defined($const)){
			return \constant($const);
		}

		throw new \InvalidArgumentException("Unable to resolve priority \"$name\"");
	}
}