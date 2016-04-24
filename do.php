<?php

include_once ("connect.php");

$action = $_GET['action'];
if ($action == 'import') { //导入XLS
    include("excel/reader.php");
	$tmp = $_FILES['file']['tmp_name'];
	if (empty ($tmp)) {
		echo '请选择要导入的Excel文件！';
		exit;
	}
	
	$save_path = "xls/";
	$file_name = $save_path.date('Ymdhis') . ".xls";
	if (copy($tmp, $file_name)) {
		$xls = new Spreadsheet_Excel_Reader();
		$xls->setOutputEncoding('utf-8');
		$xls->read($file_name);
		for ($i=3; $i<=$xls->sheets[0]['numRows']; $i++) {
			$grade_id = $xls->sheets[0]['cells'][$i][1];
			$site_logo = $xls->sheets[0]['cells'][$i][2];
			$net_logo = $xls->sheets[0]['cells'][$i][3];
			$name = $xls->sheets[0]['cells'][$i][4];
			$recommend = $xls->sheets[0]['cells'][$i][5];
			$answer = $xls->sheets[0]['cells'][$i][6];
			$installment = $xls->sheets[0]['cells'][$i][7];
			$has_invoice = $xls->sheets[0]['cells'][$i][8];
			$cash_back_rate = $xls->sheets[0]['cells'][$i][9];
			$title_attribute = $xls->sheets[0]['cells'][$i][10];
			$subject = $xls->sheets[0]['cells'][$i][11];
			$update_time = date("Y-m-d h:i:s",time());
			$data_values .= "('$grade_id','$site_logo','$net_logo','$name','$recommend','$answer','$installment','$has_invoice','$cash_back_rate','$title_attribute','$subject','1','$update_time'),";
		}
		$data_values = substr($data_values,0,-1); //去掉最后一个逗号
		$sql = "insert into lxh_seller_list (grade_id,site_logo,net_logo,name,recommend,answer,installment,has_invoice,cash_back_rate,title_attribute,subject,status,update_time) values $data_values";
		$query = mysql_query($sql);//批量插入数据表中
	    if($query){
		    echo '导入成功！';
	    }else{
		    echo '导入失败！';
	    }
	}
} elseif ($action=='export') { //导出XLS
    $result = mysql_query("select * from lxh_seller_list");
    $str = "年级\t期数\t题目序号\t题目简介\t解析格式\t答案\t选项数量\t大题难易度\t小题难易度\t知识点编号\t题目属性\t\n";
    $str = iconv('utf-8','gb2312',$str);
    while($row=mysql_fetch_array($result)){
        $grade_id = iconv('utf-8','gb2312',$row['grade_id']);
		$site_logo = iconv('utf-8','gb2312',$row['site_logo']);
		$net_logo = iconv('utf-8','gb2312',$row['net_logo']);
        $name = iconv('utf-8','gb2312',$row['name']);
		$recommend = iconv('utf-8','gb2312',$row['recommend']);
		$answer = iconv('utf-8','gb2312',$row['answer']);
		$installment = iconv('utf-8','gb2312',$row['installment']);
		$has_invoice = iconv('utf-8','gb2312',$row['has_invoice']);
		$cash_back_rate = iconv('utf-8','gb2312',$row['cash_back_rate']);
		$title_attribute = iconv('utf-8','gb2312',$row['title_attribute']);
		$subject = iconv('utf-8','gb2312',$row['subject']);
    	$str .= $grade_id."\t".$site_logo."\t".$net_logo."\t".$name."\t".$recommend."\t".$answer."\t".$installment."\t".$has_invoice."\t".$cash_back_rate."\t".$title_attribute."\t".$subject."\t"."\t\n";
    }
    $filename = date('Ymd').'.xls';
    exportExcel($filename,$str);
}


function exportExcel($filename,$content){
 	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Type: application/vnd.ms-execl");
	header("Content-Type: application/force-download");
	header("Content-Type: application/download");
    header("Content-Disposition: attachment; filename=".$filename);
    header("Content-Transfer-Encoding: binary");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo $content;
}
?>
