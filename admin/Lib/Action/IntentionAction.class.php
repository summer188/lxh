<?php
/**
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/7/24
 * Time: 20:14
 */
class IntentionAction extends BaseAction{

    public $tab;
    public $mod;
    public $period_list;

    public function __construct(){
        switch(ACTION_NAME){
            case 'test':
                $this->tab = 'intention_try';
                $this->mod = M('intention_try');
                break;
            case 'buy':
                $this->tab = 'intention_buy';
                $this->mod = M('intention_buy');
                break;
            case 'agent':
                $this->tab = 'intention_agent';
                $this->mod = M('intention_agent');
                break;
            default:
                $this->tab = 'intention_try';
                $this->mod = M('intention_try');
                break;
        }
        $this->period_list = array(
            0 => '请选择',
            1 => '小学',
            2 => '初中',
            3 => '高中'
        );
    }

    //试用申请
    public function test(){
        //获取搜索条件
        $name=isset($_GET['name'])?trim($_GET['name']):'';
        $mobile=isset($_GET['mobile'])?trim($_GET['mobile']):'';
        $qq=isset($_GET['qq'])?trim($_GET['qq']):'';
        $email=isset($_GET['email'])?trim($_GET['email']):'';
        $period=isset($_GET['period'])?trim($_GET['period']):'';
        //搜索
        $where = '1=1';
        if ($name!='') {
            $where .= " AND name LIKE '%".$name."%'";
            $this->assign('name', $name);
        }
        if ($mobile!='') {
            $where .= " AND mobile=$mobile";
            $this->assign('mobile', $mobile);
        }
        if ($qq!='') {
            $where .= " AND qq=$qq";
            $this->assign('qq', $qq);
        }
        if ($email!='') {
            $where .= " AND email='$email'";
            $this->assign('email', $email);
        }
        if ($period!='') {
            $where .= " AND period_id=$period";
            $this->assign('period', $period);
        }
        import("ORG.Util.Page");
        $count = $this->mod->where($where)->count();
        $p = new Page($count,15);
        $list = $this->mod->where($where)->limit($p->firstRow.','.$p->listRows)->order('id desc')->select();
        foreach($list as $key=>&$value){
            $value['period'] = $this->period_list[$value['period_id']];
            $value['info'] = cutString($value['info'],10);
        }
        $page = $p->show();
        $this->assign('page',$page);
        $this->assign('list',$list);
        $this->assign('period_list',$this->period_list);
        $this->display();
    }
}