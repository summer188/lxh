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
	//知识点表名(带前缀)
	public $point_tab;
    //知识点模型
    public $point_mod;
    //设置学段值
    public $period_id;
    //学科模型
    public $cate_mod;
    //所有学科列表
    public $cate_list;


    public function __construct(){

        switch(MODULE_NAME){
            case 'AdPoint':
                $this->period_id = 1;
                $this->cate_mod = 'adboard';
				$this->point_tab = C('DB_PREFIX')."ad_point";
				$this->point_mod = M("ad_point");
                break;
            case 'SellerPoint':
                $this->period_id = 2;
                $this->cate_mod = 'seller_cate';
				$this->point_tab = C('DB_PREFIX')."seller_point";
				$this->point_mod = M("seller_point");
                break;
            case 'ArticlePoint':
                $this->period_id = 3;
                $this->cate_mod = 'article_cate';
				$this->point_tab = C('DB_PREFIX')."article_point";
				$this->point_mod = M("article_point");
                break;
            default:
                $this->period_id = 1;
                $this->cate_mod = 'adboard';
        }

        //获取所有学科列表
        $this->cate_list = M($this->cate_mod)->select();
        if(!empty($this->cate_list)){
            //把id的值作为键名，重新组合数组
            $this->cate_list = array_to_key($this->cate_list,'id');
        }
    }

	//excel表格导入
	public function addExcel()
	{
		$level = $_GET['level'];
		$action = empty($level)?'insertExcel':('insertExcel'.$level);
		$this->assign('action',$action);
		$this->assign('controller',MODULE_NAME);
		$this->assign('cate_list',$this->cate_list);
		$this->display();
	}

	//修改状态
	function status()
	{
		$id = intval($_REQUEST['id']);
		$type = trim($_REQUEST['type']);
		$sql = "update ".$this->point_tab." set $type=($type+1)%2 where id='$id'";
		$this->point_mod->execute($sql);
		$values = $this->point_mod->where('id=' . $id)->find();
		$this->ajaxReturn($values[$type]);
	}

	//排序--尚未启用
	public function sort(){
		$id = intval($_REQUEST['id']);
		$type = trim($_REQUEST['type']);
		$num = trim($_REQUEST['num']);
		if(!is_numeric($num)){
			$values = $this->point_mod->where('id='.$id)->find();
			$this->ajaxReturn($values[$type]);
			exit;
		}
		$sql    = "update ".C('DB_PREFIX').'point'." set $type=$num where id='$id'";

		$this->point_mod->execute($sql);
		$values = $this->point_mod->where('id='.$id)->find();
		$this->ajaxReturn($values[$type]);
	}

	//删除目录及其所有子目录
	public function delete(){
		$flag = true;
		if (isset($_POST['id']) && is_array($_POST['id'])) {
			$id_array=$_POST['id'];
			for ($i=0;$i<count($id_array);$i++){
                $point1 = $this->point_mod->where("id='{$id_array[$i]}'")->find();
                $alias1 = $point1['alias'];
                //删除目录及其所有子目录
                $sql = "DELETE FROM `{$this->point_tab}` WHERE LOCATE('$alias1',`alias`)=1";
				$result = $this->point_mod->execute($sql);
				if($result <= 0){
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
}