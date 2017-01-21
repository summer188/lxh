<?php
/**
 * 题目工具公共控制器，主要功能有：
 * 检测并创建题目上传目录；单一和多级目录的检测和创建；
 * 获取知识点目录树；根据条件获取相关知识点列表
 * 检测上传的word和png类型；删除题目文档；保存导入的excel数据等
 *
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/4/14
 * Time: 9:22
 */
class QuestionToolAction extends QuestionBaseAction{

	/**
	 * 判断或创建题目目录
     *
	 * @param Int $cate_id--学科id
	 * @param Int $grade_id--年级id
     *
     * @return String 单个题目信息存放目录
	 */
	public function checkQuestionDir($cate_id,$grade_id){
		//定义题目上传根目录(相对路径)
		$root = 'upload/';
		//学科目录,以学科别名命名
        $cate = $this->cate_list[$cate_id];
		$cate_root = $root.$cate['alias'].'/';
        //年级目录
        $grade_root = $cate_root.$grade_id.'/';
        $this->checkDirUrl($grade_root);
        return $grade_root;
	}

	/**
     * 检查并创建多级目录
     *
     * @param String $rootUrl--目录
     */
    public function checkDirUrl($rootUrl){
        if(!empty($rootUrl) && is_string($rootUrl)){
            $rootArr = explode('/',$rootUrl);
            if(count($rootArr)>0){
                $url = '';
                foreach($rootArr as $key=>$value){
                    $url .= $value.'/';
                    $this->checkDir($url);
                }
            }
        }

    }

	/**
	 * 检查并创建单一目录
	 *
	 * @param String $root--目录
	 */
	public function checkDir($root){
		if(!file_exists($root)){
			mkdir($root);
			if(!chmod($root, 0777)){
				$this->error(L('OPERATION_FAILURE'));
			}
		}
	}

	/**
	 * 获取该学段下所有年级和学科的所有六级知识点目录
	 *
	 * @return Array
	 */
	public function getPointAll(){
		$one = $this->getPoint('',1,true);
		$one_list = array();
		if(!empty($one)){
			foreach($one as $key=>$value){
				$k = strval($value['cate_id']);
				$one_list[$k][] = $value;
			}
		}

		$two = $this->getPoint('',2);
		$two_list = array();
		if(!empty($two)){
			foreach($two as $key=>$value){
				$k = substr($value['alias'],0,-3);
				$two_list[$k][] = $value;
			}
		}

		$three = $this->getPoint('',3);
		$three_list = array();
		if(!empty($three)){
			foreach($three as $key=>$value){
				$k = substr($value['alias'],0,-3);
				$three_list[$k][] = $value;
			}
		}

		$four = $this->getPoint('',4);
		$four_list = array();
		if(!empty($four)){
			foreach($four as $key=>$value){
				$k = substr($value['alias'],0,-3);
				$four_list[$k][] = $value;
			}
		}

		$five = $this->getPoint('',5);
		$five_list = array();
		if(!empty($five)){
			foreach($five as $key=>$value){
				$k = substr($value['alias'],0,-3);
				$five_list[$k][] = $value;
			}
		}

		$six = $this->getPoint('',6);
		$six_list = array();
		if(!empty($six)){
			foreach($six as $key=>$value){
				$k = substr($value['alias'],0,-3);
				$six_list[$k][] = $value;
			}
		}

		$arr = array(
			'one_list'=>$one_list,
			'two_list'=>$two_list,
			'three_list'=>$three_list,
			'four_list'=>$four_list,
			'five_list'=>$five_list,
			'six_list'=>$six_list
		);
		return $arr;
	}

	/**
	 * 根据条件获取知识点列表
	 *
	 * @param String $cate_id 学科id
	 * @param Int $level 知识点级别
	 * @param Bool $gc 是否需要年级和学科字段
	 * @param String $key 结果数组的键名
	 * @return Array
	 */
	public function getPoint($cate_id='',$level=0,$gc=false,$key='id'){
		$where = '1=1';
		if($level>0){
			$where .= " AND level=$level";
		}
		$where .= " AND period_id=$this->period_id";
		if(!empty($cate_id)){
			$where .= " AND cate_id=$cate_id";
		}
        $where .= " AND status=1";
		if($gc){
			$field = 'id,alias,name,cate_id';
		}else{
			$field = 'id,alias,name';
		}
		$point_list = $this->point_mod->where($where)->field($field)->select();
		if($point_list){
			$point_list = array_to_key($point_list,$key);
			return $point_list;
		}else{
			return array();
		}
	}

	/**
	 * 检查上传的文档类型
	 *
	 * @param Array $file--文档信息
	 * @param String $type--要求的文档类型'word','png' or 'pdf'
	 * @return Boolean
	 */
	public function checkFileType($file,$type){
		$style = '';
		$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
		if($type=='word'){
			$type = 'doc';
			$style = 'application/msword';
			if($ext=='docx'){
				$type = 'docx';
				$style = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
			}
		}elseif($type=='png'){
			$style = 'image/png';
			if($ext=='jpg'){
				$type = 'jpg';
				$style = 'image/jpeg';
			}
		}elseif($type=='pdf'){
            $style = 'application/pdf';
        }

		if($ext==$type && $file['type']==$style){
			return true;
		}else{
			return false;
		}
	}

    /**
     * 删除题目word文档和png图片
     *
     * @param String $dir--准备删除的文件夹url
     * @param String $net_logo--题目序号
     * @return Boolean
     */
    public function delFile($dir,$net_logo){
        $word_url = $dir.$net_logo.'.doc';
        $word_url2 = $dir.$net_logo.'.docx';
        $png_url = $dir.$net_logo.'.png';
        $png_url2 = $dir.$net_logo.'.jpg';
        $pdf_url = $dir.$net_logo.'.pdf';
        if(file_exists($word_url)){
            unlink($word_url);
        }
		if(file_exists($word_url2)){
			unlink($word_url2);
		}
        if(file_exists($png_url)){
            unlink($png_url);
        }
		if(file_exists($png_url2)){
			unlink($png_url2);
		}
        if(file_exists($pdf_url)){
            unlink($pdf_url);
        }
        rmdir($dir);
    }

	/**
	 * 保存导入的excel数据
	 *
	 *
	 */
	public function insertExcel(){
		$grade_id = $_POST['grade_id'];
		$cate_id = $_POST['cate_id'];
		$create_id = $_SESSION['admin_info']['id'];
		$school_id = $_SESSION['admin_info']['school_id'];

		$save_path = "xls/";
		$file_name = $save_path.date('Ymdhis') . ".xls";
		if (move_uploaded_file($_FILES['file']['tmp_name'], $file_name)) {
			include("excel/reader.php");
			$xls = new Spreadsheet_Excel_Reader();
			$xls->setOutputEncoding('utf-8');
			$xls->read($file_name);
			$data_values = '';
			for ($i=3; $i<=$xls->sheets[0]['numRows']; $i++) {
				$grade = $xls->sheets[0]['cells'][$i][1];
				$site_logo = $xls->sheets[0]['cells'][$i][2];
				$net_logo = $xls->sheets[0]['cells'][$i][3];
				$name = $xls->sheets[0]['cells'][$i][4];
				$recommend = $xls->sheets[0]['cells'][$i][5];
				$answer = $xls->sheets[0]['cells'][$i][6];
				$installment = $xls->sheets[0]['cells'][$i][7];
				$has_invoice = $xls->sheets[0]['cells'][$i][8];
				$cash_back_rate = $xls->sheets[0]['cells'][$i][9];
				$title_attribute = $xls->sheets[0]['cells'][$i][10];
				$subject = $xls->sheets[0]['cells'][$i][11];
				$update_time = date("Y-m-d h:i:s",time());
				$data_values .= "('$grade_id','$cate_id','$site_logo','$net_logo','$name','$recommend','$answer','$installment','$has_invoice','$cash_back_rate','$title_attribute','$subject','1','$create_id','$school_id','$update_time'),";
			}
			$data_values = substr($data_values,0,-1); //去掉最后一个逗号
			$sql = "insert into ".C('DB_PREFIX').$this->question_tab." (grade_id,cate_id,site_logo,net_logo,name,recommend,answer,installment,has_invoice,cash_back_rate,title_attribute,subject,status,create_id,school_id,update_time) values $data_values";
			$query = mysql_query($sql);//批量插入数据表中
			unlink($file_name);
			if($query){
				$this->success('导入成功！');
				exit();
			}else{
				$this->error(L('operation_failure'));
				exit();
			}
		}
	}
}