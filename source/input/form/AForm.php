<?php

namespace lola\input\form;

use lola\io\http\payload\IHttpPayload;
use lola\input\valid\IValidator;



abstract class AForm
implements IForm
{

	private $_id;

	private $_processor;


	public function __construct(string $id, array $fields, IValidator $validator) {
		if (empty($id)) throw new \ErrorException();

		$this->_id = $id;

		$this->_processor = new Processor($fields, $validator);
	}


	public function isValidated() : bool {
		return (bool) ($this->_processor->getState() & IProcessor::FLAG_VALIDATE);
	}

	public function isSubmitted() : bool {
		return (bool) ($this->_processor->getState() & IProcessor::FLAG_COMMIT);
	}

	public function isModified() : bool {
		return (bool) ($this->_processor->getState() & IProcessor::FLAG_MODIFIED);
	}

	public function isValid() : bool {
		return (bool) ($this->_processor->getState() & IProcessor::FLAG_VALID);
	}


	public function getId() : string {
		return $this->_id;
	}


	public function validate(IHttpPayload $payload) : IForm {
		$data = $payload->isValid() ? $payload->get() : [];

		$this->_processor->validate($data);

		return $this;
	}

	public function getProjection() : array {
		$res = $this->_processor->getProjection();

		return array_merge($res, [
			'id' => $this->getId()
		]);
	}
}
