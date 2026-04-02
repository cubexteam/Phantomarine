<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace raklib\protocol;

use raklib\RakLib;

abstract class OfflineMessage extends Packet{
	protected $magic;
	protected function readMagic(){
		$this->magic = $this->get(16);
	}
	protected function writeMagic(){
		$this->put(RakLib::MAGIC);
	}

	public function isValid() : bool{
		return $this->magic === RakLib::MAGIC;
	}

}
