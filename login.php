<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>云题库系统</title>
	<link href="css/style.css" rel="stylesheet" type="text/css" />
	<script language="JavaScript" src="js/jquery.js"></script>
	<script src="js/cloud.js" type="text/javascript"></script>

	<script language="javascript">
		$(function(){
			$('.loginbox').css({'position':'absolute','left':($(window).width()-692)/2});
			$(window).resize(function(){
				$('.loginbox').css({'position':'absolute','left':($(window).width()-692)/2});
			})
		});
	</script>

</head>

<body style="background-color:#1c77ac; background-image:url(images/light.png); background-repeat:no-repeat; background-position:center top; overflow:hidden;">

<input type="hidden" id="remember_login" value="<?php echo empty($_COOKIE['remember_login'])?'':$_COOKIE['remember_login'];?>"/>

<div id="mainBody">
	<div id="cloud1" class="cloud"></div>
	<div id="cloud2" class="cloud"></div>
</div>


<div class="logintop">
	<span>欢迎进入千校联盟云题库系统</span>
</div>

<div class="loginbody">

	<span class="systemlogo"></span>

	<div class="loginbox loginbox1">
		<form action="/admin.php?m=Public&a=login" method="post" id="myform">
			<input type="hidden" name="loginFlag" id="loginFlag" value=""/>
			<input type="hidden" name="ajaxcode" id="ajaxcode"/>
			<ul>
				<li><input name="username" id="username" type="text" class="loginuser" value="" onclick="JavaScript:this.value=''"/></li>
				<li><input name="password" id="password" type="password" class="loginpwd" value=""/></li>
				<li class="yzm">
					<span><input id="verify" type="text" name="verify" type="text" value="" onclick="JavaScript:this.value=''"/></span><cite><img width="115" height="46" id="verify_img" src="/admin.php?m=Public&a=verify&rand=1450794520" title="看不清？点击刷新" onclick="javascript:this.src='/admin.php?m=Public&a=verify&mt='+Math.random()" /></cite>
				</li>
				<li>
					<input name="" type="submit" class="loginbtn" value="登录"/>
					<label><input name="" id="remember" type="checkbox" value="" onclick="isRemember()"/>记住密码</label>
					<label>
						<input name="" type="checkbox" value="" checked="checked" />
						<a href="#" onclick="open('disclaimer.html','免责声明','width=530,height=600,left=150,top=150,resizable=no,scrollbars=no,status=yes,toolbar=no,location=no,menubar=no,menu=yes')">免责声明</a>
					</label><!--label><a href="findpassword.html">忘记密码？</a></label--></li>
			</ul>
		</form>

	</div>

</div>


<div class="loginbm">版权所有  2016&nbsp;&nbsp;&nbsp; <strong>北京蚂蚁学教育科技有限公司</strong> &nbsp;&nbsp;&nbsp;免责声明</div>


</body>

</html>

<!--smm修改于2016-3-27 实现记住密码功能-->
<script language="javascript">
	//显示密码
	function showPwd(){
		$("#username").val("<?php echo empty($_COOKIE['admin_name'])?'':$_COOKIE['admin_name'];?>");
		$("#password").val("<?php echo empty($_COOKIE['admin_pwd'])?'':$_COOKIE['admin_pwd'];?>");
	}
	//从cookie里取得用户上次选择的是否记住密码的设置值，为true就显示密码
	$(function(){
		var remember = $("#remember_login").val();
		if(remember=='yes'){
			$("#remember").attr("checked","checked");
			showPwd();
		}
	});

	//判断“记住密码”框是否勾选,如果勾选就记住密码
	function isRemember(){
		var flag = $("#remember").attr("checked");
		if(flag){
			$("#loginFlag").val('yes');
		}else{
			$("#loginFlag").val("no");
		}
	}
</script>