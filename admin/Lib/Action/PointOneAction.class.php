<?php
/**
 * 知识点一级目录控制器
 *
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/4/23
 * Time: 8:00
 */
class PointOneAction extends PointBaseAction{
    //一级目录列表
    public function one(){
        //获取搜索条件
        $keyword=isset($_GET['keyword'])?trim($_GET['keyword']):'';
        $grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
        $cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
        //搜索
        $where = "period_id=$this->period_id AND level=1";
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
        $count = $this->point_mod->where($where)->count();
        $p = new Page($count,15);
        $point_list = $this->point_mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('grade_id asc,cate_id asc')->select();
        foreach($point_list as $key=>&$value){
            $value['grade'] = $this->grade_list[$value['grade_id']]['name'];
            $value['cate'] = $this->cate_list[$value['cate_id']]['name'];
            $value['name'] = cutString($value['name'],25);
        }
        $page = $p->show();
        $this->assign('page',$page);
        $this->assign('controller',MODULE_NAME);
        $this->assign('point_list',$point_list);
        $this->assign('grade_list',$this->grade_list);
        $this->assign('cate_list',$this->cate_list);
        $this->display();
    }

    //添加一级目录
    public function addOne()
    {
        $this->assign('controller',MODULE_NAME);
        $this->assign('grade_list',$this->grade_list);
        $this->assign('cate_list',$this->cate_list);
        $this->display();
    }

    //向数据表里插入数据
    public function insertOne()
    {
        $data = $this->point_mod->create();
        if(false === $data){
            $this->error($this->point_mod->error());
        }
        $data['level'] = 1;
        $data['period_id'] = $this->period_id;
        $data['create_id'] = $_SESSION['admin_info']['id'];
        $data['create_time'] = date("Y-m-d h:i:s",time());
        $result = $this->point_mod->add($data);
        if($result){
            $this->success(L('operation_success'), '', '', 'addOne');
        }else{
            $this->error(L('operation_failure'));
        }
    }
    //编辑一级目录
    public function editOne()
    {
        if(isset($_GET['id']) && intval($_GET['id'])){
            $id = intval($_GET['id']);
            $point_info = $this->point_mod->where('id='.$id)->find();
            $this->assign('show_header', false);
            $this->assign('controller',MODULE_NAME);
            $this->assign('point_info',$point_info);
            $this->assign('grade_list',$this->grade_list);
            $this->assign('cate_list',$this->cate_list);
            $this->display();
        }else{
            $this->error(L('please_select'));
        }
    }
    //编辑数据存入数据库
    public function updateOne(){
        $data = $this->point_mod->create();
        if(false === $data){
            $this->error($this->point_mod->error());
        }
        $data['level'] = 1;
        $data['update_id'] = $_SESSION['admin_info']['id'];
        $data['update_time'] = date('Y-m-d h:i:s',time());
        $result = $this->point_mod->save($data);
        if($result){
            $this->success(L('operation_success'), '', '', 'editOne');
        }else{
            $this->error(L('operation_failure'));
        }

    }
    //修改状态
    function statusOne()
    {
        $id = intval($_REQUEST['id']);
        $type = trim($_REQUEST['type']);
        $sql = "update " . C('DB_PREFIX') . "point set $type=($type+1)%2 where id='$id'";
        $this->point_mod->execute($sql);
        $values = $this->point_mod->where('id=' . $id)->find();
        $this->ajaxReturn($values[$type]);
    }

}