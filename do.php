<?php

include_once ("connect.php");

$action = $_GET['action'];
if ($action == 'import') { //导入XLS
    include_once("excel/reader.php");
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
		for ($i=2; $i<=$xls->sheets[0]['numRows']; $i++) {
			$site_logo = $xls->sheets[0]['cells'][$i][0];
			$installment = $xls->sheets[0]['cells'][$i][1];
			$click_url = $xls->sheets[0]['cells'][$i][2];
			$net_logo = $xls->sheets[0]['cells'][$i][3];
			$name = $xls->sheets[0]['cells'][$i][4];
			$description = $xls->sheets[0]['cells'][$i][5];
			$recommend = $xls->sheets[0]['cells'][$i][6];
			$cash_back_rate = $xls->sheets[0]['cells'][$i][7];
			$has_invoice = $xls->sheets[0]['cells'][$i][8];
			$site_logo = $xls->sheets[0]['cells'][$i][9];
			$title_attribute = $xls->sheets[0]['cells'][$i][10];
			$subject = $xls->sheets[0]['cells'][$i][11];
			$data_values .= "('$installment','$click_url','$net_logo','$name','$description','$recommend','$cash_back_rate','$has_invoice','$site_logo','$title_attribute','$subject'),";
		}
		$data_values = substr($data_values,0,-1); //去掉最后一个逗号
		$query = mysql_query("insert into lxh_seller_list (installment,click_url,net_logo,name,description,recommend,cash_back_rate,has_invoice,site_logo,title_attribute,subject) values $data_values");//批量插入数据表中
	    if($query){
		    echo '导入成功！';
	    }else{
		    echo '导入失败！';
	    }
	}
} elseif ($action=='export') { //导出XLS
    $result = mysql_query("select * from lxh_seller_list");
    $str = "年级\t期数\t题目序号\t题目简介\t答案\t选项数量\t大题难易度\t小题难易度\t知识点编号\t题目属性\t学科\t\n";
    $str = iconv('utf-8','gb2312',$str);
    while($row=mysql_fetch_array($result)){
		$installment = iconv('utf-8','gb2312',$row['installment']);
		$click_url = iconv('utf-8','gb2312',$row['click_url']);
		$net_logo = iconv('utf-8','gb2312',$row['net_logo']);
        $name = iconv('utf-8','gb2312',$row['name']);
		$description = iconv('utf-8','gb2312',$row['description']);
		$recommend = iconv('utf-8','gb2312',$row['recommend']);
		$cash_back_rate = iconv('utf-8','gb2312',$row['cash_back_rate']);
		$has_invoice = iconv('utf-8','gb2312',$row['has_invoice']);
		$site_logo = iconv('utf-8','gb2312',$row['site_logo']);
		$title_attribute = iconv('utf-8','gb2312',$row['title_attribute']);
		$subject = iconv('utf-8','gb2312',$row['subject']);
    	$str .= $installment."\t".$click_url."\t".$net_logo."\t".$name."\t".$format."\t".$description."\t".$recommend."\t".$cash_back_rate."\t".$has_invoice."\t".$site_logo."\t".$title_attribute."\t".$subject."\t"."\t\n";
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
