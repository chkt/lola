<?php

namespace chkt\ctrl;

use chkt\ctrl\AReplyController;

use chkt\route\Route;

use chkt\http\HttpRequest;
use chkt\http\HttpReply;



abstract class ACollectionController extends AReplyController {
	
	const VERSION = '0.1.0';
		
	
	
	protected $_itemController = '';
	
	
	public function __construct() {
		$this
			->setResolveTransform(function(Route $route) {
				$request = $this->useRequest();

				$mime = $request->getPreferedAcceptMime([
					HttpRequest::MIME_JSON
				]);

				if (empty($mime)) return 'unavailable';
				
				switch($request->getMethod()) {
					case HttpRequest::METHOD_GET : return 'read';
					case HttpRequest::METHOD_PUT : return 'create';
					default : return 'unavailable';
				}
			})
			->useReplyProcessor()
			->append('view', function(Route $route, HttpReply& $reply) {
				$reply
					->setContent(json_encode($route->useActionResult()->popItem()))
					->setMime(HttpReply::MIME_JSON);
			});
	}
	
	
	protected function createAction(Route $route) {
		if (empty($this->_itemController)) return $this->unavailableAction($route);
		
		$request =& $this->useRequest();
		
		return $route
			->setCtrl($this->_itemController)
			->setAction('resolve')
			->enter(function(AItemController $ctrl) use ($request) {
				$ctrl->setRequest($request);
			});
	}
	
	
	abstract protected function readAction(Route $route);
	
	
	public function unavailableAction(Route $route) {
		$this
			->useReply()
			->setCode(400)
			->setMime(HttpReply::MIME_PLAIN)
			->send();
	}
	
	public function unauthenticatedAction(Route $route) {
		$this
			->useReply()
			->setCode(403)
			->setMime(HttpReply::MIME_PLAIN)
			->send();
	}
}
