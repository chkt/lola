<?php

namespace lola\log;

use eve\access\ITraversableAccessor;
use eve\inject\IInjectableIdentity;
use lola\io\http\HttpRequest;
use lola\io\http\HttpClient;
use lola\io\http\HttpReply;
use lola\ctrl\AReplyController;



class FileLogger
implements IInjectableIdentity, ILogger
{

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
		ILogger::TAG_ERROR_ARG_VAL => [Colorizer::F_GREEN],
		ILogger::TAG_STACK => [Colorizer::F_RED, Colorizer::MOD_BRIGHT],
		ILogger::TAG_STACK_FILE => [Colorizer::F_BLUE],
		ILogger::TAG_STACK_LINE => [],
		ILogger::TAG_PROPERTY_TYPE => [Colorizer::MOD_BRIGHT],
		ILogger::TAG_PROPERTY_KEY => [Colorizer::F_BLUE, Colorizer::MOD_BRIGHT],
		ILogger::TAG_ARRAY_KEY => [Colorizer::F_BLUE, Colorizer::MOD_BRIGHT],
		ILogger::TAG_PROPERTY_VALUE => [Colorizer::F_GREEN],
		ILogger::TAG_SCOPE_OPEN => [],
		ILogger::TAG_SCOPE_CLOSE => [],
		ILogger::TAG_KEY => [Colorizer::F_BLUE, Colorizer::MOD_BRIGHT],
		ILogger::TAG_VALUE => [],
		ILogger::TAG_REPORTER => [Colorizer::F_MAGENTA, Colorizer::MOD_BRIGHT]
	];


	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [];
	}

	static public function getInstanceIdentity(ITraversableAccessor $config) : string {
		return IInjectableIdentity::IDENTITY_SINGLE;
	}



	static protected function _createTag($type, $content) {
		return [
			ILogger::TOKEN_TYPE => $type,
			ILogger::TOKEN_CONTENT => $content
		];
	}


	static protected function _valueToString($value) {
		if (is_null($value)) return 'null';
		else if (is_bool($value)) return $value ? 'true' : 'false';
		else if (is_callable($value)) return 'callable';
		else if (is_string($value)) return "'$value'";
		else if (is_object($value)) return get_class($value);
		else if (is_array($value)) return 'array';
		else return (string) $value;
	}


	static protected function _getErrorName(int $type) {
		$map = [
			E_ERROR => 'ERROR',
			E_WARNING => 'WARNING',
			E_PARSE => 'PARSE',
			E_NOTICE => 'NOTICE',
			E_CORE_ERROR => 'CORE_ERROR',
			E_CORE_WARNING => 'CORE_WARNING',
			E_COMPILE_ERROR => 'COMPILE_ERROR',
			E_COMPILE_WARNING => 'COMPILE_WARNING',
			E_USER_ERROR => 'USER_ERROR',
			E_USER_WARNING => 'USER_WARNING',
			E_USER_NOTICE => 'USER_NOTICE',
			E_STRICT => 'STRICT',
			E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
			E_DEPRECATED => 'DEPRECATED',
			E_USER_DEPRECATED => 'USER_DEPRECATED'
		];

		return array_key_exists($type, $map) ? $map[$type] : 'UNDEFINED';
	}


	static protected function _buildRequest(HttpRequest $request, $stackOffset = self::STACK_IGNORE) {
		$tags = [
			self::_createTag(ILogger::TAG_TYPE, '<'),
			self::_createTag(ILogger::TAG_MESSAGE, $request->getMethod()),
			self::_createTag(ILogger::TAG_URL_PATH, $request->getPath())
		];

		if ($stackOffset !== self::STACK_IGNORE) $tags = array_merge($tags, self::_buildStackOrigin($stackOffset + 1));

		return $tags;
	}

	static protected function _buildReply(HttpReply $reply, $stackOffset = self::STACK_IGNORE) {
		$tags = [
			self::_createTag(ILogger::TAG_TYPE, '>'),
			self::_createTag(ILogger::TAG_MESSAGE, $reply->getCodeMessage())
		];

		if ($reply->isRedirect()) {
			$tags[] = self::_createTag(ILogger::TAG_VOID, 'REDIRECT');
			$tags[] = self::_createTag(ILogger::TAG_URL_PATH, $reply->getRedirectTarget());
		}

		if ($stackOffset !== self::STACK_IGNORE) $tags = array_merge($tags, self::_buildStackOrigin($stackOffset + 1));

		return $tags;
	}


	static protected function _buildClient(HttpClient $client, $ip = self::IP_OR_UA) {
		$tags = [ self::_createTag(ILogger::TAG_TYPE, '~') ];

		$ua = $client->getUA();

		if (!empty($ua)) $tags[] = self::_createTag(ILogger::TAG_CLIENT_UA, $ua);

		if (
			$ip === self::IP_ALWAYS ||
			$ip === self::IP_OR_UA && empty($ua)
		) $tags[] = self::_createTag(ILogger::TAG_CLIENT_IP, '[' . $client->getIP() . ']');

		return $tags;
	}


	static protected function _buildObject($obj, $depth = 0) {
		$tags = [
			self::_createTag(self::TAG_PROPERTY_TYPE, get_class($obj)),
			self::_createTag(self::TAG_SCOPE_OPEN, '{'),
		];

		$cast = (array) $obj;

		foreach ($cast as $prop => $value) {
			$segs = explode("\0", $prop);
			$key = $segs[count($segs) - 1];

			$tags[] = self::_createTag(self::TAG_PROPERTY_KEY, $key);
			$tags = array_merge($tags, self::_buildValue($value, $depth - 1));
		}

		$tags[] = self::_createTag(self::TAG_SCOPE_CLOSE, '}');

		return $tags;
	}

	static protected function _buildArray(array $arr, $depth = 0) {
		$tags = [
			self::_createTag(self::TAG_SCOPE_OPEN, '['),
		];

		foreach ($arr as $key => $value) {
			$tags[] = self::_createTag(self::TAG_ARRAY_KEY, $key);
			$tags = array_merge($tags, self::_buildValue($value, $depth - 1));
		}

		$tags[] = self::_createTag(self::TAG_SCOPE_CLOSE, ']');

		return $tags;
	}

	static protected function _buildValue($value, $depth = 0) {
		$type = is_object($value) ? 'o' : (is_array($value) ? 'a' : 's');

		if ($depth === 0 || $type === 's') return [ self::_createTag(self::TAG_PROPERTY_VALUE, self::_valueToString($value)) ];
		else if ($type === 'o') return self::_buildObject($value, $depth);
		else return self::_buildArray($value, $depth);
	}


	static protected function _buildStackOrigin($offset) {
		$stack = debug_backtrace(0, $offset);
		$step = $stack[$offset - 1];

		return [
			self::_createTag(ILogger::TAG_SOURCE_FILE, $step['file']),
			self::_createTag(ILogger::TAG_SOURCE_LINE, $step['line'])
		];
	}

	static protected function _buildTraceOrigin(array $trace) {
		$tags = [];

		if (array_key_exists('file', $trace)) {
			$tags[] = self::_createTag(ILogger::TAG_STACK_FILE, $trace['file']);

			if (array_key_exists('line', $trace)) $tags[] = self::_createTag(ILogger::TAG_STACK_LINE, $trace['line']);
		}

		return $tags;
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

	static protected function _buildNamedTraceArguments(array $args, array $params) {
		$tags = [];

		foreach ($params as $key => $param) {
			$tags[] = self::_createTag(ILogger::TAG_ERROR_ARG_NAME, "$$param->name");

			if (array_key_exists($key, $args)) $tags[] = self::_createTag(ILogger::TAG_ERROR_ARG_VAL, self::_valueToString ($args[$key]));
			else if ($param->isDefaultValueAvailable()) $tags[] = self::_createTag(ILogger::TAG_ERROR_ARG_VAL, self::_valueToString($param->getDefaultValue()));
		}

		return $tags;
	}

	static protected function _buildAnonymousTraceArguments(array $args) {
		$tags = [];

		foreach ($args as $value) $tags[] = self::_createTag(ILogger::TAG_ERROR_ARG_VAL, self::_valueToString($value));

		return $tags;
	}


	static protected function _buildExceptionStack(\Exception $ex) {
		$trace = $ex->getTrace();
		$tags = [];

		for ($i = 0, $l = count($trace); $i < $l; $i += 1) {
			$step = $trace[$i];
			$tags[] = self::_createTag(ILogger::TAG_STACK, (string) $i);

			if (
				array_key_exists('class', $step) &&
				method_exists($step['class'], $step['function'])
			) {
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
				!is_null($params) ? self::_buildNamedTraceArguments($step['args'], $params) : self::_buildAnonymousTraceArguments($step['args']),
				self::_buildTraceOrigin($step)
			);
		}

		return $tags;
	}



	public function log($str) : ILogger {
		if (!is_string($str) || empty($str)) throw new \ErrorException();

		error_log($str);

		return $this;
	}

	public function logTags(Array $message) : ILogger {
		$formater = new Formater();

		$prevType = $message[0][ILogger::TOKEN_TYPE];
		$res = Colorizer::encode($message[0][ILogger::TOKEN_CONTENT], self::$_map[$prevType]);

		for ($i = 1, $l = count($message); $i < $l; $i += 1) {
			$currentType = $message[$i][ILogger::TOKEN_TYPE];
			$currentContent = $message[$i][ILogger::TOKEN_CONTENT];

			$res .= $formater->apply($prevType, $currentType) . Colorizer::encode($currentContent, self::$_map[$currentType]);

			$prevType = $currentType;
		}

		return $this->log($res .= $formater->apply($prevType));
	}


	public function logRequest(HttpRequest $request, $stackOffset = self::STACK_IGNORE) : ILogger {
		return $this->logTags(self::_buildRequest($request, $stackOffset));
	}

	public function logClient(HttpClient $client, $ip = self::IP_OR_UA) : ILogger {
		return $this->logTags(self::_buildClient($client, $ip));
	}

	public function logReply(HttpReply $reply, $stackOffset = self::STACK_IGNORE) : ILogger {
		return $this->logTags(self::_buildReply($reply, $stackOffset));
	}


	public function logCtrlState(AReplyController $ctrl) : ILogger {
		$tags = $this->_buildRequest($ctrl->useRequest());
		$tags = array_merge($tags, $this->_buildReply($ctrl->useReply()));

		return $this->logTags($tags);
	}


	public function logStats($label, array $stats) : ILogger {
		if (!is_string($label) || empty($label)) throw new \ErrorException();

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


	public function logObject($obj, $depth = 1, $stackOffset = self::STACK_IGNORE) : ILogger {
		if (!is_int($depth) || $depth < 0) throw new \ErrorException();

		$tags[] = self::_createTag(self::TAG_TYPE, '@');

		if ($stackOffset !== self::STACK_IGNORE) $tags = array_merge($tags, self::_buildStackOrigin($stackOffset + 1));

		$tags = array_merge($tags, self::_buildValue($obj, $depth));

		return $this->logTags($tags);
	}


	public function logException(\Throwable $ex, $stack = true, $deep = true) : ILogger {
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

	public function logError(array $error) : ILogger {
		return $this->logTags([
			self::_createTag(self::TAG_ERROR, sprintf('! %s', self::_getErrorName($error['type']))),
			self::_createTag(self::TAG_ERROR_MESSAGE, sprintf('"%s"', $error['message'])),
			self::_createTag(self::TAG_VOID, 'IN'),
			self::_createTag(self::TAG_ERROR_FILE, $error['file']),
			self::_createTag(self::TAG_ERROR_LINE, $error['line'])
		]);
	}
}
