<?php
/**
 * 题目工具公共控制器，主要功能有：
 * 题目编辑、排序、修改状态、删除；获取知识点、章、节、题目类型列表；
 * 检测并创建题目上传目录；字符串截取等
 *
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/4/14
 * Time: 9:22
 */
class QuestionToolAction extends QuestionBaseAction{

	/**
	 * 编辑题目
	 *
	 */
	public function editQuestion(){
		if(isset($_GET['id']) && intval($_GET['id'])){
			$id = intval($_GET['id']);
			$this->upnew($id);
		}else{
			$this->error(L('please_select'));
		}
	}

	/**
	 * 修改题目状态
	 *
	 */
	function statusQuestion()
	{
		$question_mod = M('question');
		$id = intval($_REQUEST['id']);
		$type = trim($_REQUEST['type']);
		$sql = "update " . C('DB_PREFIX') . "question set $type=($type+1)%2 where id='$id'";
		$question_mod->execute($sql);
		$values = $question_mod->where('id=' . $id)->find();
		$this->ajaxReturn($values[$type]);
	}

	/**
	 * 题目排序
	 *
	 */
	public function sortQuestion(){
		$question_mod = M('question');
		$id = intval($_REQUEST['id']);
		$type = trim($_REQUEST['type']);
		$num = trim($_REQUEST['num']);
		if(!is_numeric($num)){
			$values = $question_mod->where('id='.$id)->find();
			$this->ajaxReturn($values[$type]);
			exit;
		}
		$sql    = "update ".C('DB_PREFIX').'question'." set $type=$num where id='$id'";

		$question_mod->execute($sql);
		$values = $question_mod->where('id='.$id)->find();
		$this->ajaxReturn($values[$type]);
	}

	/**
	 * 删除题目
	 *
	 */
	public function deleteQuestion(){
		$flag = true;
		if (isset($_POST['id']) && is_array($_POST['id'])) {
			$question_mod = M('question');
			$id_array=$_POST['id'];
			for ($i=0;$i<count($id_array);$i++){
				$question_info = $question_mod->where("id={$id_array[$i]}")->find();
				//先删除题目信息存放文件和目录
				unlink($question_info['title_url']);
				unlink($question_info['info_url']);
				$question_dir = $this->getQuestionDir($question_info['grade_id'],$question_info['cate_id'],$id_array[$i]);
				rmdir($question_dir);
				//再删除数据表中数据
				$result = $question_mod->where("id={$id_array[$i]}")->delete();
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

	/**
	 * 获取知识点列表--响应ajax请求
	 *
	 * @return Array
	 */
	public function getPointList(){
		$grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
		$cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
		$point_list = $this->getPointAll($grade_id,$cate_id);
		$this->ajaxReturn($point_list,'JSON');
	}

	/**
	 * 根据条件获取章列表
	 *
	 * @param String $grade_id 年级id
	 * @param String $cate_id 学科id
	 * @return Array
	 */
	public function getPointAll($grade_id='',$cate_id=''){
		if($grade_id!='' && $cate_id!=''){
			$period_id = $this->getPeriod();
			$where = "period_id=$period_id";
			$where .= " AND grade_id=$grade_id";
			$where .= " AND cate_id=$cate_id";
			$point_list = M("point")->where($where)->field('id,name')->select();
			if($point_list){
				//超过8个字就把多余的字符换成...显示
				foreach($point_list as $key=>&$value){
					$value['name'] = $this->cutString($value['name'],8);
				}
				return $point_list;
			}else{
				return array();
			}
		}

	}

	/**
	 * 获取章列表--响应ajax请求
	 *
	 * @return Array
	 */
	public function getChapterList(){
		$grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
		$cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
		$chapter_list = $this->getChapterAll($grade_id,$cate_id);
		if(!empty($chapter_list)){
			foreach($chapter_list as $key=>&$value){
				//获取节列表
				$value['section'] = $this->getSectionList($value['id']);
			}
		}
		$this->ajaxReturn($chapter_list,'JSON');

	}

	/**
	 * 根据条件获取章列表
	 *
	 * @param String $grade_id 年级id
	 * @param String $cate_id 学科id
	 * @return Array
	 */
	public function getChapterAll($grade_id='',$cate_id=''){
		$chapter_list = array();
		if($grade_id!='' && $cate_id!=''){
			$period_id = $this->getPeriod();
			$where = "period_id=$period_id";
			$where .= " AND grade_id=$grade_id";
			$where .= " AND cate_id=$cate_id";
			$chapter_list = M("chapter")->where($where)->field('id,alias,name')->select();
			if(is_array($chapter_list) && !empty($chapter_list)){
				//超过8个字就把多余的字符换成...显示
				foreach($chapter_list as $key=>&$value){
					$value['name'] = $this->cutString($value['name'],8);
				}
				return $chapter_list;
			}else{
				return array();
			}
		}

	}

	/**
	 * 获取节列表
	 *
	 * @param Int $chapter_id 章id
	 * @return Array
	 */
	public function getSectionList($chapter_id){
		if($chapter_id!=''){
			$where = "chapter_id=$chapter_id";
			$section_list = M("section")->where($where)->field('id,alias,name')->select();
			if(is_array($section_list) && !empty($section_list)){
				//超过8个字就把多余的字符换成...显示
				foreach($section_list as $key=>&$value){
					$value['name'] = $this->cutString($value['name'],8);
				}
				return $section_list;
			}else{
				return array();
			}

		}else{
			return false;
		}
	}

	/**
	 * 获取题目类型列表--响应ajax请求
	 *
	 * @return Array
	 */
	public function getTypeList(){
		$cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
		$type_list = $this->getTypeAll($cate_id);
		$this->ajaxReturn($type_list,'JSON');
	}

	/**
	 * 根据条件获取题目类型列表
	 *
	 * @param String $cate_id 学科id
	 * @return Array
	 */
	public function getTypeAll($cate_id=''){
		$period_id = $this->getPeriod();
		//获取所有学科下的题目类型
		$all_list = M("question_type")->where("period_id=$period_id AND cate_id=-1")->field('id,name')->select();
		if(is_array($all_list) && !empty($all_list)){
			//超过8个字就把多余的字符换成...显示
			foreach($all_list as $key=>&$value){
				$value['name'] = $this->cutString($value['name'],8);
			}
		}else{
			$all_list = array();
		}
		//存取单一学科的题目类型
		$type_list = array();
		if($cate_id!=''){
			$where = "period_id=$period_id";
			$where .= " AND cate_id=$cate_id";
			$type_list = M("question_type")->where($where)->select();
			if(is_array($type_list) && !empty($type_list)){
				//超过8个字就把多余的字符换成...显示
				foreach($type_list as $key=>&$value){
					$value['name'] = $this->cutString($value['name'],8);
				}
			}else{
				$type_list = array();
			}
		}
		return array_merge($all_list,$type_list);
	}


	/**
	 * 获取测验类型列表
	 *
	 * @return Array
	 */
	public function getStyleList(){
		$period_id = $this->getPeriod();
		$style_list = M('style')->where("period_id=$period_id")->field('id,name')->select();
		if($style_list){
			//超过8个字就把多余的字符换成...显示
			foreach($style_list as $key=>&$value){
				$value['name'] = $this->cutString($value['name'],8);
			}
		}
		return array_to_key($style_list,'id');
	}

	/**
	 * 获取学科列表
	 *
	 * @return Array
	 */
	public function getCateList(){
		$period_id = $this->getPeriod();
		$cate_mod = '';
		switch($period_id){
			case '1':
				$cate_mod = 'adboard';
				break;
			case '2':
				$cate_mod = 'seller_cate';
				break;
			case '3':
				$cate_mod = 'article_cate';
				break;
		}
		$cate_list = M($cate_mod)->field('id,name')->select();
		if(!empty($cate_list)){
			//超过8个字就把多余的字符换成...显示
			foreach($cate_list as $key=>&$value){
				$value['name'] = $this->cutString($value['name'],8);
			}

			//把id的值作为键名，重新组合数组
			$cate_list = array_to_key($cate_list,'id');
		}
		return $cate_list;
	}

	/**
	 * 获取年级列表
	 *
	 * @return Array
	 */
	public function getGradeList(){
		$period_id = $this->getPeriod();
		$grade_list = M('grade')->where("period_id=$period_id")->field('id,name')->select();
		if(!empty($grade_list)){
			//超过8个字就把多余的字符换成...显示
			foreach($grade_list as $key=>&$value){
				$value['name'] = $this->cutString($value['name'],8);
			}
			//把id的值作为键名，重新组合数组
			$grade_list = array_to_key($grade_list,'id');
		}
		return $grade_list;
	}

	/**
	 * 获取学段id
	 *
	 */
	public function getPeriod(){
		$period_id = 0;
		switch(MODULE_NAME){
			case 'Ad':
				$period_id = 1;
				break;
			case 'SellerList':
				$period_id = 2;
				break;
			case 'Article':
				$period_id = 3;
				break;
		}
		return $period_id;
	}

	/**
	 * 字符串超过一定个数截断显示
	 *
	 * @param String $str 要截掉的字符串
	 * @param Int $num 允许显示的字符最大个数
	 * @return String
	 */
	public function cutString($str,$num){
		if(is_string($str) && !empty($str)){
			$str = mb_strlen($str,'utf8')>$num ? mb_substr($str,0,$num,'utf8').'...' : $str;
		}
		return $str;
	}

	/**
	 * 去除字符串的所有格式，获取纯文本内容
	 *
	 * @param String $str 目标字符串
	 * @return String
	 */
	public function cleanFormat($str){
		$str = trim($str); //清除字符串两边的空格
		$str = strip_tags($str,""); //利用php自带的函数清除html格式
		$str = preg_replace("/\t/","",$str); //使用正则表达式替换内容，如：空格，换行，并将替换为空。
		$str = preg_replace("/\r\n/","",$str);
		$str = preg_replace("/\r/","",$str);
		$str = preg_replace("/\n/","",$str);
		$str = preg_replace("/ /","",$str);
		$str = preg_replace("/&nbsp;/","",$str);  //匹配html中的空格
		return trim($str);
	}

	/**
	 * 获取题目存放目录,格式为'./控制器名/grade_id(年级id)/cate_id(学科id)/question_id(题目id)
	 *
	 * @param Int $grade_id 年级id
	 * @param Int $cate_id 学科id
	 * @param Int $id 题目id
	 * @return String
	 */
	public function getQuestionDir($grade_id,$cate_id,$id){
		$root = 'upload/'.MODULE_NAME.'/'.'grade_'.$grade_id.'/cate_'.$cate_id.'/';
		$question_dir = $root.'question_'.$id.'/';
		return $question_dir;
	}

	/**
	 * 判断或创建题目目录
	 *
	 */
	public function checkQuestionDir(){
		//定义题目上传根目录(相对路径)
		$root = 'upload/';
		$this->checkDir($root);
		//学段（小学、初中、高中题目各自存放目录）
		$period_root = $root.MODULE_NAME.'/';
		$this->checkDir($period_root);
		//年级目录
		$grade_root_arr = $this->getGradeList();
		//学科目录
		$cate_root_arr = $this->getCateList();
		if(!empty($grade_root_arr)){
			//检测年级目录
			$this->checkDirArr($period_root,'grade_',$grade_root_arr);
			//检测学科目录
			if(count($grade_root_arr) > 0){
				foreach($grade_root_arr as $key=>$value){
					$this->checkDirArr($period_root.'grade_'.$value['id'].'/','cate_', $cate_root_arr);
				}
			}
		}
	}

	/**
	 * 检查并创建文件目录
	 *
	 * @param String $root 上级目录，末尾须带‘/’
	 * @param String $prefix 要检查或创建的目标文件前缀
	 * @param Array $rootArr 目录数组
	 */
	public function checkDirArr($root, $prefix,$rootArr){
		if(count($rootArr) > 0){
			foreach($rootArr as $value){
				$this->checkDir($root.$prefix.$value['id'].'/');
			}
		}
	}

	/**
	 * 检查并创建文件目录
	 *
	 * @param String $root	目录
	 */
	public function checkDir($root){
		if(!file_exists($root)){
			mkdir($root);
			if(!chmod($root, 0777)){
				$this->error(L('OPERATION_FAILURE'));
			}
		}
	}
}