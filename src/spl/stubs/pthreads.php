<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */
define('PTHREADS_INHERIT_ALL', 0x111111);
define('PTHREADS_INHERIT_NONE', 0);
define('PTHREADS_INHERIT_INI', 0x1);
define('PTHREADS_INHERIT_CONSTANTS', 0x10);
define('PTHREADS_INHERIT_CLASSES', 0x100);
define('PTHREADS_INHERIT_FUNCTIONS', 0x100);
define('PTHREADS_INHERIT_INCLUDES', 0x10000);
define('PTHREADS_INHERIT_COMMENTS', 0x100000);
define('PTHREADS_ALLOW_HEADERS', 0x1000000);
class Threaded implements Traversable, Countable, ArrayAccess, Collectable{
	public function addRef(){}
	public function chunk($size, $preserve = false){}
	public function count(){}
	public function delRef(){}
	public static function extend($class){}
	public function getRefCount(){}
	public function isGarbage() : bool{}
	public function isRunning(){}
	public function isTerminated(){}
	public function merge($from, $overwrite = true){}
	public function notify(){}
	public function notifyOne(){}
	public function offsetGet($offset){}
	public function offsetSet($offset, $value){}
	public function offsetExists($offset){}
	public function offsetUnset($offset){}
	public function pop(){}
	public function run(){}
	public function shift(){}
	public function synchronized(\Closure $function, ...$args){}
	public function wait(int $timeout = 0){}
}
class Volatile extends Threaded{
}
class Thread extends Threaded{
	public function getCreatorId(){}
	public static function getCurrentThread(){}
	public static function getCurrentThreadId(){}
	public function getThreadId(){}
	public function isJoined(){}
	public function isStarted(){}
	public function join(){}
	public function start(int $options = PTHREADS_INHERIT_ALL){}
}
class Worker extends Thread{
	public function collect(callable $function = null){}
	public function collector(Collectable $collectable){}
	public function getStacked(){}
	public function isShutdown(){}
	public function shutdown(){}
	public function stack(Threaded $work){}
	public function unstack(){}
}
class Pool{
	protected $size;
	protected $class;
	protected $workers;
	protected $ctor;
	protected $last;
	public function __construct(int $size, string $class = Worker::class, array $ctor = array()){}
	public function collect(callable $collector = null){}
	public function resize(int $size){}
	public function shutdown(){}
	public function submit(Threaded $task){}
	public function submitTo(int $worker, Threaded $task){}
}
interface Collectable{
	public function isGarbage() : bool;
}

class Socket extends \Threaded{
	public const AF_UNIX = 1;
	public const AF_INET = 2;
	public const AF_INET6 = 10;
	public const SOCK_STREAM = 1;
	public const SOCK_DGRAM = 2;
	public const SOCK_RAW = 3;
	public const SOCK_SEQPACKET = 5;
	public const SOCK_RDM = 4;
	public const SO_DEBUG = 1;
	public const SO_REUSEADDR = 2;
	public const SO_REUSEPORT = 15;
	public const SO_KEEPALIVE = 9;
	public const SO_DONTROUTE = 5;
	public const SO_LINGER = 13;
	public const SO_BROADCAST = 6;
	public const SO_OOBINLINE = 10;
	public const SO_SNDBUF = 7;
	public const SO_RCVBUF = 8;
	public const SO_SNDLOWAT = 19;
	public const SO_RCVLOWAT = 18;
	public const SO_SNDTIMEO = 21;
	public const SO_RCVTIMEO = 20;
	public const SO_TYPE = 3;
	public const SO_ERROR = 4;
	public const SO_BINDTODEVICE = 25;
	public const SOMAXCONN = 128;
	public const TCP_NODELAY = 1;
	public const SOL_SOCKET = 1;
	public const SOL_TCP = 6;
	public const SOL_UDP = 17;
	public const MSG_OOB = 1;
	public const MSG_WAITALL = 256;
	public const MSG_CTRUNC = 8;
	public const MSG_TRUNC = 32;
	public const MSG_PEEK = 2;
	public const MSG_DONTROUTE = 4;
	public const MSG_EOR = 128;
	public const MSG_CONFIRM = 2048;
	public const MSG_ERRQUEUE = 8192;
	public const MSG_NOSIGNAL = 16384;
	public const MSG_MORE = 32768;
	public const MSG_WAITFORONE = 65536;
	public const MSG_CMSG_CLOEXEC = 1073741824;
	public const EPERM = 1;
	public const ENOENT = 2;
	public const EINTR = 4;
	public const EIO = 5;
	public const ENXIO = 6;
	public const E2BIG = 7;
	public const EBADF = 9;
	public const EAGAIN = 11;
	public const ENOMEM = 12;
	public const EACCES = 13;
	public const EFAULT = 14;
	public const ENOTBLK = 15;
	public const EBUSY = 16;
	public const EEXIST = 17;
	public const EXDEV = 18;
	public const ENODEV = 19;
	public const ENOTDIR = 20;
	public const EISDIR = 21;
	public const EINVAL = 22;
	public const ENFILE = 23;
	public const EMFILE = 24;
	public const ENOTTY = 25;
	public const ENOSPC = 28;
	public const ESPIPE = 29;
	public const EROFS = 30;
	public const EMLINK = 31;
	public const EPIPE = 32;
	public const ENAMETOOLONG = 36;
	public const ENOLCK = 37;
	public const ENOSYS = 38;
	public const ENOTEMPTY = 39;
	public const ELOOP = 40;
	public const EWOULDBLOCK = 11;
	public const ENOMSG = 42;
	public const EIDRM = 43;
	public const ECHRNG = 44;
	public const EL2NSYNC = 45;
	public const EL3HLT = 46;
	public const EL3RST = 47;
	public const ELNRNG = 48;
	public const EUNATCH = 49;
	public const ENOCSI = 50;
	public const EL2HLT = 51;
	public const EBADE = 52;
	public const EBADR = 53;
	public const EXFULL = 54;
	public const ENOANO = 55;
	public const EBADRQC = 56;
	public const EBADSLT = 57;
	public const ENOSTR = 60;
	public const ENODATA = 61;
	public const ETIME = 62;
	public const ENOSR = 63;
	public const ENONET = 64;
	public const EREMOTE = 66;
	public const ENOLINK = 67;
	public const EADV = 68;
	public const ESRMNT = 69;
	public const ECOMM = 70;
	public const EPROTO = 71;
	public const EMULTIHOP = 72;
	public const EBADMSG = 74;
	public const ENOTUNIQ = 76;
	public const EBADFD = 77;
	public const EREMCHG = 78;
	public const ERESTART = 85;
	public const ESTRPIPE = 86;
	public const EUSERS = 87;
	public const ENOTSOCK = 88;
	public const EDESTADDRREQ = 89;
	public const EMSGSIZE = 90;
	public const EPROTOTYPE = 91;
	public const ENOPROTOOPT = 92;
	public const EPROTONOSUPPORT = 93;
	public const ESOCKTNOSUPPORT = 94;
	public const EOPNOTSUPP = 95;
	public const EPFNOSUPPORT = 96;
	public const EAFNOSUPPORT = 97;
	public const EADDRINUSE = 98;
	public const EADDRNOTAVAIL = 99;
	public const ENETDOWN = 100;
	public const ENETUNREACH = 101;
	public const ENETRESET = 102;
	public const ECONNABORTED = 103;
	public const ECONNRESET = 104;
	public const ENOBUFS = 105;
	public const EISCONN = 106;
	public const ENOTCONN = 107;
	public const ESHUTDOWN = 108;
	public const ETOOMANYREFS = 109;
	public const ETIMEDOUT = 110;
	public const ECONNREFUSED = 111;
	public const EHOSTDOWN = 112;
	public const EHOSTUNREACH = 113;
	public const EALREADY = 114;
	public const EINPROGRESS = 115;
	public const EISNAM = 120;
	public const EREMOTEIO = 121;
	public const EDQUOT = 122;
	public const ENOMEDIUM = 123;
	public const EMEDIUMTYPE = 124;

	public function __construct(int $domain, int $type, int $protocol){}

	public function setOption(int $level, int $name, int $value) : bool{}

	public function getOption(int $level, int $name) : int{}

	public function bind(string $host, int $port = 0) : bool{}

	public function listen(int $backlog = 0) : bool{}

	public function accept($class = self::class){}

	public function connect(string $host, int $port = 0) : bool{}

	public static function select(array &$read, array &$write, array &$except, ?int $sec, int $usec = 0, int &$error = null){}

	public function read(int $length, int $flags = 0){}

	public function write(string $buffer, int $length = 0){}

	public function send(string $buffer, int $length, int $flags){}

	public function recvfrom(string &$buffer, int $length, int $flags, string &$name, int &$port = null){}

	public function sendto(string $buffer, int $length, int $flags, string $addr, int $port = 0){}

	public function setBlocking(bool $blocking) : bool{}

	public function getPeerName(bool $port = true) : array{}

	public function getSockName(bool $port = true) : array{}

	public function close(){}

	public function getLastError(bool $clear = false){}

	public function clearError(){}
}