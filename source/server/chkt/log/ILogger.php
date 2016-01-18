<?php

namespace chkt\log;

use chkt\ctrl\AReplyController;
use chkt\http\HttpRequest;
use chkt\http\HttpReply;



interface ILogger {
	const STACK_IGNORE = 0;
	const STACK_SELF = 1;
	
	const IP_NEVER = 0;
	const IP_OR_UA = 1;
	const IP_ALWAYS = 2;
	
	const TOKEN_TYPE = 'type';
	const TOKEN_CONTENT = 'content';
	
	const TAG_TYPE = 1;
	const TAG_MESSAGE = 5;
	const TAG_VOID = 8;
	
	const TAG_URL_PATH = 2;
	const TAG_SOURCE_FILE = 3;
	const TAG_SOURCE_LINE = 4;
	
	const TAG_CLIENT_UA = 6;	
	const TAG_CLIENT_IP = 7;
	
	const TAG_ERROR = 14;
	const TAG_ERROR_CODE = 9;
	const TAG_ERROR_MESSAGE = 10;
	const TAG_ERROR_SOURCE = 13;
	const TAG_ERROR_ARG_NAME = 15;
	const TAG_ERROR_ARG_VAL = 16;
	const TAG_ERROR_FILE = 11;
	const TAG_ERROR_LINE = 12;
	const TAG_STACK = 17;
	const TAG_STACK_FILE = 18;
	const TAG_STACK_LINE = 19;
	
	
	
	public function log($str);
	
	public function logTags(Array $str);
	
	public function logRequest(HttpRequest $request, $stackOffset = self::STACK_IGNORE);
	
	public function logClient($ip = self::IP_OR_UA);
	
	public function logReply(HttpReply $reply, $stackOffset = self::STACK_IGNORE);
	
	public function logCtrlState(AReplyController $ctrl);
	
	public function logException(\Exception $ex, $stack = true, $deep = true);
}
