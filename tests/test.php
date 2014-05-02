<?php
include(dirname(dirname(__FILE__))."/pri/pri.inc.php");

$pri = $GLOBALS['pri'];
$test = $pri->load("test");
$test1 = $pri->load("test");
$s = $test->getName(array("sag"=>array("hihu"=>1)));
var_dump($s['sag']);
