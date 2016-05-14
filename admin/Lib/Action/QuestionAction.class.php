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
		$point_id=isset($_GET['point_id'])?$_GET['point_id']:'';
		if($point_id!=''){
			$arrGet['point_id'] = $point_id;
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
        $admin_id = $_SESSION['admin_info']['id'];
        $collect_mod = M("{$this->getCollectMod()}");

        //获取搜索条件
        $where = '';

        //我的下载
        $is_download=isset($_GET['download'])?intval($_GET['download']):0;
        if($is_download==1){
            //获取下载列表
            $where_download = "admin_id=$admin_id AND period_id=$this->period_id AND is_download=1";
            $download_list = $collect_mod->where($where_download)->select();
            $where .= "id in(";
            if(is_array($download_list) && count($download_list)>0){
                foreach($download_list as $key=>$value){
                    $where .= "{$value['question_id']},";
                }
            }
            $where = rtrim($where,',');
            $where .= ") AND ";
            $this->assign('download',1);
        }

        //我的收藏
        $is_collect=isset($_GET['collect'])?intval($_GET['collect']):0;
        if($is_collect==1){
            //获取收藏列表
            $where_collect = "admin_id=$admin_id AND period_id=$this->period_id AND is_collect=1";
            $collect_list = $collect_mod->where($where_collect)->select();
            $where .= "id in(";
            if(is_array($collect_list) && count($collect_list)>0){
                foreach($collect_list as $key=>$value){
                    $where .= "{$value['question_id']},";
                }
            }
            $where = rtrim($where,',');
            $where .= ") AND ";
            $this->assign('collect',1);
        }

        $where .= "period_id={$this->period_id}";
        $grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
        if ($grade_id!='') {
            $where .= " AND grade_id=$grade_id";
            $this->assign('grade_id', $grade_id);
        }
        $cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
        if ($cate_id!='') {
            $where .= " AND cate_id=$cate_id";
            $this->assign('cate_id', $cate_id);
        }
        $point_id=isset($_GET['point_id'])?$_GET['point_id']:'';
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

        //我的上传
        $is_upload=isset($_GET['upload'])?intval($_GET['upload']):0;
        if($is_upload==1 || (!$is_download&&!$is_collect)){
            $where .= " AND create_id=$admin_id";
            $this->assign('upload',1);
        }
        import("ORG.Util.Page");
        $count = $this->question_mod->where($where)->count();
        $p = new Page($count,15);
        //搜索符合条件的题目
        $question_list = $this->question_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('grade_id asc,cate_id asc,id desc')->select();
        $collect_list = $this->getCollectAll();
        foreach($question_list as $key=>&$value){
            $value['grade'] = $this->grade_list[$value['grade_id']]['name'];
            $value['cate'] = $this->cate_list[$value['cate_id']]['name'];
//            $value['name'] = cutString($value['name'],30);
			$question_dir = $this->checkQuestionDir($value['cate_id'],$value['grade_id'],$value['site_logo'],$value['net_logo']);
			$value['src'] = $question_dir.$value['net_logo'].'.png';
            //取收藏记录
            if(!empty($collect_list[$value['id']])){
                $value['is_collect'] = $collect_list[$value['id']]['is_collect'];
                $value['is_download'] = $collect_list[$value['id']]['is_download'];
            }else{
                $value['is_collect'] = 0;
                $value['is_download'] = 0;
            }
        }
        $page = $p->show();
        $this->assign('page',$page);
        $this->assign('controller',MODULE_NAME);
        $this->assign('period_id',$this->period_id);
        $this->assign('grade_list',$this->grade_list);
        $this->assign('cate_list',$this->cate_list);
        $this->assign('question_list',$question_list);
        $this->assign('question_tab',$this->question_tab);
        $this->assign('admin_id',$admin_id);
        $this->display();
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
		$p = new Page($count,10);
		$question_list = $this->question_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('grade_id asc,cate_id asc,id desc')->select();
        $collect_list = $this->getCollectAll();
		foreach($question_list as $key=>&$value){
			$value['grade'] = $this->grade_list[$value['grade_id']]['name'];
			$value['cate'] = $this->cate_list[$value['cate_id']]['name'];
//			$value['name'] = cutString($value['name'],30);
			$question_dir = $this->checkQuestionDir($value['cate_id'],$value['grade_id'],$value['site_logo'],$value['net_logo']);
			$value['src'] = $question_dir.$value['net_logo'].'.png';
			//取收藏记录
			if(!empty($collect_list[$value['id']])){
				$value['is_collect'] = $collect_list[$value['id']]['is_collect'];
				$value['is_download'] = $collect_list[$value['id']]['is_download'];
			}else{
				$value['is_collect'] = 0;
				$value['is_download'] = 0;
			}
		}
		$page = $p->show();
		$this->assign('page',$page);
		$this->assign('controller',MODULE_NAME);
		$this->assign('period_id',$this->period_id);
		$this->assign('grade_list',$this->grade_list);
		$this->assign('cate_list',$this->cate_list);
		$this->assign('question_list',$question_list);
		$this->assign('question_tab',$this->question_tab);
		$this->assign('display',$display);
		$this->display($display);
	}

}