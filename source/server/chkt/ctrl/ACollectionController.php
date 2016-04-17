<?php

namespace lola\ctrl;

use lola\ctrl\AReplyController;

use lola\route\Route;

use lola\http\HttpRequest;
use lola\http\HttpReply;



abstract class ACollectionController extends AReplyController {
	
	const VERSION = '0.1.0';
		
	
	
	protected $_itemController = '';
	
	
	public function __construct() {
		$this
			->useRequestProcessor()
			->append('resolve', function(AReplyController& $ctrl) {
				$route =& $ctrl->useRoute();
				$request =& $ctrl->useRequest();

				$mime = $request->getPreferedAcceptMime([
					HttpRequest::MIME_JSON
				]);

				if (empty($mime)) return 'unavailable';
				
				switch($request->getMethod()) {
					case HttpRequest::METHOD_GET : return $route->setAction('read');
					case HttpRequest::METHOD_PUT : return $route->setAction('create');
					default : return $route->setAction('unavailable');
				}
			});
			
		$this
			->useReplyProcessor()
			->append('view', function(AReplyController& $ctrl) {
				$json = $ctrl->useRoute()->useActionResult()->popItem();
				
				$ctrl
					->useReply()
					->setContent(json_encode($json))
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
