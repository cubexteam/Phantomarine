<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event;

class TranslationContainer extends TextContainer{
	protected $params = [];
	public function __construct($text, array $params = []){
		parent::__construct($text);

		$this->setParameters($params);
	}
	public function getParameters(){
		return $this->params;
	}
	public function getParameter(int $i){
		return $this->params[$i] ?? null;
	}
	public function setParameter($i, $str){
		if($i < 0 or $i > count($this->params)){
			throw new \InvalidArgumentException("Invalid index $i, have " . count($this->params));
		}

		$this->params[(int) $i] = $str;
	}
	public function setParameters(array $params){
		$i = 0;
		foreach($params as $str){
			$this->params[$i] = (string) $str;

			++$i;
		}
	}
}