<?php

namespace lola\ctrl;

use lola\io\mime\IMimeConfig;
use lola\io\http\IHttpConfig;
use lola\io\http\IHttpDriver;
use lola\route\Route;



abstract class AItemController
extends AReplyController
{

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


	public function invalidAction(Route $route) {
		$this
			->useReply()
			->setCode(IHttpConfig::CODE_NOT_VALID)
			->setMime(IMimeConfig::MIME_PLAIN)
			->send();
	}

	public function unavailableAction(Route $route) {
		$this
			->useReply()
			->setCode(IHttpConfig::CODE_NOT_VALID)
			->setMime(IMimeConfig::MIME_PLAIN)
			->send();
	}

	public function unauthenticatedAction(Route $route) {
		$this
			->useReply()
			->setCode(IHttpConfig::CODE_NOT_AUTH)
			->setMime(IMimeConfig::MIME_PLAIN)
			->send();
	}
}
