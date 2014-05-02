<?php
include(dirname(dirname(__FILE__))."/pri/pri.inc.php");

$pri = $GLOBALS['pri'];
$test = $pri->load("test");
$test1 = $pri->load("test");
var_dump($test->getName(array("asgd")));
