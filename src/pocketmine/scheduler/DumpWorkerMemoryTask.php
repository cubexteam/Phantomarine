<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\scheduler;

use pocketmine\MemoryManager;
use const DIRECTORY_SEPARATOR;
class DumpWorkerMemoryTask extends AsyncTask{
	private $outputFolder;
	private $maxNesting;
	private $maxStringSize;

	public function __construct(string $outputFolder, int $maxNesting, int $maxStringSize){
		$this->outputFolder = $outputFolder;
		$this->maxNesting = $maxNesting;
		$this->maxStringSize = $maxStringSize;
	}

	public function onRun(){
		MemoryManager::dumpMemory(
			$this->worker,
			$this->outputFolder . DIRECTORY_SEPARATOR . "AsyncWorker#" . $this->worker->getAsyncWorkerId(),
			$this->maxNesting,
			$this->maxStringSize,
			$this->worker->getLogger()
		);
	}
}