<?php

namespace lola\ctrl;

use lola\io\http\HttpConfig;



class RESTReplyTransform
extends ControllerTransform
{

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
			->useVars();

		$ctrl
			->useReply()
			->setMime(HttpConfig::MIME_JSON)
			->setBody(json_encode($json));
	}
}
