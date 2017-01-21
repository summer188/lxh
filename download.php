<?php
/**
 * 由于在thinkphp框架内下载题目后，文档内容乱码经多次调试后无效，故把此功能移到框架外部实现
 *
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/5/4
 * Time: 11:50
 */
include_once ("connect.php");

if(!empty($_REQUEST['id']) && !empty($_REQUEST['pid']) && !empty($_REQUEST['tab'])){
	//题目id
	$id = intval($_REQUEST['id']);
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
	//取题目信息
	$sql = "SELECT * FROM $tab where id=$id AND period_id=$pid";
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
			$filename = time().'.doc';
			if(!file_exists($question_file)){
				$question_file = $question_dir.$question['net_logo'].'.docx';
				$filename = time().'.docx';
			}
			//下载题目
			header('Content-type: application/force-download');
			header('Content-Disposition: attachment; filename='.$filename);
			header('Content-Length: '.filesize($question_file));
			readfile($question_file);
			exit;

		}
	}
}