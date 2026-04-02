<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level\generator;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\item\Item;
use pocketmine\level\ChunkManager;
use pocketmine\level\format\Chunk;
use pocketmine\level\generator\object\OreType;
use pocketmine\level\generator\populator\Ore;
use pocketmine\level\generator\populator\Populator;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use function array_map;
use function count;
use function explode;
use function preg_match;
use function preg_match_all;

class Flat extends Generator{
	private $chunk;
	private $populators = [];
	private $structure;
	private $floorLevel;
	private $biome;
	private $options;
	private $preset;
	public function getSettings(){
		return $this->options;
	}
	public function getName() : string{
		return "flat";
	}
	public function __construct(array $options = []){
		$this->options = $options;
		if(isset($this->options["preset"]) and $this->options["preset"] != ""){
			$this->preset = $this->options["preset"];
		}else{
			$this->preset = "2;7,2x3,2;1;";
		}

		$this->parsePreset();

		if(isset($this->options["decoration"])){
			$ores = new Ore();
			$ores->setOreTypes([
				new OreType(BlockFactory::get(Block::COAL_ORE), 20, 16, 0, 128),
				new OreType(BlockFactory::get(Block::IRON_ORE), 20, 8, 0, 64),
				new OreType(BlockFactory::get(Block::REDSTONE_ORE), 8, 7, 0, 16),
				new OreType(BlockFactory::get(Block::LAPIS_ORE), 1, 6, 0, 32),
				new OreType(BlockFactory::get(Block::GOLD_ORE), 2, 8, 0, 32),
				new OreType(BlockFactory::get(Block::DIAMOND_ORE), 1, 7, 0, 16),
				new OreType(BlockFactory::get(Block::DIRT), 20, 32, 0, 128),
				new OreType(BlockFactory::get(Block::GRAVEL), 10, 16, 0, 128)
			]);
			$this->populators[] = $ores;
		}

	}
	public static function parseLayers(string $layers) : array{
		$result = [];
		$split = array_map('\trim', explode(',', $layers));
		$y = 0;
		foreach($split as $line){
			preg_match('#^(?:(\d+)[x|*])?(.+)$#', $line, $matches);
			if(count($matches) !== 3){
				throw new InvalidGeneratorOptionsException("Invalid preset layer \"$line\"");
			}

			$cnt = $matches[1] !== "" ? (int) $matches[1] : 1;
			try{
				if(Item::$list === null){
					Item::init();
				}
				$b = Item::fromString($matches[2])->getBlock();
			}catch(\InvalidArgumentException $e){
				throw new InvalidGeneratorOptionsException("Invalid preset layer \"$line\": " . $e->getMessage(), 0, $e);
			}
			for($cY = $y, $y += $cnt; $cY < $y; ++$cY){
				$result[$cY] = [$b->getId(), $b->getDamage()];
			}
		}

		return $result;
	}

	protected function parsePreset() : void{
		$preset = explode(";", $this->preset);
		$blocks = (string) ($preset[1] ?? "");
		$this->biome = (int) ($preset[2] ?? 1);
		$options = (string) ($preset[3] ?? "");
		$this->structure = self::parseLayers($blocks);

		$this->floorLevel = count($this->structure);

		preg_match_all('#(([0-9a-z_]{1,})\(?([0-9a-z_ =:]{0,})\)?),?#', $options, $matches);
		foreach($matches[2] as $i => $option){
			$params = true;
			if($matches[3][$i] !== ""){
				$params = [];
				$p = explode(" ", $matches[3][$i]);
				foreach($p as $k){
					$k = explode("=", $k);
					if(isset($k[1])){
						$params[$k[0]] = $k[1];
					}
				}
			}
			$this->options[$option] = $params;
		}
	}

	protected function generateBaseChunk() : void{
		$this->chunk = new Chunk(0, 0);
		$this->chunk->setGenerated();

		for($Z = 0; $Z < 16; ++$Z){
			for($X = 0; $X < 16; ++$X){
				$this->chunk->setBiomeId($X, $Z, $this->biome);
			}
		}

		$count = count($this->structure);
		for($sy = 0; $sy < $count; $sy += 16){
			$subchunk = $this->chunk->getSubChunk($sy >> 4, true);
			for($y = 0; $y < 16 and isset($this->structure[$y | $sy]); ++$y){
				list($id, $meta) = $this->structure[$y | $sy];

				for($Z = 0; $Z < 16; ++$Z){
					for($X = 0; $X < 16; ++$X){
						$subchunk->setBlock($X, $y, $Z, $id, $meta);
					}
				}
			}
		}
	}

	public function init(ChunkManager $level, Random $random) : void{
		parent::init($level, $random);
		$this->generateBaseChunk();
	}

	public function generateChunk($chunkX, $chunkZ){
		$chunk = clone $this->chunk;
		$chunk->setX($chunkX);
		$chunk->setZ($chunkZ);
		$this->level->setChunk($chunkX, $chunkZ, $chunk);
	}

	public function populateChunk($chunkX, $chunkZ){
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
		foreach($this->populators as $populator){
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}

	}

	public function getSpawn(){
		return new Vector3(128, $this->floorLevel, 128);
	}
}
