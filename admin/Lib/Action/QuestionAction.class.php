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
		$question_mod = M('question');
		$grade_list = $this->getGradeList();
		$cate_list = $this->getCateList();
		$style_list = $this->getStyleList();
		//获取搜索条件
		$grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
		$cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
		$point_id=isset($_GET['point_id'])?trim($_GET['point_id']):'';
		$chapter_id=isset($_GET['point_id'])?trim($_GET['point_id']):'';
		$section_id=isset($_GET['point_id'])?trim($_GET['point_id']):'';
		$style_id=isset($_GET['point_id'])?trim($_GET['point_id']):'';
		$type_id=isset($_GET['point_id'])?trim($_GET['point_id']):'';
		//搜索
		$period_id = $this->getPeriod();
		$where = "period_id={$period_id}";
		if ($grade_id!='') {
			$where .= " AND grade_id=$grade_id";
			$this->assign('grade_id', $grade_id);
		}
		if ($cate_id!='') {
			$where .= " AND cate_id=$cate_id";
			$this->assign('cate_id', $cate_id);
		}
		if ($point_id!='') {
			$where .= " AND point_id=$point_id";
			$this->assign('point_id', $point_id);
		}
		if ($chapter_id!='') {
			$where .= " AND chapter_id=$chapter_id";
			$this->assign('chapter_id', $chapter_id);
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
		$question_list = $question_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('grade_id asc,cate_id asc,sort asc')->select();
		foreach($question_list as $key=>$value){
			$question_list[$key]['grade'] = $grade_list[$value['grade_id']]['name'];
			$question_list[$key]['cate'] = $cate_list[$value['cate_id']]['name'];
			$question_list[$key]['style'] = $style_list[$value['cate_id']]['name'];
		}
		$page = $p->show();
		$this->assign('page',$page);
		$this->assign('controller',MODULE_NAME);
		$this->assign('grade_list',$grade_list);
		$this->assign('cate_list',$cate_list);
		$this->assign('style_list',$style_list);
		$this->display();
	}

	/**
	 * 上传新题
	 *
	 */
	public function upnew(){
		$grade_list = $this->getGradeList();
		$cate_list = $this->getCateList();
		$style_list = $this->getStyleList();
		$this->assign('grade_list',$grade_list);
		$this->assign('cate_list',$cate_list);
		$this->assign('style_list',$style_list);
		$this->assign('controller',MODULE_NAME);
		$this->display();
	}

	/**
	 * 保存新题
	 *
	 * @return Array
	 */
	public function upsave(){
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
		$id = $question_mod->add($data);
		$flag = true;
		if($id){
			//将题干和题目解析存入文档
			//题干和题目解析的文档存放目录均为'./控制器名/grade_id(年级id)/cate_id(学科id)/question_id(题目id)
			$root = 'upload/'.MODULE_NAME.'/'.'grade_'.$data['grade_id'].'/cate_'.$data['cate_id'].'/';
			$title_dir = $root.'question_'.$id.'/';
			$this->checkDir($title_dir);
			$title_url = $title_dir.'title_'.$id.'.txt';
			$title_file = fopen($title_url,"w");
			fwrite($title_file, $title);
			fclose($title_file);

			$info_url = $title_dir.'info_'.$id.'.txt';
			$info_file = fopen($info_url,"w");
			fwrite($info_file, $info);
			fclose($info_file);

			$arr = array(
				'id' => $id,
				'title_url' => $title_url,
				'info_url' => $info_url
			);
			$result = $question_mod->save($arr);
			if(!$result){
				$flag = false;
			}

		}else{
			$flag = false;
		}

		if($flag){
			$this->success(L('operation_success'));
			exit();
		}else{
			$this->error(L('operation_failure'));
		}
	}

	/**
	 * 获取知识点列表
	 *
	 * @return Array
	 */
	public function getPointList(){
		$grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
		$cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
		$point_list = array();
		if($grade_id!='' && $cate_id!=''){
			$period_id = $this->getPeriod();
			$where = "period_id=$period_id";
			$where .= " AND grade_id=$grade_id";
			$where .= " AND cate_id=$cate_id";
			$point_list = M("point")->where($where)->field('id,name')->select();
		}
		$this->ajaxReturn($point_list,'JSON');
	}

	/**
	 * 获取章列表
	 *
	 * @return Array
	 */
	public function getChapterList(){
		$grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
		$cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
		if($grade_id!='' && $cate_id!=''){
			$period_id = $this->getPeriod();
			$where = "period_id=$period_id";
			$where .= " AND grade_id=$grade_id";
			$where .= " AND cate_id=$cate_id";
			$chapter_list = M("chapter")->where($where)->field('id,alias,name')->select();
			if($chapter_list){
				foreach($chapter_list as $key=>&$value){
					$value['section'] = $this->getSectionList($value['id']);
				}
			}
			$this->ajaxReturn($chapter_list,'JSON');
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
			return $section_list;
		}
	}

	/**
	 * 获取题目类型列表
	 *
	 * @return Array
	 */
	public function getTypeList(){
		$cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
		$period_id = $this->getPeriod();
		$all_list = M("question_type")->where("period_id=$period_id AND cate_id=-1")->field('id,name')->select();
		$type_list = array();
		if($cate_id!=''){
			$where = "period_id=$period_id";
			$where .= " AND cate_id=$cate_id";
			$type_list = M("question_type")->where($where)->select();

		}
		$this->ajaxReturn(array_merge($all_list,$type_list),'JSON');
	}

	/**
	 * 获取测验类型列表
	 *
	 * @return Array
	 */
	public function getStyleList(){
		$period_id = $this->getPeriod();
		$style_list = M('style')->where("period_id=$period_id")->field('id,name')->select();
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