<?php

if (!defined('THINK_PATH'))	exit();

$config = require("config.inc.php");
$array = array( 	
    'URL_MODEL' => 0,
	//DEFAULT_LANG' => 'zh-cn',
    'LANG_SWITCH_ON' => true,
    'DEFAULT_LANG' => 'zh-cn', // 默认语言
    'LANG_AUTO_DETECT' => true, // 自动侦测语言
 	//'APP_AUTOLOAD_PATH'=>'@.TagLib',//	
	'IGNORE_PRIV_LIST'=>array(
		array(
			'module_name'=>'Admin',
			'action_list'=>array('ajaxCheckUsername')
		),
		array(
			'module_name'=>'Public',
			'action_list'=>array()
		),
		array(
			'module_name'=>'Index',
			'action_list'=>array()
		),array(
			'module_name'=>'Cache',
			'action_list'=>array()
		),array(
			'module_name'=>'Ad',
			'action_list'=>array(
				'yun','school','my'
			)
		),array(
			'module_name'=>'SellerList',
			'action_list'=>array(
				'yun','school','my'
			)
		),array(
			'module_name'=>'Article',
			'action_list'=>array(
				'yun','school','my'
			)
		)
	),
  //'URL_CASE_INSENSITIVE' =>true,    
    'APP_AUTOLOAD_PATH' => '@.TagLib', //自动加载项目类库
	'TMPL_ACTION_ERROR'     => 'Public:error',
    'TMPL_ACTION_SUCCESS'   => 'Public:success',

    /* Cookie设置 */
    'COOKIE_EXPIRE'         => 3600*24*30,    // Coodie有效期
    'COOKIE_DOMAIN'         => $_SERVER['SERVER_NAME'],      // Cookie有效域名
    'COOKIE_PATH'           => '/',     // Cookie路径
    'COOKIE_PREFIX'         => '',      // Cookie前缀 避免冲突
);
return array_merge($config,$array);
?>