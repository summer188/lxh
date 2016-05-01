<?php
/**
 * 题目工具公共控制器，主要功能有：
 * 题目编辑、排序、修改状态、删除；获取知识点列表；
 * 检测并创建题目上传目录等
 *
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/4/14
 * Time: 9:22
 */
class QuestionToolAction extends QuestionBaseAction{

	/**
	 * 修改题目状态
	 *
	 */
	function statusQuestion()
	{
		$question_mod = M('question');
		$id = intval($_REQUEST['id']);
		$type = trim($_REQUEST['type']);
		$sql = "update " . C('DB_PREFIX') . "question set $type=($type+1)%2 where id='$id'";
		$question_mod->execute($sql);
		$values = $question_mod->where('id=' . $id)->find();
		$this->ajaxReturn($values[$type]);
	}

	/**
	 * 题目排序
	 *
	 */
	public function sortQuestion(){
		$question_mod = M('question');
		$id = intval($_REQUEST['id']);
		$type = trim($_REQUEST['type']);
		$num = trim($_REQUEST['num']);
		if(!is_numeric($num)){
			$values = $question_mod->where('id='.$id)->find();
			$this->ajaxReturn($values[$type]);
			exit;
		}
		$sql    = "update ".C('DB_PREFIX').'question'." set $type=$num where id='$id'";

		$question_mod->execute($sql);
		$values = $question_mod->where('id='.$id)->find();
		$this->ajaxReturn($values[$type]);
	}

	/**
	 * 删除题目
	 *
	 */
	public function deleteQuestion(){
		$flag = true;
		if (isset($_POST['id']) && is_array($_POST['id'])) {
			$question_mod = M('question');
			$id_array=$_POST['id'];
			for ($i=0;$i<count($id_array);$i++){
				$question_info = $question_mod->where("id={$id_array[$i]}")->find();
				//先删除题目信息存放文件和目录
				unlink($question_info['title_url']);
				unlink($question_info['info_url']);
				$question_dir = $this->getQuestionDir($question_info['grade_id'],$question_info['cate_id'],$id_array[$i]);
				rmdir($question_dir);
				//再删除数据表中数据
				$result = $question_mod->where("id={$id_array[$i]}")->delete();
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

	/**
	 * 获取知识点列表--响应ajax请求
	 *
	 * @return Array
	 */
	public function getPointList(){
		$grade_id=isset($_GET['grade_id'])?trim($_GET['grade_id']):'';
		$cate_id=isset($_GET['cate_id'])?trim($_GET['cate_id']):'';
		$point_list = $this->getPointAll($grade_id,$cate_id);
		$this->ajaxReturn($point_list,'JSON');
	}

	/**
	 * 根据条件获取章列表
	 *
	 * @param String $grade_id 年级id
	 * @param String $cate_id 学科id
	 * @return Array
	 */
	public function getPointAll($grade_id='',$cate_id=''){
		if($grade_id!='' && $cate_id!=''){
			$where = "level=1";
			$where .= " AND period_id=$this->period_id";
			$where .= " AND grade_id=$grade_id";
			$where .= " AND cate_id=$cate_id";
			$point_list = M("point")->where($where)->field('id,alias,name')->select();
			if($point_list){
				return $point_list;
			}else{
				return array();
			}
		}

	}

	/**
	 * 去除字符串的所有格式，获取纯文本内容
	 *
	 * @param String $str 目标字符串
	 * @return String
	 */
	public function cleanFormat($str){
		$str = trim($str); //清除字符串两边的空格
		$str = strip_tags($str,""); //利用php自带的函数清除html格式
		$str = preg_replace("/\t/","",$str); //使用正则表达式替换内容，如：空格，换行，并将替换为空。
		$str = preg_replace("/\r\n/","",$str);
		$str = preg_replace("/\r/","",$str);
		$str = preg_replace("/\n/","",$str);
		$str = preg_replace("/ /","",$str);
		$str = preg_replace("/&nbsp;/","",$str);  //匹配html中的空格
		return trim($str);
	}

	/**
	 * 获取题目存放目录,格式为'./控制器名/grade_id(年级id)/cate_id(学科id)/question_id(题目id)
	 *
	 * @param Int $grade_id 年级id
	 * @param Int $cate_id 学科id
	 * @param Int $id 题目id
	 * @return String
	 */
	public function getQuestionDir($grade_id,$cate_id,$id){
		$root = 'upload/'.$this->cate_list[$cate_id]['alias'].'/'.$grade_id.'/';
		$question_dir = $root.'question_'.$id.'/';
		return $question_dir;
	}

	/**
	 * 判断或创建题目目录
     *
	 * @param Int $cate_id--学科id
	 * @param Int $grade_id--年级id
     * @param String $site_logo--期数
     * @param String $net_logo--题目序号
     *
     * @return String 单个题目信息存放目录
	 */
	public function checkQuestionDir($cate_id,$grade_id,$site_logo,$net_logo){
		//定义题目上传根目录(相对路径)
		$root = 'upload/';
		//学科目录,以学科别名命名
        $cate = $this->cate_list[$cate_id];
		$cate_root = $root.$cate['alias'].'/';
        //年级目录
        $grade_root = $cate_root.$grade_id.'/';
		//期数目录
		$site_root = $grade_root.$site_logo.'/';
		//题目序号目录
		$net_root = $site_root.$net_logo.'/';
        $this->checkDirUrl($net_root);
        return $net_root;
	}

	/**
	 * 检查并创建文件目录
	 *
	 * @param String $root 上级目录，末尾须带‘/’
	 * @param String $prefix 要检查或创建的目标文件前缀
	 * @param Array $rootArr 目录数组
	 */
	public function checkDirArr($root, $prefix,$rootArr){
		if(count($rootArr) > 0){
			foreach($rootArr as $value){
				$this->checkDir($root.$prefix.$value['id'].'/');
			}
		}
	}

    /**
     * 检查并创建多级目录
     *
     * @param String $rootUrl--目录
     */
    public function checkDirUrl($rootUrl){
        if(!empty($rootUrl) && is_string($rootUrl)){
            $rootArr = explode('/',$rootUrl);
            if(count($rootArr)>0){
                $url = '';
                foreach($rootArr as $key=>$value){
                    $url .= $value.'/';
                    $this->checkDir($url);
                }
            }
        }

    }

	/**
	 * 检查并创建单一目录
	 *
	 * @param String $root--目录
	 */
	public function checkDir($root){
		if(!file_exists($root)){
			mkdir($root);
			if(!chmod($root, 0777)){
				$this->error(L('OPERATION_FAILURE'));
			}
		}
	}
}