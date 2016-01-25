<?php

namespace chkt\log;

use chkt\inject\IInjectable;
use chkt\log\ILogger;

use chkt\log\Colorizer;

use chkt\ctrl\AReplyController;
use chkt\http\HttpRequest;
use chkt\http\HttpReply;



class FileLogger implements IInjectable, ILogger {	
	
	static $_map = [
		ILogger::TAG_TYPE => [Colorizer::F_MAGENTA, Colorizer::MOD_BRIGHT],
		ILogger::TAG_MESSAGE => [Colorizer::MOD_BRIGHT],
		ILogger::TAG_VOID => [],
		ILogger::TAG_URL_PATH => [Colorizer::F_GREEN, Colorizer::MOD_BRIGHT],
		ILogger::TAG_SOURCE_FILE => [Colorizer::F_BLUE, Colorizer::MOD_BRIGHT],
		ILogger::TAG_SOURCE_LINE => [],
		ILogger::TAG_CLIENT_UA => [],
		ILogger::TAG_CLIENT_IP => [Colorizer::F_RED, Colorizer::MOD_BRIGHT],
		ILogger::TAG_ERROR => [Colorizer::F_RED, Colorizer::MOD_BRIGHT],
		ILogger::TAG_ERROR_CODE => [],
		ILogger::TAG_ERROR_MESSAGE => [],
		ILogger::TAG_ERROR_FILE => [Colorizer::F_RED],
		ILogger::TAG_ERROR_LINE => [],
		ILogger::TAG_ERROR_SOURCE => [Colorizer::F_RED],
		ILogger::TAG_ERROR_ARG_NAME => [],
		ILogger::TAG_ERROR_ARG_VAL => [],
		ILogger::TAG_STACK => [Colorizer::F_RED, Colorizer::MOD_BRIGHT],
		ILogger::TAG_STACK_FILE => [Colorizer::F_BLUE],
		ILogger::TAG_STACK_LINE => [],
		ILogger::TAG_KEY => [],
		ILogger::TAG_VALUE => [Colorizer::F_GREEN],
		ILogger::TAG_REPORTER => [Colorizer::F_MAGENTA, Colorizer::MOD_BRIGHT]
	];
	
	
	static public function getDependencyConfig(Array $config) {
		return [];
	}
	
	
	static protected function _createTag($type, $content) {
		return [
			ILogger::TOKEN_TYPE => $type,
			ILogger::TOKEN_CONTENT => $content
		];
	}
	
	
	static protected function _valueToString($value) {
		if (is_null($value)) return 'null';
		else if (is_callable($value)) return 'callable';
		else if (is_string($value)) return "'$value'";
		else if (is_object($value)) return get_class($value);
		else if (is_array($value)) return 'Array';
		else return (string) $value;
	}
	
	
	static protected function _buildStackOrigin($offset) {
		$stack = debug_backtrace(0, $offset);
		$step = $stack[$offset - 1];
		
		return [
			self::_createTag(ILogger::TAG_SOURCE_FILE, $step['file']),
			self::_createTag(ILogger::TAG_SOURCE_LINE, $step['line'])
		];
	}
	
	static protected function _buildTraceOrigin(Array $trace) {
		return [
			self::_createTag(ILogger::TAG_STACK_FILE, array_key_exists('file', $trace) ? $trace['file'] : '?'),
			self::_createTag(ILogger::TAG_STACK_LINE, array_key_exists('line', $trace) ? $trace['line'] : '?')
		];
	}
	
	
	
	static protected function _buildExceptionMessage(\Exception $ex) {
		$tags = [ self::_createTag(ILogger::TAG_ERROR, '! ' . get_class($ex)) ];
		
		$code = $ex->getCode();
		$msg = $ex->getMessage();
		
		if (!empty($code)) $tags[] = self::_createTag(ILogger::TAG_ERROR_CODE, "[$code]");
		if (!empty($msg)) $tags[] = self::_createTag(ILogger::TAG_ERROR_MESSAGE, "'$msg'");
		
		$tags[] = self::_createTag(ILogger::TAG_VOID, 'IN');
		$tags[] = self::_createTag(ILogger::TAG_ERROR_FILE, $ex->getFile());
		$tags[] = self::_createTag(ILogger::TAG_ERROR_LINE, $ex->getLine());
		
		return $tags;
	}
	
	static protected function _buildTraceArguments(Array $args, Array $params = null) {
		$tags = [ self::_createTag(ILogger::TAG_VOID, '(') ];
		
		if (is_null($params)) $tags[] = self::_createTag(ILogger::TAG_ERROR_ARG_NAME, '?');
		else {
			foreach ($params as $key => $param) {
				$tags[] = self::_createTag(ILogger::TAG_ERROR_ARG_NAME, "$$param->name");
				
				if (array_key_exists($key, $args)) $tags[] = self::_createTag(ILogger::TAG_ERROR_ARG_VAL, self::_valueToString($args[$key]));
				else if ($param->isOptional()) $tags[] = self::_createTag(ILogger::TAG_ERROR_ARG_VAL, self::_valueToString($param->getDefaultValue()));
				else $tags[] = self::_createTag(ILogger::TAG_ERROR_ARG_VAL, 'unset');
				
				$tags[] = self::_CreateTag(ILogger::TAG_VOID, ',');
			}
		}
		
		array_pop($tags);
		array_push($tags, self::_createTag(ILogger::TAG_VOID, ')'));
		
		return $tags;
	}
	
	
	static protected function _buildExceptionStack(\Exception $ex) {
		$trace = $ex->getTrace();
		$tags = [];
		
		for ($i = 0, $l = count($trace); $i < $l; $i += 1) {
			$step = $trace[$i];
			$tags[] = self::_createTag(ILogger::TAG_VOID, PHP_EOL);
			$tags[] = self::_createTag(ILogger::TAG_STACK, (string) $i);
			
			if (array_key_exists('class', $step)) {
				$tags[] = self::_createTag(ILogger::TAG_ERROR_SOURCE, $step['class'] . $step['type'] . $step['function']);
				
				$method = new \ReflectionMethod($step['class'], $step['function']);
				$params = $method->getParameters();
			}
			else {
				$tags[] = self::_createTag(ILogger::TAG_ERROR_SOURCE, $step['function']);
				
				try {
					$fn = new \ReflectionFunction($step['function']);
					$params = $fn->getParameters();
				} catch (\Exception $ex) {
					$params = null;
				}
			}
			
			$tags = array_merge(
				$tags,
				self::_buildTraceArguments($step['args'], $params),
				self::_buildTraceOrigin($step)
			);
		}
		
		return $tags;
	}
	
	
	
	public function log($str) {
		if (!is_string($str) || empty($str)) throw new \ErrorException();
		
		error_log($str);
		
		return $this;
	}
	
	public function logTags(Array $message) {
		$items = [];
		
		foreach ($message as $token) {
			$type = $token[ILogger::TOKEN_TYPE];
			$content = $token[ILogger::TOKEN_CONTENT];
			
			$items[] = Colorizer::encode($content, self::$_map[$type]);
		}
		
		return $this->log(implode(' ', $items));
	}
	
	
	public function logRequest(HttpRequest $request, $stackOffset = self::STACK_IGNORE) {		
		$tags = [
			self::_createTag(ILogger::TAG_TYPE, '<'),
			self::_createTag(ILogger::TAG_MESSAGE, $request->getMethod()),
			self::_createTag(ILogger::TAG_URL_PATH, $request->getPath())
		];
		
		if ($stackOffset !== self::STACK_IGNORE) $tags = array_merge($tags, self::_buildStackOrigin($stackOffset + 1));
		
		return $this->logTags($tags);
	}
	
	public function logClient($ip = self::IP_OR_UA) {
		$tags = [ self::_createTag(ILogger::TAG_TYPE, '~') ];
		
		$ua = HttpRequest::originClientUA();
		
		if (!empty($ua)) $tags[] = self::_createTag (ILogger::TAG_CLIENT_UA, $ua);
		
		if (
			$ip === self::IP_ALWAYS ||
			$ip === self::IP_OR_UA && empty($ua)
		) $tags[] = self::_createTag('[' . HttpRequest::originClientIP() . ']', ILogger::TAG_CLIENT_IP);
		
		return $this->logTags($tags);
	}
	
	public function logReply(HttpReply $reply, $stackOffset = self::STACK_IGNORE) {
		$tags = [
			self::_createTag(ILogger::TAG_TYPE, '>'),
			self::_createTag(ILogger::TAG_MESSAGE, $reply->getCodeMessage())
		];
		
		if ($reply->isRedirect()) {
			$tags[] = self::_createTag(ILogger::TAG_VOID, 'REDIRECT');
			$tags[] = self::_createTag(ILogger::TAG_URL_PATH, $reply->getRedirectTarget());
		}
		
		if ($stackOffset !== self::STACK_IGNORE) $tags = array_merge($tags, self::_buildStackOrigin ($stackOffset + 1));
		
		return $this->logTags($tags);
	}
	
	
	public function logCtrlState(AReplyController $ctrl) {
		return $this
			->logRequest($ctrl->useRequest())
			->logClient()
			->logReply($ctrl->useReply());
	}
	
	
	public function logStats($label, Array $stats) {
		if (!is_string($label) || empty($label)) throw new TypeError();
		
		$tags = [
			self::_createTag(ILogger::TAG_TYPE, 'i'),
			self::_createTag(ILogger::TAG_REPORTER, $label)
		];
		
		foreach ($stats as $name => $value) {
			$tags[] = self::_createTag(ILogger::TAG_KEY, $name);
			$tags[] = self::_createTag(ILogger::TAG_VALUE, $value);
		}
		
		return $this->logTags($tags);
	}
	
	
	public function logException(\Exception $ex, $stack = true, $deep = true) {
		$tags = self::_buildExceptionMessage($ex);
		
		if ($deep) {
			while(!is_null($prev = $ex->getPrevious())) {
				$tags[] = self::_createTag(ILogger::TAG_VOID, PHP_EOL);
				$tags = array_merge($tags, self::_buildExceptionMessage($prev));
				
				$ex = $prev;
			}
		}
		
		if ($stack) $tags = array_merge($tags, self::_buildExceptionStack($ex));
		
		return $this->logTags($tags);
	}
}
