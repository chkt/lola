<?php

namespace lola\input;

use lola\inject\IInjectable;

use lola\input\Processor;
use lola\io\http\HttpRequest;



class Form
implements IInjectable
{

	const VERSION = '0.1.2';



	static public function getDependencyConfig(Array $config) {
		return [];
	}



	protected $_id = '';
	protected $_processor = null;


	public function __construct($id, Array $fields, Callable $cb = null) {
		if (!is_string($id) || empty($id)) throw new ErrorException();

		$processor = Processor::Fields($fields);
		$processor->setValidationCallback($cb);

		$this->_id = $id;
		$this->_processor = $processor;
	}


	public function validate(HttpRequest $request) {
		if (empty($request->getBody())) return false;

		$payload =& $request->usePayload();
		$data = $payload->isValid() ? $payload->get() : [];

		$this->_processor->validate($data);

		return $this->_processor->getState() === Processor::STATE_VALID;
	}

	public function invalidate() {
		$processor =& $this->_processor;

		$processor->setState($processor->getState() & ~Processor::FLAG_VALID);

		return $this;
	}


	public function getData() {
		$res = $this->_processor->getData();

		$res['id'] = $this->_id;

		foreach ($res['field'] as & $field) $field['qname'] = $this->_id . '.' . $field['name'];

		return $res;
	}
}
