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
			->setResolveTransform(function(Route $route) {
				$request = $this->useRequest();
				
				$mime = $request->getPreferedAcceptMime([
					HttpRequest::MIME_JSON
				]);
				
				if (empty($mime)) return 'unavailable';
				
				switch($request->getMethod()) {
					case $request::METHOD_GET : return 'read';
					case $request::METHOD_PUT : return 'create';
					case $request::METHOD_PATCH : return 'update';
					case $request::METHOD_DELETE : return 'delete';
					default : return 'unavailable';
				}
			})
			->setReplyTransform(function(Route $route, $reply) {
				return json_encode($reply);
			})
			->useReply()
			->setMime(HttpReply::MIME_JSON);
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
