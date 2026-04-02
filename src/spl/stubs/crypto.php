<?php

namespace Crypto{
	class Cipher {
		const MODE_ECB = 1;
		const MODE_CBC = 2;
		const MODE_CFB = 3;
		const MODE_OFB = 4;
		const MODE_CTR = 5;
		const MODE_GCM = 6;
		const MODE_CCM = 7;
		const MODE_XTS = 65537;
		public static function getAlgorithms($aliases = false, $prefix = null) {}
		public static function hasAlgorithm($algorithm) {}
		public static function hasMode($mode) {}
		public static function __callStatic($name, $arguments) {}
		public function __construct($algorithm, $mode = NULL, $key_size = NULL) {}
		public function getAlgorithmName() {}
		public function encryptInit($key, $iv = null) {}
		public function encryptUpdate($data) {}
		public function encryptFinish() {}
		public function encrypt($data, $key, $iv = null) {}
		public function decryptInit($key, $iv = null) {}
		public function decryptUpdate($data) {}
		public function decryptFinish() {}
		public function decrypt($data, $key, $iv = null) {}
		public function getBlockSize() {}
		public function getKeyLength() {}
		public function getIVLength() {}
		public function getMode() {}
		public function getTag() {}
		public function setTag($tag) {}
		public function setTagLength($tag_length) {}
		public function setAAD($aad) {}

	}
	class CipherException extends \Exception {
		const ALGORITHM_NOT_FOUND = 1;
		const STATIC_METHOD_NOT_FOUND = 2;
		const STATIC_METHOD_TOO_MANY_ARGS = 3;
		const MODE_NOT_FOUND = 4;
		const MODE_NOT_AVAILABLE = 5;
		const AUTHENTICATION_NOT_SUPPORTED = 6;
		const KEY_LENGTH_INVALID = 7;
		const IV_LENGTH_INVALID = 8;
		const AAD_SETTER_FORBIDDEN = 9;
		const AAD_SETTER_FAILED = 10;
		const AAD_LENGTH_HIGH = 11;
		const TAG_GETTER_FORBIDDEN = 12;
		const TAG_SETTER_FORBIDDEN = 13;
		const TAG_GETTER_FAILED = 14;
		const TAG_SETTER_FAILED = 15;
		const TAG_LENGTH_SETTER_FORBIDDEN = 16;
		const TAG_LENGTH_LOW = 17;
		const TAG_LENGTH_HIGH = 18;
		const TAG_VERIFY_FAILED = 19;
		const INIT_ALG_FAILED = 20;
		const INIT_CTX_FAILED = 21;
		const INIT_ENCRYPT_FORBIDDEN = 22;
		const INIT_DECRYPT_FORBIDDEN = 23;
		const UPDATE_FAILED = 24;
		const UPDATE_ENCRYPT_FORBIDDEN = 25;
		const UPDATE_DECRYPT_FORBIDDEN = 26;
		const FINISH_FAILED = 27;
		const FINISH_ENCRYPT_FORBIDDEN = 28;
		const FINISH_DECRYPT_FORBIDDEN = 29;
		const INPUT_DATA_LENGTH_HIGH = 30;

	}
	class Hash {
		public static function getAlgorithms($aliases = false, $prefix = null) {}
		public static function hasAlgorithm($algorithm) {}
		public static function __callStatic($name, $arguments) {}
		public function __construct($algorithm) {}
		public function getAlgorithmName() {}
		public function update($data) {}
		public function digest() {}
		public function hexdigest() {}
		public function getBlockSize() {}
		public function getSize() {}

	}
	class HashException extends \Exception {
		const HASH_ALGORITHM_NOT_FOUND = 1;
		const STATIC_METHOD_NOT_FOUND = 2;
		const STATIC_METHOD_TOO_MANY_ARGS = 3;
		const INIT_FAILED = 4;
		const UPDATE_FAILED = 5;
		const DIGEST_FAILED = 6;
		const INPUT_DATA_LENGTH_HIGH = 7;

	}
	abstract class MAC extends Hash {
		public function __construct($algorithm, $key) {}

	}
	class MACException extends HashException {
		const MAC_ALGORITHM_NOT_FOUND = 1;
		const KEY_LENGTH_INVALID = 2;

	}
	class HMAC extends MAC {
	}
	class CMAC extends MAC {
	}
	abstract class KDF {
		public function __construct($length, $salt = NULL) {}
		public function getLength() {}
		public function setLength($length) {}
		public function getSalt() {}
		public function setSalt($salt) {}

	}
	class KDFException {
		const KEY_LENGTH_LOW = 1;
		const KEY_LENGTH_HIGH = 2;
		const SALT_LENGTH_HIGH = 3;
		const PASSWORD_LENGTH_INVALID = 4;
		const DERIVATION_FAILED = 5;

	}
	class PBKDF2 extends KDF {
		public function __construct($hashAlgorithm, $length, $salt = NULL, $iterations = 1000) {}
		public function derive($password) {}
		public function getIterations() {}
		public function setIterations($iterations) {}
		public function getHashAlgorithm() {}
		public function setHashAlgorithm($hashAlgorithm) {}

	}
	class PBKDF2Exception extends KDFException {
		const HASH_ALGORITHM_NOT_FOUND = 1;
		const ITERATIONS_HIGH = 2;

	}
	class Base64 {
		public function encode($data) {}
		public function decode($data) {}
		public function __construct() {}
		public function encodeUpdate($data) {}
		public function encodeFinish() {}
		public function decodeUpdate($data) {}
		public function decodeFinish() {}

	}
	class Base64Exception extends \Exception {
		const ENCODE_UPDATE_FORBIDDEN = 1;
		const ENCODE_FINISH_FORBIDDEN = 2;
		const DECODE_UPDATE_FORBIDDEN = 3;
		const DECODE_FINISH_FORBIDDEN = 4;
		const DECODE_UPDATE_FAILED = 5;
		const INPUT_DATA_LENGTH_HIGH = 6;

	}
	class Rand {
		public static function generate($num, $must_be_strong = true, &$returned_strong_result = true) {}
		public static function seed($buf, $entropy = (float) strlen($buf)) {}
		public static function cleanup() {}
		public static function loadFile($filename, $max_bytes = -1) {}
		public static function writeFile($filename) {}

	}
	class RandException extends \Exception {
		const GENERATE_PREDICTABLE = 1;
		const FILE_WRITE_PREDICTABLE = 2;
		const REQUESTED_BYTES_NUMBER_TOO_HIGH = 3;
		const SEED_LENGTH_TOO_HIGH = 4;

	}
}