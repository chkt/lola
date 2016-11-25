<?php

namespace lola\ctrl;

use lola\ctrl\AReplyController;

use lola\io\http\HttpConfig;
use lola\route\Route;
use lola\ctrl\RESTItemRequestTransform;
use lola\ctrl\RESTReplyTransform;



abstract class AItemController
extends AReplyController
{

	const VERSION = '0.5.0';



	public function __construct() {
		$this
			->setRequestTransform(new RESTItemRequestTransform())
			->setReplyProcessor(new RESTReplyTransform());
	}


	abstract protected function createAction(Route $route);

	abstract protected function readAction(Route $route);

	abstract protected function updateAction(Route $route);

	abstract protected function deleteAction(Route $route);


	public function unavailableAction(Route $route) {
		$this
			->useReply()
			->setCode(HttpConfig::CODE_NOT_VALID)
			->setMime(HttpConfig::MIME_PLAIN)
			->send();
	}

	public function unauthenticatedAction(Route $route) {
		$this
			->useReply()
			->setCode(HttpConfig::CODE_NOT_AUTH)
			->setMime(HttpConfig::MIME_PLAIN)
			->send();
	}
}
