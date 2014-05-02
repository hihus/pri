<?php
include(dirname(dirname(__FILE__))."/pri/pri.inc.php");

$pri = $GLOBALS['pri'];
$test = $pri->load("test");
$test1 = $pri->load("test");
$test->getName("pri succ! \n");
print_r($test->getSelfConfig('test'));
print_r($test->pri->getConfig('','test'));
