<?php
/**
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/7/24
 * Time: 20:14
 */
class IntentionAction extends BaseAction{

    public $period_list;

    public function __construct(){

        $this->period_list = array(
            1 => '小学',
            2 => '初中',
            3 => '高中'
        );
    }

    //试用申请
    public function test(){
		$mod = M('intention_try');
        //获取搜索条件
        $name=isset($_GET['name'])?trim($_GET['name']):'';
        $mobile=isset($_GET['mobile'])?trim($_GET['mobile']):'';
        $qq=isset($_GET['qq'])?trim($_GET['qq']):'';
        $email=isset($_GET['email'])?trim($_GET['email']):'';
        $period=isset($_GET['period'])?trim($_GET['period']):'';
        //搜索
        $where = '1=1';
        if ($name!='') {
            $where .= " AND name LIKE '%".$name."%'";
            $this->assign('name', $name);
        }
        if ($mobile!='') {
            $where .= " AND mobile=$mobile";
            $this->assign('mobile', $mobile);
        }
        if ($qq!='') {
            $where .= " AND qq=$qq";
            $this->assign('qq', $qq);
        }
        if ($email!='') {
            $where .= " AND email='$email'";
            $this->assign('email', $email);
        }
        if ($period!='' && $period>0) {
            $where .= " AND period_id=$period";
            $this->assign('period', $period);
        }
        import("ORG.Util.Page");
        $count = $mod->where($where)->count();
        $p = new Page($count,15);
        $list = $mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('id desc')->select();
        foreach($list as $key=>&$value){
            $value['period'] = $value['period_id']==0?'--':$this->period_list[$value['period_id']];
            $value['info'] = cutString($value['info'],20);
        }
        $page = $p->show();
        $this->assign('page',$page);
        $this->assign('list',$list);
        $this->assign('period_list',$this->period_list);
        $this->display();
    }

	//添加备注信息
	public function editTest()
	{
		$mod = M('intention_try');
		if(isset($_GET['id']) && intval($_GET['id'])){
			$id = intval($_GET['id']);
			$info = $mod->where('id='.$id)->find();
			$info['period'] = $info['period_id']==0?'--':$this->period_list[$info['period_id']];
			$this->assign('show_header', false);
			$this->assign('info',$info);
			$this->display();
		}else{
			$this->error(L('please_select'));
		}
	}

	//编辑数据存入数据库
	public function updateTest(){
		$mod = M('intention_try');
		$data = $mod->create();
		if(false === $data){
			$this->error($mod->error());
		}
		$result = $mod->save($data);
		if($result){
			$this->success(L('operation_success'), '', '', 'editTest');
		}else{
			$this->error(L('operation_failure'));
		}

	}

	//产品购买
	public function buy(){
		$mod = M('intention_buy');
		//获取搜索条件
		$name=isset($_GET['name'])?trim($_GET['name']):'';
		$mobile=isset($_GET['mobile'])?trim($_GET['mobile']):'';
		$qq=isset($_GET['qq'])?trim($_GET['qq']):'';
		$email=isset($_GET['email'])?trim($_GET['email']):'';
		$period=isset($_GET['period'])?trim($_GET['period']):'';
		//搜索
		$where = '1=1';
		if ($name!='') {
			$where .= " AND name LIKE '%".$name."%'";
			$this->assign('name', $name);
		}
		if ($mobile!='') {
			$where .= " AND mobile=$mobile";
			$this->assign('mobile', $mobile);
		}
		if ($qq!='') {
			$where .= " AND qq=$qq";
			$this->assign('qq', $qq);
		}
		if ($email!='') {
			$where .= " AND email='$email'";
			$this->assign('email', $email);
		}
		if ($period!='') {
			$where .= " AND period_id=$period";
			$this->assign('period', $period);
		}
		import("ORG.Util.Page");
		$count = $mod->where($where)->count();
		$p = new Page($count,15);
		$list = $mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('id desc')->select();
		foreach($list as $key=>&$value){
			$value['period'] = $value['period_id']==0?'--':$this->period_list[$value['period_id']];
			$value['info'] = cutString($value['info'],20);
		}
		$page = $p->show();
		$this->assign('page',$page);
		$this->assign('list',$list);
		$this->assign('period_list',$this->period_list);
		$this->display();
	}

	//添加备注信息
	public function editBuy()
	{
		$mod = M('intention_buy');
		if(isset($_GET['id']) && intval($_GET['id'])){
			$id = intval($_GET['id']);
			$info = $mod->where('id='.$id)->find();
			$info['period'] = $info['period_id']==0?'--':$this->period_list[$info['period_id']];
			$this->assign('show_header', false);
			$this->assign('info',$info);
			$this->display();
		}else{
			$this->error(L('please_select'));
		}
	}

	//编辑数据存入数据库
	public function updateBuy(){
		$mod = M('intention_buy');
		$data = $mod->create();
		if(false === $data){
			$this->error($mod->error());
		}
		$result = $mod->save($data);
		if($result){
			$this->success(L('operation_success'), '', '', 'editBuy');
		}else{
			$this->error(L('operation_failure'));
		}

	}

	//代理分销
	public function agent(){
		$mod = M('intention_agent');
		//获取搜索条件
		$name=isset($_GET['name'])?trim($_GET['name']):'';
		$mobile=isset($_GET['mobile'])?trim($_GET['mobile']):'';
		$qq=isset($_GET['qq'])?trim($_GET['qq']):'';
		$email=isset($_GET['email'])?trim($_GET['email']):'';
		$period=isset($_GET['period'])?trim($_GET['period']):'';
		//搜索
		$where = '1=1';
		if ($name!='') {
			$where .= " AND name LIKE '%".$name."%'";
			$this->assign('name', $name);
		}
		if ($mobile!='') {
			$where .= " AND mobile=$mobile";
			$this->assign('mobile', $mobile);
		}
		if ($qq!='') {
			$where .= " AND qq=$qq";
			$this->assign('qq', $qq);
		}
		if ($email!='') {
			$where .= " AND email='$email'";
			$this->assign('email', $email);
		}
		if ($period!='') {
			$where .= " AND period_id=$period";
			$this->assign('period', $period);
		}
		import("ORG.Util.Page");
		$count = $mod->where($where)->count();
		$p = new Page($count,15);
		$list = $mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('id desc')->select();
		foreach($list as $key=>&$value){
			$value['period'] = $value['period_id']==0?'--':$this->period_list[$value['period_id']];
			$value['info'] = cutString($value['info'],20);
		}
		$page = $p->show();
		$this->assign('page',$page);
		$this->assign('list',$list);
		$this->assign('period_list',$this->period_list);
		$this->display();
	}

	//添加备注信息
	public function editAgent()
	{
		$mod = M('intention_agent');
		if(isset($_GET['id']) && intval($_GET['id'])){
			$id = intval($_GET['id']);
			$info = $mod->where('id='.$id)->find();
			$info['period'] = $info['period_id']==0?'--':$this->period_list[$info['period_id']];
			$this->assign('show_header', false);
			$this->assign('info',$info);
			$this->display();
		}else{
			$this->error(L('please_select'));
		}
	}

	//编辑数据存入数据库
	public function updateAgent(){
		$mod = M('intention_agent');
		$data = $mod->create();
		if(false === $data){
			$this->error($mod->error());
		}
		$result = $mod->save($data);
		if($result){
			$this->success(L('operation_success'), '', '', 'editAgent');
		}else{
			$this->error(L('operation_failure'));
		}

	}
}