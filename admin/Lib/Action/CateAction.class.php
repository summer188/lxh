<?php
/**
 * 学科管理公共控制器
 *
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/5/25
 * Time: 14:16
 */
class CateAction extends BaseAction{
	//表名(带前缀)
	public $tab;
	//模型
	public $mod;

	public function __construct(){

		switch(MODULE_NAME){
			case 'Adboard':
				$this->tab = C('DB_PREFIX')."adboard";
				$this->mod = M("adboard");
				break;
			case 'SellerCate':
				$this->tab = C('DB_PREFIX')."seller_cate";
				$this->mod = M("seller_cate");
				break;
			case 'ArticleCate':
				$this->tab = C('DB_PREFIX')."article_cate";
				$this->mod = M("article_cate");
				break;
			default:
				$this->tab = C('DB_PREFIX')."adboard";
				$this->mod = M("adboard");
		}

	}

	//显示列表
	public function index()
	{
		$keyword=isset($_GET['keyword'])?trim($_GET['keyword']):'';
		//搜索
		$where = '1=1';
		if ($keyword!='') {
			$where .= " AND name LIKE '%".$keyword."%'";
			$this->assign('keyword', $keyword);
		}
		import("ORG.Util.Page");
		$count = $this->mod->where($where)->count();
		$p = new Page($count,20);
		$list = $this->mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('sort asc')->select();
		$page = $p->show();
		$this->assign('page',$page);
		$this->assign('list',$list);
		$this->assign('controller',MODULE_NAME);
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
		$data = $this->mod->create();
		if(false === $data){
			$this->error('操作失败！');
		}
		$result = $this->mod->add($data);
		if($result){
			$this->success(L('operation_success'), '', '', 'add');
		}else{
			$this->error(L('operation_failure'));
		}
	}

	//修改
	public function edit()
	{
		$id = 0;
		if( isset($_GET['id']) ){
			$id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error(L('please_select'));
		}
		$info = $this->mod->where('id='.$id)->find();
		$this->assign('show_header', false);
		$this->assign('info',$info);
		$this->assign('controller',MODULE_NAME);
		$this->display();
	}

	//更新
	public function update()
	{
		if((!isset($_POST['id']) || empty($_POST['id']))) {
			$this->error('请选择要编辑的数据');
		}
		//获取原图片
		$data = $this->mod->create();
		if(false === $data){
			$this->error($this->mod->error());
		}
		$result = $this->mod->save($data);
		if(false !== $result){
			$this->success(L('operation_success'), '', '', 'edit');
		}else{
			$this->error(L('operation_failure'));
		}
	}

	//修改状态
	function status()
	{
		$id 	= intval($_REQUEST['id']);
		$type 	= trim($_REQUEST['type']);
		$sql 	= "update ".$this->tab." set $type=($type+1)%2 where id='$id'";
		$res 	= $this->mod->execute($sql);
		if($res){
			$values = $this->mod->where('id='.$id)->find();
			$this->ajaxReturn($values[$type]);
		}
	}
	//排序
	public function sort(){
		$id = intval($_REQUEST['id']);
		$type = trim($_REQUEST['type']);
		$num = trim($_REQUEST['num']);
		if(!is_numeric($num)){
			$values = $this->mod->where('id='.$id)->find();
			$this->ajaxReturn($values[$type]);
			exit;
		}
		$sql    = "update ".$this->tab." set $type=$num where id='$id'";

		$this->mod->execute($sql);
		$values = $this->mod->where('id='.$id)->find();
		$this->ajaxReturn($values[$type]);
	}
	//删除
	public function delete()
	{
		if((!isset($_GET['id']) || empty($_GET['id'])) && (!isset($_POST['id']) || empty($_POST['id']))) {
			$this->error('请选择删除项！');
		}
		if( isset($_POST['id'])&&is_array($_POST['id']) ){
			$ids = implode(',',$_POST['id']);
			$this->mod->delete($ids);
		}else{
			$id = intval($_GET['id']);
			$this->mod->where('id='.$id)->delete();
		}
		$this->success(L('operation_success'));
	}
}