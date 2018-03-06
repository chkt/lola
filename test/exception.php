<?php

set_exception_handler(function(\Throwable $ex) {
	print 'exception: ' . $ex->getMessage() . "\n";
});

set_error_handler(function(int $type, string $message, string $file, int $line) {
	if (error_reporting() === 0) return;

	print 'error: ' . $type . ' ' . $message . "\n";

	$recoverable = E_WARNING | E_NOTICE | E_COMPILE_WARNING | E_USER_WARNING | E_USER_NOTICE | E_STRICT | E_DEPRECATED | E_USER_DEPRECATED;

	if (($type & $recoverable) === 0) die();
});

register_shutdown_function(function() {
	$error = error_get_last();

	if (is_null($error)) return;

	print 'shutdown: ' . $error['message'] . "\n";
});


$a = @$b;
//$a = [];
//$a['foo'];
//trigger_error('bang', E_USER_ERROR);
//$a['bar'];
