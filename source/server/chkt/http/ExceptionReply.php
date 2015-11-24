<?php

namespace chkt\http;

use chkt\http\HttpReply;
use chkt\http\ReplyException;



class ExceptionReply extends HttpReply {
	public function send() {
		throw new ReplyException($this);
	}
}
