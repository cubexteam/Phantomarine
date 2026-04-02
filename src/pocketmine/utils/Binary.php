<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\utils;

use InvalidArgumentException;
use function chr;
use function ord;
use function pack;
use function preg_replace;
use function round;
use function sprintf;
use function substr;
use function unpack;
use const PHP_INT_MAX;

class Binary{
	public static function signByte(int $value) : int{
		return $value << 56 >> 56;
	}

	public static function unsignByte(int $value) : int{
		return $value & 0xff;
	}

	public static function signShort(int $value) : int{
		return $value << 48 >> 48;
	}

	public static function unsignShort(int $value) : int{
		return $value & 0xffff;
	}

	public static function signInt(int $value) : int{
		return $value << 32 >> 32;
	}

	public static function unsignInt(int $value) : int{
		return $value & 0xffffffff;
	}

	public static function flipShortEndianness(int $value) : int{
		return self::readLShort(self::writeShort($value));
	}

	public static function flipIntEndianness(int $value) : int{
		return self::readLInt(self::writeInt($value));
	}

	public static function flipLongEndianness(int $value) : int{
		return self::readLLong(self::writeLong($value));
	}
	private static function safeUnpack(string $formatCode, string $bytes) : array{
		$result = unpack($formatCode, $bytes);
		if($result === false){
			throw new BinaryDataException("Invalid input data (not enough?)");
		}
		return $result;
	}
	public static function readBool(string $b) : bool{
		return $b[0] !== "\x00";
	}
	public static function writeBool(bool $b) : string{
		return $b ? "\x01" : "\x00";
	}
	public static function readByte(string $c){
		if($c === ""){
			throw new BinaryDataException("Expected a string of length 1");
		}
		return ord($c[0]);
	}
	public static function readSignedByte(string $c) : int{
		if($c === ""){
			throw new BinaryDataException("Expected a string of length 1");
		}
		return self::signByte(ord($c[0]));
	}
	public static function writeByte(int $c) : string{
		return chr($c);
	}
	public static function readShort($str){
		return self::safeUnpack("n", $str)[1];
	}
	public static function readSignedShort($str){
		return self::signShort(self::safeUnpack("n", $str)[1]);
	}
	public static function writeShort($value){
		return pack("n", $value);
	}
	public static function readLShort($str){
		return self::safeUnpack("v", $str)[1];
	}
	public static function readSignedLShort($str){
		return self::signShort(self::safeUnpack("v", $str)[1]);
	}
	public static function writeLShort($value){
		return pack("v", $value);
	}
	public static function readTriad(string $str) : int{
		return self::safeUnpack("N", "\x00" . $str)[1];
	}
	public static function writeTriad(int $value) : string{
		return substr(pack("N", $value), 1);
	}
	public static function readLTriad(string $str) : int{
		return self::safeUnpack("V", $str . "\x00")[1];
	}
	public static function writeLTriad(int $value) : string{
		return substr(pack("V", $value), 0, -1);
	}
	public static function readInt(string $str) : int{
		return self::signInt(self::safeUnpack("N", $str)[1]);
	}
	public static function writeInt(int $value) : string{
		return pack("N", $value);
	}
	public static function readLInt($str){
		return self::signInt(self::safeUnpack("V", $str)[1]);
	}
	public static function writeLInt($value){
		return pack("V", $value);
	}
	public static function readFloat($str){
		return self::safeUnpack("G", $str)[1];
	}
	public static function readRoundedFloat(string $str, int $accuracy) : float{
		return round(self::readFloat($str), $accuracy);
	}
	public static function writeFloat($value){
		return pack("G", $value);
	}
	public static function readLFloat($str){
		return self::safeUnpack("g", $str)[1];
	}
	public static function readRoundedLFloat(string $str, int $accuracy) : float{
		return round(self::readLFloat($str), $accuracy);
	}
	public static function writeLFloat($value){
		return pack("g", $value);
	}
	public static function printFloat($value) : string{
		return preg_replace("/(\\.\\d+?)0+$/", "$1", sprintf("%F", $value));
	}
	public static function readDouble($str){
		return self::safeUnpack("E", $str)[1];
	}
	public static function writeDouble($value){
		return pack("E", $value);
	}
	public static function readLDouble($str){
		return self::safeUnpack("e", $str)[1];
	}
	public static function writeLDouble($value){
		return pack("e", $value);
	}
	public static function readLong($str){
		return self::safeUnpack("J", $str)[1];
	}
	public static function writeLong($value){
		return pack("J", $value);
	}
	public static function readLLong($str){
		return self::safeUnpack("P", $str)[1];
	}
	public static function writeLLong($value){
		return pack("P", $value);
	}
	public static function readVarInt(string $buffer, int &$offset) : int{
		$raw = self::readUnsignedVarInt($buffer, $offset);
		$temp = ((($raw << 63) >> 63) ^ $raw) >> 1;
		return $temp ^ ($raw & (1 << 63));
	}
	public static function readUnsignedVarInt(string $buffer, int &$offset) : int{
		$value = 0;
		for($i = 0; $i <= 28; $i += 7){
			if(!isset($buffer[$offset])){
				throw new BinaryDataException("No bytes left in buffer");
			}
			$b = ord($buffer[$offset++]);
			$value |= (($b & 0x7f) << $i);

			if(($b & 0x80) === 0){
				return $value;
			}
		}

		throw new BinaryDataException("VarInt did not terminate after 5 bytes!");
	}
	public static function writeVarInt($v){
		$v = ($v << 32 >> 32);
		return self::writeUnsignedVarInt(($v << 1) ^ ($v >> 31));
	}
	public static function writeUnsignedVarInt($value){
		$buf = "";
		$remaining = $value & 0xffffffff;
		for($i = 0; $i < 5; ++$i){
			if(($remaining >> 7) !== 0){
				$buf .= chr($remaining | 0x80);
			}else{
				$buf .= chr($remaining & 0x7f);
				return $buf;
			}

			$remaining = (($remaining >> 7) & (PHP_INT_MAX >> 6));
		}

		throw new InvalidArgumentException("Value too large to be encoded as a VarInt");
	}
	public static function readVarLong(string $buffer, int &$offset){
		$raw = self::readUnsignedVarLong($buffer, $offset);
		$temp = ((($raw << 63) >> 63) ^ $raw) >> 1;
		return $temp ^ ($raw & (1 << 63));
	}
	public static function readUnsignedVarLong(string $buffer, int &$offset){
		$value = 0;
		for($i = 0; $i <= 63; $i += 7){
			if(!isset($buffer[$offset])){
				throw new BinaryDataException("No bytes left in buffer");
			}
			$b = ord($buffer[$offset++]);
			$value |= (($b & 0x7f) << $i);

			if(($b & 0x80) === 0){
				return $value;
			}
		}

		throw new BinaryDataException("VarLong did not terminate after 10 bytes!");
	}
	public static function writeVarLong($v){
		return self::writeUnsignedVarLong(($v << 1) ^ ($v >> 63));
	}
	public static function writeUnsignedVarLong($value) : string{
		$buf = "";
		$remaining = $value;
		for($i = 0; $i < 10; ++$i){
			if(($remaining >> 7) !== 0){
				$buf .= chr($remaining | 0x80);
			}else{
				$buf .= chr($remaining & 0x7f);
				return $buf;
			}

			$remaining = (($remaining >> 7) & (PHP_INT_MAX >> 6));
		}

		throw new InvalidArgumentException("Value too large to be encoded as a VarLong");
	}
}