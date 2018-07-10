<?php

namespace lola\ctrl;

use lola\ctrl\ControllerTransform;

use lola\io\http\HttpConfig;
use lola\ctrl\AItemController;



class RESTItemRequestTransform
extends ControllerTransform
{
	const VERSION = '0.5.0';

	const STEP_RESOLVE = 'resolve';
	const STEP_CREATE = 'create';
	const STEP_READ = 'read';
	const STEP_UPDATE = 'update';
	const STEP_DELETE = 'delete';
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
					'create' => self::STEP_CREATE,
					'read' => self::STEP_READ,
					'update' => self::STEP_UPDATE,
					'delete' => self::STEP_DELETE,
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
			self::STEP_UPDATE => [
				'transform' => 'update',
				'next' => [
					self::STEP_SUCCESS => self::STEP_END,
					self::STEP_FAIL => self::STEP_UNAVAILABLE
				]
			],
			self::STEP_DELETE => [
				'transform' => 'delete',
				'next' => [
					self::STEP_SUCCESS => self::STEP_END,
					self::STEP_FAIL => self::STEP_UNAVAILABLE
				]
			],
			self::STEP_UNAVAILABLE => [
				'transform' => 'unavailable'
			]
		]);
	}


	private function _setAction(AItemController& $ctrl, string $action) {
		if (!$ctrl->hasAction($action)) return 'unavailable';

		$ctrl
			->useRoute()
			->setAction($action);
	}


	public function resolveStep(AItemController& $ctrl) {
		$request = $ctrl->useRequest();

		$mime = $request->getPreferedAcceptMime([
			HttpConfig::MIME_JSON
		]);

		if (empty($mime)) return self::STEP_FAIL;

		switch($request->getMethod()) {
			case HttpConfig::METHOD_GET : return 'read';
			case HttpConfig::METHOD_PUT : return 'create';
			case HttpConfig::METHOD_PATCH : return 'update';
			case HttpConfig::METHOD_DELETE : return 'delete';
			default : return self::STEP_FAIL;
		}
	}

	public function createStep(AItemController& $ctrl) {
		return $this->_setAction($ctrl, 'create');
	}

	public function readStep(AItemController& $ctrl) {
		return $this->_setAction($ctrl, 'read');
	}

	public function updateStep(AItemController& $ctrl) {
		return $this->_setAction($ctrl, 'update');
	}

	public function deleteStep(AItemController& $ctrl) {
		return $this->_setAction($ctrl, 'delete');
	}

	public function unavailableStep(AItemController& $ctrl) {
		$ctrl
			->useRoute()
			->setAction('unavailable');
	}
}
