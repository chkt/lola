<?php

namespace lola\error;

use eve\access\ITraversableAccessor;
use eve\inject\IInjector;



final class ErrorDriver
implements IErrorDriver
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [ 'injector:' ];
	}

	static public function getInstanceIdentity(ITraversableAccessor $config) : string {
		return $config->hasKey('id') ? $config->getItem('id') : self::IDENTITY_DEFAULT;
	}



	private $_emitter;
	private $_handlers;


	public function __construct(IInjector $injector) {
		$this->_emitter = $injector->produce(ErrorEmitter::class);		//TODO: ErrorEmitter is fixed
		$this->_handlers = [];
	}


	public function hasHandler(IErrorHandler $handler) : bool {
		return in_array($handler, $this->_handlers);
	}

	public function removeHandler(IErrorHandler $handler) : IErrorDriver {
		$index = array_search($handler, $this->_handlers);

		if ($index !== false) {
			$this->_emitter->removeIndex($this->_emitter->indexOfItem($handler));

			array_splice($this->_handlers, $index, 1);
		}

		return $this;
	}

	public function setHandler(IErrorHandler $handler) : IErrorDriver {
		if (!in_array($handler, $this->_handlers)) {
			$this->_emitter->appenditem($handler);

			array_push($this->_handlers, $handler);
		}

		return $this;
	}
}
