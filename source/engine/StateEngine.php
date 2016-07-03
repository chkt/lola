<?php

namespace lola\engine;

use lola\engine\NoPathException;



class StateEngine 
{
	
	const VERSION = '0.2.4';
	
	
	
	
	private $_states = null;
	
	private $_models = null;
	private $_targets = null;
	
	private $_defaultCost = 1.0;
	
	
	public function __construct(array $states, $defaultPathCost = 1.0) {
		if (!is_float($defaultPathCost) || $defaultPathCost < 1.0) throw new \ErrorException();
		
		$this->_states = $states;
		
		$this->_models = [];
		$this->_targets = [];
		
		$this->_defaultCost = $defaultPathCost;
	}
	
	
	public function isValidState($state) {
		return array_key_exists($state, $this->_states);
	}
	
	
	public function getWeightOfState($state) {
		if (!is_string($state) || empty($state)) throw new \ErrorException();
		
		$states =& $this->_states;
		
		return array_key_exists($state, $states) && array_key_exists('weight', $states[$state]) ? $states[$state]['weight'] : -1;
	}
	
	
	public function getMappedNameOfState($state) {
		if (!is_string($state) || empty($state)) throw new \ErrorException();
		
		$states =& $this->_states;
		
		return array_key_exists($state, $states) && array_key_exists('map', $states[$state]) ? $states[$state]['map'] : '';
	}
	
	public function getStateOfMappedName($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		$states =& $this->_states;
		
		foreach ($states as $state => $data) {
			if (array_key_exists('map', $data) && $data['map'] === $name) return $state;
		}
		
		return '';
	}
	
	
	private function _buildPath(array $origin, $target) {
		$res = [];
		
		while (array_key_exists($target, $origin)) {
			$res[] = $target;
			$target = $origin[$target];
		}
		
		return array_reverse($res);
	}
	
	private function _spliceNext(array& $openSet, array $fScore) {
		$name = '';
		$pos = 0;
		$score = PHP_INT_MAX;
		
		foreach ($openSet as $index => $item) {
			if ($fScore[$item] >= $score) continue;
			
			$name = $item;
			$pos = $index;
			$score = $fScore[$name];
		}
		
		array_splice($openSet, $pos, 1);
		
		return $name;
	}
	
	private function _getPath($source, $target) {
		$states = $this->_states;
		$targetWeight = $states[$target]['weight'];
		
		$closedSet = [];
		$openSet = [ $source ];
		
		$shortPath = [];
		
		$gScore = [ $source => 0 ];
		$fScore = [ $source => abs($states[$target]['weight'] - $states[$source]['weight']) ];
		$steps = [ $source => 0 ];
		
		while (!empty($openSet)) {
			$current = $this->_spliceNext($openSet, $fScore);
			$currentState = $states[$current];
			$currentWeight = $currentState['weight'];
			
			if ($current === $target) return $this->_buildPath($shortPath, $target);
			
			$closedSet[] = $current;
			
			if (
				!array_key_exists('transition', $currentState) ||
				array_key_exists('follow', $currentState) && $currentState['follow'] === false
			) continue;
			
			$currentStep = $steps[$current];
			$decay = 2 - pow(2, -0.1 * $currentStep); 
			
			foreach ($currentState['transition'] as $next => $action) {
				if (array_key_exists($next, $closedSet)) continue;
				
				$nextState = $states[$next];
				$nextWeight = $nextState['weight'];
				$cost = array_key_exists('cost', $nextState) && $nextState['cost'] >= 1.0 ? $nextState['cost'] : $this->_defaultCost;
				$score = $gScore[$current] + abs($nextWeight - $currentWeight) * $cost * $decay;
				
				if (!array_key_exists($next, $openSet)) $openSet[] = $next;
				else if ($score >= $gScore[$next]) continue;
				
				$shortPath[$next] = $current;
				$gScore[$next] = $score;
				$fScore[$next] = $score + abs($targetWeight - $nextWeight);
				$steps[$next] = $currentStep + 1;
			}
		}
		
		throw new NoPathException($source, $target);
	}
	
	
	public function enter($action, IStateEngineModel& $model) {
		if (!is_string($action) || empty($action)) throw new \ErrorException();
		
		$action .= 'Action';
		
		if (!method_exists($this, $action)) throw new \ErrorException();
		
		return $this->$action($model);
	}
	
	
	private function _transition(IStateEngineModel& $model, $state) {
		$states = $this->_states;
		$current = $states[ $model->getState() ];
		$model->setState($state);
		
		if (array_key_exists('exit', $current)) $this->enter($current['exit'], $model);

		$action = $current['transition'][$state];

		if (!empty($action)) $this->enter($action, $model);
		
		$next = $states[$state];

		if (array_key_exists('enter', $next)) $this->enter($next['enter'], $model);
		
		return $this;
	}
	
	
	private function _resolvePath(IStateEngineModel& $model, $path) {
		$states = $this->_states;
		
		for ($i = 0, $l = count($path); $i < $l; $i += 1) {
			$stateId = $path[$i];

			$this->_transition($model, $stateId);

			$state = $states[ $stateId ]; 

			if (
				$i === $l - 1 &&
				array_key_exists('transition', $state) && count($state['transition']) === 1 &&
				array_key_exists('forward', $state) && $state['forward'] === true
			) {
				$path[] = array_keys($state['transition'])[0];
				$l += 1;
			}
		}
	}
	
	private function _processModel(IStateEngineModel& $model, array $queue) {
		$model->deferUpdates();
		
		while(count($queue) !== 0) {
			$path = $this->_getPath($model->getState(), array_shift($queue));
			$this->_resolvePath($model, $path);
		}
		
		$model->update();
	}
	
	
	public function transition(IStateEngineModel& $model, $next) {
		if (
			!$this->isValidState($model->getState()) ||
			!$this->isValidState($next)
		) throw new \ErrorException();
		
		$models =& $this->_models;
		$targets =& $this->_targets;
		
		$index = array_search($model, $models);
		
		if ($index === false) {
			$index = count($models);
			$models[] = $model;
			$targets[] = [];
		}
		
		$queue =& $targets[$index];
		$queue[] = $next;
		
		if (count($queue) === 1) {
			$this->_processModel($model, $queue);
			
			array_splice($models, $index, 1);
			array_splice($targets, $index, 1);
		}
		
		return $this;
	}
}
