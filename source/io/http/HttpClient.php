<?php

namespace lola\io\http;

use lola\io\http\IHttpDriver;
use lola\io\http\IHttpClient;

use lola\io\IClient;



class HttpClient
implements IHttpClient
{

	const VERSION = '0.5.0';



	private $_driver;
	private $_source;

	private $_properties;


	public function __construct(IHttpDriver& $driver) {
		$this->_driver =& $driver;
		$this->_source = $driver->useRequestResource();

		$this->_properties = [];
	}


	public function isIP4() : bool {
		return (bool) filter_var($this->getIP(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	}

	public function isIP6() : bool {
		return (bool) filter_var($this->getIP(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
	}


	public function getIP() : string {
		if (!array_key_exists('ip', $this->_properties)) $this->_properties['ip'] = $this->_source->getClientIP();

		return $this->_properties['ip'];
	}

	public function setIP(string $ip) : IClient {
		if (filter_var($ip, FILTER_VALIDATE_IP) === false) throw new \ErrorException();

		$this->_properties['ip'] = $ip;

		return $this;
	}


	public function getUA() : string {
		if (!array_key_exists('ua', $this->_properties)) $this->_properties['ua'] = $this->_source->getClientUA();

		return $this->_properties['ua'];
	}

	public function setUA(string $ua) : IClient {
		$this->_properties['ua'] = $ua;

		return $this;
	}


	public function getTime() : int {
		if (!array_key_exists('time', $this->_properties)) $this->_properties['time'] = $this->_source->getClientTime();

		return $this->_properties['time'];
	}

	public function setTime(int $time) : IHttpClient {
		if ($time < 0) throw new \ErrorException();

		$this->_properties['time'] = $time;

		return $this;
	}
}
