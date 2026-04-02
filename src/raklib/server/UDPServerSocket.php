<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace raklib\server;

use raklib\utils\InternetAddress;
use function socket_bind;
use function socket_close;
use function socket_create;
use function socket_last_error;
use function socket_recvfrom;
use function socket_sendto;
use function socket_set_nonblock;
use function socket_set_option;
use function socket_strerror;
use function strlen;
use function trim;
use const AF_INET;
use const AF_INET6;
use const IPV6_V6ONLY;
use const SO_RCVBUF;
use const SO_SNDBUF;
use const SOCK_DGRAM;
use const SOCKET_EADDRINUSE;
use const SOL_SOCKET;
use const SOL_UDP;

class UDPServerSocket{
	protected $socket;
	private $bindAddress;

	public function __construct(InternetAddress $bindAddress){
		$this->bindAddress = $bindAddress;
		$socket = @socket_create($bindAddress->version === 4 ? AF_INET : AF_INET6, SOCK_DGRAM, SOL_UDP);
		if($socket === false){
			throw new \RuntimeException("Failed to create socket: " . trim(socket_strerror(socket_last_error())));
		}
		$this->socket = $socket;

		if($bindAddress->version === 6){
			socket_set_option($this->socket, IPPROTO_IPV6, IPV6_V6ONLY, 1);
		}

		if(@socket_bind($this->socket, $bindAddress->ip, $bindAddress->port) === true){
			$this->setSendBuffer(1024 * 1024 * 8)->setRecvBuffer(1024 * 1024 * 8);
		}else{
			$error = socket_last_error($this->socket);
			if($error === SOCKET_EADDRINUSE){
				throw new \RuntimeException("Failed to bind socket: Something else is already running on $bindAddress");
			}
			throw new \RuntimeException("Failed to bind to " . $bindAddress . ": " . trim(socket_strerror(socket_last_error($this->socket))));
		}
		socket_set_nonblock($this->socket);
	}

	public function getBindAddress() : InternetAddress{
		return $this->bindAddress;
	}
	public function getSocket(){
		return $this->socket;
	}

	public function close() : void{
		socket_close($this->socket);
	}

	public function getLastError() : int{
		return socket_last_error($this->socket);
	}
	public function readPacket(?string &$buffer, ?string &$source, ?int &$port){
		return @socket_recvfrom($this->socket, $buffer, 65535, 0, $source, $port);
	}
	public function writePacket(string $buffer, string $dest, int $port){
		return socket_sendto($this->socket, $buffer, strlen($buffer), 0, $dest, $port);
	}
	public function setSendBuffer(int $size){
		@socket_set_option($this->socket, SOL_SOCKET, SO_SNDBUF, $size);

		return $this;
	}
	public function setRecvBuffer(int $size){
		@socket_set_option($this->socket, SOL_SOCKET, SO_RCVBUF, $size);

		return $this;
	}

}
