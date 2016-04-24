<?php
/**
 * 知识点基础控制器
 *
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/4/23
 * Time: 7:57
 */
class PointBaseAction extends BaseAction{
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


    public function __construct(){

        $this->point_mod = M("point");

        switch(MODULE_NAME){
            case 'AdPoint':
                $this->period_id = 1;
                $this->cate_mod = 'adboard';
                break;
            case 'SellerPoint':
                $this->period_id = 2;
                $this->cate_mod = 'seller_cate';
                break;
            case 'ArticlePoint':
                $this->period_id = 3;
                $this->cate_mod = 'article_cate';
                break;
            default:
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
    }
}