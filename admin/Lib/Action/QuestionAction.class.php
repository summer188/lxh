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
		$collect_list = $this->getCollectAll();
		foreach($question_list as $key=>&$value){
			$value['grade'] = $grade_list[$value['grade_id']]['name'];
			$value['cate'] = $cate_list[$value['cate_id']]['name'];
			$value['style'] = $style_list[$value['style_id']]['name'];
			$title = $this->cleanFormat(file_get_contents($value['title_url']));
			$value['title'] = $this->cutString($title,8);
			$point = M('point')->where("id={$value['point_id']}")->find();
			$value['point'] = $this->cutString($point['name'],8);
			$chapter = M('chapter')->where("id={$value['chapter_id']}")->find();
			$value['chapter'] = $this->cutString($chapter['name'],8);
			$section = M('section')->where("id={$value['section_id']}")->find();
			$value['section'] = $this->cutString($section['name'],8);
			$type = M('question_type')->where("id={$value['type_id']}")->find();
			$value['type'] = $this->cutString($type['name'],8);
			//取收藏记录
			if(!empty($collect_list[$value['id']])){
				$value['is_collect'] = 1;
				$value['is_download'] = $collect_list[$value['id']]['is_download'];
			}else{
				$value['is_collect'] = 0;
				$value['is_download'] = 0;
			}
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





}