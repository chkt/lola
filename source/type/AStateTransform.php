<?php

namespace lola\type;

use lola\type\IStateTransform;



class AStateTransform
implements IStateTransform
{

	/**
	 * The version string
	 */
	const VERSION = '0.4.0';

	/**
	 * The canonical first transform id
	 */
	const STEP_FIRST = 'start';
	/**
	 * The end transformation id
	 */
	const STEP_END = '';

	/**
	 * The transformation success return value
	 */
	const STEP_SUCCESS = 'success';
	/**
	 * The transformation failure return value
	 */
	const STEP_FAIL = 'failure';



	/**
	 * The transformation steps
	 * @var array
	 */
	private $_steps = null;
	/**
	 * The transformation target
	 * @var mixed
	 */
	private $_target = null;


	/**
	 * Creates a new instance
	 * @param array $steps The transformation steps
	 */
	public function __construct(array $steps = []) {
		$this->_steps = $steps;
		$this->_target = null;
	}


	/**
	 * Returns true if the step referenced by $id exists, false otherwise
	 * @param string $id
	 * @return boolean
	 * @throws \ErrorException if $id is not a nonempty string
	 */
	public function hasStep($id) {
		if (!is_string($id) || empty($id)) throw new \ErrorException();

		return array_key_exists($id, $this->_steps);
	}

	/**
	 * Returns a reference to the step referenced by $id
	 * @param string $id
	 * @return array
	 * @throws \ErrorException if $id is not a nonempty string
	 */
	public function& useStep($id) {
		if (!is_string($id) || empty($id) || !array_key_exists($id, $this->_steps)) throw new \ErrorException();

		return $this->_steps[$id];
	}

	/**
	 * Sets the step referenced by $id
	 * @param string $id The step id
	 * @param array $step The step
	 * @return AStateTransform
	 * @throws \ErrorException if $id is not a nonempty string
	 */
	public function setStep($id, array $step) {
		if (!is_string($id) || empty($id)) throw new \ErrorException();

		$this->_steps[$id] = $step;

		return $this;
	}

	/**
	 * Resets the step referenced by $id
	 * @param string $id
	 * @return AStateTransform
	 * @throws \ErrorException if $id is not a nonempty string
	 * @throws \ErrorException if the step referenced by $id does not exist
	 */
	public function resetStep($id) {
		if (!is_string($id) || empty($id) || !array_key_exists($id, $this->_steps)) throw new \ErrorException();

		unset($this->_steps[$id]);

		return $this;
	}


	/**
	 * Sets the steps represented by $steps
	 * @param array $steps
	 * @return AStateTransform
	 */
	public function setSteps(array $steps) {
		$this->_steps = array_merge($this->_steps, $steps);

		return $this;
	}


	/**
	 * Sets the transformation target
	 * @param type $target
	 * @return AStateTransform
	 */
	public function setTarget(& $target) {
		$this->_target =& $target;

		return $this;
	}

	/**
	 * Returns a reference to the transformation target
	 * @return mixed
	 */
	public function& useTarget() {
		return $this->_target;
	}


	/**
	 * Applies the transform referenced by $id
	 * @param string $id The transform id
	 * @return string
	 * @throws \ErrorException if the step referenced by $id does not contain a valid transformation
	 * @throws \ErrorException if transform referenced by $id has no valid method
	 */
	private function _processTransform($id) {
		$transformName = $this->_steps[$id]['transform'];

		if (!is_string($transformName) || empty($transformName)) throw new \ErrorException('TRN malformed transform - ' . $id);

		$method = $transformName . 'Step';

		if (!method_exists($this, $method)) throw new \ErrorException('TRN: method missing - ' . $method);

		return $this->$method($this->_target);
	}

	/**
	 * Applies the transform referenced by $id to $target
	 * @param string $id The transform id
	 * @return string
	 * @throws \ErrorException if $id is not a valid transformation step
	 * @throws \ErrorException if the step referenced by $id does not resolve to a valid next step
	 */
	private function _processStep($id) {
		if (!array_key_exists($id, $this->_steps)) throw new \ErrorException('TRN: step missing - ' . $id);

		$step = $this->_steps[$id];
		$nextId = self::STEP_SUCCESS;

		if (array_key_exists('transform', $step)) {
			$ret = $this->_processTransform($id);

			if (!is_null($ret)) $nextId = $ret;
		}

		if (!array_key_exists('next', $step)) return '';

		$next = $step['next'];

		if (!array_key_exists($nextId, $next)) throw new \ErrorException('TRN: target missing');

		return $next[$nextId];
	}


	/**
	 * Applies the instance transformations to $target
	 * @param string $id The first transform id
	 * @return AStateTransform
	 * @throws \ErrorException if $id is not a nonempty string
	 */
	public function process($id = self::STEP_FIRST) {
		if (!is_string($id) || empty($id)) throw new \ErrorException();

		while ($id !== self::STEP_END) $id = $this->_processStep($id);

		return $this;
	}
}
