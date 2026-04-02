<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event;

use pocketmine\entity\Living;
use pocketmine\Server;

class TimingsHandler{
	private static $HANDLERS = [];
	private static $enabled = false;
	private static $timingStart = 0;
	public static function printTimings($fp){
		fwrite($fp, "Minecraft" . PHP_EOL);

		foreach(self::$HANDLERS as $timings){
			$time = $timings->totalTime;
			$count = $timings->count;
			if($count === 0){
				continue;
			}

			$avg = $time / $count;

			fwrite($fp, "    " . $timings->name . " Time: " . round($time * 1000000000) . " Count: " . $count . " Avg: " . round($avg * 1000000000) . " Violations: " . $timings->violations . PHP_EOL);
		}

		fwrite($fp, "# Version " . Server::getInstance()->getVersion() . PHP_EOL);
		fwrite($fp, "# " . Server::getInstance()->getName() . " " . Server::getInstance()->getPocketMineVersion() . PHP_EOL);

		$entities = 0;
		$livingEntities = 0;
		foreach(Server::getInstance()->getLevels() as $level){
			$entities += count($level->getEntities());
			foreach($level->getEntities() as $e){
				if($e instanceof Living){
					++$livingEntities;
				}
			}
		}

		fwrite($fp, "# Entities " . $entities . PHP_EOL);
		fwrite($fp, "# LivingEntities " . $livingEntities . PHP_EOL);

		$sampleTime = microtime(true) - self::$timingStart;
		fwrite($fp, "Sample time " . round($sampleTime * 1000000000) . " (" . $sampleTime . "s)" . PHP_EOL);
	}

	public static function isEnabled() : bool{
		return self::$enabled;
	}

	public static function setEnabled(bool $enable = true) : void{
		self::$enabled = $enable;
		self::reload();
	}

	public static function getStartTime() : float{
		return self::$timingStart;
	}

	public static function reload(){
		if(self::$enabled){
			foreach(self::$HANDLERS as $timings){
				$timings->reset();
			}
			self::$timingStart = microtime(true);
		}
	}
	public static function tick(bool $measure = true){
		if(self::$enabled){
			if($measure){
				foreach(self::$HANDLERS as $timings){
					if($timings->curTickTotal > 0.05){
						$timings->violations += (int) round($timings->curTickTotal / 0.05);
					}
					$timings->curTickTotal = 0;
					$timings->curCount = 0;
					$timings->timingDepth = 0;
				}
			}else{
				foreach(self::$HANDLERS as $timings){
					$timings->totalTime -= $timings->curTickTotal;
					$timings->count -= $timings->curCount;

					$timings->curTickTotal = 0;
					$timings->curCount = 0;
					$timings->timingDepth = 0;
				}
			}
		}
	}
	private $name;
	private $parent = null;
	private $count = 0;
	private $curCount = 0;
	private $start = 0;
	private $timingDepth = 0;
	private $totalTime = 0;
	private $curTickTotal = 0;
	private $violations = 0;
	public function __construct($name, TimingsHandler $parent = null){
		$this->name = $name;
		$this->parent = $parent;

		self::$HANDLERS[spl_object_hash($this)] = $this;
	}

	public function startTiming(){
		if(self::$enabled){
			$this->internalStartTiming(microtime(true));
		}
	}

	private function internalStartTiming(float $now) : void{
		if(++$this->timingDepth === 1){
			$this->start = $now;
			if($this->parent !== null){
				$this->parent->internalStartTiming($now);
			}
		}
	}

	public function stopTiming(){
		if(self::$enabled){
			$this->internalStopTiming(microtime(true));
		}
	}

	private function internalStopTiming(float $now) : void{
		if($this->timingDepth === 0){
			return;
		}
		if(--$this->timingDepth !== 0 or $this->start == 0){
			return;
		}

		$diff = $now - $this->start;
		$this->totalTime += $diff;
		$this->curTickTotal += $diff;
		++$this->curCount;
		++$this->count;
		$this->start = 0;
		if($this->parent !== null){
			$this->parent->internalStopTiming($now);
		}
	}

	public function reset(){
		$this->count = 0;
		$this->curCount = 0;
		$this->violations = 0;
		$this->curTickTotal = 0;
		$this->totalTime = 0;
		$this->start = 0;
		$this->timingDepth = 0;
	}

	public function remove(){
		unset(self::$HANDLERS[spl_object_hash($this)]);
	}
}