<?php
/**
 * Created by Sunmiaomiao.
 * Email: sunmiaomiao@qq.com
 * Date: 2016/7/21
 * Time: 18:58
 */

include_once ("connect.php");

$data = $_POST;

//要操作的表名
$table = '';
if(isset($data['tag']) && $data['tag']>0){
    switch ($data['tag']){
        case 1:
            $table = 'lxh_intention_try';
            break;
        case 2:
            $table = 'lxh_intention_buy';
            break;
        case 3:
            $table = 'lxh_intention_agent';
            break;
    }
}else{
    echo json_encode(array('sta'=>false,'msg'=>'未获取到tag！'));
    exit;
}

//验证姓名
if(!empty($data['name'])){
    if(preg_match('/\f|\n|\r|\t|\v|\d/', $data['name'])){
        echo json_encode(array('sta'=>false,'msg'=>'您的输入中含有非法字符，请检查后重新填写！'));
        exit;
    }
    $data['name'] = check($data['name']);
}else{
    echo json_encode(array('sta'=>false,'msg'=>'为了保证及时满足您的需求，请填写姓名或称谓！'));
    exit;
}

//验证手机号
if(!empty($data['mobile'])){
	if(!preg_match('/^1[34578]\d{9}$/', $data['mobile'])){
		echo json_encode(array('sta'=>false,'msg'=>'手机号格式不正确，请检查后重新填写！'));
		exit;
	}
    $exist = 'SELECT * FROM '.$table.' WHERE mobile='.$data['mobile'];
    $result = mysql_query($exist);
    $arr = array();
    if($result){
        $arr = mysql_fetch_row($result);
    }
    if(!empty($arr)){
        echo json_encode(array('sta'=>false,'msg'=>'该手机号已被使用，请检查后重新填写！'));
        exit;
    }
    $data['mobile'] = check($data['mobile']);
}else{
	echo json_encode(array('sta'=>false,'msg'=>'为了保证及时满足您的需求，请填写手机号码！'));
	exit;
}

//验证qq号
if(!empty($data['qq'])){
    if(!preg_match('/^\d{5,10}$/', $data['qq'])){
        echo json_encode(array('sta'=>false,'msg'=>'QQ号格式不正确，请检查后重新填写！'));
        exit;
    }
    $exist = 'SELECT * FROM '.$table.' WHERE qq='.$data['qq'];
    $result = mysql_query($exist);
    $arr = array();
    if($result){
        $arr = mysql_fetch_row($result);
    }
    if(!empty($arr)){
        echo json_encode(array('sta'=>false,'msg'=>'该QQ已被使用，请检查后重新填写！'));
        exit;
    }
    $data['qq'] = check($data['qq']);
}

//验证邮箱
if(!empty($data['email'])){
    if(!preg_match('/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/', $data['email'])){
        echo json_encode(array('sta'=>false,'msg'=>'邮箱格式不正确，请检查后重新填写！'));
        exit;
    }
    $exist = "SELECT * FROM $table WHERE email='{$data['email']}'";
    $result = mysql_query($exist);
    $arr = array();
    if($result){
        $arr = mysql_fetch_row($result);
    }
    if(!empty($arr)){
        echo json_encode(array('sta'=>false,'msg'=>'该邮箱已被使用，请检查后重新填写！'));
        exit;
    }
    $data['email'] = preg_replace('/\s(select|insert|and|or|update|delete|\/|\*|union|into|load_file|outfile)/','',$data['email']);
}

if(!empty($data['period'])){
    $data['period'] = preg_replace('/\D/','',$data['period']);
}

//数据入库
$cTime = time();
$sql = "INSERT INTO $table(name,mobile,qq,email,period_id,time) VALUES('{$data['name']}','{$data['mobile']}','{$data['qq']}','{$data['email']}',{$data['period']},$cTime)";
$result = mysql_query($sql);
if($result){
    echo json_encode(array('sta'=>true));
    exit;
}else{
    echo json_encode(array('sta'=>false,'msg'=>'系统繁忙，请稍后再试！！'));
    exit;
}

//检查字符，防注入
function check($str){
    preg_replace('/\s(select|insert|and|or|update|delete|\/|\*|union|into|load_file|outfile)/','',$str);
    if(!get_magic_quotes_gpc()) {
        $str = addslashes($str);
    }
    $regex = '/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/';
    $str = preg_replace($regex,'',$str);
    $str = str_replace('_', '\_', $str);
    $str = str_replace('%', '\%', $str);
    $str = preg_replace('/\f\n\r/', '&nbsp;', $str);
    $str = htmlspecialchars($str);
    return $str;
}






