<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\permission;


interface PermissionRemovedExecutor{
	public function attachmentRemoved(PermissionAttachment $attachment);
}