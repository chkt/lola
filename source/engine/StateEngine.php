<?php

namespace lola\engine;

use lola\engine\NoPathException;



class StateEngine 
{
	
	const VERSION = '0.1.6';
	
	
	
	
	private $_states = null;
	
	private $_defaultCost = 1.0;
	
	
	public function __construct(Array $states, $defaultPathCost = 1.0) {
		if (!is_float($defaultPathCost) || $defaultPathCost < 1.0) throw new \ErrorException();
		
		$this->_states = $states;
		
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
	
	
	private function _buildPath(Array $origin, $target) {		
		while (array_key_exists($target, $origin)) {
			$res[] = $target;
			$target = $origin[$target];
		}
		
		return array_reverse($res);
	}
	
	private function _spliceNext(Array& $openSet, Array $fScore) {
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
		
		$closedSet = [];
		$openSet = [ $source ];
		
		$shortPath = [];
		
		$gScore = [ $source => 0 ];
		$fScore = [ $source => abs($states[$target]['weight'] - $states[$source]['weight']) ];
		
		while (!empty($openSet)) {
			$current = $this->_spliceNext($openSet, $fScore);
			
			if ($current === $target) return $this->_buildPath($shortPath, $target);
			
			$closedSet[] = $current;
			
			if (!array_key_exists('transition', $states[$current])) continue;
			
			foreach ($states[$current]['transition'] as $name => $action) {
				if (array_key_exists($name, $closedSet)) continue;
				
				$cost = array_key_exists('cost', $states[$name]) && $states[$name]['cost'] >= 1.0 ? $states[$name]['cost'] : $this->_defaultCost;
				$score = $gScore[$current] + abs($states[$name]['weight'] - $states[$current]['weight']) * $cost;
				
				if (!array_key_exists($name, $openSet)) $openSet[] = $name;
				else if ($score >= $gScore[$name]) continue;
				
				$shortPath[$name] = $current;
				$gScore[$name] = $score;
				$fScore[$name] = $score + abs($states[$target]['weight'] - $states[$name]['weight']);
			}
		}
		
		throw new NoPathException($source, $target);
	}
	
	
	public function enter($action, AStateEngineModel& $model) {
		if (!is_string($action) || empty($action)) throw new \ErrorException();
		
		$action .= 'Action';
		
		if (!method_exists($this, $action)) throw new \ErrorException();
		
		return $this->$action($model);
	}
	
	
	private function _transition(AStateEngineModel& $model, $state) {
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
	
	
	public function transition(AStateEngineModel& $model, $next) {
		if (
			!$this->isValidState($model->getState()) ||
			!$this->isValidState($next)
		) throw new \ErrorException();
		
		$states = $this->_states;		
		$path = $this->_getPath($model->getState(), $next);
		
		$model->deferUpdates();
		
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
		
		$model->update();
		
		return $this;
	}
}
