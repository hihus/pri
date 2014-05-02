<?php

class test_inc extends pri_interface {
	public function init(){
		return true;
	}

	public function getName($f,$m){
		return $this->_callModule();
	}
}