<?php

namespace lola\ctrl;

use lola\ctrl\AReplyController;

use lola\io\http\IHttpDriver;
use lola\io\http\IHttpConfig;
use lola\route\Route;
use lola\ctrl\RESTItemRequestTransform;
use lola\ctrl\RESTReplyTransform;



abstract class AItemController
extends AReplyController
{

	const VERSION = '0.5.0';



	public function __construct(IHttpDriver& $driver) {
		parent::__construct($driver);

		$this
			->setRequestTransform(new RESTItemRequestTransform())
			->setReplyTransform(new RESTReplyTransform());
	}


	abstract protected function createAction(Route $route);

	abstract protected function readAction(Route $route);

	abstract protected function updateAction(Route $route);

	abstract protected function deleteAction(Route $route);


	public function unavailableAction(Route $route) {
		$this
			->useReply()
			->setCode(IHttpConfig::CODE_NOT_VALID)
			->setMime(IHttpConfig::MIME_PLAIN)
			->send();
	}

	public function unauthenticatedAction(Route $route) {
		$this
			->useReply()
			->setCode(IHttpConfig::CODE_NOT_AUTH)
			->setMime(IHttpConfig::MIME_PLAIN)
			->send();
	}
}
