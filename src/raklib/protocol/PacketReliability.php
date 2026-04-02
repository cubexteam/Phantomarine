<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace raklib\protocol;

abstract class PacketReliability{

	/*
	 * From https:
	 *
	 * Default: 0b010 (2) or 0b011 (3)
	 */

	public const UNRELIABLE = 0;
	public const UNRELIABLE_SEQUENCED = 1;
	public const RELIABLE = 2;
	public const RELIABLE_ORDERED = 3;
	public const RELIABLE_SEQUENCED = 4;
	public const UNRELIABLE_WITH_ACK_RECEIPT = 5;
	public const RELIABLE_WITH_ACK_RECEIPT = 6;
	public const RELIABLE_ORDERED_WITH_ACK_RECEIPT = 7;

	public static function isReliable(int $reliability) : bool{
		return (
			$reliability === self::RELIABLE or
			$reliability === self::RELIABLE_ORDERED or
			$reliability === self::RELIABLE_SEQUENCED or
			$reliability === self::RELIABLE_WITH_ACK_RECEIPT or
			$reliability === self::RELIABLE_ORDERED_WITH_ACK_RECEIPT
		);
	}

	public static function isSequenced(int $reliability) : bool{
		return (
			$reliability === self::UNRELIABLE_SEQUENCED or
			$reliability === self::RELIABLE_SEQUENCED
		);
	}

	public static function isOrdered(int $reliability) : bool{
		return (
			$reliability === self::RELIABLE_ORDERED or
			$reliability === self::RELIABLE_ORDERED_WITH_ACK_RECEIPT
		);
	}

	public static function isSequencedOrOrdered(int $reliability) : bool{
		return (
			$reliability === self::UNRELIABLE_SEQUENCED or
			$reliability === self::RELIABLE_ORDERED or
			$reliability === self::RELIABLE_SEQUENCED or
			$reliability === self::RELIABLE_ORDERED_WITH_ACK_RECEIPT
		);
	}
}
