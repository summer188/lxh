<?php
/**
 * 题目上传、编辑、预览、收藏、下载、删除公共控制器
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
                //取png图片
                $cate_alias = $this->cate_list[$question_info['cate_id']]['alias'];
                $question_dir = 'upload/'.$cate_alias.'/'.$question_info['grade_id'].'/'.$question_info['site_logo'].'/'.$question_info['net_logo'].'/';
                $png_url = $question_dir.$question_info['net_logo'].'.png';
				if(!file_exists($png_url)){
					$png_url = $question_dir.$question_info['net_logo'].'.jpg';
				}

				$this->assign('id',$id);
				$this->assign('question_info',$question_info);
				$this->assign('png_url',$png_url);
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

		//上传新题时保存文档，编辑时不操作文档
		if($id == 0){
			//检测上传题目存放目录是否存在，不存在就创建
			$cate_id = intval($_POST['cate_id']);
			$grade_id = intval($_POST['grade_id']);
			$site_logo = $_POST['site_logo'];
			$net_logo = $_POST['net_logo'];
			$question_dir = $this->checkQuestionDir($cate_id,$grade_id,$site_logo,$net_logo);

			//将题目word文档和png图片临时路径移动到指定目录
			if(!empty($_FILES['word']) && !empty($_FILES['png'])){
				$word = $_FILES['word'];
				$png = $_FILES['png'];
				$pdf = $_FILES['pdf'];
				$check_word = $this->checkFileType($word,'word');
				if(!$check_word){
					$this->error('word文档类型不正确，请检查后重新上传！');
					exit();
				}
				$check_png = $this->checkFileType($png,'png');
				if(!$check_png){
					$this->error('png或jpg图片类型不正确，请检查后重新上传！');
					exit();
				}
                $check_pdf = $this->checkFileType($pdf,'pdf');
                if(!$check_pdf){
                    $this->error('pdf文档类型不正确，请检查后重新上传！');
                    exit();
                }
				$word_ext = pathinfo($word['name'], PATHINFO_EXTENSION);
				$png_ext = pathinfo($png['name'], PATHINFO_EXTENSION);
				$pdf_ext = pathinfo($pdf['name'], PATHINFO_EXTENSION);
				$word_url = $question_dir.$net_logo.'.'.$word_ext;
				$png_url = $question_dir.$net_logo.'.'.$png_ext;
				$pdf_url = $question_dir.$net_logo.'.'.$pdf_ext;

				if(!move_uploaded_file($word['tmp_name'],$word_url)){
					$flag = false;
				}
				if(!move_uploaded_file($png['tmp_name'],$png_url)){
					$flag = false;
				}
                if(!move_uploaded_file($pdf['tmp_name'],$pdf_url)){
                    $flag = false;
                }
			}
		}

        //数据表保存数据
		$data = $this->question_mod->create();
		if(false === $data){
			$this->error($this->question_mod->error());
		}
		$data['period_id'] = $this->period_id;
        $data['create_id'] = $_SESSION['admin_info']['id'];
		$data['school_id'] = $_SESSION['admin_info']['school_id'];
		$data['update_time'] = date("Y-m-d h:i:s",time());

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
     * 检测题目目录--响应ajax请求
     *
     * @return Array
     */
    public function upword(){
        $jsondata = array('status'=>false,'info'=>'经检测，暂时不能上传文件，请稍后再试！');
        $grade_id=isset($_GET['grade'])?trim($_GET['grade']):'';
        $cate_id=isset($_GET['cate'])?trim($_GET['cate']):'';
        $site_logo=isset($_GET['site'])?trim($_GET['site']):'';
        $net_logo=isset($_GET['net'])?trim($_GET['net']):'';

        $result=$this->checkQuestionDir($cate_id,$grade_id,$site_logo,$net_logo);
        if($result){
            $jsondata['status'] = true;
            $jsondata['info'] = '经检测，可以上传！';
        }
        $this->ajaxReturn($jsondata,'JSON');
    }

	/**
	 * 预览题目
	 *
	 */
	public function lookQuestion(){
		if(isset($_GET['id']) && intval($_GET['id'])){
			$id = intval($_GET['id']);
			$question_info = $this->question_mod->where("id=$id")->find();
			$question_dir = $this->checkQuestionDir($question_info['cate_id'],$question_info['grade_id'],$question_info['site_logo'],$question_info['net_logo']);
			$question_file = $question_dir.$question_info['net_logo'];
			$src = $question_file.'.pdf';
			$this->assign('id',$id);
			$this->assign('src',$src);
            $this->assign('show_header', false);
            $this->assign('controller',MODULE_NAME);
			$this->display('lookQuestion');
		}else{
			$this->error(L('please_select'));
		}
	}

	/**
	 * 编辑题目
	 *
	 */
	public function editQuestion(){
		$access = $this->checkEditAccess();
		if($access){
			if(isset($_GET['id']) && intval($_GET['id'])){
				$display = $_GET['display'];
				$id = intval($_GET['id']);
				$this->upnew($id,$display);
			}else{
				$this->error(L('please_select'));
			}
		}else{
			$this->error('抱歉，您目前还没有相关权限！');
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
				$collect_mod = M("{$this->getCollectMod()}");
				$admin_id = $_SESSION['admin_info']['id'];
				//先查看下是否有记录
                $where = "admin_id=$admin_id AND period_id={$this->period_id} AND question_id=$id";
				$record = $collect_mod->where($where)->find();
				if(!$record){//没有记录，就添加记录同时置收藏状态
					$data = array(
						'admin_id' => $admin_id,
                        'period_id' => $this->period_id,
						'question_id' => $id,
						'is_collect' => 1
					);
					$result = $collect_mod->add($data);
					if(!$result){
						$flag = false;
					}
				}else{//若有记录
					if($record['is_collect']==1){//且已经收藏
						$this->error('您已经收藏过本题！');
					}elseif($record['is_collect']==0){//若未收藏过
						$record['is_collect'] = 1;
						$result = $collect_mod->data($record)->where($where)->save($record);
						if(!$result){
							$flag = false;
						}
					}

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
	 * 取消收藏
	 *
	 * @return Array
	 */
	public function removeCollect(){
		$flag = true;
		if (isset($_GET['id']) && is_string($_GET['id'])) {
			$id = intval($_GET['id']);
			if($id > 0){
				$collect_mod = M("{$this->getCollectMod()}");
				$admin_id = $_SESSION['admin_info']['id'];
				//先查看下是否有记录
				$where = "admin_id=$admin_id AND period_id={$this->period_id} AND question_id=$id";
				$record = $collect_mod->where($where)->find();
				if(!empty($record)){//若有记录
					if($record['is_collect']==1){//若已经收藏，则直接取消收藏
						$data['is_collect'] = 0;
						$result = $collect_mod->where($where)->save($data);
						if(!$result){
							$flag = false;
						}
					}
				}
			}
		}else{
			$flag = false;
		}

		if($flag){
			$this->success('操作成功！');
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
		$jsondata = array();
		if (isset($_GET['id']) && is_string($_GET['id'])) {
			$id = intval($_GET['id']);
			if($id > 0){
				$collect_mod = M("{$this->getCollectMod()}");
				$admin_id = $_SESSION['admin_info']['id'];
				//先查看下是否有记录
                $where = "admin_id=$admin_id AND period_id={$this->period_id} AND question_id=$id";
				$record = $collect_mod->where($where)->find();
				if($record==null){//若没有记录，需要先添加记录
					$data = array(
						'admin_id' => $admin_id,
                        'period_id' => $this->period_id,
						'question_id' => $id,
						'is_download' => 1
					);
					$result = $collect_mod->add($data);
					if(!$result){
						$flag = false;
					}
					$jsondata['status'] = 1;
				}else{//若已有记录，就需要先判断其下载状态
					if($record['is_download'] == 0){//未下载
						$record['is_download'] = 1;
						$result = $collect_mod->data($record)->where($where)->save($record);
						if(!$result){
							$flag = false;
						}
						$jsondata['status'] = 1;
					}else{//已下载
						$jsondata['info'] = '您已经下载过本题，是否要重新下载？';
						$jsondata['status'] = 2;
					}
				}
                if(isset($_GET['act']) && $_GET['act']=='look'){
                    if($flag){
                        $this->success(L('operation_success'), '', '', 'lookQuestion');
                    }else{
                        $this->error(L('operation_failure'));
                    }
                }else{
                    if($flag){
                        $this->ajaxReturn($jsondata,'JSON');
                    }else{
                        $this->error(L('operation_failure'),'',3,false,true);
                    }
                }


			}
		}else{
			$this->error('错误操作！','',3,false,true);
		}
	}

    /**
     * 预览》下载题目
     *
     * @return Array
     */
    public function lookDownload(){
        $flag = true;
        if (isset($_POST['id'])) {
            $id = intval($_POST['id']);
            if($id > 0){
                $collect_mod = M("{$this->getCollectMod()}");
                $admin_id = $_SESSION['admin_info']['id'];
                //先查看下是否有记录
                $where = "admin_id=$admin_id AND period_id={$this->period_id} AND question_id=$id";
                $record = $collect_mod->where($where)->find();
                if($record==null){//若没有记录，需要先添加记录
                    $data = array(
                        'admin_id' => $admin_id,
                        'period_id' => $this->period_id,
                        'question_id' => $id,
                        'is_download' => 1
                    );
                    $result = $collect_mod->add($data);
                    if(!$result){
                        $flag = false;
                    }
                }else{//若已有记录，就需要先判断其下载状态
                    if($record['is_download'] == 0){//未下载
                        $record['is_download'] = 1;
                        $result = $collect_mod->data($record)->where($where)->save($record);
                        if(!$result){
                            $flag = false;
                        }
                    }
                }
                if($flag){
                    //取题目路径
                    $info = $this->question_mod->where('id='.$id)->select();
                    if(!empty($info)){
                        $quetion_info = $info[0];
                        $question_dir = $this->checkQuestionDir($quetion_info['cate_id'],$quetion_info['grade_id'],$quetion_info['site_logo'],$quetion_info['net_logo']);
                        $question_file = $question_dir.$quetion_info['net_logo'].'.doc';
                        $filename = time().'.doc';
                        if(!file_exists($question_file)){
                            $question_file = $question_dir.$quetion_info['net_logo'].'.docx';
                            $filename = time().'.docx';
                        }
                        //下载题目
                        header('Content-type: application/force-download');
                        header('Content-Disposition: attachment; filename='.$filename);
                        header('Content-Length: '.filesize($question_file));
                        readfile($question_file);
                    }
                }else{
                    $this->error(L('operation_failure'));
                }
            }
        }
    }

	/**
	 * 删除题目
	 *
	 * @return String
	 */
	public function deleteQuestion(){
		$access = $this->checkDeleteAccess();
		if($access){
			if(!isset($_POST['id']) || empty($_POST['id'])){
				$this->error('请选择要删除的题目！');
			}
			if (isset($_POST['id']) && is_array($_POST['id'])) {
				$arr = $_POST['id'];
				$collect_mod = M("{$this->getCollectMod()}");
				$admin_id = $_SESSION['admin_info']['id'];
				foreach($arr as $key=>$value){
					//删除文档和截图
					$question_info = $this->question_mod->where("id=$value")->find();
					$cate_alias = $this->cate_list[$question_info['cate_id']]['alias'];
					$question_dir = 'upload/'.$cate_alias.'/'.$question_info['grade_id'].'/'.$question_info['site_logo'].'/'.$question_info['net_logo'].'/';
					$this->delFile($question_dir,$question_info['net_logo']);
					//删除收藏和下载记录表信息
					$where = "admin_id=$admin_id AND period_id=$this->period_id AND question_id=$value";
					$collect_mod->where($where)->delete();
				}
				//删除题目表中信息
				$ids = implode(',', $arr);
				$result = $this->question_mod->delete($ids);
				if($result){
					$this->success(L('operation_success'));
				}else{
					$this->error('操作失败！');
				}
			}
		}else{
			$this->error('没有权限！');
		}

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