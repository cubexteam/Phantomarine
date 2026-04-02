<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine;

use pocketmine\utils\MainLogger;
use function spl_object_hash;

class ThreadManager extends \Volatile{
	private static $instance = null;

	public static function init(){
		self::$instance = new ThreadManager();
	}
	public static function getInstance(){
		return self::$instance;
	}
	public function add($thread){
		if($thread instanceof Thread or $thread instanceof Worker){
			$this->{spl_object_hash($thread)} = $thread;
		}
	}
	public function remove($thread){
		if($thread instanceof Thread or $thread instanceof Worker){
			unset($this->{spl_object_hash($thread)});
		}
	}
	public function getAll() : array{
		$array = [];
		foreach($this as $key => $thread){
			$array[$key] = $thread;
		}

		return $array;
	}

	public function stopAll() : int{
		$logger = MainLogger::getLogger();

		$erroredThreads = 0;

		foreach($this->getAll() as $thread){
			$logger->debug("Stopping " . $thread->getThreadName() . " thread");
			try{
				$thread->quit();
				$logger->debug($thread->getThreadName() . " thread stopped successfully.");
			}catch(\ThreadException $e){
				++$erroredThreads;
				$logger->debug("Could not stop " . $thread->getThreadName() . " thread: " . $e->getMessage());
			}
		}

		return $erroredThreads;
	}
}