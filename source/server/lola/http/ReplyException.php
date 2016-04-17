<?php

namespace lola\http;

use lola\http\HttpReply;



class ReplyException extends \Exception {
	
	private $_reply = null;
	
	
	public function __construct(HttpReply $reply) {
		$code = $reply->getCode();
		
		parent::__construct(HttpReply::messageOfCode($code), $code);
		
		$this->_reply = $reply;
	}
	
	
	public function getReply() {
		return $this->_reply;
	}
}
