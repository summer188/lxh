<?php
/**
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/5/14
 * Time: 11:40
 */
include_once ("connect.php");

if(!empty($_REQUEST['ids']) && !empty($_REQUEST['pid']) && !empty($_REQUEST['admin_id'])){
	//题目ids
	$ids = $_REQUEST['ids'];
	$admin_id = $_REQUEST['admin_id'];
	$tag = $admin_id % 10;
	//收藏下载记录表
	$user_tab = 'lxh_admin_question'.$tag;
	//学段id
	$pid = intval($_REQUEST['pid']);
	//题目表
	$ids = explode(',',$ids);
	//根据学段id取学科表
	switch($pid){
		case 1:
			$tab = 'lxh_ad';
			$cate_tab = 'lxh_adboard';
			break;
		case 2:
			$tab = 'lxh_seller_list';
			$cate_tab = 'lxh_seller_cate';
			break;
		case 3:
			$tab = 'lxh_article';
			$cate_tab = 'lxh_article_cate';
			break;
		default:
			$tab = '';
			$cate_tab = '';
			break;
	}
	//创建压缩文件
	$zip = new ZipArchive();
	$zname = time().'.zip';
	$zurl = 'temp/'.$zname;
	$create = $zip->open($zurl, ZipArchive::CREATE);
	if($create!==TRUE){
		echo '系统错误！';
	}else{
		if(is_array($ids) && count($ids)>0){
			foreach($ids as $key=>$value){
				//先查看下是否有下载记录
				$where = "admin_id=$admin_id AND period_id=$pid AND question_id=$value";
				$record_sql = "SELECT * FROM $user_tab WHERE $where";
				$res = mysql_query($record_sql);
				$record = array();
				while($arr=mysql_fetch_array($res)){
						$record[] = $arr;
				}
				$dl_sql = "";
				if(empty($record)){//若没有记录，需要先添加记录
					$dl_sql = "INSERT INTO $user_tab (admin_id,period_id,question_id,is_download) VALUES($admin_id,$pid,$value,1)";
				}else{//若已有记录，就需要先判断其下载状态
					if($record['is_download'] == 0){//未下载
						$dl_sql = "UPDATE $user_tab SET is_download=1 where id={$record['id']}";
					}
				}
				mysql_query($dl_sql);
				//取题目信息
				$sql = "SELECT * FROM $tab where id=$value AND period_id=$pid";
				$result = mysql_query($sql);
				if($question = mysql_fetch_array($result)){
					//取学科别名alias
					$alias = '';
					if(!empty($cate_tab)){
						$query = "SELECT * FROM $cate_tab where id={$question['cate_id']}";
						$res = mysql_query($query);
						if($cate = mysql_fetch_array($res)){
							$alias = $cate['alias'];
						}
					}
					if(!empty($alias)){
						//取题目路径
						$question_dir = 'upload/'.$alias.'/'.$question['grade_id'].'/';
						$question_file = $question_dir.$question['net_logo'].'.doc';
						$new_file = 'temp/'.$value.'.doc';
						if(!file_exists($question_file)){
							$question_file = $question_dir.$question['net_logo'].'.docx';
							$new_file = 'temp/'.$value.'.docx';
						}
						copy($question_file,$new_file);
						$zip->addFile($new_file);
					}
				}
			}
		}
	}
	$zip->close();
	ob_end_clean();
	header("Content-Type: application/force-download");
	header("Content-Transfer-Encoding: binary");
	header('Content-Type: application/zip');
	header('Content-Disposition: attachment; filename='.$zname);
	header('Content-Length: '.filesize($zurl));
	error_reporting(0);
	readfile($zurl);
	flush();
	deldir('temp/');
	exit;
}

//删除文件夹内元素
function deldir($dir){
	//删除目录下的文件：
	$dh=opendir($dir);
	while ($file=readdir($dh)){
		if($file!="." && $file!=".."){
			$fullpath=$dir."/".$file;
			if(!is_dir($fullpath)){
				unlink($fullpath);
			}else{
				deldir($fullpath);
			}
		}
	}
	closedir($dh);
}
