<?php

namespace chkt\log;

use chkt\http\HttpRequest;
use chkt\http\HttpReply;



interface ILogger {
	const STACK_IGNORE = 0;
	const STACK_SELF = 1;
	
	const IP_NEVER = 0;
	const IP_OR_UA = 1;
	const IP_ALWAYS = 2;
	
	
	
	public function log($str);
	
	public function logRequest(HttpRequest $request, $stackOffset = self::STACK_IGNORE);
	
	public function logClient($ip = self::IP_OR_UA);
	
	public function logReply(HttpReply $reply, $stackOffset = self::STACK_IGNORE);
	
	public function logException(\Exception $ex, $stack = true, $deep = true);
}
