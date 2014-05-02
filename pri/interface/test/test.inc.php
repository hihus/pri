<?php

class test_inc extends pri_interface {
	public function init(){
		return true;
	}

	public function getName($args){
		return $this->_callModule();
	}
}