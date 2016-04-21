<?php
/**
 * 知识点公共控制器
 *
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/3/30
 * Time: 13:17
 */
class PointAction extends BaseAction{
	//知识点模型
	public $point_mod;
	//设置学段值
	public $period_id;
	//学科模型
	public $cate_mod;
	//所有年级列表
	public $grade_list;
	//所有学科列表
	public $cate_list;


	public function __construct(){

		$this->point_mod = M("point");

		switch(MODULE_NAME){
			case 'AdPoint':
				$this->period_id = 1;
				$this->cate_mod = 'adboard';
				break;
			case 'SellerPoint':
				$this->period_id = 2;
				$this->cate_mod = 'seller_cate';
				break;
			case 'ArticlePoint':
				$this->period_id = 3;
				$this->cate_mod = 'article_cate';
				break;
			default:
				$this->period_id = 1;
				$this->cate_mod = 'adboard';
		}

		//获取所有年级列表
		$this->grade_list = M('grade')->where("period_id=$this->period_id")->select();
		if(!empty($this->grade_list)){
			//把id的值作为键名，重新组合数组
			$this->grade_list = array_to_key($this->grade_list,'id');
		}

		//获取所有学科列表
		$this->cate_list = M($this->cate_mod)->select();
		if(!empty($this->cate_list)){
			//把id的值作为键名，重新组合数组
			$this->cate_list = array_to_key($this->cate_list,'id');
		}
	}

	//知识点目录列表
	public function index(){
		//获取搜索条件
		$keyword=isset($_GET['keyword'])?trim($_GET['keyword']):'';
		$grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
		$cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
		//搜索
		$where = "period_id=$this->period_id AND level=4";
		if ($keyword!='') {
			$where .= " AND name LIKE '%".$keyword."%'";
			$this->assign('keyword', $keyword);
		}
		if ($grade_id!='') {
			$where .= " AND grade_id=$grade_id";
			$this->assign('grade_id', $grade_id);
		}
		if ($cate_id!='') {
			$where .= " AND cate_id=$cate_id";
			$this->assign('cate_id', $cate_id);
		}
		import("ORG.Util.Page");
		$count = $this->point_mod->where($where)->count();
		$p = new Page($count,15);
		$point_list = $this->point_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('grade_id asc,cate_id asc')->select();
		foreach($point_list as $key=>&$value){
			$value['grade'] = $this->grade_list[$value['grade_id']]['name'];
			$value['cate'] = $this->cate_list[$value['cate_id']]['name'];
			$value['name'] = cutString($value['name'],8);
			$arr = $this->getAllLevel($value['alias']);
			//获取其三级目录
			$value['alias3'] = $arr['alias3'];
			$value['name3'] = cutString($arr['name3'],8);
			//获取其二级目录
			$value['alias2'] = $arr['alias2'];
			$value['name2'] = cutString($arr['name2'],8);
			//获取其一级目录
			$value['alias1'] = $arr['alias1'];
			$value['name1'] = cutString($arr['name1'],8);
		}
		$page = $p->show();
		$this->assign('page',$page);
        $this->assign('controller',MODULE_NAME);
		$this->assign('point_list',$point_list);
		$this->assign('grade_list',$this->grade_list);
		$this->assign('cate_list',$this->cate_list);
		$this->display();
	}

	//excel表格导入
	public function addExcel()
	{
		$this->assign('controller',MODULE_NAME);
		$this->assign('grade_list',$this->grade_list);
		$this->assign('cate_list',$this->cate_list);
		$this->display();
	}

	//excel表格数据保存
	public function insertExcel()
	{
		//接收前端传来的post和file
		$grade_id = intval($_POST['grade_id']);
		$cate_id = intval($_POST['cate_id']);
		$file = $_FILES['file'];

		//检查上传的文件类型是否正确
		$correct_ext  = "xls";
		$correct_type = "application/vnd.ms-excel";
		$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
		if($ext!=$correct_ext || $file['type']!=$correct_type){
			$this->error('文件类型不正确！');
		}

		//上传文件
		$base_url = "./upload/";
		checkDir($base_url);
		$period_url = $base_url.MODULE_NAME.'/';
		checkDir($period_url);
		$excel_url = $period_url.date('Ymdhis').".xls";
		if(!move_uploaded_file($file['tmp_name'],$excel_url)) {
			$this->error('表格导入失败！');
		}

		//表格内容存入数据库
		include("excel/reader.php");
		$xls = new Spreadsheet_Excel_Reader();
		$xls->setOutputEncoding('utf-8');
		$xls->read($excel_url);
		$data_values = '';
		for ($i=2; $i<=$xls->sheets[0]['numRows']; $i++) {
			$update_id = $_SESSION['admin_info']['id'];
			$update_time = date("Y-m-d h:i:s",time());

			if($xls->sheets[0]['cells'][$i][1]!='' && $xls->sheets[0]['cells'][$i][2]!=''){
				$alias1 = $xls->sheets[0]['cells'][$i][1];
				$name1 = $xls->sheets[0]['cells'][$i][2];
				$data_value1 = "('$alias1','$name1','1','$this->period_id','$grade_id','$cate_id','1','$update_id','$update_time'),";
				$data_values .= $data_value1;
			}
			if($xls->sheets[0]['cells'][$i][3]!='' && $xls->sheets[0]['cells'][$i][4]!=''){
				$alias2 = $xls->sheets[0]['cells'][$i][3];
				$name2 = $xls->sheets[0]['cells'][$i][4];
				$data_value2 = "('$alias2','$name2','2','$this->period_id','$grade_id','$cate_id','1','$update_id','$update_time'),";
				$data_values .= $data_value2;
			}
			if($xls->sheets[0]['cells'][$i][5]!='' && $xls->sheets[0]['cells'][$i][6]!=''){
				$alias3 = $xls->sheets[0]['cells'][$i][5];
				$name3 = $xls->sheets[0]['cells'][$i][6];
				$data_value3 = "('$alias3','$name3','3','$this->period_id','$grade_id','$cate_id','1','$update_id','$update_time'),";
				$data_values .= $data_value3;
			}
			if($xls->sheets[0]['cells'][$i][7]!='' && $xls->sheets[0]['cells'][$i][8]!=''){
				$alias4 = $xls->sheets[0]['cells'][$i][7];
				$name4 = $xls->sheets[0]['cells'][$i][8];
				$data_value4 = "('$alias4','$name4','4','$this->period_id','$grade_id','$cate_id','1','$update_id','$update_time'),";
				$data_values .= $data_value4;
			}
		}
		$data_values = substr($data_values,0,-1); //去掉最后一个逗号
		$sql = "insert into lxh_point (alias,name,level,period_id,grade_id,cate_id,status,update_id,update_time) values $data_values";
		$result = mysql_query($sql);//批量插入数据表中
		if($result){
			$this->success('表格导入成功！', '', 3, 'addExcel');
		}else{
			$this->error('表格导入失败！');
		}

	}

	/**
	 * 获取某知识点的所有上级目录
	 *
	 * @param String $alias 知识点的编号
	 * @return Array
	 */
	public function getAllLevel($alias){
		$arr = array();

		//获取其三级目录
		$alias3 = substr($alias,0,-3);
		$point3 = $this->point_mod->where("alias=$alias3")->find();
		$arr['alias3'] = $alias3;
		$arr['name3'] = $point3['name'];
		//获取其二级目录
		$alias2 = substr($alias3,0,-3);
		$point2 = $this->point_mod->where("alias=$alias2")->find();
		$arr['alias2'] = $alias2;
		$arr['name2'] = $point2['name'];
		//获取其一级目录
		$alias1 = substr($alias2,0,-3);
		$point1 = $this->point_mod->where("alias=$alias1")->find();
		$arr['alias1'] = $alias1;
		$arr['name1'] = $point1['name'];

		return $arr;
	}

	//增加
	public function add()
	{
        $this->assign('controller',MODULE_NAME);
		$this->assign('grade_list',$this->grade_list);
		$this->assign('cate_list',$this->cate_list);
		$this->display();
	}

	//插入数据
	public function insert()
	{
		$data = $this->point_mod->create();
		if(false === $data){
			$this->error($this->point_mod->error());
		}
		$data['period_id'] = $this->period_id;
		$data['create_id'] = $_SESSION['admin_info']['id'];
		$data['create_time'] = time();
		$result = $this->point_mod->add($data);
		if($result){
			$this->success(L('operation_success'), '', '', 'add');
		}else{
			$this->error(L('operation_failure'));
		}
	}

	//修改
	public function edit()
	{
		if(isset($_GET['id']) && intval($_GET['id'])){
			$id = intval($_GET['id']);
			$point_info = $this->point_mod->where('id='.$id)->find();
			$this->assign('show_header', false);
			$this->assign('controller',MODULE_NAME);
			$this->assign('point_info',$point_info);
			$this->assign('grade_list',$this->grade_list);
			$this->assign('cate_list',$this->cate_list);
			$this->display();
		}else{
			$this->error(L('please_select'));
		}
	}

	//更新
	public function update()
	{
		if((!isset($_POST['id']) || empty($_POST['id']))) {
			$this->error('请选择要编辑的数据');
		}
		$data = $this->point_mod->create();
		if(false === $data){
			$this->error($this->point_mod->error());
		}
		$data['update_id'] = $_SESSION['admin_info']['id'];
		$data['update_time'] = time();
		$result = $this->point_mod->save($data);
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
				$result = $this->point_mod->where("id='{$id_array[$i]}'")->delete();
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
		$sql = "update " . C('DB_PREFIX') . "point set $type=($type+1)%2 where id='$id'";
		$this->point_mod->execute($sql);
		$values = $this->point_mod->where('id=' . $id)->find();
		$this->ajaxReturn($values[$type]);
	}

	//排序
	public function sort(){
		$id = intval($_REQUEST['id']);
		$type = trim($_REQUEST['type']);
		$num = trim($_REQUEST['num']);
		if(!is_numeric($num)){
			$values = $this->point_mod->where('id='.$id)->find();
			$this->ajaxReturn($values[$type]);
			exit;
		}
		$sql    = "update ".C('DB_PREFIX').'point'." set $type=$num where id='$id'";

		$this->point_mod->execute($sql);
		$values = $this->point_mod->where('id='.$id)->find();
		$this->ajaxReturn($values[$type]);
	}
}