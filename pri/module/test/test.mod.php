<?php
class test_mod{
	public function init(){
		$this->pri->loadf('show');
		echo "mod for test \n";
	}

	public function getName($args){
		$this->show->getShow("hihu_kpi");
		$this->pri->loadf('kas');
		$this->pri->loadf('kas');
		$this->kas->show();
		$t = new test();
		$t->getLibs();
		return $args;
	}
}