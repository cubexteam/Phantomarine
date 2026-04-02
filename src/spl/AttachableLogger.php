<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

interface AttachableLogger extends \Logger{
	public function addAttachment(\LoggerAttachment $attachment);
	public function removeAttachment(\LoggerAttachment $attachment);

	public function removeAttachments();
	public function getAttachments();
}