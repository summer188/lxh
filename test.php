<?php
/**
 * Created by Sunmiaomiao.
 * Email: sunmiaomiao@kangq.com
 * Date: 2016/5/3
 * Time: 16:55
 */
$word = new COM("word.application") or die("Unable to instanciate Word");
$word->Visible = 0;
$word->Documents->Open("a.doc") or die("Unable to open this file");
$content = $word->ActiveDocument->Content->Text;
echo $content;