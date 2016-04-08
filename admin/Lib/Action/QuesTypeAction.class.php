<?php
/**
 * 题目类型公共控制器
 *
 * Created by Sunmiaomiao.
 * Email: sunmiaomiao@kangq.com
 * Date: 2016/4/5
 * Time: 10:00
 */
class QuesTypeAction extends BaseAction{

	//题目类型模型
	public $type_mod;
	//设置学段值
	public $period_id;
	//学科模型
	public $cate_mod;
	//所有学科列表
	public $cate_list;

	public function __construct(){

		$this->type_mod = M("question_type");

		switch(MODULE_NAME){
			case 'AdQuesType':
				$this->period_id = 1;
				$this->cate_mod = 'adboard';
				break;
			case 'SellerQuesType':
				$this->period_id = 2;
				$this->cate_mod = 'seller_cate';
				break;
			case 'ArticleQuesType':
				$this->period_id = 3;
				$this->cate_mod = 'article_cate';
				break;
			default:
				$this->period_id = 1;
		}

		//获取所有学科列表
		$this->cate_list = M($this->cate_mod)->field('id,name')->select();
		if(!empty($this->cate_list)){
			//把id的值作为键名，重新组合数组
			$this->cate_list = array_to_key($this->cate_list,'id');
			$this->cate_list[-1] = array('id'=>-1,'name'=>'全部学科');
			ksort($this->cate_list);
		}
	}

	//题目类型目录列表
	public function index(){
		//获取搜索条件
		$keyword=isset($_GET['keyword'])?trim($_GET['keyword']):'';
		$cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
		//搜索
		$where = "period_id=$this->period_id";
		if ($keyword!='') {
			$where .= " AND name LIKE '%".$keyword."%'";
			$this->assign('keyword', $keyword);
		}
		if ($cate_id!='') {
			$where .= " AND cate_id=$cate_id";
			$this->assign('cate_id', $cate_id);
		}
		import("ORG.Util.Page");
		$count = $this->type_mod->where($where)->count();
		$p = new Page($count,20);
		$type_list = $this->type_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('sort asc')->select();
		foreach($type_list as $key=>$value){
			$type_list[$key]['cate'] = $this->cate_list[$value['cate_id']]['name'];
		}
		$page = $p->show();
		$this->assign('page',$page);
		$this->assign('controller',MODULE_NAME);
		$this->assign('type_list',$type_list);
		$this->assign('cate_list',$this->cate_list);
		$this->display();
	}

	//增加
	public function add()
	{
		$this->assign('controller',MODULE_NAME);
		$this->assign('cate_list',$this->cate_list);
		$this->display();
	}

	//插入数据
	public function insert()
	{
		$data = $this->type_mod->create();
		if(false === $data){
			$this->error($this->type_mod->error());
		}
		$data['period_id'] = $this->period_id;
		$data['create_id'] = $_SESSION['admin_info']['id'];
		$data['create_time'] = time();
		$result = $this->type_mod->add($data);
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
			$type_id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error(L('please_select'));
		}
		$type_info = $this->type_mod->where('id='.$type_id)->find();
		$this->assign('show_header', false);
		$this->assign('controller',MODULE_NAME);
		$this->assign('cate_list',$this->cate_list);
		$this->assign('type_info',$type_info);
		$this->display();
	}

	//更新
	public function update()
	{
		if((!isset($_POST['id']) || empty($_POST['id']))) {
			$this->error('请选择要编辑的数据');
		}
		$data = $this->type_mod->create();
		if(false === $data){
			$this->error($this->type_mod->error());
		}
		$data['update_id'] = $_SESSION['admin_info']['id'];
		$data['update_time'] = time();
		$result = $this->type_mod->save($data);
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
				$result = $this->type_mod->where("id='{$id_array[$i]}'")->delete();
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
		$sql = "update " . C('DB_PREFIX') . "question_type set $type=($type+1)%2 where id='$id'";
		$this->type_mod->execute($sql);
		$values = $this->type_mod->where('id=' . $id)->find();
		$this->ajaxReturn($values[$type]);
	}

	//排序
	public function sort(){
		$id = intval($_REQUEST['id']);
		$type = trim($_REQUEST['type']);
		$num = trim($_REQUEST['num']);
		if(!is_numeric($num)){
			$values = $this->type_mod->where('id='.$id)->find();
			$this->ajaxReturn($values[$type]);
			exit;
		}
		$sql    = "update ".C('DB_PREFIX').'question_type'." set $type=$num where id='$id'";

		$this->type_mod->execute($sql);
		$values = $this->type_mod->where('id='.$id)->find();
		$this->ajaxReturn($values[$type]);
	}
}