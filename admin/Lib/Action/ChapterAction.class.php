<?php
/**
 * 章公共控制器
 *
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/4/3
 * Time: 7:58
 */
class ChapterAction extends BaseAction{

    //章模型
    public $chapter_mod;
    //设置学段值
    public $period_id;
    //学科模型
    public $cate_mod;
    //所有年级列表
    public $grade_list;
    //所有学科列表
    public $cate_list;


    public function __construct(){

        $this->chapter_mod = M("chapter");

        switch(MODULE_NAME){
            case 'AdChpater':
                $this->period_id = 1;
                $this->cate_mod = 'adboard';
                break;
            case 'SellerChapter':
                $this->period_id = 2;
                $this->cate_mod = 'seller_cate';
                break;
            case 'ArticleChapter':
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

    //章目录列表
    public function index(){
        //获取搜索条件
        $keyword=isset($_GET['keyword'])?trim($_GET['keyword']):'';
        $grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
        $cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
        //搜索
        $where = "period_id=$this->period_id";
        if ($keyword!='') {
            $where .= " AND name LIKE '%".$keyword."%'";
            $this->assign('keyword', $keyword);
        }
        if ($grade_id!='') {
            $where .= " AND grade_id=$grade_id";
            $this->assign('grade_id', $grade_id);
        }
        if ($cate_id!='') {
            $where .= " AND cate_id=$cate_id";
            $this->assign('cate_id', $cate_id);
        }
        import("ORG.Util.Page");
        $count = $this->chapter_mod->where($where)->count();
        $p = new Page($count,20);
        $chapter_list = $this->chapter_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('grade_id asc,cate_id asc,sort asc')->select();
        foreach($chapter_list as $key=>$value){
            $chapter_list[$key]['grade'] = $this->grade_list[$value['grade_id']]['name'];
            $chapter_list[$key]['cate'] = $this->cate_list[$value['cate_id']]['name'];
        }
        $page = $p->show();
        $this->assign('page',$page);
        $this->assign('chapter_list',$chapter_list);
        $this->assign('grade_list',$this->grade_list);
        $this->assign('cate_list',$this->cate_list);
        $this->display();
    }

    //增加
    public function add()
    {
        $this->assign('grade_list',$this->grade_list);
        $this->assign('cate_list',$this->cate_list);
        $this->display();
    }

    //插入数据
    public function insert()
    {
        $data = $this->chapter_mod->create();
        $data['period_id'] = $this->period_id;
        $data['create_id'] = $_SESSION['admin_info']['id'];
        $data['create_time'] = time();
        if(false === $data){
            $this->error($this->chapter_mod->error());
        }
        $result = $this->chapter_mod->add($data);
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
            $chapter_id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error(L('please_select'));
        }
        $chapter_info = $this->chapter_mod->where('id='.$chapter_id)->find();
        $this->assign('show_header', false);
        $this->assign('chapter_info',$chapter_info);
        $this->assign('grade_list',$this->grade_list);
        $this->assign('cate_list',$this->cate_list);
        $this->display();
    }

    //更新
    public function update()
    {
        if((!isset($_POST['id']) || empty($_POST['id']))) {
            $this->error('请选择要编辑的数据');
        }
        $data = $this->chapter_mod->create();
        if(false === $data){
            $this->error($this->chapter_mod->error());
        }
        $data['update_id'] = $_SESSION['admin_info']['id'];
        $data['update_time'] = time();
        $result = $this->chapter_mod->save($data);
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
                $result = $this->chapter_mod->where("id='{$id_array[$i]}'")->delete();
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
        $sql = "update " . C('DB_PREFIX') . "chapter set $type=($type+1)%2 where id='$id'";
        $this->chapter_mod->execute($sql);
        $values = $this->chapter_mod->where('id=' . $id)->find();
        $this->ajaxReturn($values[$type]);
    }

    //排序
    public function sort(){
        $id = intval($_REQUEST['id']);
        $type = trim($_REQUEST['type']);
        $num = trim($_REQUEST['num']);
        if(!is_numeric($num)){
            $values = $this->chapter_mod->where('id='.$id)->find();
            $this->ajaxReturn($values[$type]);
            exit;
        }
        $sql    = "update ".C('DB_PREFIX').'chapter'." set $type=$num where id='$id'";

        $this->chapter_mod->execute($sql);
        $values = $this->chapter_mod->where('id='.$id)->find();
        $this->ajaxReturn($values[$type]);
    }
}