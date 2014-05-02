<?php

class test_inc extends pri_interface {
	public function init(){
		echo "test for interface \n";
	}

	public function getName($args){
		return $this->_callModule();
	}
}