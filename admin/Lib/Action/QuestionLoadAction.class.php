<?php
/**
 * 题目收藏、上传、下载公共控制器
 *
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/4/14
 * Time: 9:19
 */
class QuestionLoadAction extends QuestionToolAction{

	/**
	 * 上传新题或编辑题目
	 *
	 * @param String $id 编辑题目id
	 * @param String $display 操作成功后转向的页面
	 */
	public function upnew($id='',$display='yun'){
		//编辑时有id
		if($id!='' && intval($id)>0){
			$id = intval($id);
			$question_info = $this->question_mod->where("id=$id")->find();
			if($question_info){
                //从相关word文档里取题干和题目解析
                $cate_alias = $this->cate_list[$question_info['cate_id']]['alias'];
                $question_dir = 'upload/'.$cate_alias.'/'.$question_info['grade_id'].'/'.$question_info['site_logo'].'/'.$question_info['net_logo'].'/';
                $title_url = $question_dir.'title'.$question_info['net_logo'].'.doc';
                $info_url = $question_dir.'info'.$question_info['net_logo'].'.doc';
                $all_url = $question_dir.$question_info['net_logo'].'.doc';
                //检测是否有单独存放题干的文档
                if(file_exists($title_url)){//单个题目上传时有单独文档
                    $title = file_get_contents($title_url);
                    $info = file_get_contents($info_url);
                }else{//批量导入的文档里无题干和解析的单独存放文档
                    $all = file_get_contents($all_url);
                    $title = substr($all,0,strpos($all,'解析'));
                    $info = substr($all,strpos($all,'解析'),strlen($all));
                }

				$this->assign('id',$id);
				$this->assign('question_info',$question_info);
				$this->assign('title',$title);
				$this->assign('info',$info);
			}
		}
		$this->assign('grade_list',$this->grade_list);
		$this->assign('cate_list',$this->cate_list);
		$this->assign('question_tab','lxh_'.$this->question_tab);
		$this->assign('controller',MODULE_NAME);
		$this->assign('display',$display);
		$this->display('upnew');
	}

	/**
	 * 保存新题
	 *
	 * @return Array
	 */
	public function upsave(){
        //操作成功后要跳转的页面
        $display = $_POST['display'];
        unset($_POST['display']);

        //操作成功与否的记录变量
        $flag = true;

        //编辑时的题目id
		$id = intval($_POST['id']);
		//取得题干和题目解析
		$title = $_POST['title'];
		unset($_POST['title']);
		$info = $_POST['info'];
		unset($_POST['info']);
        $answer = $_POST['answer'];
		$all = $title.'<br/>解析：<br/>'.$info.'<br/>答案：'.$answer;

        //取得解析视频临时信息
        $click_url = '';
        if(!empty($_FILES['file'])){
            $file = $_FILES['file'];
            unset($_FILES);

            //把视频从临时目录移动到指定目录,和通过kindeditor上传的图片放在同一目录
            $ext = substr($file['name'],strrpos($file['name'],'.'));
            $date = date("Ymd",time());
            $file_name = date("Ymdhis",time()).'_'.mt_rand(10000,99999).$ext;
            $file_dir = "data/news/image/$date/";
            $this->checkDirUrl($file_dir);
            $click_url = $file_dir.$file_name;
            if(!move_uploaded_file($file['tmp_name'],$click_url)){
                $flag = false;
            }
        }

		//检测上传题目存放目录是否存在，不存在就创建
        $cate_id = intval($_POST['cate_id']);
        $grade_id = intval($_POST['grade_id']);
        $site_logo = $_POST['site_logo'];
        $net_logo = $_POST['net_logo'];
		$question_dir = $this->checkQuestionDir($cate_id,$grade_id,$site_logo,$net_logo);
        //题干url
        $title_url = $question_dir.'title'.$net_logo.'.doc';
        //题目解析url
        $info_url = $question_dir.'info'.$net_logo.'.doc';
        //存有题目全部信息的url
        $all_url = $question_dir.$net_logo.'.doc';

        //数据表保存数据
		$data = $this->question_mod->create();
		if(false === $data){
			$this->error($this->question_mod->error());
		}
		$data['period_id'] = $this->period_id;
        $data['create_id'] = $_SESSION['admin_info']['id'];
		$data['school_id'] = $_SESSION['admin_info']['school_id'];
		$data['update_time'] = date("Y-m-d h:i:s",time());
        if(!empty($click_url)){
            $data['click_url'] = $click_url;
        }

		if($id > 0){
			//编辑
			$result = $this->question_mod->save($data);
		}else{
			//新增
            $result = $this->question_mod->add($data);
		}
        if(!$result){
            $flag = false;
        }

		//将题干、解析和答案存入文档
		$title_file = fopen($title_url,"w");
		fwrite($title_file, $title);
		fclose($title_file);

		$info_file = fopen($info_url,"w");
		fwrite($info_file, $info);
		fclose($info_file);

		$all_file = fopen($all_url,"w");
		fwrite($all_file,$all);
		fclose($all_file);

        //所有操作完成后提示+页面跳转
		if($flag){
            $display = empty($display)?'yun':$display;
			$redirect_url = U(MODULE_NAME.'/'.$display);
			$this->success(L('operation_success'),$redirect_url);
			exit();
		}else{
			$this->error(L('operation_failure'));
			exit();
		}
	}

	/**
	 * 编辑题目
	 *
	 */
	public function editQuestion(){
		if(isset($_GET['id']) && intval($_GET['id'])){
            $display = $_GET['display'];
			$id = intval($_GET['id']);
			$this->upnew($id,$display);
		}else{
			$this->error(L('please_select'));
		}
	}

	/**
	 * 收藏题目
	 *
	 * @return Array
	 */
	public function collectQuestion(){
		$flag = true;
        $where = "period_id={$this->period_id}";
		if (isset($_GET['id']) && is_string($_GET['id'])) {
			$id = intval($_GET['id']);
			if($id > 0){
				$this->question_mod = M("{$this->getCollectMod()}");
				$admin_id = $_SESSION['admin_info']['id'];
				//先查看下是否已经收藏过
                $where .= " AND admin_id=$admin_id AND question_id=$id";
				$record = $this->question_mod->where($where)->find();
				if(!$record){
					$data = array(
						'admin_id' => $admin_id,
                        'period_id' => $this->period_id,
						'question_id' => $id
					);
					$result = $this->question_mod->add($data);
					if(!$result){
						$flag = false;
					}
				}else{
					$this->error('您已经收藏过本题！');
				}

			}
		}else{
			$flag = false;
		}

		if($flag){
			$this->success('收藏成功！');
		}else{
			$this->error('操作失败！');
		}
	}

	/**
	 * 下载题目
	 *
	 * @return Array
	 */
	public function downloadQuestion(){
		$flag = true;
        $where = "period_id={$this->period_id}";
		if (isset($_GET['id']) && is_string($_GET['id'])) {
			$id = intval($_GET['id']);
			if($id > 0){
				$this->question_mod = M("{$this->getCollectMod()}");
				$admin_id = $_SESSION['admin_info']['id'];
				//先查看下是否已经收藏过
                $where .= " AND admin_id=$admin_id AND question_id=$id";
				$record = $this->question_mod->where($where)->find();
				if(!$record){//若未收藏过，就先收藏再下载
					$data = array(
						'admin_id' => $admin_id,
                        'period_id' => $this->period_id,
						'question_id' => $id,
						'is_download' => 1
					);
					$result = $this->question_mod->add($data);
					if(!$result){
						$flag = false;
					}
					//下载题目

				}else{//若已收藏过，就需要先判断其下载状态
					if($record['is_download'] == 0){//未下载
						$record['is_download'] = 1;
//						$record = $this->question_mod->create($record);
						$result = $this->question_mod->data($record)->where($where)->save($record);
						if(!$result){
							$flag = false;
						}
					}else{//已下载
						$jsondata = array(
							'info' => '您已经下载过本题，是否要重新下载？',
							'status' => 2
						);
						$this->ajaxReturn($jsondata,'JSON');
//						$this->error('您已经下载过本题，是否要重新下载？','',3,false,true);
					}
				}
			}
		}else{
			$this->error('错误操作！','',3,false,true);
		}

		if($flag){
			$this->success('下载完成！');
		}else{
			$this->error(L('operation_failure'),'',3,false,true);
		}
	}

	/**
	 * 是否下载题目--弹窗
	 *
	 */
	public function downloadAsk(){
		if (isset($_GET['id']) && is_string($_GET['id'])) {
			$this->assign('controller',MODULE_NAME);
			$this->assign('id',intval($_GET['id']));
			$this->assign('show_header', false);
			$this->display();
		}
	}

	/**
	 * 直接下载题目
	 *
	 * @return Array
	 */
	public function downloadDirect(){
		$this->success(L('operation_success'), '', 3, 'downloadAsk');
//		var_dump($_POST['id']);
//		exit;
//		$this->success('操作成功！','',3,false,true);
//		$this->error('操作失败！','',3,false,true);
	}

	/**
	 * 获取题目收藏记录表
	 *
	 * @return String
	 */
	public function getCollectMod(){
		$tag = $_SESSION['admin_info']['id'] % 10;
		return 'admin_question'.$tag;
	}
	/**
	 * 获取某管理员的所有收藏记录
	 *
	 * @return Array
	 */
	public function getCollectAll(){
		$collect_mod = M("{$this->getCollectMod()}");
		$collect_list = $collect_mod->where("admin_id={$_SESSION['admin_info']['id']}")->select();
		return array_to_key($collect_list,'question_id');
	}
}