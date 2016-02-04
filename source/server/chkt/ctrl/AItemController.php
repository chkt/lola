<?php

namespace chkt\ctrl;

use chkt\ctrl\AReplyController;

use chkt\route\Route;

use \chkt\http\HttpRequest;
use \chkt\http\HttpReply;



abstract class AItemController extends AReplyController {
	
	const VERSION = '0.0.7';
	
	
	
	public function __construct() {
		$this
			->useRequestProcessor()
			->append('resolve', function(AReplyController &$ctrl) {
				$route =& $ctrl->useRoute();
				$request =& $ctrl->useRequest();
				
				$mime = $request->getPreferedAcceptMime([
					HttpRequest::MIME_JSON
				]);
				
				if (empty($mime)) return 'unavailable';
				
				switch($request->getMethod()) {
					case $request::METHOD_GET : return $route->setAction('read');
					case $request::METHOD_PUT : return $route->setAction('create');
					case $request::METHOD_PATCH : return $route->setAction('update');
					case $request::METHOD_DELETE : return $route->setAction('delete');
					default : $route->setAction('unavailable');
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
	
		
	abstract protected function createAction(Route $route);
	
	abstract protected function readAction(Route $route);
	
	abstract protected function updateAction(Route $route);
	
	abstract protected function deleteAction(Route $route);
	
	
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
