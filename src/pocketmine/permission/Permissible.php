<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\permission;

use pocketmine\plugin\Plugin;

interface Permissible extends ServerOperator{
	public function isPermissionSet($name);
	public function hasPermission($name);
	public function addAttachment(Plugin $plugin, $name = null, $value = null);
	public function removeAttachment(PermissionAttachment $attachment);
	public function recalculatePermissions();
	public function getEffectivePermissions();

}