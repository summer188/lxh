<?php

class AdminAction extends BaseAction
{
	public $school_list = array();
	public $school_id = 0;
	public function __construct(){
		$admin_id = $_SESSION['admin_info']['id'];
		$admin = M('admin')->field('school_id')->where("id=$admin_id")->find();
		if(!empty($admin) && $admin['school_id']>0){
			$this->school_id = $admin['school_id'];
		}
		if($this->school_id == 0){
			$this->school_list = M('school')->field('id,name')->where('status=1')->select();
			$this->school_list = array_to_key($this->school_list,'id');
		}
		if(!empty($this->school_list)){
			$this->school_list[0] = array('id'=>0,'name'=>'所有学校');
			ksort($this->school_list);
		}
	}
	function index()
	{
		$admin_mod = M('admin');
		$where = '';
		if($this->school_id > 0){
			$where = "school_id={$this->school_id}";
		}

		import("ORG.Util.Page");
		$prex = C('DB_PREFIX');
		$count = $admin_mod->where($where)->count();
		$p = new Page($count,30);
		$admin_list = $admin_mod->field($prex.'admin.*,'.$prex.'role.name as role_name')->join('LEFT JOIN '.$prex.'role ON '.$prex.'admin.role_id = '.$prex.'role.id ')->limit($p->firstRow.','.$p->listRows)->where($where)->order($prex.'admin.add_time DESC')->select();
		$key = 1;
		foreach($admin_list as $k=>&$val){
			$admin_list[$k]['key'] = ++$p->firstRow;
			if(!empty($this->school_list)){
				$admin_list[$k]['user_school'] = $this->school_list[$val['school_id']]['name'];
			}else{
				$school = M('school')->field('name')->where("id=$this->school_id")->find();
				if(!empty($school)){
					$admin_list[$k]['user_school'] = $school['name'];
				}
			}
            if($val['start']>0){
                $val['start'] = date("Y-m-d H:i",$val['start']);
            }else{
                $val['start'] = '--';
            }
            if($val['end']>0){
                $val['end'] = date("Y-m-d H:i",$val['end']);
            }else{
                $val['end'] = '--';
            }
		}
		$big_menu = array('javascript:window.top.art.dialog({id:\'add\',iframe:\'?m=Admin&a=add\', title:\'添加管理员\', width:\'480\', height:\'250\', lock:true}, function(){var d = window.top.art.dialog({id:\'add\'}).data.iframe;var form = d.document.getElementById(\'dosubmit\');form.click();return false;}, function(){window.top.art.dialog({id:\'add\'}).close()});void(0);', '添加管理员');
		$page = $p->show();
		$this->assign('page',$page);
		$this->assign('big_menu',$big_menu);
		$this->assign('admin_list',$admin_list);
		$this->assign('school_list',$this->school_list);
		$this->display();
	}

	function add()
	{
	    if(isset($_POST['dosubmit'])){
	    	$admin_mod = D('admin');
			if(!isset($_POST['user_name'])||($_POST['user_name']=='')){
				$this->error('用户名不能为空');
			}
			if($_POST['password'] != $_POST['repassword']){
				$this->error('两次输入的密码不相同');
			}
			$result = $admin_mod->where("user_name='".$_POST['user_name']."'")->count();
			if($result){
			    $this->error('管理员'.$_POST['user_name'].'已经存在');
			}
			unset($_POST['repassword']);
			$_POST['password'] = md5($_POST['password']);
            if(!empty($_POST['start'])){
                $_POST['start'] = strtotime($_POST['start']);
            }
            if(!empty($_POST['end'])){
                $_POST['end'] = strtotime($_POST['end']);
            }
			$data = $admin_mod->create();
            //smm修改于2016-3-26
            //管理员加入学校分类
			$data['school_id'] = $_POST['school_id'];
			if(!empty($_POST['user_school']) && $_POST['school_id']==0){
				$data['school_id'] = $_POST['user_school'];
			}
			$data['add_time'] = time();
			$data['last_time'] = time();
			$result = $admin_mod->add($data);
			if($result){
				$this->success(L('operation_success'), '', '', 'add');
			}else{
				$this->error(L('operation_failure'));
			}

	    }else{
		    $role_mod = D('role');
		    $role_list = $role_mod->where('status=1')->select();
		    $this->assign('role_list',$role_list);
			$this->assign('school_id',$this->school_id);
			$this->assign('school_list',$this->school_list);
		    $this->assign('show_header', false);
			$this->display();
	    }
	}

	function edit()
	{
		if(isset($_POST['dosubmit'])){
			$admin_mod = D('admin');
			$count=$admin_mod->where("id!=".$_POST['id']." and user_name='".$_POST['user_name']."'")->count();
			if($count>0){
				$this->error('用户名已经存在！');
			}

            //smm修改于2016-3-26
            //密码若为空或默认初始值000000，则不更新表中原有密码记录
            if($_POST['password'] && $_POST['password']!='000000'){
                if($_POST['password'] != $_POST['repassword']){
                    $this->error('两次输入的密码不相同');
                }
                $_POST['password'] = md5($_POST['password']);
            }else{
                unset($_POST['password']);
            }

            unset($_POST['repassword']);
			//smm修改于2016-3-26
			//管理员加入学校分类
			$school_id = $_POST['school_id'];
			if(!empty($_POST['user_school']) && $_POST['school_id']==0){
				$school_id = $_POST['user_school'];
			}
			unset($_POST['school_id']);
			unset($_POST['user_school']);
            if(!empty($_POST['start'])){
                $_POST['start'] = strtotime($_POST['start']);
            }
            if(!empty($_POST['end'])){
                $_POST['end'] = strtotime($_POST['end']);
            }
            $data = $admin_mod->create();
			if (false === $data) {
				$this->error($admin_mod->getError());
			}

			$data['school_id'] = $school_id;
			$data['last_time'] = time();
			$result = $admin_mod->save($data);
			if(false !== $result){
				$this->success(L('operation_success'), '', '', 'edit');
			}else{
				$this->error(L('operation_failure'));
			}
		}else{
			$id = 0;
			if( isset($_GET['id']) ){
				$id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error('参数错误');
			}
			$role_mod = D('role');
		    $role_list = $role_mod->where('status=1')->select();
		    $this->assign('role_list',$role_list);

		    $admin_mod = D('admin');
			$admin_info = $admin_mod->where('id='.$id)->find();
            if($admin_info['start']>0){
                $admin_info['start'] = date("Y-m-d H:i",$admin_info['start']);
            }else{
                $admin_info['start'] = '';
            }
            if($admin_info['end']>0){
                $admin_info['end'] = date("Y-m-d H:i",$admin_info['end']);
            }else{
                $admin_info['end'] = '';
            }
			$this->assign('admin_info', $admin_info);
			$this->assign('show_header', false);
			$this->assign('school_id',$this->school_id);
			$this->assign('school_list',$this->school_list);
			$this->display();
		}
	}

	function delete()
	{
		if((!isset($_GET['id']) || empty($_GET['id'])) && (!isset($_POST['id']) || empty($_POST['id']))) {
            $this->error('请选择要删除的会员！');
		}
		$admin_mod = D('admin');
		if (isset($_POST['id']) && is_array($_POST['id'])) {
		    $ids = implode(',', $_POST['id']);
		    $admin_mod->delete($ids);
		} else {
			$id = intval($_GET['id']);
			$admin_mod->delete($id);
		}
		$this->success(L('operation_success'));
	}

	public function ajaxCheckUsername()
	{
	    $user_name = $_GET['user_name'];
        $id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : '';
        
        $where = "user_name='$user_name'";       
        $id = D('Admin')->where($where)->getField('id');        
        if (!$id) {
        	//不存在
            echo '1';
        } else {
        	//存在
            echo '0';
        }
        exit;
	}
    function ajax_check_used(){
    	$admin_mod = D('admin');
    	$count=$admin_mod->where("id!=".$_get['id']." and user_name='".$_get['user_name']."'")->count();
    	echo $count;exit;
    	if($count>0){
    		echo "0";
    	}else{
    		echo "1";
    	}
    }
	//修改状态
	function status()
	{
		$admin_mod = D('admin');
		$id 	= intval($_REQUEST['id']);
		$type 	= trim($_REQUEST['type']);
		$sql 	= "update ".C('DB_PREFIX')."admin set $type=($type+1)%2 where id='$id'";
		$res 	= $admin_mod->execute($sql);
		if($res){
			$values = $admin_mod->where('id='.$id)->find();
			$this->ajaxReturn($values[$type]);
		}
	}

	//以下均为smm添加于2016-4-8
	//学校列表
	public function school(){
		$school_mod = M('school');
		//获取搜索条件
		$keyword=isset($_GET['keyword'])?trim($_GET['keyword']):'';

		//搜索
		$where = '';
		if ($keyword!='') {
			$where .= " AND name LIKE '%".$keyword."%'";
			$this->assign('keyword', $keyword);
		}
		import("ORG.Util.Page");
		$count = $school_mod->where($where)->count();
		$p = new Page($count,15);
		$school_list = $school_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('sort asc')->select();
		$page = $p->show();
		$this->assign('page',$page);
		$this->assign('school_list',$school_list);
		$this->display();
	}

	//增加学校
	public function addSchool()
	{
		$this->display();
	}

	//插入学校信息数据
	public function insertSchool()
	{
		$school_mod = M("school");
		$data = $school_mod->create();
		if(false === $data){
			$this->error($school_mod->error());
		}
		$data['create_id'] = $_SESSION['admin_info']['id'];
		$data['create_time'] = time();
		$result = $school_mod->add($data);
		if($result){
			$this->success(L('operation_success'), '', '', 'addSchool');
		}else{
			$this->error(L('operation_failure'));
		}
	}

	//修改学校信息
	public function editSchool()
	{
		if( isset($_GET['id']) ){
			$school_id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error(L('please_select'));
		}
		$school_info = M('school')->where('id='.$school_id)->find();
		$this->assign('show_header', false);
		$this->assign('school_info',$school_info);
		$this->display();
	}

	//更新学校信息
	public function updateSchool()
	{
		if((!isset($_POST['id']) || empty($_POST['id']))) {
			$this->error('请选择要编辑的数据');
		}
		$school_mod = M('school');
		$data = $school_mod->create();
		if(false === $data){
			$this->error($school_mod->error());
		}
		$data['update_id'] = $_SESSION['admin_info']['id'];
		$data['update_time'] = time();
		$result = $school_mod->save($data);
		if(false !== $result){
			$this->success(L('operation_success'), '', '', 'editSchool');
		}else{
			$this->error(L('operation_failure'));
		}
	}

	//删除学校
	public function deleteSchool(){
		$flag = true;
		if (isset($_POST['id']) && is_array($_POST['id'])) {
			$id_array=$_POST['id'];
			for ($i=0;$i<count($id_array);$i++){
				$result = M('school')->where("id='{$id_array[$i]}'")->delete();
				if(!$result){
					$flag = false;
				}
			}
		}
		if($flag){
			$this->success(L('operation_success'));
		}else{
			$this->error(L('operation_failure'));
		}
	}

	//修改学校状态
	function statusSchool()
	{
		$school_mod = M('school');
		$id = intval($_REQUEST['id']);
		$type = trim($_REQUEST['type']);
		$sql = "update " . C('DB_PREFIX') . "school set $type=($type+1)%2 where id='$id'";
		$school_mod->execute($sql);
		$values = $school_mod->where('id=' . $id)->find();
		$this->ajaxReturn($values[$type]);
	}

	//学校排序
	public function sortSchool(){
		$school_mod = M('school');
		$id = intval($_REQUEST['id']);
		$type = trim($_REQUEST['type']);
		$num = trim($_REQUEST['num']);
		if(!is_numeric($num)){
			$values = $school_mod->where('id='.$id)->find();
			$this->ajaxReturn($values[$type]);
			exit;
		}
		$sql    = "update ".C('DB_PREFIX').'school'." set $type=$num where id='$id'";

		$school_mod->execute($sql);
		$values = $school_mod->where('id='.$id)->find();
		$this->ajaxReturn($values[$type]);
	}
}
?>