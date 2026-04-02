<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\plugin;

use pocketmine\command\PluginCommand;
use pocketmine\command\SimpleCommandMap;
use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\event\HandlerList;
use pocketmine\event\Listener;
use pocketmine\event\Timings;
use pocketmine\event\TimingsHandler;
use pocketmine\permission\Permissible;
use pocketmine\permission\Permission;
use pocketmine\Server;
use pocketmine\utils\Utils;
use function mb_strtoupper;
class PluginManager{
	private const MAX_EVENT_CALL_DEPTH = 50;
	private $server;
	private $commandMap;
	protected $plugins = [];
	protected $permissions = [];
	protected $defaultPerms = [];
	protected $defaultPermsOp = [];
	protected $permSubs = [];
	protected $defSubs = [];
	protected $defSubsOp = [];
	protected $fileAssociations = [];
	private $eventCallDepth = 0;
	public function __construct(Server $server, SimpleCommandMap $commandMap){
		$this->server = $server;
		$this->commandMap = $commandMap;
	}
	public function getPlugin($name){
		if(isset($this->plugins[$name])){
			return $this->plugins[$name];
		}

		return null;
	}
	public function registerInterface($loaderName){
		if(is_subclass_of($loaderName, PluginLoader::class)){
			$loader = new $loaderName($this->server);
		}else{
			return false;
		}

		$this->fileAssociations[$loaderName] = $loader;

		return true;
	}
	public function getPlugins(){
		return $this->plugins;
	}
	public function loadPlugin($path, $loaders = null){
        foreach($loaders ?? $this->fileAssociations as $loader){
			if(preg_match($loader->getPluginFilters(), basename($path)) > 0){
				$description = $loader->getPluginDescription($path);
				if($description instanceof PluginDescription){
					if(($plugin = $loader->loadPlugin($path)) instanceof Plugin){
						$this->plugins[$plugin->getDescription()->getName()] = $plugin;

						$pluginCommands = $this->parseYamlCommands($plugin);

						if(count($pluginCommands) > 0){
							$this->commandMap->registerAll($plugin->getDescription()->getName(), $pluginCommands);
						}

						return $plugin;
					}
				}
			}
		}

		return null;
	}
	public function loadPlugins($directory, $newLoaders = null){
		if(is_dir($directory)){
			$plugins = [];
			$loadedPlugins = [];
			$dependencies = [];
			$softDependencies = [];
			if(is_array($newLoaders)){
				$loaders = [];
				foreach($newLoaders as $key){
					if(isset($this->fileAssociations[$key])){
						$loaders[$key] = $this->fileAssociations[$key];
					}
				}
			}else{
				$loaders = $this->fileAssociations;
			}
			foreach($loaders as $loader){
				foreach(new \RegexIterator(new \DirectoryIterator($directory), $loader->getPluginFilters()) as $file){
					if($file === "." or $file === ".."){
						continue;
					}
					$file = $directory . $file;
					if(!$loader->canLoadPlugin($file)){
						continue;
					}
					try{
						$description = $loader->getPluginDescription($file);
						if($description instanceof PluginDescription){
							$name = $description->getName();
							if(stripos($name, "pocketmine") !== false or stripos($name, "minecraft") !== false or stripos($name, "mojang") !== false){
								$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [$name, "%pocketmine.plugin.restrictedName"]));
								continue;
							}elseif(strpos($name, " ") !== false){
								$this->server->getLogger()->warning($this->server->getLanguage()->translateString("pocketmine.plugin.spacesDiscouraged", [$name]));
							}

							if(isset($plugins[$name]) or $this->getPlugin($name) instanceof Plugin){
								$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.duplicateError", [$name]));
								continue;
							}

							if(!$this->isCompatibleApi(...$description->getCompatibleApis())){
								if($this->server->loadIncompatibleAPI === true){
									$this->server->getLogger()->debug("插件{$name}的API与服务器不符,但Phantomarine仍然加载了它");
								}else{
									$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [$name, "%pocketmine.plugin.incompatibleAPI"]));
									continue;
								}
							}

							$plugins[$name] = $file;

							$softDependencies[$name] = array_merge($softDependencies[$name] ?? [], $description->getSoftDepend());
							$dependencies[$name] = $description->getDepend();

							foreach($description->getLoadBefore() as $before){
								if(isset($softDependencies[$before])){
									$softDependencies[$before][] = $name;
								}else{
									$softDependencies[$before] = [$name];
								}
							}
						}
					}catch(\Throwable $e){
						$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.fileError", [$file, $directory, $e->getMessage()]));
						$this->server->getLogger()->logException($e);
					}
				}
			}


			while(count($plugins) > 0){
				$loadedThisLoop = 0;
				foreach($plugins as $name => $file){
					if(isset($dependencies[$name])){
						foreach($dependencies[$name] as $key => $dependency){
							if(isset($loadedPlugins[$dependency]) or $this->getPlugin($dependency) instanceof Plugin){
								unset($dependencies[$name][$key]);
							}elseif(!isset($plugins[$dependency])){
								$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [$name, "%pocketmine.plugin.unknownDependency"]));
								unset($plugins[$name]);
								continue 2;
							}
						}

						if(count($dependencies[$name]) === 0){
							unset($dependencies[$name]);
						}
					}

					if(isset($softDependencies[$name])){
						foreach($softDependencies[$name] as $key => $dependency){
							if(isset($loadedPlugins[$dependency]) or $this->getPlugin($dependency) instanceof Plugin){
								$this->server->getLogger()->debug("Successfully resolved soft dependency \"$dependency\" for plugin \"$name\"");
								unset($softDependencies[$name][$key]);
							}elseif(!isset($plugins[$dependency])){
								$this->server->getLogger()->debug("Skipping resolution of missing soft dependency \"$dependency\" for plugin \"$name\"");
								unset($softDependencies[$name][$key]);
							}else{
								$this->server->getLogger()->debug("Deferring resolution of soft dependency \"$dependency\" for plugin \"$name\" (found but not loaded yet)");
							}
						}

						if(count($softDependencies[$name]) === 0){
							unset($softDependencies[$name]);
						}
					}

					if(!isset($dependencies[$name]) and !isset($softDependencies[$name])){
						unset($plugins[$name]);
						$loadedThisLoop++;
						if(($plugin = $this->loadPlugin($file, $loaders)) instanceof Plugin){
							$loadedPlugins[$name] = $plugin;
						}else{
							$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.plugin.genericLoadError", [$name]));
						}
					}
				}

				if($loadedThisLoop === 0){
					foreach($plugins as $name => $file){
						$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [$name, "%pocketmine.plugin.circularDependency"]));
					}
					$plugins = [];
				}
			}

			return $loadedPlugins;
		}else{
			return [];
		}
	}
	public function isCompatibleApi(string ...$versions) : bool{
		$serverString = $this->server->getApiVersion();
		$serverApi = array_pad(explode("-", $serverString, 2), 2, "");
		$serverNumbers = array_map("intval", explode(".", $serverApi[0]));

		foreach($versions as $version){
			if($version !== $serverString){
				$pluginApi = array_pad(explode("-", $version, 2), 2, "");

				if(mb_strtoupper($pluginApi[1]) !== mb_strtoupper($serverApi[1])){
					continue;
				}

				$pluginNumbers = array_map("intval", array_pad(explode(".", $pluginApi[0]), 3, "0"));

				if($pluginNumbers[0] !== $serverNumbers[0]){
					continue;
				}

				if($pluginNumbers[1] > $serverNumbers[1]){
					continue;
				}

				if($pluginNumbers[2] > $serverNumbers[2]){
					continue;
				}
			}

			return true;
		}

		return false;
	}
	public function getPermission($name){
		return $this->permissions[$name] ?? null;
	}
	public function addPermission(Permission $permission){
		if(!isset($this->permissions[$permission->getName()])){
			$this->permissions[$permission->getName()] = $permission;
			$this->calculatePermissionDefault($permission);

			return true;
		}

		return false;
	}
	public function removePermission($permission){
		if($permission instanceof Permission){
			unset($this->permissions[$permission->getName()]);
		}else{
			unset($this->permissions[$permission]);
		}
	}
	public function getDefaultPermissions($op){
		if($op === true){
			return $this->defaultPermsOp;
		}else{
			return $this->defaultPerms;
		}
	}
	public function recalculatePermissionDefaults(Permission $permission){
		if(isset($this->permissions[$permission->getName()])){
			unset($this->defaultPermsOp[$permission->getName()]);
			unset($this->defaultPerms[$permission->getName()]);
			$this->calculatePermissionDefault($permission);
		}
	}
	private function calculatePermissionDefault(Permission $permission){
		Timings::$permissionDefaultTimer->startTiming();
		if($permission->getDefault() === Permission::DEFAULT_OP or $permission->getDefault() === Permission::DEFAULT_TRUE){
			$this->defaultPermsOp[$permission->getName()] = $permission;
			$this->dirtyPermissibles(true);
		}

		if($permission->getDefault() === Permission::DEFAULT_NOT_OP or $permission->getDefault() === Permission::DEFAULT_TRUE){
			$this->defaultPerms[$permission->getName()] = $permission;
			$this->dirtyPermissibles(false);
		}
		Timings::$permissionDefaultTimer->stopTiming();
	}
	private function dirtyPermissibles($op){
		foreach($this->getDefaultPermSubscriptions($op) as $p){
			$p->recalculatePermissions();
		}
	}
	public function subscribeToPermission($permission, Permissible $permissible){
		if(!isset($this->permSubs[$permission])){
			$this->permSubs[$permission] = [];
		}
		$this->permSubs[$permission][spl_object_hash($permissible)] = $permissible;
	}
	public function unsubscribeFromPermission($permission, Permissible $permissible){
		if(isset($this->permSubs[$permission])){
			unset($this->permSubs[$permission][spl_object_hash($permissible)]);
			if(count($this->permSubs[$permission]) === 0){
				unset($this->permSubs[$permission]);
			}
		}
	}
	public function unsubscribeFromAllPermissions(Permissible $permissible) : void{
		foreach($this->permSubs as $permission => &$subs){
			unset($subs[spl_object_hash($permissible)]);
			if(empty($subs)){
				unset($this->permSubs[$permission]);
			}
		}
	}
	public function getPermissionSubscriptions($permission){
		return $this->permSubs[$permission] ?? [];
	}
	public function subscribeToDefaultPerms($op, Permissible $permissible){
		if($op === true){
			$this->defSubsOp[spl_object_hash($permissible)] = $permissible;
		}else{
			$this->defSubs[spl_object_hash($permissible)] = $permissible;
		}
	}
	public function unsubscribeFromDefaultPerms($op, Permissible $permissible){
		if($op === true){
			unset($this->defSubsOp[spl_object_hash($permissible)]);
		}else{
			unset($this->defSubs[spl_object_hash($permissible)]);
		}
	}
	public function getDefaultPermSubscriptions($op){
		if($op === true){
			return $this->defSubsOp;
		}

		return $this->defSubs;
	}
	public function getPermissions(){
		return $this->permissions;
	}
	public function isPluginEnabled(Plugin $plugin){
		return isset($this->plugins[$plugin->getDescription()->getName()]) and $plugin->isEnabled();
	}
	public function enablePlugin(Plugin $plugin){
		if(!$plugin->isEnabled()){
			try{
				foreach($plugin->getDescription()->getPermissions() as $perm){
					$this->addPermission($perm);
				}
				$plugin->getScheduler()->setEnabled(true);
				$plugin->getPluginLoader()->enablePlugin($plugin);
			}catch(\Throwable $e){
				$this->server->getLogger()->logException($e);
				$this->disablePlugin($plugin);
			}
		}
	}
	protected function parseYamlCommands(Plugin $plugin){
		$pluginCmds = [];

		foreach($plugin->getDescription()->getCommands() as $key => $data){
			if(strpos($key, ":") !== false){
				$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.plugin.commandError", [$key, $plugin->getDescription()->getFullName()]));
				continue;
			}
			if(is_array($data)){
				$newCmd = new PluginCommand($key, $plugin);
				if(isset($data["description"])){
					$newCmd->setDescription($data["description"]);
				}

				if(isset($data["usage"])){
					$newCmd->setUsage($data["usage"]);
				}

				if(isset($data["aliases"]) and is_array($data["aliases"])){
					$aliasList = [];
					foreach($data["aliases"] as $alias){
						if(strpos($alias, ":") !== false){
							$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.plugin.aliasError", [$alias, $plugin->getDescription()->getFullName()]));
							continue;
						}
						$aliasList[] = $alias;
					}

					$newCmd->setAliases($aliasList);
				}

				if(isset($data["permission"])){
					$newCmd->setPermission($data["permission"]);
				}

				if(isset($data["permission-message"])){
					$newCmd->setPermissionMessage($data["permission-message"]);
				}

				$pluginCmds[] = $newCmd;
			}
		}

		return $pluginCmds;
	}

	public function disablePlugins(){
		foreach($this->getPlugins() as $plugin){
			$this->disablePlugin($plugin);
		}
	}
	public function disablePlugin(Plugin $plugin){
		if($plugin->isEnabled()){
			try{
				$plugin->getPluginLoader()->disablePlugin($plugin);
			}catch(\Throwable $e){
				$this->server->getLogger()->logException($e);
			}

			$plugin->getScheduler()->shutdown();
			HandlerList::unregisterAll($plugin);
			foreach($plugin->getDescription()->getPermissions() as $perm){
				$this->removePermission($perm);
			}
		}
	}

	public function tickSchedulers(int $currentTick) : void{
		foreach($this->plugins as $p){
			if($p->isEnabled()){
				$p->getScheduler()->mainThreadHeartbeat($currentTick);
			}
		}
	}

	public function clearPlugins(){
		$this->disablePlugins();
		$this->plugins = [];
		$this->fileAssociations = [];
		$this->permissions = [];
		$this->defaultPerms = [];
		$this->defaultPermsOp = [];
	}
	public function callEvent(Event $event){
		if($this->eventCallDepth >= self::MAX_EVENT_CALL_DEPTH){
			throw new \RuntimeException("Recursive event call detected (reached max depth of " . self::MAX_EVENT_CALL_DEPTH . " calls)");
		}

		++$this->eventCallDepth;
		foreach($event->getHandlers()->getRegisteredListeners() as $registration){
			if(!$registration->getPlugin()->isEnabled()){
				continue;
			}

			$registration->callEvent($event);
		}
		--$this->eventCallDepth;
	}
	public function registerEvents(Listener $listener, Plugin $plugin){
		if(!$plugin->isEnabled()){
			throw new PluginException("Plugin attempted to register " . get_class($listener) . " while not enabled");
		}

		$reflection = new \ReflectionClass(get_class($listener));
		foreach($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method){
			if(!$method->isStatic() and $method->getDeclaringClass()->implementsInterface(Listener::class)){
				$tags = Utils::parseDocComment((string) $method->getDocComment());
				if(isset($tags["notHandler"])){
					continue;
				}

				$parameters = $method->getParameters();
				if(count($parameters) !== 1){
					continue;
				}
				try{
					$paramType = $parameters[0]->getType();
					if($paramType instanceof \ReflectionNamedType && !$paramType->isBuiltin()){
						$paramClass = $paramType->getName();
						$eventClass = new \ReflectionClass($paramClass);
					}else{
						$eventClass = null;
					}
				}catch(\ReflectionException $e){
					if(isset($tags["softDepend"]) && !isset($this->plugins[$tags["softDepend"]])){
						$this->server->getLogger()->debug("Not registering @softDepend listener " . get_class($listener) . "::" . $method->getName() . "() because plugin \"" . $tags["softDepend"] . "\" not found");
						continue;
					}

					throw $e;
				}
				if($eventClass === null or !$eventClass->isSubclassOf(Event::class)){
					continue;
				}

				try{
					$priority = isset($tags["priority"]) ? EventPriority::fromString($tags["priority"]) : EventPriority::NORMAL;
				}catch(\InvalidArgumentException $e){
					throw new PluginException("Event handler " . get_class($listener) . "->" . $method->getName() . "() declares invalid/unknown priority \"" . $tags["priority"] . "\"");
				}

				$ignoreCancelled = false;
				if(isset($tags["ignoreCancelled"])){
					switch(strtolower($tags["ignoreCancelled"])){
						case "true":
						case "":
							$ignoreCancelled = true;
							break;
						case "false":
							$ignoreCancelled = false;
							break;
						default:
							throw new PluginException("Event handler " . get_class($listener) . "->" . $method->getName() . "() declares invalid @ignoreCancelled value \"" . $tags["ignoreCancelled"] . "\"");
					}
				}

				$this->registerEvent($eventClass->getName(), $listener, $priority, new MethodEventExecutor($method->getName()), $plugin, $ignoreCancelled);
			}
		}
	}
	public function registerEvent($event, Listener $listener, $priority, EventExecutor $executor, Plugin $plugin, $ignoreCancelled = false){
		if(!is_subclass_of($event, Event::class)){
			throw new PluginException($event . " is not an Event");
		}
		$class = new \ReflectionClass($event);
		if($class->isAbstract()){
			throw new PluginException($event . " is an abstract Event");
		}
		if($class->getProperty("handlerList")->getDeclaringClass()->getName() !== $event){
			throw new PluginException($event . " does not have a handler list");
		}

		if(!$plugin->isEnabled()){
			throw new PluginException("Plugin attempted to register " . $event . " while not enabled");
		}

		$timings = new TimingsHandler("Plugin: " . $plugin->getDescription()->getFullName() . " Event: " . get_class($listener) . "::" . ($executor instanceof MethodEventExecutor ? $executor->getMethod() : "???") . "(" . (new \ReflectionClass($event))->getShortName() . ")");

		$this->getEventListeners($event)->register(new RegisteredListener($listener, $executor, $priority, $plugin, $ignoreCancelled, $timings));
	}
	private function getEventListeners($event){
		if($event::$handlerList === null){
			$event::$handlerList = new HandlerList();
		}

		return $event::$handlerList;
	}
}