<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */



namespace pocketmine\command;

use pocketmine\snooze\SleeperNotifier;
use pocketmine\Thread;
use function fclose;
use function fgets;
use function fopen;
use function fstat;
use function is_resource;
use function microtime;
use function preg_replace;
use function stream_isatty;
use function stream_select;
use function trim;
use function usleep;

class CommandReader extends Thread{

	const TYPE_READLINE = 0;
	const TYPE_STREAM = 1;
	const TYPE_PIPED = 2;
	private static $stdin;
	protected $buffer;
	private $shutdown = false;
	private $type = self::TYPE_STREAM;
	private $notifier;

	public function __construct(?SleeperNotifier $notifier = null){
		$this->buffer = new \Threaded;
		$this->notifier = $notifier;

		$this->setClassLoader();
	}
	public function shutdown(){
		$this->shutdown = true;
	}

	public function quit(){
		$wait = microtime(true) + 0.5;
		while(microtime(true) < $wait){
			if($this->isRunning()){
				usleep(100000);
			}else{
				parent::quit();
				return;
			}
		}

		$message = "Thread blocked for unknown reason";
		if($this->type === self::TYPE_PIPED){
			$message = "STDIN is being piped from another location and the pipe is blocked, cannot stop safely";
		}

		throw new \ThreadException($message);
	}

	private function initStdin() : void{
		if(is_resource(self::$stdin)){
			fclose(self::$stdin);
		}

		self::$stdin = fopen("php://stdin", "r");
		if($this->isPipe(self::$stdin)){
			$this->type = self::TYPE_PIPED;
		}else{
			$this->type = self::TYPE_STREAM;
		}
	}
	private function isPipe($stream) : bool{
		return is_resource($stream) and (!stream_isatty($stream) or ((fstat($stream)["mode"] & 0170000) === 0010000));
	}
	private function readLine() : bool{
		if(!is_resource(self::$stdin)){
			$this->initStdin();
		}

		$r = [self::$stdin];
		$w = $e = null;
		if(($count = stream_select($r, $w, $e, 0, 200000)) === 0){
			return true;
		}elseif($count === false){
			$this->initStdin();
		}

		if(($raw = fgets(self::$stdin)) === false){
			$this->initStdin();
			$this->synchronized(function () : void{
				$this->wait(200000);
			});
			return true;
		}

		$line = trim($raw);

		if($line !== ""){
			$this->buffer[] = preg_replace("#\\x1b\\x5b([^\\x1b]*\\x7e|[\\x40-\\x50])#", "", $line);
			if($this->notifier !== null){
				$this->notifier->wakeupSleeper();
			}
		}

		return true;
	}
	public function getLine(){
		if($this->buffer->count() !== 0){
			return $this->buffer->shift();
		}

		return null;
	}

	public function run(){
		$this->registerClassLoader();

		while(!$this->shutdown and $this->readLine()) ;

		fclose(self::$stdin);
	}
	public function getThreadName(){
		return "Console";
	}
}
