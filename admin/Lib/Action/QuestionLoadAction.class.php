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
	 */
	public function upnew($id=''){
		$grade_list = $this->getGradeList();
		$cate_list = $this->getCateList();
		$style_list = $this->getStyleList();
		//编辑时有id
		if($id!='' && intval($id)>0){
			$id = intval($id);
			$question_info = M("question")->where("id=$id")->find();
			if($question_info){
				$point_list = $this->getPointAll($question_info['grade_id'],$question_info['cate_id']);
				$chapter_list = $this->getChapterAll($question_info['grade_id'],$question_info['cate_id']);
				$section_list = $this->getSectionList($question_info['chapter_id']);
				$type_list = $this->getTypeAll($question_info['cate_id']);
				$title = file_get_contents($question_info['title_url']);
				$info = file_get_contents($question_info['info_url']);
				$this->assign('id',$id);
				$this->assign('question_info',$question_info);
				$this->assign('point_list',$point_list);
				$this->assign('chapter_list',$chapter_list);
				$this->assign('section_list',$section_list);
				$this->assign('type_list',$type_list);
				$this->assign('title',$title);
				$this->assign('info',$info);
			}
		}
		$this->assign('grade_list',$grade_list);
		$this->assign('cate_list',$cate_list);
		$this->assign('style_list',$style_list);
		$this->assign('controller',MODULE_NAME);
		$this->display('upnew');
	}

	/**
	 * 保存新题
	 *
	 * @return Array
	 */
	public function upsave(){
		$id = intval($_POST['id']);
		//取得题干和题目解析
		$title = $_POST['title'];
		unset($_POST['title']);
		$info = $_POST['info'];
		unset($_POST['info']);
		$all = $title.'<br/>题目解析：<br/>'.$info;

		//检测上传题目存放目录是否存在，不存在就创建
		$this->checkQuestionDir();

		$question_mod = M('question');
		$data = $question_mod->create();
		if(false === $data){
			$this->error($question_mod->error());
		}
		$data['period_id'] = $this->getPeriod();
		$data['school_id'] = $_SESSION['admin_info']['school_id'];
		$data['create_id'] = $_SESSION['admin_info']['id'];
		$data['create_time'] = time();
		$flag = true;
		if($id > 0){
			//编辑
			$result = $question_mod->save($data);
			if(!$result){
				$flag = false;
			}
			$question_info = $question_mod->where("id=$id")->find();
			$title_url = $question_info['title_url'];
			$info_url = $question_info['info_url'];
			$all_url = $question_info['all_url'];
		}else{
			//新增
			$id = $question_mod->add($data);
			if(!$id){
				$flag = false;
			}
			//获取题干和题目解析的文档存放目录
			$question_dir = $this->getQuestionDir($data['grade_id'],$data['cate_id'],$id);
			$this->checkDir($question_dir);
			$title_url = $question_dir.'title_'.$id.'.doc';
			$info_url = $question_dir.'info_'.$id.'.doc';
			$all_url = $question_dir.'all_'.$id.'.doc';
			$arr = array(
				'id' => $id,
				'title_url' => $title_url,
				'info_url' => $info_url,
				'all_url' => $all_url
			);
			$result = $question_mod->save($arr);
			if(!$result){
				$flag = false;
			}
		}

		//将题干和题目解析存入文档
		$title_file = fopen($title_url,"w");
		fwrite($title_file, $title);
		fclose($title_file);

		$info_file = fopen($info_url,"w");
		fwrite($info_file, $info);
		fclose($info_file);

		$all_file = fopen($all_url,"w");
		fwrite($all_file,$all);
		fclose($all_file);

		if($flag){
			$redirect_url = U(MODULE_NAME.'/yun');
			$this->success(L('operation_success'),$redirect_url);
			exit();
		}else{
			$this->error(L('operation_failure'));
			exit();
		}
	}
	/**
	 * 收藏题目
	 *
	 * @return Array
	 */
	public function collectQuestion(){
		$flag = true;
		if (isset($_GET['id']) && is_string($_GET['id'])) {
			$id = intval($_GET['id']);
			if($id > 0){
				$question_mod = M("{$this->getCollectMod()}");
				$admin_id = $_SESSION['admin_info']['id'];
				//先查看下是否已经收藏过
				$record = $question_mod->where("admin_id=$admin_id AND question_id=$id")->find();
				if(!$record){
					$data = array(
						'admin_id' => $admin_id,
						'question_id' => $id
					);
					$result = $question_mod->add($data);
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
		if (isset($_GET['id']) && is_string($_GET['id'])) {
			$id = intval($_GET['id']);
			if($id > 0){
				$question_mod = M("{$this->getCollectMod()}");
				$admin_id = $_SESSION['admin_info']['id'];
				//先查看下是否已经收藏过
				$record = $question_mod->where("admin_id=$admin_id AND question_id=$id")->find();
				if(!$record){//若未收藏过，就先收藏再下载
					$data = array(
						'admin_id' => $admin_id,
						'question_id' => $id,
						'is_download' => 1
					);
					$result = $question_mod->add($data);
					if(!$result){
						$flag = false;
					}
					//下载题目

				}else{//若已收藏过，就需要先判断其下载状态
					if($record['is_download'] == 0){//未下载
						$record['is_download'] = 1;
//						$record = $question_mod->create($record);
						$result = $question_mod->data($record)->where("admin_id=$admin_id AND question_id=$id")->save($record);
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
		var_dump($_GET['id']);
		exit;
		$this->success('操作成功！','',3,false,true);
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