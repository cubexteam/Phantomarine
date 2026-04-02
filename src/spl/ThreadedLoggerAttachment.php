<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

abstract class ThreadedLoggerAttachment extends \Volatile implements \LoggerAttachment{
	final public function call($level, $message){
		$this->log($level, $message);
	}
}