<?php

namespace lola\ctrl;

use lola\ctrl\ControllerTransform;

use lola\io\http\HttpConfig;
use lola\ctrl\ACollectionController;



class RESTCollectionRequestTransform
extends ControllerTransform
{
	const VERSION = '0.5.0';

	const STEP_RESOLVE = 'resolve';
	const STEP_CREATE = 'create';
	const STEP_READ = 'read';
	const STEP_UNAVAILABLE = 'unavailable';



	public function __construct() {
		parent::__construct([
			self::STEP_FIRST => [
				'next' => [
					self::STEP_SUCCESS => self::STEP_RESOLVE
				]
			],
			self::STEP_RESOLVE => [
				'transform' => 'resolve',
				'next' => [
					'read' => self::STEP_READ,
					'create' => self::STEP_CREATE,
					self::STEP_FAIL => self::STEP_UNAVAILABLE
				]
			],
			self::STEP_CREATE => [
				'transform' => 'create',
				'next' => [
					self::STEP_SUCCESS => self::STEP_END,
					self::STEP_FAIL => self::STEP_UNAVAILABLE
				]
			],
			self::STEP_READ => [
				'transform' => 'read',
				'next' => [
					self::STEP_SUCCESS => self::STEP_END,
					self::STEP_FAIL => self::STEP_UNAVAILABLE
				]
			],
			self::STEP_UNAVAILABLE => [
				'transform' => 'unavailable',
				'next' => [
					self::STEP_SUCCESS => self::STEP_END
				]
			]
		]);
	}


	private function _setAction(ACollectionController& $ctrl, string $action) {
		if (!$ctrl->hasAction($action)) return 'unvailable';

		$ctrl
			->useRoute()
			->setAction($action);
	}


	public function resolveStep(ACollectionController& $ctrl) {
		$request = $ctrl->useRequest();

		$mime = $request->getPreferedAcceptMime([
			HttpConfig::MIME_JSON
		]);

		if (empty($mime)) return self::STEP_FAIL;

		switch($request->getMethod()) {
			case HttpConfig::METHOD_GET : return 'read';
			case HttpConfig::METHOD_PUT : return 'create';
			default : return self::STEP_FAIL;
		}
	}

	public function createStep(ACollectionController& $ctrl) {
		return $this->_setAction($ctrl, 'create');
	}

	public function readStep(ACollectionController& $ctrl) {
		return $this->_setAction($ctrl, 'read');
	}

	public function unavailableStep(ACollectionController& $ctrl) {
		$ctrl
			->useRoute()
			->setAction('unavailable');
	}
}
