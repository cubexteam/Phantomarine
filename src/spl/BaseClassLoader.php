<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

class BaseClassLoader extends \Threaded implements ClassLoader{
	private $parent;
	private $lookup;
	private $classes;

	public function __construct(ClassLoader $parent = null){
		$this->parent = $parent;
		$this->lookup = new \Threaded;
		$this->classes = new \Threaded;
	}
	public function addPath($path, $prepend = false){

		foreach($this->lookup as $p){
			if($p === $path){
				return;
			}
		}

		if($prepend){
			$this->lookup->synchronized(function ($path){
				$entries = $this->getAndRemoveLookupEntries();
				$this->lookup[] = $path;
				foreach($entries as $entry){
					$this->lookup[] = $entry;
				}
			}, $path);
		}else{
			$this->lookup[] = $path;
		}
	}
	protected function getAndRemoveLookupEntries(){
		$entries = [];
		while($this->lookup->count() > 0){
			$entries[] = $this->lookup->shift();
		}
		return $entries;
	}
	public function removePath($path){
		foreach($this->lookup as $i => $p){
			if($p === $path){
				unset($this->lookup[$i]);
			}
		}
	}
	public function getClasses(){
		$classes = [];
		foreach($this->classes as $class){
			$classes[] = $class;
		}
		return $classes;
	}
	public function getParent(){
		return $this->parent;
	}
	public function register($prepend = false){
		return spl_autoload_register(function (string $name) : void{
			$this->loadClass($name);
		}, true, $prepend);
	}
	public function loadClass($name){
		$path = $this->findClass($name);
		if($path !== null){
			include($path);
			if(!class_exists($name, false) and !interface_exists($name, false) and !trait_exists($name, false)){
				return false;
			}

			if(method_exists($name, "onClassLoaded") and (new ReflectionClass($name))->getMethod("onClassLoaded")->isStatic()){
				$name::onClassLoaded();
			}

			$this->classes[] = $name;

			return true;
		}

		return false;
	}
	public function findClass($name){
		$baseName = str_replace("\\", DIRECTORY_SEPARATOR, $name);

		foreach($this->lookup as $path){
			$filename = $path . DIRECTORY_SEPARATOR . $baseName . ".php";
			if(file_exists($filename)){
				return $filename;
			}
		}

		return null;
	}
}