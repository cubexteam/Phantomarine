<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace pocketmine\block\utils;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\math\Facing;
use function array_fill_keys;
use function intdiv;
use function min;
final class MinimumCostFlowCalculator{

    private const CAN_FLOW_DOWN = 1;
    private const CAN_FLOW = 0;
    private const BLOCKED = -1;
    private array $flowCostVisited = [];
    public function __construct(
        private Level $level,
        private int $flowDecayPerBlock,
        private \Closure $canFlowInto
    ){}

    private function calculateFlowCost(int $blockX, int $blockY, int $blockZ, int $accumulatedCost, int $maxCost, int $originOpposite, int $lastOpposite) : int{
        $cost = 1000;

        foreach(Facing::HORIZONTAL as $j){
            if($j === $originOpposite or $j === $lastOpposite){
                continue;
            }

            $x = $blockX;
            $y = $blockY;
            $z = $blockZ;

            match($j){
                Facing::WEST => --$x,
                Facing::EAST => ++$x,
                Facing::NORTH => --$z,
                Facing::SOUTH => ++$z
            };

            if(!isset($this->flowCostVisited[$hash = Level::blockHash($x, $y, $z)])){
                if(!$this->level->isInWorld($x, $y, $z) || !$this->canFlowInto($this->level->getBlockAt($x, $y, $z))){
                    $this->flowCostVisited[$hash] = self::BLOCKED;
                }elseif($this->level->getBlockAt($x, $y - 1, $z)->canBeFlowedInto()){
                    $this->flowCostVisited[$hash] = self::CAN_FLOW_DOWN;
                }else{
                    $this->flowCostVisited[$hash] = self::CAN_FLOW;
                }
            }

            $status = $this->flowCostVisited[$hash];

            if($status === self::BLOCKED){
                continue;
            }elseif($status === self::CAN_FLOW_DOWN){
                return $accumulatedCost;
            }

            if($accumulatedCost >= $maxCost){
                continue;
            }

            $realCost = $this->calculateFlowCost($x, $y, $z, $accumulatedCost + 1, $maxCost, $originOpposite, Facing::opposite($j));

            if($realCost < $cost){
                $cost = $realCost;
            }
        }

        return $cost;
    }
    public function getOptimalFlowDirections(int $originX, int $originY, int $originZ) : array{
        $flowCost = array_fill_keys(Facing::HORIZONTAL, 1000);
        $maxCost = intdiv(4, $this->flowDecayPerBlock);
        foreach(Facing::HORIZONTAL as $j){
            $x = $originX;
            $y = $originY;
            $z = $originZ;

            match($j){
                Facing::WEST => --$x,
                Facing::EAST => ++$x,
                Facing::NORTH => --$z,
                Facing::SOUTH => ++$z
            };

            if(!$this->level->isInWorld($x, $y, $z) || !$this->canFlowInto($this->level->getBlockAt($x, $y, $z))){
                $this->flowCostVisited[Level::blockHash($x, $y, $z)] = self::BLOCKED;
            }elseif($this->level->getBlockAt($x, $y - 1, $z)->canBeFlowedInto()){
                $this->flowCostVisited[Level::blockHash($x, $y, $z)] = self::CAN_FLOW_DOWN;
                $flowCost[$j] = $maxCost = 0;
            }elseif($maxCost > 0){
                $this->flowCostVisited[Level::blockHash($x, $y, $z)] = self::CAN_FLOW;
                $opposite = Facing::opposite($j);
                $flowCost[$j] = $this->calculateFlowCost($x, $y, $z, 1, $maxCost, $opposite, $opposite);
                $maxCost = min($maxCost, $flowCost[$j]);
            }
        }

        $this->flowCostVisited = [];

        $minCost = min($flowCost);

        $isOptimalFlowDirection = [];

        foreach($flowCost as $facing => $cost){
            if($cost === $minCost){
                $isOptimalFlowDirection[] = $facing;
            }
        }

        return $isOptimalFlowDirection;
    }

    private function canFlowInto(Block $block) : bool{
        return ($this->canFlowInto)($block);
    }
}