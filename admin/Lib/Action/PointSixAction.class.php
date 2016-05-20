<?php
/**
 * 知识点六级目录
 *
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/5/19
 * Time: 17:26
 */
class PointSixAction extends PointFiveAction{
	//目录列表
	public function six(){
		//获取搜索条件
		$keyword=isset($_GET['keyword'])?trim($_GET['keyword']):'';
		$grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
		$cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
		//搜索
		$where = "period_id=$this->period_id AND level=6";
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
			$arr = $this->getAllLevelSix($value['alias']);
			//获取其五级目录
			$value['alias5'] = $arr['alias5'];
			$value['name5'] = cutString($arr['name5'],8);
			//获取其四级目录
			$value['alias4'] = $arr['alias4'];
			$value['name4'] = cutString($arr['name4'],8);
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

	//添加页面
	public function addSix()
	{
		$this->assign('controller',MODULE_NAME);
		$this->display();
	}

	//添加数据
	public function insertSix()
	{
		$post = $_POST;
		//取得其一级目录
		$alias = $post['alias'];
		$alias5 = substr($alias,0,-3);
		$alias4 = substr($alias5,0,-3);
		$alias3 = substr($alias4,0,-3);
		$alias2 = substr($alias3,0,-3);
		$alias1 = substr($alias2,0,-3);
		$point1 = $this->point_mod->where("alias=$alias1")->find();
		//数据入库
		$data = $this->point_mod->create($post);
		if(false === $data){
			$this->error($this->point_mod->error());
		}
		$data['level'] = 6;
		$data['period_id'] = $this->period_id;
		$data['grade_id'] = $point1['grade_id'];
		$data['cate_id'] = $point1['cate_id'];
		$data['create_id'] = $_SESSION['admin_info']['id'];
		$data['create_time'] = date("Y-m-d h:i:s",time());
		$result = $this->point_mod->add($data);
		if($result){
			$this->success(L('operation_success'), '', '', 'addSix');
		}else{
			$this->error(L('operation_failure'));
		}
	}

	//编辑页面
	public function editSix()
	{
		if(isset($_GET['id']) && intval($_GET['id'])){
			$id = intval($_GET['id']);
			$point_info = $this->point_mod->where('id='.$id)->find();
			//取得年级名称
			$point_info['grade'] = $this->grade_list[$point_info['grade_id']]['name'];
			//取得学科名称
			$point_info['cate'] = $this->cate_list[$point_info['cate_id']]['name'];
			$arr = $this->getAllLevelSix($point_info['alias']);
			$point_info['alias5'] = $arr['alias5'];
			$point_info['name5'] = $arr['name5'];
			$point_info['alias4'] = $arr['alias4'];
			$point_info['name4'] = $arr['name4'];
			$point_info['alias3'] = $arr['alias3'];
			$point_info['name3'] = $arr['name3'];
			$point_info['alias2'] = $arr['alias2'];
			$point_info['name2'] = $arr['name2'];
			$point_info['alias1'] = $arr['alias1'];
			$point_info['name1'] = $arr['name1'];
			$this->assign('show_header', false);
			$this->assign('controller',MODULE_NAME);
			$this->assign('point_info',$point_info);
			$this->display();
		}else{
			$this->error(L('please_select'));
		}
	}

	//编辑数据
	public function updateSix(){
		$data = $this->point_mod->create();
		if(false === $data){
			$this->error($this->point_mod->error());
		}
		$data['level'] = 6;
		$data['update_id'] = $_SESSION['admin_info']['id'];
		$data['update_time'] = date('Y-m-d h:i:s',time());
		$result = $this->point_mod->save($data);
		if($result){
			$this->success(L('operation_success'), '', '', 'editSix');
		}else{
			$this->error(L('operation_failure'));
		}
	}

	//excel表格数据保存
	public function insertExcelSix()
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
			$create_id = $_SESSION['admin_info']['id'];
			$create_time = date("Y-m-d h:i:s",time());

			if($xls->sheets[0]['cells'][$i][1]!='' && $xls->sheets[0]['cells'][$i][2]!=''){
				$alias1 = $xls->sheets[0]['cells'][$i][1];
				$name1 = $xls->sheets[0]['cells'][$i][2];
				$data_value1 = "('$alias1','$name1','1','$this->period_id','$grade_id','$cate_id','1','$create_id','$create_time'),";
				$data_values .= $data_value1;
			}
			if($xls->sheets[0]['cells'][$i][3]!='' && $xls->sheets[0]['cells'][$i][4]!=''){
				$alias2 = $xls->sheets[0]['cells'][$i][3];
				$name2 = $xls->sheets[0]['cells'][$i][4];
				$data_value2 = "('$alias2','$name2','2','$this->period_id','$grade_id','$cate_id','1','$create_id','$create_time'),";
				$data_values .= $data_value2;
			}
			if($xls->sheets[0]['cells'][$i][5]!='' && $xls->sheets[0]['cells'][$i][6]!=''){
				$alias3 = $xls->sheets[0]['cells'][$i][5];
				$name3 = $xls->sheets[0]['cells'][$i][6];
				$data_value3 = "('$alias3','$name3','3','$this->period_id','$grade_id','$cate_id','1','$create_id','$create_time'),";
				$data_values .= $data_value3;
			}
			if($xls->sheets[0]['cells'][$i][7]!='' && $xls->sheets[0]['cells'][$i][8]!=''){
				$alias4 = $xls->sheets[0]['cells'][$i][7];
				$name4 = $xls->sheets[0]['cells'][$i][8];
				$data_value4 = "('$alias4','$name4','4','$this->period_id','$grade_id','$cate_id','1','$create_id','$create_time'),";
				$data_values .= $data_value4;
			}
			if($xls->sheets[0]['cells'][$i][9]!='' && $xls->sheets[0]['cells'][$i][10]!=''){
				$alias5 = $xls->sheets[0]['cells'][$i][9];
				$name5 = $xls->sheets[0]['cells'][$i][10];
				$data_value5 = "('$alias5','$name5','5','$this->period_id','$grade_id','$cate_id','1','$create_id','$create_time'),";
				$data_values .= $data_value5;
			}
			if($xls->sheets[0]['cells'][$i][11]!='' && $xls->sheets[0]['cells'][$i][12]!=''){
				$alias6 = $xls->sheets[0]['cells'][$i][11];
				$name6 = $xls->sheets[0]['cells'][$i][12];
				$data_value6 = "('$alias6','$name6','6','$this->period_id','$grade_id','$cate_id','1','$create_id','$create_time'),";
				$data_values .= $data_value6;
			}
		}
		$data_values = substr($data_values,0,-1); //去掉最后一个逗号
		$sql = "insert into ".$this->point_tab." (alias,name,level,period_id,grade_id,cate_id,status,create_id,create_time) values $data_values";
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
	public function getAllLevelSix($alias){
		$arr = array();

		//获取其五级目录
		$alias5 = substr($alias,0,-3);
		$point5 = $this->point_mod->where("alias=$alias5")->find();
		$arr['alias5'] = $alias5;
		$arr['name5'] = $point5['name'];
		//获取其四级目录
		$alias4 = substr($alias5,0,-3);
		$point4 = $this->point_mod->where("alias=$alias4")->find();
		$arr['alias4'] = $alias4;
		$arr['name4'] = $point4['name'];
		//获取其三级目录
		$alias3 = substr($alias4,0,-3);
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
}