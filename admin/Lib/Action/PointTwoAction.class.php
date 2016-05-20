<?php
/**
 * 知识点二级目录控制器
 *
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/4/23
 * Time: 8:01
 */
class PointTwoAction extends PointOneAction{
    //二级目录列表
    public function two(){
        //获取搜索条件
        $keyword=isset($_GET['keyword'])?trim($_GET['keyword']):'';
        $grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
        $cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
        //搜索
        $where = "period_id=$this->period_id AND level=2";
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
            $value['name'] = cutString($value['name'],20);
            //获取其二级目录
            $alias1 = substr($value['alias'],0,-3);
            $value['alias1'] = $alias1;
            $arr = $this->point_mod->where("alias=$alias1 AND level=1")->find();
            $value['name1'] = cutString($arr['name'],15);
        }
        $page = $p->show();
        $this->assign('page',$page);
        $this->assign('controller',MODULE_NAME);
        $this->assign('point_list',$point_list);
        $this->assign('grade_list',$this->grade_list);
        $this->assign('cate_list',$this->cate_list);
        $this->display();
    }

    //添加二级目录
    public function addTwo()
    {
        $this->assign('controller',MODULE_NAME);
        $this->display();
    }

    //向数据表里插入数据
    public function insertTwo()
    {
        $post = $_POST;
        //取得其一级目录
        $alias = $post['alias'];
        $alias1 = substr($alias,0,-3);
        $point1 = $this->point_mod->where("alias=$alias1")->find();
        //数据入库
        $data = $this->point_mod->create($post);
        if(false === $data){
            $this->error($this->point_mod->error());
        }
        $data['level'] = 2;
        $data['period_id'] = $this->period_id;
        $data['grade_id'] = $point1['grade_id'];
        $data['cate_id'] = $point1['cate_id'];
        $data['create_id'] = $_SESSION['admin_info']['id'];
        $data['create_time'] = date("Y-m-d h:i:s",time());
        $result = $this->point_mod->add($data);
        if($result){
            $this->success(L('operation_success'), '', '', 'addTwo');
        }else{
            $this->error(L('operation_failure'));
        }
    }
	//excel表格数据保存
	public function insertExcelTwo()
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
    //编辑二级目录
    public function editTwo()
    {
        if(isset($_GET['id']) && intval($_GET['id'])){
            $id = intval($_GET['id']);
            $point_info = $this->point_mod->where('id='.$id)->find();
            //取得年级名称
            $point_info['grade'] = $this->grade_list[$point_info['grade_id']]['name'];
            //取得学科名称
            $point_info['cate'] = $this->cate_list[$point_info['cate_id']]['name'];
            //取得其一级目录
            $alias1 = substr($point_info['alias'],0,-3);
            $point1 = $this->point_mod->where("alias=$alias1")->find();
            $point_info['alias1'] = $point1['alias'];
            $point_info['name1'] = $point1['name'];
            $this->assign('show_header', false);
            $this->assign('controller',MODULE_NAME);
            $this->assign('point_info',$point_info);
            $this->display();
        }else{
            $this->error(L('please_select'));
        }
    }
    //编辑数据存入数据库
    public function updateTwo(){
        $data = $this->point_mod->create();
        if(false === $data){
            $this->error($this->point_mod->error());
        }
        $data['level'] = 2;
        $data['update_id'] = $_SESSION['admin_info']['id'];
        $data['update_time'] = date('Y-m-d h:i:s',time());
        $result = $this->point_mod->save($data);
        if($result){
            $this->success(L('operation_success'), '', '', 'editTwo');
        }else{
            $this->error(L('operation_failure'));
        }

    }
}