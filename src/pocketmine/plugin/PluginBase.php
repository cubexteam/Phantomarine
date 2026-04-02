<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\plugin;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;
use pocketmine\utils\Config;

abstract class PluginBase implements Plugin{
	private $loader;
	private $server;
	private $isEnabled = false;
	private $initialized = false;
	private $description;
	private $dataFolder;
	private $config;
	private $configFile;
	private $file;
	private $logger;
	private $scheduler;
	public function onLoad(){

	}

	public function onEnable(){

	}

	public function onDisable(){

	}
	public final function isEnabled(){
		return $this->isEnabled === true;
	}
	public final function setEnabled($boolean = true){
		if($this->isEnabled !== $boolean){
			$this->isEnabled = $boolean;
			if($this->isEnabled === true){
				$this->onEnable();
			}else{
				$this->onDisable();
			}
		}
	}
	public final function isDisabled(){
		return $this->isEnabled === false;
	}
	public final function getDataFolder(){
		return $this->dataFolder;
	}
	public final function getDescription(){
		return $this->description;
	}
	public final function init(PluginLoader $loader, Server $server, PluginDescription $description, $dataFolder, $file){
		if($this->initialized === false){
			$this->initialized = true;
			$this->loader = $loader;
			$this->server = $server;
			$this->description = $description;
			$this->dataFolder = rtrim($dataFolder, "/" . DIRECTORY_SEPARATOR) . "/";
			$this->file = rtrim($file, "/" . DIRECTORY_SEPARATOR) . "/";
			$this->configFile = $this->dataFolder . "config.yml";
			$this->logger = new PluginLogger($this);
			$this->scheduler = new TaskScheduler($this->logger);
		}
	}
	public function getLogger(){
		return $this->logger;
	}
	public final function isInitialized(){
		return $this->initialized;
	}
	public function getCommand($name){
		$command = $this->getServer()->getPluginCommand($name);
		if($command === null or $command->getPlugin() !== $this){
			$command = $this->getServer()->getPluginCommand(strtolower($this->description->getName()) . ":" . $name);
		}

		if($command instanceof PluginIdentifiableCommand and $command->getPlugin() === $this){
			return $command;
		}else{
			return null;
		}
	}
	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		return false;
	}
	protected function isPhar(){
        return strpos($this->file, "phar://") === 0;
	}
	public function getResource($filename){
		$filename = rtrim(str_replace(DIRECTORY_SEPARATOR, "/", $filename), "/");
		if(file_exists($this->file . "resources/" . $filename)){
			return fopen($this->file . "resources/" . $filename, "rb");
		}

		return null;
	}
	public function saveResource($filename, $replace = false){
		if(trim($filename) === ""){
			return false;
		}

		$out = $this->dataFolder . $filename;
		if(file_exists($out) && !$replace){
			return false;
		}

		if(($resource = $this->getResource($filename)) === null){
			return false;
		}

		if(!file_exists(dirname($out))){
			mkdir(dirname($out), 0755, true);
		}

		$ret = stream_copy_to_stream($resource, $fp = fopen($out, "wb")) > 0;
		fclose($fp);
		fclose($resource);
		return $ret;
	}
	public function getResources(){
		$resources = [];
		if(is_dir($this->file . "resources/")){
			foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->file . "resources/")) as $resource){
				if($resource->isFile()){
					$resources[] = $resource;
				}
			}
		}

		return $resources;
	}
	public function getConfig(){
		if(!isset($this->config)){
			$this->reloadConfig();
		}

		return $this->config;
	}
	public function saveConfig(){
		if($this->getConfig()->save() === false){
			$this->getLogger()->critical("Could not save config to " . $this->configFile);
		}
	}
	public function saveDefaultConfig(){
		if(!file_exists($this->configFile)){
			$this->saveResource("config.yml", false);
		}
	}
	public function reloadConfig(){
		if(!$this->saveDefaultConfig()){
			@mkdir($this->dataFolder);
		}
		$this->config = new Config($this->configFile);
	}
	public final function getServer(){
		return $this->server;
	}
	public final function getName(){
		return $this->description->getName();
	}
	public final function getFullName(){
		return $this->description->getFullName();
	}
	protected function getFile(){
		return $this->file;
	}
	public function getPluginLoader(){
		return $this->loader;
	}
	public function getScheduler() : TaskScheduler{
		return $this->scheduler;
	}
}