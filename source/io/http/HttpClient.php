<?php

namespace lola\io\http;

use lola\io\IClient;
use lola\io\connect\IConnection;



class HttpClient
implements IHttpClient
{

	private $_driver;

	private $_connection;
	private $_message;


	public function __construct(IHttpDriver& $driver) {
		$this->_driver =& $driver;

		$this->_connection = $driver->useConnection();
		$this->_message = $driver->useRequestMessage();
	}


	public function isIP4() : bool {
		return (bool) filter_var($this->getIP(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	}

	public function isIP6() : bool {
		return (bool) filter_var($this->getIP(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
	}


	public function getIP() : string {
		return $this->_connection->getString(IConnection::CLIENT_IP);
	}

	public function setIP(string $ip) : IClient {
		if (filter_var($ip, FILTER_VALIDATE_IP) === false) throw new \ErrorException();

		$this->_connection->setString(IConnection::CLIENT_IP, $ip);

		return $this;
	}


	public function getUA() : string {
		return $this->_message->getHeader(IHttpMessage::HEADER_USER_AGENT);
	}

	public function setUA(string $ua) : IClient {
		$this->_message->setHeader(IHttpMessage::HEADER_USER_AGENT, $ua);

		return $this;
	}


	public function getTime() : int {
		return strtotime($this->_message->getHeader(IHttpMessage::HEADER_DATE));
	}

	public function setTime(int $time) : IHttpClient {
		if ($time < 0) throw new \ErrorException();

		$this->_message->setHeader(IHttpMessage::HEADER_DATE, gmdate('D, d M Y H:i:s T', $time));

		return $this;
	}
}
