<?php

namespace lola\ctrl;

use lola\ctrl\AReplyController;

use lola\io\http\HttpConfig;
use lola\route\Route;
use lola\ctrl\RESTCollectionRequestTransform;
use lola\ctrl\RESTReplyTransform;



abstract class ACollectionController
extends AReplyController
{

	const VERSION = '0.5.0';



	protected $_itemController = '';


	public function __construct() {
		$this
			->setRequestTransform(new RESTCollectionRequestTransform())
			->setReplyTransform(new RESTReplyTransform());
	}


	protected function createAction(Route $route) {
		if (empty($this->_itemController)) return $this->unavailableAction($route);

		return $route
			->setCtrl($this->_itemController)
			->setAction('resolve')
			->enter(function(AItemController& $ctrl) {
				$ctrl->setDriver($this->useDriver());
			});
	}


	abstract protected function readAction(Route $route);


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
