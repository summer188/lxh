<?php
/**
 * Created by Sunmiaomiao.
 * Email: sunmiaomiao@qq.com
 * Date: 2016/7/21
 * Time: 18:58
 */
$data = $_POST;

//ajax返回数组
$return = array();

//验证手机号
if(!empty($data['mobile'])){
	if(!preg_match("/^1[34578]d{9}$/", $data['mobile'])){
		echo json_encode(array('sta'=>false,'msg'=>'手机号格式不正确，请检查后重新填写！'));
		exit;
	}
}else{
	echo json_encode(array('sta'=>false,'msg'=>'为了保证及时满足您的需求，请填写手机号码！'));
	exit;
}

//验证qq号


