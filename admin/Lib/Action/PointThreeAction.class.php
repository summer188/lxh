<?php
/**
 * 知识点三级目录控制器
 *
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/4/23
 * Time: 8:02
 */
class PointThreeAction extends PointTwoAction{
    //三级目录列表
    public function Three(){
        //获取搜索条件
        $keyword=isset($_GET['keyword'])?trim($_GET['keyword']):'';
        $cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
        $status=isset($_GET['status'])?trim($_GET['status']):2;
        //搜索
        $where = "period_id=$this->period_id AND level=3";
        if ($keyword!='') {
            $where .= " AND name LIKE '%".$keyword."%'";
            $this->assign('keyword', $keyword);
        }
        if ($cate_id!='') {
            $where .= " AND cate_id=$cate_id";
            $this->assign('cate_id', $cate_id);
        }
        if ($status!=2) {
            $where .= " AND status=$status";
        }
        import("ORG.Util.Page");
        $count = $this->point_mod->where($where)->count();
        $p = new Page($count,15);
        $point_list = $this->point_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('cate_id asc')->select();
        foreach($point_list as $key=>&$value){
            $value['cate'] = $this->cate_list[$value['cate_id']]['name'];
            $value['name'] = cutString($value['name'],15);
            //获取其二级目录
            $alias2 = substr($value['alias'],0,-3);
            $value['alias2'] = $alias2;
            $point2 = $this->point_mod->where("alias=$alias2 AND level=2")->find();
            $value['name2'] = cutString($point2['name'],10);
            //获取其一级目录
            $alias1 = substr($alias2,0,-3);
            $value['alias1'] = $alias1;
            $point1 = $this->point_mod->where("alias=$alias1 AND level=1")->find();
            $value['name1'] = cutString($point1['name'],10);
        }
        $page = $p->show();
        $this->assign('page',$page);
        $this->assign('status', $status);
        $this->assign('controller',MODULE_NAME);
        $this->assign('point_list',$point_list);
        $this->assign('cate_list',$this->cate_list);
        $this->display();
    }

    //添加三级目录
    public function addThree()
    {
        $this->assign('controller',MODULE_NAME);
        $this->display();
    }

    //向数据表里插入数据
    public function insertThree()
    {
        $post = $_POST;
        //取得其一级目录
        $alias = $post['alias'];
        $alias2 = substr($alias,0,-3);
        $alias1 = substr($alias2,0,-3);
        $point1 = $this->point_mod->where("alias=$alias1")->find();
        //数据入库
        $data = $this->point_mod->create($post);
        if(false === $data){
            $this->error($this->point_mod->error());
        }
        $data['level'] = 3;
        $data['period_id'] = $this->period_id;
        $data['cate_id'] = $point1['cate_id'];
        $data['create_id'] = $_SESSION['admin_info']['id'];
        $data['create_time'] = date("Y-m-d h:i:s",time());
        $result = $this->point_mod->add($data);
        if($result){
            $this->success(L('operation_success'), '', '', 'addThree');
        }else{
            $this->error(L('operation_failure'));
        }
    }
	//excel表格数据保存
	public function insertExcelThree()
	{
		//接收前端传来的post和file
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
				$data_value1 = "('$alias1','$name1','1','$this->period_id','$cate_id','1','$create_id','$create_time'),";
				$data_values .= $data_value1;
			}
			if($xls->sheets[0]['cells'][$i][3]!='' && $xls->sheets[0]['cells'][$i][4]!=''){
				$alias2 = $xls->sheets[0]['cells'][$i][3];
				$name2 = $xls->sheets[0]['cells'][$i][4];
				$data_value2 = "('$alias2','$name2','2','$this->period_id','$cate_id','1','$create_id','$create_time'),";
				$data_values .= $data_value2;
			}
			if($xls->sheets[0]['cells'][$i][5]!='' && $xls->sheets[0]['cells'][$i][6]!=''){
				$alias3 = $xls->sheets[0]['cells'][$i][5];
				$name3 = $xls->sheets[0]['cells'][$i][6];
				$data_value3 = "('$alias3','$name3','3','$this->period_id','$cate_id','1','$create_id','$create_time'),";
				$data_values .= $data_value3;
			}
		}
		$data_values = substr($data_values,0,-1); //去掉最后一个逗号
		$sql = "insert into ".$this->point_tab." (alias,name,level,period_id,cate_id,status,create_id,create_time) values $data_values";
		$result = mysql_query($sql);//批量插入数据表中
		if($result){
			$this->success('表格导入成功！', '', 3, 'addExcel');
		}else{
			$this->error('表格导入失败！');
		}

	}
    //编辑三级目录
    public function editThree()
    {
        if(isset($_GET['id']) && intval($_GET['id'])){
            $id = intval($_GET['id']);
            $point_info = $this->point_mod->where('id='.$id)->find();
            //取得学科名称
            $point_info['cate'] = $this->cate_list[$point_info['cate_id']]['name'];
            //获取其二级目录
            $alias2 = substr($point_info['alias'],0,-3);
            $point_info['alias2'] = $alias2;
            $point2 = $this->point_mod->where("alias=$alias2 AND level=2")->find();
            $point_info['name2'] = cutString($point2['name'],10);
            //获取其一级目录
            $alias1 = substr($alias2,0,-3);
            $point_info['alias1'] = $alias1;
            $point1 = $this->point_mod->where("alias=$alias1 AND level=1")->find();
            $point_info['name1'] = cutString($point1['name'],10);
            $this->assign('show_header', false);
            $this->assign('controller',MODULE_NAME);
            $this->assign('point_info',$point_info);
            $this->display();
        }else{
            $this->error(L('please_select'));
        }
    }
    //编辑数据存入数据库
    public function updateThree(){
        $data = $this->point_mod->create();
        if(false === $data){
            $this->error($this->point_mod->error());
        }
        $data['level'] = 3;
        $data['update_id'] = $_SESSION['admin_info']['id'];
        $data['update_time'] = date('Y-m-d h:i:s',time());
        $result = $this->point_mod->save($data);
        if($result){
            $this->success(L('operation_success'), '', '', 'editThree');
        }else{
            $this->error(L('operation_failure'));
        }

    }
}