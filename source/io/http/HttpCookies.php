<?php

namespace lola\io\http;

use lola\io\http\IHttpCookies;
use lola\io\http\IHttpDriver;



class HttpCookies
implements IHttpCookies
{

	private $_driver;
	private $_source;

	private $_state;
	private $_value;
	private $_expires;
	private $_path;
	private $_domain;

	private $_changed;


	public function __construct(IHttpDriver& $driver) {
		$this->_driver =& $driver;
		$this->_source = $driver->useRequestResource();

		$this->_state = [];
		$this->_value = [];

		$this->_expires = [];
		$this->_path = [];
		$this->_domain = [];

		$this->_changed = false;
	}


	public function hasChanges() : bool {
		return $this->_changed;
	}

	public function getChangedNames() : array {
		$res = [];

		foreach ($this->_state as $name => $state) {
			if (($state & 0x06) !== 0x00) $res[] = $name;
		}

		return $res;
	}


	public function hasCookie(string $name) : bool {
		if (!array_key_exists($name, $this->_state)) $this->_state[$name] = $this->_source->hasCookie($name) ? 0x01 : 0x00;

		return ($this->_state[$name] & 0x01) === 0x01;
	}

	public function isUpdated(string $name) : bool {
		return array_key_exists($name, $this->_state) && ($this->_state[$name] & 0x02) === 0x02;
	}

	public function isRemoved(string $name) : bool {
		return array_key_exists($name, $this->_state) && ($this->_state[$name] & 0x04) === 0x04;
	}

	public function isSecure(string $name) : bool {
		return array_key_exists($name, $this->_state) && ($this->_state[$name] & 0x08) === 0x08;
	}

	public function isHttpOnly(string $name) : bool {
		return array_key_exists($name, $this->_state) && ($this->_state[$name] & 0x10) === 0x10;
	}


	public function getValue(string $name) : string {
		if (!array_key_exists($name, $this->_state)) $this->_state[$name] = $this->_source->hasCookie($name) ? 0x01 : 0x00;

		if (($this->_state[$name] & 0x01) === 0x00) return '';

		if (!array_key_exists($name, $this->_value)) $this->_value[$name] = $this->_source->getCookie($name);

		return $this->_value[$name];
	}

	public function getExpiry(string $name) : int {
		return array_key_exists($name, $this->_expires) ? $this->_expires[$name] : 0;
	}

	public function getPath(string $name) : string {
		return array_key_exists($name, $this->_path) ? $this->_path[$name] : '';
	}

	public function getDomain(string $name) : string {
		return array_key_exists($name, $this->_domain) ? $this->_domain[$name] : '';
	}


	public function set(string $name, string $value, int $expires = 0, array $options = []) : IHttpCookies {
		$this->_changed = true;

		$this->_state[$name] = 0x03;
		$this->_value[$name] = $value;

		$this->_expires[$name] = $expires;
		$this->_path[$name] = array_key_exists('path', $options) ? $options['path'] : '';
		$this->_domain[$name] = array_key_exists('domain', $options) ? $options['domain'] : '';

		if (array_key_exists('secure', $options) && $options['secure'] === true) $this->_state[$name] |= 0x08;
		if (array_key_exists('http', $options) && $options['http'] === true) $this->_state[$name] |= 0x10;

		return $this;
	}

	public function reset(string $name) : IHttpCookies {
		$this->_changed = true;

		$this->_state[$name] = 0x05;
		$this->_value[$name] = '';

		unset($this->_expires[$name]);
		unset($this->_path[$name]);
		unset($this->_domain[$name]);

		return $this;
	}
}
