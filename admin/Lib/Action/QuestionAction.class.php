<?php
/**
 * 题库公共控制器，主要包括：
 * 1.千校云题库，2.本校题库，3.我的题库，4.获取题库列表
 *
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/4/5
 * Time: 14:53
 */
class QuestionAction extends QuestionLoadAction{

	/**
	 * 千校云题库
	 *
	 */
	public function yun(){
//		var_dump($_GET);
//		exit;
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
		$point_id=isset($_GET['point_id'])?$_GET['point_id']:'';
		if($point_id!=''){
			$arrGet['point_id'] = $point_id;
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
	 * 我的题库
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
//		$result = M('admin_question')->where("admin_id={$_SESSION['admin_info']['id']}")->find();
//		$question_ids = trim($result['question_ids'],',');
//		$condition = " AND id IN ($question_ids)";
//		$this->getQuestionList($condition,'my',$arrGet);
	}

	/**
	 * 获取题库列表
	 *
	 * @param String $condition 固定搜索条件
	 * @param String $display 要加载的视图文件
	 * @param Array $arrGet 前台get来的搜索条件
	 */
	public function getQuestionList($condition='',$display='',$arrGet){

		//获取搜索条件
		if(!empty($arrGet)){
			$grade_id = $arrGet['grade_id'];
			$cate_id = $arrGet['cate_id'];
			$point_id = $arrGet['point_id'];
		}else{
			$grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
			$cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
			$point_id=isset($_GET['point_id'])?$_GET['point_id']:'';
		}

		//搜索
		$where = "period_id={$this->period_id}".$condition;
		if ($grade_id!='') {
			$where .= " AND grade_id=$grade_id";
			$this->assign('grade_id', $grade_id);
		}
		if ($cate_id!='') {
			$where .= " AND cate_id=$cate_id";
			$this->assign('cate_id', $cate_id);
		}
		if($grade_id!='' && $cate_id!=''){
			$checked = array();
			if (!empty($point_id) && is_array($point_id)) {
				$where .= " AND (";
				foreach($point_id as $key=>$value){
					$where .= " title_attribute LIKE '%{$value}%' OR";
					$checked[$value] = 'checked';
				}
				$where = rtrim($where,'OR');
				$where .= ")";
				$this->assign('point_id',$point_id);
				$this->assign('checked',json_encode($checked));
			}
		}

		import("ORG.Util.Page");
		$count = $this->question_mod->where($where)->count();
		$p = new Page($count,15);
		$question_list = $this->question_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('grade_id asc,cate_id asc,id desc')->select();
//		echo $this->question_mod->getLastSql();
//		$collect_list = $this->getCollectAll();
		foreach($question_list as $key=>&$value){
			$value['grade'] = $this->grade_list[$value['grade_id']]['name'];
			$value['cate'] = $this->cate_list[$value['cate_id']]['name'];
			$value['name'] = cutString($value['name'],30);
			//取收藏记录
//			if(!empty($collect_list[$value['id']])){
//				$value['is_collect'] = 1;
//				$value['is_download'] = $collect_list[$value['id']]['is_download'];
//			}else{
//				$value['is_collect'] = 0;
//				$value['is_download'] = 0;
//			}
		}
		$page = $p->show();
		$this->assign('page',$page);
		$this->assign('controller',MODULE_NAME);
		$this->assign('grade_list',$this->grade_list);
		$this->assign('cate_list',$this->cate_list);
		$this->assign('question_list',$question_list);
		$this->assign('display',$display);
		$this->display($display);
	}





}