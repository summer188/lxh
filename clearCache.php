<?php
/**
 * Created by Sunmiaomiao.
 * Email: 652308259@qq.com
 * Date: 2016/6/24
 * Time: 13:26
 */

deldir('./admin/Runtime');
$data = array('sta'=>1);
return json_encode($data);

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
	//删除空文件夹
	rmdir($dir);
}