<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\utils;

use pocketmine\Thread;
use function getmypid;
use function hrtime;
use function intdiv;

class ServerKiller extends Thread{

	public $time;
	private $stopped = false;
	public function __construct($time = 15){
		$this->time = $time;
	}

	public function run(){
		$this->registerClassLoader();
		$start = hrtime(true);
		$remaining = $this->time * 1_000_000;
		$this->synchronized(function() use (&$remaining, $start) : void{
			while(!$this->stopped && $remaining > 0){
				$this->wait($remaining);
				$remaining -= intdiv(hrtime(true) - $start, 1000);
			}
		});
		if($remaining <= 0){
			echo "\nTook too long to stop, server was killed forcefully!\n";
			@Utils::kill(getmypid());
		}
	}

	public function quit() : void{
		$this->synchronized(function () : void{
			$this->stopped = true;
			$this->notify();
		});
		parent::quit();
	}
	public function getThreadName(){
		return "Server Killer";
	}
}
