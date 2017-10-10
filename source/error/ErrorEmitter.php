<?php

namespace lola\error;

use eve\access\ITraversableAccessor;
use eve\inject\IInjector;



final class ErrorEmitter
implements IErrorEmitter
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [ 'injector:' ];
	}

	static public function getInstanceIdentity(ITraversableAccessor $config) : string {
		return $config->hasKey('id') ? $config->getItem('id') : self::IDENTITY_DEFAULT;
	}



	private $_injector;

	private $_queue;
	private $_fallback;


	public function __construct(IInjector $injector) {
		$this->_injector = $injector;

		$this->_queue = [];
		$this->_fallback = null;
	}


	private function _getFallbackHandler() : IErrorHandler {
		if (is_null($this->_fallback)) $this->_fallback = $this->_injector->produce(BasicErrorHandler::class);

		return $this->_fallback;
	}


	public function handleException(\Throwable $ex) {
		foreach ($this->iterate() as $handler) {
			try {
				$handler->handleException($ex);
			}
			catch (\Throwable $handlingException) {
				$this
					->_getFallbackHandler()
					->handleException($handlingException);
			}
		}
	}


	public function getLength() : int {
		return count($this->_queue);
	}

	public function hasIndex(int $index) : bool {
		return $index >= 0 && $index < $this->getLength();
	}

	public function removeIndex(int $index) : IErrorEmitter {
		if ($index >= 0 && $index < $this->getLength()) array_splice($this->_queue, $index, 1);

		return $this;
	}

	public function getItemAt(int $index) {
		if ($index < 0 || $index >= $this->getLength()) throw new \ErrorException();

		return $this->_queue[$index];
	}

	public function insertItem(int $index, $item) : IErrorEmitter {
		$index = min(max($index, 0), $this->getLength());

		array_splice($this->_queue, $index, 0, [ $item ]);

		return $this;
	}

	public function appendItem($item) : IErrorEmitter {
		return $this->insertItem($this->getLength(), $item);
	}

	public function indexOfItem($item) : int {
		$index = array_search($item, $this->_queue, true);

		return $index !== false ? $index : -1;
	}


	public function& iterate() : \Generator {
		$queue = $this->_queue;

		if (empty($queue)) $queue[] = $this->_getFallbackHandler();

		foreach ($queue as $index => $item) yield $index => $item;
	}
}
