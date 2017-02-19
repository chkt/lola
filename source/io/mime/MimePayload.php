<?php

namespace lola\io\mime;

use lola\io\mime\IMimePayload;

use lola\io\mime\IMimeParser;
use lola\io\mime\IMimeConfig;



class MimePayload
implements IMimePayload
{

	const VERSION = '0.6.1';



	private $_container;
	private $_config;

	private $_parser;


	public function __construct(IMimeContainer& $container, IMimeConfig& $config) {
		$this->_container =& $container;
		$this->_config =& $config;

		$this->_parser = [];
	}


	private function _getPayloadParser() : IMimeParser {
		$mime = $this->_container->getMime();

		if (!array_key_exists($mime, $this->_parser)) $this->_parser[$mime] = $this->_config->produceMimeParser($mime);

		return $this->_parser[$mime];
	}


	public function isValid() : bool {
		$body = $this->_container->getBody();

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
		$body = $this->_container->getBody();

		return $this
			->_getPayloadParser()
			->parse($body);
	}

	public function set(array $payload) : IMimePayload {
		$string = $this
			->_getPayloadParser()
			->stringify($payload);

		$this->_container->setBody($string);

		return $this;
	}
}
