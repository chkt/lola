<?php

namespace lola\io\http\payload;

use lola\io\http\payload\IHttpPayload;

use lola\io\http\IHttpDriver;
use lola\io\http\payload\IPayloadParser;



class HttpPayload
implements IHttpPayload
{

	const VERSION = '0.5.0';



	private $_driver;
	private $_parser;


	public function __construct(IHttpDriver& $driver) {
		$this->_driver =& $driver;
		$this->_parser = [];
	}


	private function _getPayloadParser() : IPayloadParser {
		$mime = $this->_driver
			->useRequest()
			->getMime();

		$name = $this->_driver
			->useConfig()
			->getMimePayloadParser($mime);

		if (empty($name)) throw new \ErrorException();

		if (!array_key_exists($name, $this->_parser)) {
			$class = new \ReflectionClass($name);

			if (!$class->implementsInterface(IPayloadParser::class)) throw new \ErrorException();

			$this->_parser[$name] = $class->newInstance();
		}

		return $this->_parser[$name];
	}


	public function isValid() : bool {
		$body = $this->_driver
			->useRequest()
			->getBody();

		if (empty($body)) return false;

		try {
			$this
				->_getPayloadParser()
				->parse($body);
		}
		catch (\Exception $ex) {
			return false;
		}

		return true;
	}

	public function get() : array {
		$body = $this->_driver
			->useRequest()
			->getBody();

		return $this
			->_getPayloadParser()
			->parse($body);
	}

	public function set(array $payload) : IHttpPayload {
		$string = $this
			->_getPayloadParser()
			->stringify($payload);

		$this->_driver
			->useRequest()
			->setBody($string);

		return $this;
	}
}
