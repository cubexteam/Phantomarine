<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

/*
 *Zip材质包加载接口
 *
*/

namespace pocketmine\resourcepacks;

use function assert;
use function count;
use function fclose;
use function feof;
use function file_exists;
use function filesize;
use function fopen;
use function fread;
use function fseek;
use function gettype;
use function hash_file;
use function implode;
use function json_decode;
use function preg_match;
use function strlen;

class ZippedResourcePack implements ResourcePack{
	public static function verifyManifest(\stdClass $manifest){
		if(!isset($manifest->format_version) or !isset($manifest->header) or !isset($manifest->modules)){
			return false;
		}

		return
			isset($manifest->header->description) and
			isset($manifest->header->name) and
			isset($manifest->header->uuid) and
			isset($manifest->header->version) and
			count($manifest->header->version) === 3;
	}
	protected $path;
	protected $manifest;
	protected $sha256 = null;
	protected $fileResource;
	public function __construct(string $zipPath){
		$this->path = $zipPath;

		if(!file_exists($zipPath)){
			throw new ResourcePackException("File not found");
		}

		$archive = new \ZipArchive();
		if(($openResult = $archive->open($zipPath)) !== true){
			throw new ResourcePackException("Encountered ZipArchive error code $openResult while trying to open $zipPath");
		}

		if(($manifestData = $archive->getFromName("manifest.json")) === false){
			$manifestPath = null;
			$manifestIdx = null;
			for($i = 0; $i < $archive->numFiles; ++$i){
				$name = $archive->getNameIndex($i);
				if(
					($manifestPath === null or strlen($name) < strlen($manifestPath)) and
					preg_match('#.*/manifest.json$#', $name) === 1
				){
					$manifestPath = $name;
					$manifestIdx = $i;
				}
			}
			if($manifestIdx !== null){
				$manifestData = $archive->getFromIndex($manifestIdx);
				assert($manifestData !== false);
			}elseif($archive->locateName("pack_manifest.json") !== false){
				throw new ResourcePackException("Unsupported old pack format");
			}else{
				throw new ResourcePackException("manifest.json not found in the archive root");
			}
		}

		$archive->close();

		try{
			$manifest = json_decode($manifestData);
		}catch(\RuntimeException $e){
			throw new ResourcePackException("Failed to parse manifest.json: " . $e->getMessage(), $e->getCode(), $e);
		}

		if(!($manifest instanceof \stdClass)){
			throw new ResourcePackException("manifest.json should contain a JSON object, not " . gettype($manifest));
		}
		if(!self::verifyManifest($manifest)){
			throw new ResourcePackException("manifest.json is missing required fields");
		}

		$this->manifest = $manifest;

		$this->fileResource = fopen($zipPath, "rb");
	}

	public function __destruct(){
		fclose($this->fileResource);
	}

	public function getPath() : string{
		return $this->path;
	}

	public function getPackName() : string{
		return $this->manifest->header->name;
	}

	public function getPackVersion() : string{
		return implode(".", $this->manifest->header->version);
	}

	public function getPackId() : string{
		return $this->manifest->header->uuid;
	}

	public function getPackSize() : int{
		return filesize($this->path);
	}

	public function getSha256(bool $cached = true) : string{
		if($this->sha256 === null or !$cached){
			$this->sha256 = hash_file("sha256", $this->path, true);
		}
		return $this->sha256;
	}

	public function getPackChunk(int $start, int $length) : string{
		fseek($this->fileResource, $start);
		if(feof($this->fileResource)){
			throw new \InvalidArgumentException("Requested a resource pack chunk with invalid start offset");
		}
		return fread($this->fileResource, $length);
	}
}