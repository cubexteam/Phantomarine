<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network;

use InvalidStateException;
use pocketmine\event\server\NetworkInterfaceRegisterEvent;
use pocketmine\event\server\NetworkInterfaceUnregisterEvent;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\BinaryStream;
use UnexpectedValueException;
use function spl_object_hash;
use function strlen;
use function substr;
use function zlib_decode;

class Network{
	public static $BATCH_THRESHOLD = 512;
	private $packetPool;
	private $server;
	private $interfaces = [];
	private $advancedInterfaces = [];

	private $upload = 0;
	private $download = 0;

	private $name;
	public $block = [];
	private $iptables;
	public function __construct(Server $server, bool $iptables = false){
		$this->packetPool = new PacketPool();

		$this->server = $server;
		$this->iptables = $iptables;
	}
	public function addStatistics($upload, $download){
		$this->upload += $upload;
		$this->download += $download;
	}
	public function getUpload(){
		return $this->upload;
	}
	public function getDownload(){
		return $this->download;
	}

	public function resetStatistics(){
		$this->upload = 0;
		$this->download = 0;
	}
	public function getInterfaces(){
		return $this->interfaces;
	}

	public function processInterfaces(){
		foreach($this->interfaces as $interface){
			$interface->process();
		}
	}
	public function processInterface(SourceInterface $interface) : void{
		$interface->process();
	}
	public function registerInterface(SourceInterface $interface){
		$this->server->getPluginManager()->callEvent($ev = new NetworkInterfaceRegisterEvent($interface));
		if(!$ev->isCancelled()){
			$interface->start();
			$this->interfaces[$hash = spl_object_hash($interface)] = $interface;
			if($interface instanceof AdvancedSourceInterface){
				$this->advancedInterfaces[$hash] = $interface;
				$interface->setNetwork($this);
			}
			$interface->setName($this->name);
		}
	}
	public function unregisterInterface(SourceInterface $interface){
		$this->unblockAllAddresses();

		$this->server->getPluginManager()->callEvent(new NetworkInterfaceUnregisterEvent($interface));
		unset($this->interfaces[$hash = spl_object_hash($interface)], $this->advancedInterfaces[$hash]);
	}
	public function setName($name){
		$this->name = (string) $name;
		foreach($this->interfaces as $interface){
			$interface->setName($this->name);
		}
	}

	public function getName(){
		return $this->name;
	}

	public function updateName(){
		foreach($this->interfaces as $interface){
			$interface->setName($this->name);
		}
	}
	public function registerPacket(int $id, string $class){
		$this->packetPool->register($id, $class);
	}
	public function getServer(){
		return $this->server;
	}
	public function processBatch(BatchPacket $packet, Player $player){
		$rawLen = strlen($packet->payload);
		if($rawLen === 0){
			throw new \InvalidArgumentException("BatchPacket payload is empty or packet decode error");
		}elseif($rawLen < 3){
			throw new \InvalidArgumentException("Not enough bytes, expected zlib header");
		}

		$str = zlib_decode($packet->payload, 1024 * 1024 * 2);
        if($str === ""){
			throw new InvalidStateException("Decoded BatchPacket payload is empty");
		}

		$stream = new BinaryStream($str);

		$count = 0;

		$limit_map = 
		[
			0xfe => 0,
			0x45 => 30,
			0x2a => 30,
			0x2c => 100,
			0x09 => 30,
			0x14 => 30,
			0x01 => 1,
			0x08 => 1,
			0x3c => 30,
			0x15 => 30,
			0x21 => 1024,
			0x38 => 20,
			0x2e => 30,
			0x19 => 40,
			0x1c => 20,
			0x2d => 10,
			0x36 => 20,
			0x4f => 20,
			0x0c => 1,
			0x18 => 1
		];

		$address = $player->getAddress();
		while(!$stream->feof()){
			if ($this->isBlockedAddress($address)) {
				throw new InvalidStateException("BatchPacket from a blocked player (IP), skip processing");
			}
			if($count++ >= 1300){
				throw new UnexpectedValueException("Too many packets in a single batch");
			}

			$buf = $stream->getString();

			if (isset($buf[0])){
				$pid = ord($buf[0]);
				if(!isset($limit_map[$pid])){
					$limit_map[$pid] = 100;
				}

				if(isset($limit_map[$pid]) and $limit_map[$pid]-- <= 0){
					if($pid === 0x15){
						throw new InvalidStateException("Possible attack detected from " . $address);
					}
					return;
				}
			}

			if(($pk = $this->getPacket(ord($buf[0]))) !== null){
				if(!$pk->canBeBatched()){
					throw new UnexpectedValueException("Received invalid " . get_class($pk) . " inside BatchPacket");
				}

				$pk->setBuffer($buf, 1);

				$pk->decode();
				if(!$pk->feof() and !$pk->mayHaveUnreadBytes()){
					$remains = substr($pk->buffer, $pk->offset);
					$this->server->getLogger()->debug("Still " . strlen($remains) . " bytes unread in " . $pk->getName() . ": 0x" . bin2hex($remains));
				}
				$player->handleDataPacket($pk);
			}
		}
	}
	public function getPacket($id){
		return $this->packetPool->get($id);
	}
	public function sendPacket($address, $port, $payload){
		foreach($this->advancedInterfaces as $interface){
			$interface->sendRawPacket($address, $port, $payload);
		}
	}
	public function blockAddress($address, $timeout = 300){
		$final = time() + $timeout;
		if(!isset($this->block[$address]) or $timeout === -1){
			if($timeout === -1){
				$final = PHP_INT_MAX;
			}
			$this->block[$address] = $final;
		}elseif($this->block[$address] < $final){
			$this->block[$address] = $final;
		}

		foreach($this->advancedInterfaces as $interface){
			$interface->blockAddress($address, $timeout);
		}

		if($this->iptables){
			if(filter_var($address, FILTER_VALIDATE_IP)){
				shell_exec("iptables -A INPUT -s " . escapeshellarg($address) . " -j REJECT");
			}
		}
	}
	public function unblockAddress($address){
		foreach($this->advancedInterfaces as $interface){
			$interface->unblockAddress($address);
		}

		unset($this->block[$address]);

		if($this->iptables){
			if(filter_var($address, FILTER_VALIDATE_IP)){
				shell_exec("iptables -D INPUT -s " . escapeshellarg($address) . " -j REJECT");
			}
		}
	}
	public function unblockAllAddresses() : void{
		foreach($this->getAllBlockedAddress() as $ip){
			$this->unblockAddress($ip);
		}
	}
	public function isBlockedAddress(string $address) : bool{
		if(isset($this->block[$address])){
			return true;
		}else{
			return false;
		}
	}
	public function getAllBlockedAddress() : array{
		return $this->block;
	}
	public function verifyPlayer(Player $player) : bool{
		return true;
	}
}
