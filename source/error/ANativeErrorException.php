<?php

namespace lola\error;



abstract class ANativeErrorException
extends \ErrorException
implements INativeErrorException
{

	private $_recoverable;
	private $_recovered;


	public function __construct(int $type, string $message, string  $file, int $line) {
		parent::__construct(
			$message,
			0,
			$type,
			$file,
			$line
		);

		$this->_recoverable = $type & self::ERROR_RECOVERABLE;
		$this->_recovered = false;
	}


	public function isRecoverable() : bool {
		return $this->_recoverable;
	}

	public function isRecovered() : bool {
		return $this->_recovered;
	}


	public function recover() : INativeErrorException {
		$this->_recovered = $this->_recoverable;

		return $this;
	}
}
