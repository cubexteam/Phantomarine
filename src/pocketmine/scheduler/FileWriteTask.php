<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\scheduler;

use function file_put_contents;
class FileWriteTask extends AsyncTask{
	private $path;
	private $contents;
	private $flags;
	public function __construct(string $path, $contents, int $flags = 0){
		$this->path = $path;
		$this->contents = $contents;
		$this->flags = $flags;
	}

	public function onRun(){
		file_put_contents($this->path, $this->contents, $this->flags);
	}
}