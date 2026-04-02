<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */



namespace pocketmine\level\format\io\region;


use pocketmine\level\format\Chunk;
use pocketmine\level\format\ChunkException;
use pocketmine\level\format\io\ChunkUtils;
use pocketmine\level\format\io\exception\CorruptedChunkException;
use pocketmine\level\format\SubChunk;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\{ByteArrayTag, ByteTag, CompoundTag, IntArrayTag, IntTag, ListTag, LongTag};
use pocketmine\utils\MainLogger;
use function zlib_decode;


class Anvil extends McRegion{

	const REGION_FILE_EXTENSION = "mca";
	protected function nbtSerialize(Chunk $chunk) : string{
		$nbt = new CompoundTag("Level", []);
		$nbt->xPos = new IntTag("xPos", $chunk->getX());
		$nbt->zPos = new IntTag("zPos", $chunk->getZ());

		$nbt->V = new ByteTag("V", 1);
		$nbt->LastUpdate = new LongTag("LastUpdate", 0);
		$nbt->InhabitedTime = new LongTag("InhabitedTime", 0);
		$nbt->TerrainPopulated = new ByteTag("TerrainPopulated", $chunk->isPopulated());
		$nbt->LightPopulated = new ByteTag("LightPopulated", $chunk->isLightPopulated());

		$nbt->Sections = new ListTag("Sections", []);
		$nbt->Sections->setTagType(NBT::TAG_Compound);
		$subChunks = -1;
		foreach($chunk->getSubChunks() as $y => $subChunk){
			if(!($subChunk instanceof SubChunk) or $subChunk->isEmpty()){
				continue;
			}
			$nbt->Sections[++$subChunks] = new CompoundTag("", [
				new ByteTag("Y", $y),
				new ByteArrayTag("Blocks", ChunkUtils::reorderByteArray($subChunk->getBlockIdArray())),
				new ByteArrayTag("Data", ChunkUtils::reorderNibbleArray($subChunk->getBlockDataArray())),
				new ByteArrayTag("SkyLight", ChunkUtils::reorderNibbleArray($subChunk->getBlockSkyLightArray(), "\xff")),
				new ByteArrayTag("BlockLight", ChunkUtils::reorderNibbleArray($subChunk->getBlockLightArray()))
			]);
		}

		$nbt->Biomes = new ByteArrayTag("Biomes", $chunk->getBiomeIdArray());
		$nbt->HeightMap = new IntArrayTag("HeightMap", $chunk->getHeightMapArray());

		$entities = [];

		foreach($chunk->getSavableEntities() as $entity){
			$entity->saveNBT();
			$entities[] = $entity->namedtag;
		}

		$nbt->Entities = new ListTag("Entities", $entities);
		$nbt->Entities->setTagType(NBT::TAG_Compound);

		$tiles = [];
		foreach($chunk->getTiles() as $tile){
			$tile->saveNBT();
			$tiles[] = $tile->namedtag;
		}

		$nbt->TileEntities = new ListTag("TileEntities", $tiles);
		$nbt->TileEntities->setTagType(NBT::TAG_Compound);


		$writer = new NBT(NBT::BIG_ENDIAN);
		$nbt->setName("Level");
		$writer->setData(new CompoundTag("", [$nbt]));

		return $writer->writeCompressed(ZLIB_ENCODING_DEFLATE, RegionLoader::$COMPRESSION_LEVEL);
	}
	protected function nbtDeserialize(string $data){
		$data = @zlib_decode($data);
		if($data === false){
			throw new CorruptedChunkException("Failed to decompress chunk data");
		}
		$nbt = new NBT(NBT::BIG_ENDIAN);
		try{
			$nbt->read($data);

			$chunk = $nbt->getData();

			if(!isset($chunk->Level) or !($chunk->Level instanceof CompoundTag)){
				throw new ChunkException("Invalid NBT format");
			}

			$chunk = $chunk->Level;

			$subChunks = [];
			if($chunk->Sections instanceof ListTag){
				foreach($chunk->Sections as $subChunk){
					if($subChunk instanceof CompoundTag){
						$subChunks[$subChunk->Y->getValue()] = new SubChunk(
							ChunkUtils::reorderByteArray($subChunk->Blocks->getValue()),
							ChunkUtils::reorderNibbleArray($subChunk->Data->getValue()),
							ChunkUtils::reorderNibbleArray($subChunk->SkyLight->getValue(), "\xff"),
							ChunkUtils::reorderNibbleArray($subChunk->BlockLight->getValue())
						);
					}
				}
			}

			if(isset($chunk->BiomeColors)){
				$biomeIds = ChunkUtils::convertBiomeColors($chunk->BiomeColors->getValue());
			}elseif(isset($chunk->Biomes)){
				$biomeIds = $chunk->Biomes->getValue();
			}else{
				$biomeIds = "";
			}

			$result = new Chunk(
				(int) $chunk["xPos"],
				(int) $chunk["zPos"],
				$subChunks,
				isset($chunk->Entities) ? $chunk->Entities->getValue() : [],
				isset($chunk->TileEntities) ? $chunk->TileEntities->getValue() : [],
				$biomeIds,
				isset($chunk->HeightMap) ? $chunk->HeightMap->getValue() : []
			);
			$result->setLightPopulated(isset($chunk->LightPopulated) ? ((bool) $chunk->LightPopulated->getValue()) : false);
			$result->setPopulated(isset($chunk->TerrainPopulated) ? ((bool) $chunk->TerrainPopulated->getValue()) : false);
			$result->setGenerated();
			$result->setChanged(false);
			return $result;
		}catch(\Throwable $e){
			MainLogger::getLogger()->logException($e);
			return null;
		}
	}
	public static function getProviderName() : string{
		return "anvil";
	}

	public static function getPcWorldFormatVersion() : int{
		return 19133;
	}
	public function getWorldHeight() : int{
		return 256;
	}

}