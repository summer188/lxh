<?php
/**
 * 题库基础控制器
 *
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/4/23
 * Time: 17:01
 */
class QuestionBaseAction extends BaseAction{
	//题库表名
	public $question_tab;
    //题库模型
    public $question_mod;
	//知识点模型
	public $point_mod;
    //设置学段值
    public $period_id;
    //学科模型
    public $cate_mod;
    //所有年级列表
    public $grade_list;
    //所有学科列表
    public $cate_list;
	//所以知识点一级目录列表
	public $point_list;


    public function __construct(){

        switch(MODULE_NAME){
            case 'Ad':
				$this->question_tab = 'ad';
                $this->question_mod = M('ad');
                $this->period_id = 1;
                $this->cate_mod = 'adboard';
				$this->point_mod = M("ad_point");
                break;
            case 'SellerList':
				$this->question_tab = 'seller_list';
                $this->question_mod = M('seller_list');
                $this->period_id = 2;
                $this->cate_mod = 'seller_cate';
				$this->point_mod = M("seller_point");
                break;
            case 'Article':
				$this->question_tab = 'article';
                $this->question_mod = M('article');
                $this->period_id = 3;
                $this->cate_mod = 'article_cate';
				$this->point_mod = M("article_point");
                break;
            default:
				$this->question_tab = 'ad';
                $this->question_mod = M('ad');
                $this->period_id = 1;
                $this->cate_mod = 'adboard';
        }

        //获取所有年级列表
        $this->grade_list = M('grade')->where("period_id=$this->period_id")->select();
        if(!empty($this->grade_list)){
            //把id的值作为键名，重新组合数组
            $this->grade_list = array_to_key($this->grade_list,'id');
        }

        //获取所有学科列表
        $this->cate_list = M($this->cate_mod)->select();
        if(!empty($this->cate_list)){
            //把id的值作为键名，重新组合数组
            $this->cate_list = array_to_key($this->cate_list,'id');
        }

		//获取所有知识点一级目录列表
		$this->point_list = M('point')->where("period_id={$this->period_id}")->select();
		if(!empty($this->point_list)){
			//把id的值作为键名，重新组合数组
			$this->cate_list = array_to_key($this->cate_list,'id');
		}
    }

	/**
	 * 检测当前登录用户是否有编辑权限
	 * @return Boolean
	 */
	public function checkEditAccess(){
		$module = MODULE_NAME;
		$node = M("node")->where("module='$module' AND action='editQuestion'")->find();
		$node_id = 0;
		if(!empty($node)){
			$node_id = $node['id'];
		}
		if($node_id>0){
			$access = $this->checkNodeAccess($node_id);
			return $access;
		}else{
			return false;
		}

	}

	/**
	 * 检测当前登录用户是否有下载权限
	 * @return Boolean
	 */
	public function checkDeleteAccess(){
		$module = MODULE_NAME;
		$node = M("node")->where("module='$module' AND action='deleteQuestion'")->find();
		$node_id = 0;
		if(!empty($node)){
			$node_id = $node['id'];
		}
		if($node_id>0){
			$access = $this->checkNodeAccess($node_id);
			return $access;
		}else{
			return false;
		}
	}
}