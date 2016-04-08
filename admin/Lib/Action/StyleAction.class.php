<?php
/**
 * 测验类型公共控制器
 *
 * Created by Sunmiaomiao.
 * Email: sunmiaomiao@kangq.com
 * Date: 2016/4/5
 * Time: 9:20
 */
class StyleAction extends BaseAction{

	//测验模型
	public $style_mod;
	//设置学段值
	public $period_id;

	public function __construct(){

		$this->style_mod = M("style");

		switch(MODULE_NAME){
			case 'AdStyle':
				$this->period_id = 1;
				break;
			case 'SellerStyle':
				$this->period_id = 2;
				break;
			case 'ArticleStyle':
				$this->period_id = 3;
				break;
			default:
				$this->period_id = 1;
		}
	}

	//测验目录列表
	public function index(){
		//获取搜索条件
		$keyword=isset($_GET['keyword'])?trim($_GET['keyword']):'';

		//搜索
		$where = "period_id=$this->period_id";
		if ($keyword!='') {
			$where .= " AND name LIKE '%".$keyword."%'";
			$this->assign('keyword', $keyword);
		}
		import("ORG.Util.Page");
		$count = $this->style_mod->where($where)->count();
		$p = new Page($count,20);
		$style_list = $this->style_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('sort asc')->select();
		$page = $p->show();
		$this->assign('page',$page);
		$this->assign('controller',MODULE_NAME);
		$this->assign('style_list',$style_list);
		$this->display();
	}

	//增加
	public function add()
	{
		$this->assign('controller',MODULE_NAME);
		$this->display();
	}

	//插入数据
	public function insert()
	{
		$data = $this->style_mod->create();
		if(false === $data){
			$this->error($this->style_mod->error());
		}
		$data['period_id'] = $this->period_id;
		$data['create_id'] = $_SESSION['admin_info']['id'];
		$data['create_time'] = time();
		$result = $this->style_mod->add($data);
		if($result){
			$this->success(L('operation_success'), '', '', 'add');
		}else{
			$this->error(L('operation_failure'));
		}
	}

	//修改
	public function edit()
	{
		if( isset($_GET['id']) ){
			$style_id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error(L('please_select'));
		}
		$style_info = $this->style_mod->where('id='.$style_id)->find();
		$this->assign('show_header', false);
		$this->assign('controller',MODULE_NAME);
		$this->assign('style_info',$style_info);
		$this->display();
	}

	//更新
	public function update()
	{
		if((!isset($_POST['id']) || empty($_POST['id']))) {
			$this->error('请选择要编辑的数据');
		}
		$data = $this->style_mod->create();
		if(false === $data){
			$this->error($this->style_mod->error());
		}
		$data['update_id'] = $_SESSION['admin_info']['id'];
		$data['update_time'] = time();
		$result = $this->style_mod->save($data);
		if(false !== $result){
			$this->success(L('operation_success'), '', '', 'edit');
		}else{
			$this->error(L('operation_failure'));
		}
	}

	//删除
	public function delete(){
		$flag = true;
		if (isset($_POST['id']) && is_array($_POST['id'])) {
			$id_array=$_POST['id'];
			for ($i=0;$i<count($id_array);$i++){
				$result = $this->style_mod->where("id='{$id_array[$i]}'")->delete();
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

	//修改状态
	function status()
	{
		$id = intval($_REQUEST['id']);
		$type = trim($_REQUEST['type']);
		$sql = "update " . C('DB_PREFIX') . "style set $type=($type+1)%2 where id='$id'";
		$this->style_mod->execute($sql);
		$values = $this->style_mod->where('id=' . $id)->find();
		$this->ajaxReturn($values[$type]);
	}

	//排序
	public function sort(){
		$id = intval($_REQUEST['id']);
		$type = trim($_REQUEST['type']);
		$num = trim($_REQUEST['num']);
		if(!is_numeric($num)){
			$values = $this->style_mod->where('id='.$id)->find();
			$this->ajaxReturn($values[$type]);
			exit;
		}
		$sql    = "update ".C('DB_PREFIX').'style'." set $type=$num where id='$id'";

		$this->style_mod->execute($sql);
		$values = $this->style_mod->where('id='.$id)->find();
		$this->ajaxReturn($values[$type]);
	}
}