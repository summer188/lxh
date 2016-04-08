<?php
/**
 * 节公共控制器
 *
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/4/3
 * Time: 9:21
 */
class SectionAction extends BaseAction{

    //节模型
    public $section_mod;
    //设置学段值
    public $period_id;
    //学科模型
    public $cate_mod;
    //所有年级列表
    public $grade_list;
    //所有学科列表
    public $cate_list;


    public function __construct(){

        $this->section_mod = M("section");

        switch(MODULE_NAME){
            case 'AdSection':
                $this->period_id = 1;
                $this->cate_mod = 'adboard';
                break;
            case 'SellerSection':
                $this->period_id = 2;
                $this->cate_mod = 'seller_cate';
                break;
            case 'ArticleSection':
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

    //节目录列表
    public function index(){
        //获取搜索条件
        $keyword=isset($_GET['keyword'])?trim($_GET['keyword']):'';
        $grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
        $cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
        $chapter_id=isset($_GET['chapter_id'])?trim($_GET['chapter_id']):'';
        //搜索
        $where = "period_id=$this->period_id";
        if ($grade_id!='') {
            $where .= " AND grade_id=$grade_id";
            $this->assign('grade_id', $grade_id);
        }
        if ($cate_id!='') {
            $where .= " AND cate_id=$cate_id";
            $this->assign('cate_id', $cate_id);
        }
        if($grade_id!='' && $cate_id!='') {
            $chapter_list = M("chapter")->where($where)->select();
            $this->assign('chapter_list',$chapter_list);
        }
        if ($chapter_id!='') {
            $where .= " AND chapter_id=$chapter_id";
            $this->assign('chapter_id', $chapter_id);
        }
        if ($keyword!='') {
            $where .= " AND name LIKE '%".$keyword."%'";
            $this->assign('keyword', $keyword);
        }
        import("ORG.Util.Page");
        $count = $this->section_mod->where($where)->count();
        $p = new Page($count,15);
        $section_list = $this->section_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('grade_id asc,cate_id asc,sort asc')->select();
        foreach($section_list as $key=>$value){
            $section_list[$key]['grade'] = $this->grade_list[$value['grade_id']]['name'];
            $section_list[$key]['cate'] = $this->cate_list[$value['cate_id']]['name'];
            $chapter = M("chapter")->where("id={$section_list[$key]['chapter_id']}")->find();
            $section_list[$key]['chapter'] = $chapter['alias'].' '.$chapter['name'];
        }
        $page = $p->show();
        $this->assign('page',$page);
        $this->assign('controller',MODULE_NAME);
        $this->assign('section_list',$section_list);
        $this->assign('grade_list',$this->grade_list);
        $this->assign('cate_list',$this->cate_list);
        $this->display();
    }

    //获取章列表--响应ajax请求
    public function getChapterList(){
        $grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
        $cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
        if($grade_id!='' && $cate_id!=''){
            $where = "period_id=$this->period_id";
            $where .= " AND grade_id=$grade_id";
            $where .= " AND cate_id=$cate_id";
            $chapter_list = M("chapter")->where($where)->select();
            $this->ajaxReturn($chapter_list,'JSON');
        }
    }

    //增加
    public function add()
    {
        $this->assign('controller',MODULE_NAME);
        $this->assign('grade_list',$this->grade_list);
        $this->assign('cate_list',$this->cate_list);
        $this->display();
    }

    //插入数据
    public function insert()
    {
        $data = $this->section_mod->create();
		if(false === $data){
			$this->error($this->section_mod->error());
		}
        $data['period_id'] = $this->period_id;
        $data['create_id'] = $_SESSION['admin_info']['id'];
        $data['create_time'] = time();
        $result = $this->section_mod->add($data);
        if($result){
            $this->success(L('operation_success'), '', '', 'add');
        }else{
            $this->error(L('operation_failure'));
        }
    }

    //修改
    public function edit()
    {
        if( isset($_GET['id']) ){
            $section_id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error(L('please_select'));
        }
        $section_info = $this->section_mod->where('id='.$section_id)->find();
        $where = "grade_id={$section_info['grade_id']} AND cate_id={$section_info['cate_id']}";
        $chapter_list = M("chapter")->where($where)->select();
        $this->assign('controller',MODULE_NAME);
        $this->assign('show_header', false);
        $this->assign('section_info',$section_info);
        $this->assign('grade_list',$this->grade_list);
        $this->assign('cate_list',$this->cate_list);
        $this->assign('chapter_list',$chapter_list);
        $this->display();
    }

    //更新
    public function update()
    {
        if((!isset($_POST['id']) || empty($_POST['id']))) {
            $this->error('请选择要编辑的数据');
        }
        $data = $this->section_mod->create();
        if(false === $data){
            $this->error($this->section_mod->error());
        }
        $data['update_id'] = $_SESSION['admin_info']['id'];
        $data['update_time'] = time();
        $result = $this->section_mod->save($data);
        if(false !== $result){
            $this->success(L('operation_success'), '', '', 'edit');
        }else{
            $this->error(L('operation_failure'));
        }
    }

    //删除
    public function delete(){
        $flag = true;
        if (isset($_POST['id']) && is_array($_POST['id'])) {
            $id_array=$_POST['id'];
            for ($i=0;$i<count($id_array);$i++){
                $result = $this->section_mod->where("id='{$id_array[$i]}'")->delete();
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

    //修改状态
    function status()
    {
        $id = intval($_REQUEST['id']);
        $type = trim($_REQUEST['type']);
        $sql = "update " . C('DB_PREFIX') . "section set $type=($type+1)%2 where id='$id'";
        $this->section_mod->execute($sql);
        $values = $this->section_mod->where('id=' . $id)->find();
        $this->ajaxReturn($values[$type]);
    }

    //排序
    public function sort(){
        $id = intval($_REQUEST['id']);
        $type = trim($_REQUEST['type']);
        $num = trim($_REQUEST['num']);
        if(!is_numeric($num)){
            $values = $this->section_mod->where('id='.$id)->find();
            $this->ajaxReturn($values[$type]);
            exit;
        }
        $sql    = "update ".C('DB_PREFIX').'section'." set $type=$num where id='$id'";

        $this->section_mod->execute($sql);
        $values = $this->section_mod->where('id='.$id)->find();
        $this->ajaxReturn($values[$type]);
    }
}