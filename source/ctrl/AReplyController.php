<?php

namespace lola\ctrl;

use eve\common\access\ITraversableAccessor;
use lola\io\IIOHost;



abstract class AReplyController
extends AController
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [ 'environment:io' ];
	}



	protected $_state = null;
	protected $_ioHost = null;

	protected $_requestTransform = null;
	protected $_replyTransform = null;


	public function __construct(IIOHost $io) {
		$this->_state = null;
		$this->_ioHost = $io;
	}


	public function useRoute() {
		return $this->_state;
	}

	public function setRoute(IControllerState $state) {
		$this->_state = $state;

		return $this;
	}


	public function useDriver() {
		return $this->_ioHost;
	}

	public function setDriver(IIOHost $io) {
		$this->_ioHost = $io;

		return $this;
	}


	public function& useRequest() {
		return $this
			->useDriver()
			->getRequest();
	}

	public function& useReply() {
		return $this
			->useDriver()
			->getReply();
	}


	/**
	 * Returns a reference to the request transform
	 * @return ControllerTransform
	 */
	public function& useRequestTransform() {
		if (is_null($this->_requestTransform)) $this->_requestTransform = new ControllerTransform();

		return $this->_requestTransform;
	}

	/**
	 * Sets the request transform
	 * @param ControllerTransform $transform
	 * @return AReplyController
	 */
	public function setRequestTransform(ControllerTransform $transform) {
		$this->_requestTransform = $transform;

		return $this;
	}


	/**
	 * Returns a reference to the reply transform
	 * @return ControllerTransform
	 */
	public function& useReplyTransform() {
		if (is_null($this->_replyTransform)) $this->_replyTransform = new ControllerTransform();

		return $this->_replyTransform;
	}

	/**
	 * Sets the reply transform
	 * @param ControllerTransform $transform
	 * @return AReplyController
	 */
	public function setReplyTransform(ControllerTransform $transform) {
		$this->_replyTransform = $transform;

		return $this;
	}


	public function enter(string $action, IControllerState $state) : IController {
		$this->_state = $state;

		if (!is_null($this->_requestTransform)) $this
			->useRequestTransform()
			->setTarget($this)
			->process();

		parent::enter($action, $state);

		if (!is_null($this->_replyTransform)) $this
			->useReplyTransform()
			->setTarget($this)
			->process();

		$this
			->useReply()
			->send();

		return $this;
	}
}
