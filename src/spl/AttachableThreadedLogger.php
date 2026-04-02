<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

abstract class AttachableThreadedLogger extends \ThreadedLogger{
	protected $attachments;

	public function __construct(){
		$this->attachments = new \Volatile();
	}
	public function addAttachment(\ThreadedLoggerAttachment $attachment){
		$this->attachments[] = $attachment;
	}
	public function removeAttachment(\ThreadedLoggerAttachment $attachment){
		foreach($this->attachments as $i => $a){
			if($attachment === $a){
				unset($this->attachments[$i]);
			}
		}
	}

	public function removeAttachments(){
		foreach($this->attachments as $i => $a){
			unset($this->attachments[$i]);
		}
	}
	public function getAttachments(){
		return (array) $this->attachments;
	}
}