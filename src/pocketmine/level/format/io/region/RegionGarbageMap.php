<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */



namespace pocketmine\level\format\io\region;

use pocketmine\utils\AssumptionFailedError;
use function end;
use function ksort;
use function time;
use const SORT_NUMERIC;

final class RegionGarbageMap{
	private $entries = [];
	private $clean = false;
	public function __construct(array $entries){
		foreach($entries as $entry){
			$this->entries[$entry->getFirstSector()] = $entry;
		}
	}
	public static function buildFromLocationTable(array $locationTable) : self{
		$usedMap = [];
		foreach($locationTable as $entry){
			if($entry === null){
				continue;
			}
			if(isset($usedMap[$entry->getFirstSector()])){
				throw new AssumptionFailedError("Overlapping entries detected");
			}
			$usedMap[$entry->getFirstSector()] = $entry;
		}

		ksort($usedMap, SORT_NUMERIC);
		$garbageMap = [];
		$prevEntry = null;
		foreach($usedMap as $firstSector => $entry){
			$prevEndPlusOne = ($prevEntry !== null ? $prevEntry->getLastSector() + 1 : RegionLoader::FIRST_SECTOR);
			$currentStart = $entry->getFirstSector();
			if($prevEndPlusOne < $currentStart){
				$garbageMap[$prevEndPlusOne] = new RegionLocationTableEntry($prevEndPlusOne, $currentStart - $prevEndPlusOne, 0);
			}elseif($prevEndPlusOne > $currentStart){
				throw new AssumptionFailedError("Overlapping entries detected");
			}
			$prevEntry = $entry;
		}

		return new self($garbageMap);
	}
	public function getArray() : array{
		if(!$this->clean){
			ksort($this->entries, SORT_NUMERIC);
			$prevIndex = null;
			foreach($this->entries as $k => $entry){
				if($prevIndex !== null and $this->entries[$prevIndex]->getLastSector() + 1 === $entry->getFirstSector()){
					$this->entries[$prevIndex] = new RegionLocationTableEntry(
						$this->entries[$prevIndex]->getFirstSector(),
						$this->entries[$prevIndex]->getSectorCount() + $entry->getSectorCount(),
						0
					);
					unset($this->entries[$k]);
				}else{
					$prevIndex = $k;
				}
			}
			$this->clean = true;
		}
		return $this->entries;
	}

	public function add(RegionLocationTableEntry $entry) : void{
		if(isset($this->entries[$k = $entry->getFirstSector()])){
			throw new \InvalidArgumentException("Overlapping entry starting at " . $k);
		}
		$this->entries[$k] = $entry;
		$this->clean = false;
	}

	public function remove(RegionLocationTableEntry $entry) : void{
		if(isset($this->entries[$k = $entry->getFirstSector()])){
			unset($this->entries[$k]);
		}
	}

	public function end() : ?RegionLocationTableEntry{
		$array = $this->getArray();
		$end = end($array);
		return $end !== false ? $end : null;
	}

	public function allocate(int $newSize) : ?RegionLocationTableEntry{
		foreach($this->getArray() as $start => $candidate){
			$candidateSize = $candidate->getSectorCount();
			if($candidateSize < $newSize){
				continue;
			}

			$newLocation = new RegionLocationTableEntry($candidate->getFirstSector(), $newSize, time());
			$this->remove($candidate);

			if($candidateSize > $newSize){
				$newGarbageStart = $candidate->getFirstSector() + $newSize;
				$newGarbageSize = $candidateSize - $newSize;
				$this->add(new RegionLocationTableEntry($newGarbageStart, $newGarbageSize, 0));
			}
			return $newLocation;

		}

		return null;
	}
}