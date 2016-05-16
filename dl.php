<?php
/**
 * Created by Sunmiaomiao.
 * Email: sunmiaomiao@kangq.com
 * Date: 2016/5/14
 * Time: 11:40
 */
include_once ("connect.php");

if(!empty($_REQUEST['ids']) && !empty($_REQUEST['pid']) && !empty($_REQUEST['tab'])){
	//题目ids
	$ids = $_REQUEST['ids'];
	$ids = explode(',',$ids);
	//学段id
	$pid = intval($_REQUEST['pid']);
	//题目表
	$tab = 'lxh_'.$_REQUEST['tab'];
	//根据学段id取学科表
	switch($pid){
		case 1:
			$cate_tab = 'lxh_adboard';
			break;
		case 2:
			$cate_tab = 'lxh_seller_cate';
			break;
		case 3:
			$cate_tab = 'lxh_article_cate';
			break;
		default:
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
						$question_dir = 'upload/'.$alias.'/'.$question['grade_id'].'/'.$question['site_logo'].'/'.$question['net_logo'].'/';
						$question_file = $question_dir.$question['net_logo'].'.doc';
						$new_file = 'temp/'.$value.'.doc';
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
	ob_flush();
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
