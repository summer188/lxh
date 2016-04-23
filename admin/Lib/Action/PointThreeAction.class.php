<?php
/**
 * 知识点三级目录控制器
 *
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/4/23
 * Time: 8:02
 */
class PointThreeAction extends PointTwoAction{
    //三级目录列表
    public function Three(){
        //获取搜索条件
        $keyword=isset($_GET['keyword'])?trim($_GET['keyword']):'';
        $grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
        $cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
        //搜索
        $where = "period_id=$this->period_id AND level=3";
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
            $value['name'] = cutString($value['name'],15);
            //获取其二级目录
            $alias2 = substr($value['alias'],0,-3);
            $value['alias2'] = $alias2;
            $point2 = $this->point_mod->where("alias=$alias2 AND level=2")->find();
            $value['name2'] = cutString($point2['name'],10);
            //获取其一级目录
            $alias1 = substr($alias2,0,-3);
            $value['alias1'] = $alias1;
            $point1 = $this->point_mod->where("alias=$alias1 AND level=1")->find();
            $value['name1'] = cutString($point1['name'],10);
        }
        $page = $p->show();
        $this->assign('page',$page);
        $this->assign('controller',MODULE_NAME);
        $this->assign('point_list',$point_list);
        $this->assign('grade_list',$this->grade_list);
        $this->assign('cate_list',$this->cate_list);
        $this->display();
    }

    //添加三级目录
    public function addThree()
    {
        $this->assign('controller',MODULE_NAME);
        $this->display();
    }

    //向数据表里插入数据
    public function insertThree()
    {
        $post = $_POST;
        //取得其一级目录
        $alias = $post['alias'];
        $alias2 = substr($alias,0,-3);
        $alias1 = substr($alias2,0,-3);
        $point1 = $this->point_mod->where("alias=$alias1")->find();
        //数据入库
        $data = $this->point_mod->create($post);
        if(false === $data){
            $this->error($this->point_mod->error());
        }
        $data['level'] = 3;
        $data['period_id'] = $this->period_id;
        $data['grade_id'] = $point1['grade_id'];
        $data['cate_id'] = $point1['cate_id'];
        $data['create_id'] = $_SESSION['admin_info']['id'];
        $data['create_time'] = date("Y-m-d h:i:s",time());
        $result = $this->point_mod->add($data);
        if($result){
            $this->success(L('operation_success'), '', '', 'addThree');
        }else{
            $this->error(L('operation_failure'));
        }
    }
    //编辑三级目录
    public function editThree()
    {
        if(isset($_GET['id']) && intval($_GET['id'])){
            $id = intval($_GET['id']);
            $point_info = $this->point_mod->where('id='.$id)->find();
            //取得年级名称
            $point_info['grade'] = $this->grade_list[$point_info['grade_id']]['name'];
            //取得学科名称
            $point_info['cate'] = $this->cate_list[$point_info['cate_id']]['name'];
            //获取其二级目录
            $alias2 = substr($point_info['alias'],0,-3);
            $point_info['alias2'] = $alias2;
            $point2 = $this->point_mod->where("alias=$alias2 AND level=2")->find();
            $point_info['name2'] = cutString($point2['name'],10);
            //获取其一级目录
            $alias1 = substr($alias2,0,-3);
            $point_info['alias1'] = $alias1;
            $point1 = $this->point_mod->where("alias=$alias1 AND level=1")->find();
            $point_info['name1'] = cutString($point1['name'],10);
            $this->assign('show_header', false);
            $this->assign('controller',MODULE_NAME);
            $this->assign('point_info',$point_info);
            $this->display();
        }else{
            $this->error(L('please_select'));
        }
    }
    //编辑数据存入数据库
    public function updateThree(){
        $data = $this->point_mod->create();
        if(false === $data){
            $this->error($this->point_mod->error());
        }
        $data['level'] = 3;
        $data['update_id'] = $_SESSION['admin_info']['id'];
        $data['update_time'] = date('Y-m-d h:i:s',time());
        $result = $this->point_mod->save($data);
        if($result){
            $this->success(L('operation_success'), '', '', 'editThree');
        }else{
            $this->error(L('operation_failure'));
        }

    }
    //修改状态
    function statusThree()
    {
        $id = intval($_REQUEST['id']);
        $type = trim($_REQUEST['type']);
        $sql = "update " . C('DB_PREFIX') . "point set $type=($type+1)%2 where id='$id'";
        $this->point_mod->execute($sql);
        $values = $this->point_mod->where('id=' . $id)->find();
        $this->ajaxReturn($values[$type]);
    }
}