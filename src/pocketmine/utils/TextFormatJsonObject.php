<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */



namespace pocketmine\utils;
final class TextFormatJsonObject implements \JsonSerializable{
	public $text = null;
	public $color = null;
	public $bold = null;
	public $italic = null;
	public $underlined = null;
	public $strikethrough = null;
	public $obfuscated = null;
	public $extra = null;

	public function jsonSerialize(){
		$result = (array) $this;
		foreach($result as $k => $v){
			if($v === null){
				unset($result[$k]);
			}
		}
		return $result;
	}
}