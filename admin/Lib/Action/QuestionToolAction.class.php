<?php
/**
 * 题目工具公共控制器，主要功能有：
 * 获取知识点列表；
 * 检测并创建题目上传目录；单一和多级目录的检测和创建；
 * 检测上传的word和png类型；删除题目文档等
 *
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/4/14
 * Time: 9:22
 */
class QuestionToolAction extends QuestionBaseAction{

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
     * 根据条件获取知识点列表
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
            chmod($root, 0777);
//			if(!chmod($root, 0777)){
//				$this->error(L('OPERATION_FAILURE'));
//			}
		}
	}

	/**
	 * 检查上传的文档类型
	 *
	 * @param Array $file--文档信息
	 * @param String $type--要求的文档类型'word' or 'png'
	 * @return Boolean
	 */
	public function checkFileType($file,$type){
		$style = '';
		if($type=='word'){
			$type = 'doc';
			$style = 'application/msword';
		}elseif($type=='png'){
			$style = 'image/png';
		}
		$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
		if($ext==$type && $file['type']==$style){
			return true;
		}else{
			return false;
		}
	}

    /**
     * 删除题目word文档和png图片
     *
     * @param String $dir--准备删除的文件夹url
     * @param String $net_logo--题目序号
     * @return Boolean
     */
    public function delFile($dir,$net_logo){
        $word_url = $dir.$net_logo.'.doc';
        $png_url = $dir.$net_logo.'.png';
        if(file_exists($word_url)){
            unlink($word_url);
        }
        if(file_exists($png_url)){
            unlink($png_url);
        }
        rmdir($dir);
    }
}