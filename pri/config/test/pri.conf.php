<?php

//default define
define('PRI_ROOT',dirname(dirname(dirname(dirname(__FILE__)))).'/');
define('PRI_IMPLEMENT_ROOT',PRI_ROOT.'pri/');
define('PRI_MODULE_ROOT',PRI_ROOT.'pri/module/');
define('PRI_FUNC_ROOT',PRI_ROOT.'pri/module/');
define('PRI_CONFIG_ROOT',PRI_ROOT.'pri/config/');
define('PRI_COM_ROOT',PRI_ROOT.'pri/com/');
define('PRI_INTERFACE_ROOT',PRI_ROOT.'pri/interface/');
define('PRI_DATA_ROOT',PRI_ROOT.'pri/data/');
define('PRI_API_INDEX','api.inc.php');
//加载工具类, __autoload 在cli模式不可用
foreach( scandir(PRI_COM_ROOT) as $v){
	if($v == '.' || $v == '..') continue;
	include(PRI_COM_ROOT.$v);
}
unset($v);
//default config
return array(
	'pri' =>1,
	'http_config'=>array(
			'use' => 0,
		),
	'default_mod'=>array('db','memc','log','util','http'),
	);