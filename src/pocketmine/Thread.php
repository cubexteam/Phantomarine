<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine;

use const PTHREADS_INHERIT_ALL;
abstract class Thread extends \Thread{
	protected $classLoader;
	protected $isKilled = false;
	public function getClassLoader(){
		return $this->classLoader;
	}
	public function setClassLoader(\ClassLoader $loader = null){
		if($loader === null){
			$loader = Server::getInstance()->getLoader();
		}
		$this->classLoader = $loader;
	}
	public function registerClassLoader(){
		if($this->classLoader !== null){
			$this->classLoader->register(true);
		}
	}
	public function start(int $options = PTHREADS_INHERIT_ALL){
		ThreadManager::getInstance()->add($this);

		if($this->getClassLoader() === null){
			$this->setClassLoader();
		}

		return parent::start($options);
	}
	public function quit(){
		$this->isKilled = true;

		if(!$this->isJoined()){
			$this->notify();
			$this->join();
		}

		ThreadManager::getInstance()->remove($this);
	}
	public function getThreadName(){
		return (new \ReflectionClass($this))->getShortName();
	}
}