<?php

namespace lola\http;

use lola\http\HttpReply;
use lola\http\ReplyException;



class ExceptionReply extends HttpReply {
	public function send() {
		throw new ReplyException($this);
	}
}
