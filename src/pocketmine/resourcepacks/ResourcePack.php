<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\resourcepacks;


interface ResourcePack{
	public function getPath() : string;
	public function getPackName() : string;
	public function getPackId() : string;
	public function getPackSize() : int;
	public function getPackVersion() : string;
	public function getSha256() : string;
	public function getPackChunk(int $start, int $length) : string;
}