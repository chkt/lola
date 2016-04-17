<?php

namespace lola\app;



/**
 * DEPRECATED
 */
trait TAppLanguage {
	
	public function getAvailableLanguages() {
		return $this->useLocator()->using('service')->using('locale')->getAvailableLCs();
	}
	
	public function isAvailableLanguage($lang) {
		return $this->useLocator()->using('service')->using('locale')->isAvailableLC($lang);
	}
	
	public function getDefaultLanguage() {
		return $this->useLocator()->using('service')->using('locale')->getDefaultLC();
	}
	
	public function getNegotiatedLanguage() {
		return $this->useLocator()->using('service')->using('locale')->getNegotiatedLC();
	}
	
	
	public function getAvailableLocales() {
		return $this->useLocator()->using('service')->using('locale')->getAvailableLocales();
	}
	
	public function getLocaleByLang($lang) {
		return $this->useLocator()->using('service')->using('locale')->getLocaleByLC($lang);
	}
}
