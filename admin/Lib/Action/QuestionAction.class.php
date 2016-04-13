<?php
/**
 * 上传新题公共控制器
 *
 * Created by Sunmiaomiao.
 * Email: sunmiaomiao@kangq.com
 * Date: 2016/4/5
 * Time: 14:53
 */
class QuestionAction extends BaseAction{

	/**
	 * 千校云题库
	 *
	 */
	public function yun(){
		//获取搜索条件
		$arrGet = array();
		$grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
		if($grade_id!=''){
			$arrGet['grade_id'] = $grade_id;
		}
		$cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
		if($cate_id!=''){
			$arrGet['cate_id'] = $cate_id;
		}
		$point_id=isset($_GET['point_id'])?trim($_GET['point_id']):'';
		if($point_id!=''){
			$arrGet['point_id'] = $point_id;
		}
		$chapter_id=isset($_GET['chapter_id'])?trim($_GET['chapter_id']):'';
		if($chapter_id!=''){
			$arrGet['chapter_id'] = $chapter_id;
		}
		$section_id=isset($_GET['section_id'])?trim($_GET['section_id']):'';
		if($section_id!=''){
			$arrGet['section_id'] = $section_id;
		}
		$style_id=isset($_GET['style_id'])?trim($_GET['style_id']):'';
		if($style_id!=''){
			$arrGet['style_id'] = $style_id;
		}
		$type_id=isset($_GET['type_id'])?trim($_GET['type_id']):'';
		if($type_id!=''){
			$arrGet['type_id'] = $type_id;
		}

		$this->getQuestionList('','yun',$arrGet);
	}

	/**
	 * 本校题库
	 *
	 */
	public function school(){
		//获取搜索条件
		$arrGet = array();
		$grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
		if($grade_id!=''){
			$arrGet['grade_id'] = $grade_id;
		}
		$cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
		if($cate_id!=''){
			$arrGet['cate_id'] = $cate_id;
		}
		$point_id=isset($_GET['point_id'])?trim($_GET['point_id']):'';
		if($point_id!=''){
			$arrGet['point_id'] = $point_id;
		}
		$chapter_id=isset($_GET['chapter_id'])?trim($_GET['chapter_id']):'';
		if($chapter_id!=''){
			$arrGet['chapter_id'] = $chapter_id;
		}
		$section_id=isset($_GET['section_id'])?trim($_GET['section_id']):'';
		if($section_id!=''){
			$arrGet['section_id'] = $section_id;
		}
		$style_id=isset($_GET['style_id'])?trim($_GET['style_id']):'';
		if($style_id!=''){
			$arrGet['style_id'] = $style_id;
		}
		$type_id=isset($_GET['type_id'])?trim($_GET['type_id']):'';
		if($type_id!=''){
			$arrGet['type_id'] = $type_id;
		}

		//取得管理员的所属学校id
		$school_id = $_SESSION['admin_info']['school_id'];
		if($school_id > 0){
			$condition = " AND school_id=$school_id";
		}else{
			$condition = '';
		}
		$this->getQuestionList($condition,'school',$arrGet);
	}

	/**
	 * 本校题库
	 *
	 */
	public function my(){
		//获取搜索条件
		$arrGet = array();
		$grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
		if($grade_id!=''){
			$arrGet['grade_id'] = $grade_id;
		}
		$cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
		if($cate_id!=''){
			$arrGet['cate_id'] = $cate_id;
		}
		$point_id=isset($_GET['point_id'])?trim($_GET['point_id']):'';
		if($point_id!=''){
			$arrGet['point_id'] = $point_id;
		}
		$chapter_id=isset($_GET['chapter_id'])?trim($_GET['chapter_id']):'';
		if($chapter_id!=''){
			$arrGet['chapter_id'] = $chapter_id;
		}
		$section_id=isset($_GET['section_id'])?trim($_GET['section_id']):'';
		if($section_id!=''){
			$arrGet['section_id'] = $section_id;
		}
		$style_id=isset($_GET['style_id'])?trim($_GET['style_id']):'';
		if($style_id!=''){
			$arrGet['style_id'] = $style_id;
		}
		$type_id=isset($_GET['type_id'])?trim($_GET['type_id']):'';
		if($type_id!=''){
			$arrGet['type_id'] = $type_id;
		}

		//取得管理员id
		$result = M('admin_question')->where("admin_id={$_SESSION['admin_info']['id']}")->find();
		$question_ids = trim($result['question_ids'],',');
		$condition = " AND id IN ($question_ids)";
		$this->getQuestionList($condition,'my',$arrGet);
	}

	/**
	 * 题库
	 *
	 * @param String $condition 固定搜索条件
	 * @param String $display 要加载的视图文件
	 * @param Array $arrGet 前台get来的搜索条件
	 */
	public function getQuestionList($condition='',$display='',$arrGet){
		$question_mod = M('question');
		$grade_list = $this->getGradeList();
		$cate_list = $this->getCateList();
		$style_list = $this->getStyleList();

		//获取搜索条件
		if(!empty($arrGet)){
			$grade_id = $arrGet['grade_id'];
			$cate_id = $arrGet['cate_id'];
			$point_id = $arrGet['point_id'];
			$chapter_id = $arrGet['chapter_id'];
			$section_id = $arrGet['section_id'];
			$style_id = $arrGet['style_id'];
			$type_id = $arrGet['type_id'];
		}else{
			$grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
			$cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
			$point_id=isset($_GET['point_id'])?trim($_GET['point_id']):'';
			$chapter_id=isset($_GET['chapter_id'])?trim($_GET['chapter_id']):'';
			$section_id=isset($_GET['section_id'])?trim($_GET['section_id']):'';
			$style_id=isset($_GET['style_id'])?trim($_GET['style_id']):'';
			$type_id=isset($_GET['type_id'])?trim($_GET['type_id']):'';
		}

		//搜索
		$period_id = $this->getPeriod();
		$where = "period_id={$period_id}".$condition;
		if ($grade_id!='') {
			$where .= " AND grade_id=$grade_id";
			$this->assign('grade_id', $grade_id);
		}
		if ($cate_id!='') {
			$where .= " AND cate_id=$cate_id";
			$this->assign('cate_id', $cate_id);
			$type_list = $this->getTypeAll($cate_id);
			$this->assign('type_list',$type_list);
		}
		if($grade_id!='' && $cate_id!='') {
			$point_list = $this->getPointAll($grade_id,$cate_id);
			$chapter_list = $this->getChapterAll($grade_id,$cate_id);
			$this->assign('point_list',$point_list);
			$this->assign('chapter_list',$chapter_list);
		}
		if ($point_id!='') {
			$where .= " AND point_id=$point_id";
			$this->assign('point_id', $point_id);
		}
		if ($chapter_id!='') {
			$where .= " AND chapter_id=$chapter_id";
			$this->assign('chapter_id', $chapter_id);
			$section_list = $this->getSectionList($chapter_id);
			$this->assign('section_list',$section_list);
		}
		if ($section_id!='') {
			$where .= " AND section_id=$section_id";
			$this->assign('section_id', $section_id);
		}
		if ($style_id!='') {
			$where .= " AND style_id=$style_id";
			$this->assign('style_id', $style_id);
		}
		if ($type_id!='') {
			$where .= " AND type_id=$type_id";
			$this->assign('type_id', $type_id);
		}
		import("ORG.Util.Page");
		$count = $question_mod->where($where)->count();
		$p = new Page($count,15);
		$question_list = $question_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('grade_id asc,cate_id asc,sort asc,id desc')->select();
		foreach($question_list as $key=>$value){
			$question_list[$key]['grade'] = $grade_list[$value['grade_id']]['name'];
			$question_list[$key]['cate'] = $cate_list[$value['cate_id']]['name'];
			$question_list[$key]['style'] = $style_list[$value['style_id']]['name'];
			$title = $this->cleanFormat(file_get_contents($value['title_url']));
			$question_list[$key]['title'] = $this->cutString($title,8);
			$point = M('point')->where("id={$value['point_id']}")->find();
			$question_list[$key]['point'] = $this->cutString($point['name'],8);
			$chapter = M('chapter')->where("id={$value['chapter_id']}")->find();
			$question_list[$key]['chapter'] = $this->cutString($chapter['name'],8);
			$section = M('section')->where("id={$value['section_id']}")->find();
			$question_list[$key]['section'] = $this->cutString($section['name'],8);
			$type = M('question_type')->where("id={$value['type_id']}")->find();
			$question_list[$key]['type'] = $this->cutString($type['name'],8);
		}
		$page = $p->show();
		$this->assign('page',$page);
		$this->assign('controller',MODULE_NAME);
		$this->assign('grade_list',$grade_list);
		$this->assign('cate_list',$cate_list);
		$this->assign('style_list',$style_list);
		$this->assign('question_list',$question_list);
		$this->assign('display',$display);
		$this->display($display);
	}

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
	 * 上传新题或编辑题目
	 *
	 * @param String $id 编辑题目id
	 */
	public function upnew($id=''){
		$grade_list = $this->getGradeList();
		$cate_list = $this->getCateList();
		$style_list = $this->getStyleList();
		//编辑时有id
		if($id!='' && intval($id)>0){
			$id = intval($id);
			$question_info = M("question")->where("id=$id")->find();
			if($question_info){
				$point_list = $this->getPointAll($question_info['grade_id'],$question_info['cate_id']);
				$chapter_list = $this->getChapterAll($question_info['grade_id'],$question_info['cate_id']);
				$section_list = $this->getSectionList($question_info['chapter_id']);
				$type_list = $this->getTypeAll($question_info['cate_id']);
				$title = file_get_contents($question_info['title_url']);
				$info = file_get_contents($question_info['info_url']);
				$this->assign('id',$id);
				$this->assign('question_info',$question_info);
				$this->assign('point_list',$point_list);
				$this->assign('chapter_list',$chapter_list);
				$this->assign('section_list',$section_list);
				$this->assign('type_list',$type_list);
				$this->assign('title',$title);
				$this->assign('info',$info);
			}
		}
		$this->assign('grade_list',$grade_list);
		$this->assign('cate_list',$cate_list);
		$this->assign('style_list',$style_list);
		$this->assign('controller',MODULE_NAME);
		$this->display('upnew');
	}

	/**
	 * 保存新题
	 *
	 * @return Array
	 */
	public function upsave(){
		$id = intval($_POST['id']);
		//取得题干和题目解析
		$title = $_POST['title'];
		unset($_POST['title']);
		$info = $_POST['info'];
		unset($_POST['info']);

		//检测上传题目存放目录是否存在，不存在就创建
		$this->checkQuestionDir();

		$question_mod = M('question');
		$data = $question_mod->create();
		if(false === $data){
			$this->error($question_mod->error());
		}
		$data['period_id'] = $this->getPeriod();
		$data['school_id'] = $_SESSION['admin_info']['school_id'];
		$data['create_id'] = $_SESSION['admin_info']['id'];
		$data['create_time'] = time();
		$flag = true;
		if($id > 0){
			//编辑
			$result = $question_mod->save($data);
			if(!$result){
				$flag = false;
			}
			$question_info = $question_mod->where("id=$id")->find();
			$title_url = $question_info['title_url'];
			$info_url = $question_info['info_url'];
		}else{
			//新增
			$id = $question_mod->add($data);
			if(!$id){
				$flag = false;
			}
			//获取题干和题目解析的文档存放目录
			$question_dir = $this->getQuestionDir($data['grade_id'],$data['cate_id'],$id);
			$this->checkDir($question_dir);
			$title_url = $question_dir.'title_'.$id.'.doc';
			$info_url = $question_dir.'info_'.$id.'.doc';
			$arr = array(
				'id' => $id,
				'title_url' => $title_url,
				'info_url' => $info_url
			);
			$result = $question_mod->save($arr);
			if(!$result){
				$flag = false;
			}
		}

		//将题干和题目解析存入文档
		$title_file = fopen($title_url,"w");
		fwrite($title_file, $title);
		fclose($title_file);

		$info_file = fopen($info_url,"w");
		fwrite($info_file, $info);
		fclose($info_file);

		if($flag){
			$redirect_url = U(MODULE_NAME.'/yun');
			$this->success(L('operation_success'),$redirect_url);
			exit();
		}else{
			$this->error(L('operation_failure'));
			exit();
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