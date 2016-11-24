<?php

namespace lola\ctrl;

use lola\ctrl\ControllerTransform;

use lola\ctrl\AReplyController;
use lola\io\http\HttpConfig;



class RESTReplyTransform
extends ControllerTransform
{

	const VERSION = '0.5.0';

	const STEP_VIEW = 'view';



	public function __construct() {
		parent::__construct([
			self::STEP_FIRST => [
				'next' => [
					self::STEP_SUCCESS => self::STEP_VIEW,
				]
			],
			self::STEP_VIEW => [
				'transform' => 'view',
				'next' => [
					self::STEP_SUCCESS => self::STEP_END
				]
			]
		]);
	}


	public function viewStep(AReplyController& $ctrl) {
		$json = $ctrl
			->useRoute()
			->useActionResult()
			->popItem();

		$ctrl
			->useReply()
			->setMime(HttpConfig::MIME_JSON)
			->setBody(json_encode($json));
	}
}
