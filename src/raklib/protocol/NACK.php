<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace raklib\protocol;

class NACK extends AcknowledgePacket{
	public static $ID = 0xa0;
}
