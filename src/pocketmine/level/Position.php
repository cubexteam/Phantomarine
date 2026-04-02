<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level;

use pocketmine\math\Vector3;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\MainLogger;
use function assert;

class Position extends Vector3{
	public $level = null;
	public function __construct($x = 0, $y = 0, $z = 0, Level $level = null){
		parent::__construct($x, $y, $z);
		$this->setLevel($level);
	}
	public static function fromObject(Vector3 $pos, Level $level = null){
		return new Position($pos->x, $pos->y, $pos->z, $level);
	}
	public function asPosition() : Position{
		return new Position($this->x, $this->y, $this->z, $this->level);
	}
	public function add($x, $y = 0, $z = 0): Vector3
	{
		if($x instanceof Vector3){
			return new Position($this->x + $x->x, $this->y + $x->y, $this->z + $x->z, $this->level);
		}else{
			return new Position($this->x + $x, $this->y + $y, $this->z + $z, $this->level);
		}
	}
	public function getLevel(){
		if($this->level !== null and $this->level->isClosed()){
			MainLogger::getLogger()->debug("Position was holding a reference to an unloaded Level");
			$this->level = null;
		}

		return $this->level;
	}
	public function getLevelNonNull() : Level{
		$world = $this->getLevel();
		if($world === null){
			throw new AssumptionFailedError("Position world is null");
		}
		return $world;
	}
	public function setLevel(Level $level = null){
		if($level !== null and $level->isClosed()){
			throw new \InvalidArgumentException("Specified world has been unloaded and cannot be used");
		}

		$this->level = $level;
		return $this;
	}
	public function isValid() : bool{
		if($this->level !== null and $this->level->isClosed()){
			$this->level = null;

			return false;
		}

		return $this->level !== null;
	}
	public function getSide(int $side, int $step = 1){
		assert($this->isValid());

		return Position::fromObject(parent::getSide($side, $step), $this->level);
	}

	public function __toString(){
		return "Position(level=" . ($this->isValid() ? $this->getLevelNonNull()->getName() : "null") . ",x=" . $this->x . ",y=" . $this->y . ",z=" . $this->z . ")";
	}
	public function fromObjectAdd(Vector3 $pos, $x, $y, $z){
		if($pos instanceof Position){
			$this->level = $pos->level;
		}
		parent::fromObjectAdd($pos, $x, $y, $z);
		return $this;
	}

	public function equals(Vector3 $v) : bool{
		if($v instanceof Position){
			return parent::equals($v) and $v->getLevel() === $this->getLevel();
		}
		return parent::equals($v);
	}
}