<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="renderer" content="webkit" />
<title>房猫后台管理</title>

<link href="<?php echo base_url();?>dwz/themes/default/style.css" rel="stylesheet" type="text/css" media="screen"/>
<link href="<?php echo base_url();?>dwz/themes/css/core.css" rel="stylesheet" type="text/css" media="screen"/>
<link href="<?php echo base_url();?>dwz/themes/css/print.css" rel="stylesheet" type="text/css" media="print"/>
<link href="<?php echo base_url();?>dwz/uploadify/css/uploadify.css" rel="stylesheet" type="text/css" media="screen"/>
<!--[if IE]>
<link href="themes/css/ieHack.css" rel="stylesheet" type="text/css" media="screen"/>
<![endif]-->

<!--[if lte IE 9]>
<script src="js/speedup.js" type="text/javascript"></script>
<![endif]-->


<script src="<?php echo base_url();?>dwz/js/speedup.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>dwz/js/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>dwz/js/jquery.cookie.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>dwz/js/jquery.validate.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>dwz/js/jquery.bgiframe.js" type="text/javascript"></script>

<script src="<?php echo base_url();?>dwz/xheditor/xheditor-1.2.1.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>dwz/xheditor/xheditor_lang/zh-cn.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>dwz/bin/dwz.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>dwz/js/dwz.regional.zh.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>dwz/uploadify/scripts/jquery.uploadify.js" type="text/javascript"></script>

<!--plupload start--------------------------------------------------------------------------->
<link rel="stylesheet" href="<?php echo base_url();?>plupload/js/jquery.ui.plupload/css/jquery-ui-1.9.2.custom.min.css" type="text/css" />
<!--<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>-->
<script src="<?php echo base_url();?>plupload/js/jquery-ui-1.9.2.custom.min.js"></script>

<!-- Load plupload and all it's runtimes and finally the UI widget -->
<link rel="stylesheet" href="<?php echo base_url();?>plupload/js/jquery.ui.plupload/css/jquery.ui.plupload.css" type="text/css" />

<!-- production -->
<script type="text/javascript" src="<?php echo base_url();?>plupload/js/plupload.full.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>plupload/js/zh_CN.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>plupload/js/jquery.ui.plupload/jquery.ui.plupload.js"></script>

<!--plupload end---------------------------------------------------------------------------->

<script type="text/javascript">
$(function(){
	DWZ.init("<?php echo base_url();?>dwz/dwz.frag.xml", {
		loginUrl:"<?php echo site_url('manage_login/login');?>", loginTitle:"登录",	// 弹出登录对话框
//		loginUrl:"login.html",	// 跳到登录页面
		statusCode:{ok:200, error:300, timeout:301}, //【可选】
		pageInfo:{pageNum:"pageNum", numPerPage:"numPerPage", orderField:"orderField", orderDirection:"orderDirection"}, //【可选】
		debug:false,	// 调试模式 【true|false】
		callback:function(){
			initEnv();
			$("#themeList").theme({themeBase:"<?php echo base_url();?>dwz/themes"}); // themeBase 相对于index页面的主题base路径
		}
	});
});

</script>
</head>

<body scroll="no">
	<div id="layout">
		<div id="header">
			<div class="headerNav">
				<ul style="float: left; padding-left: 5px; padding-top: 5px">
					<img src="<?php echo base_url();?>static/images/index/index_logo.png" height="40">
				</ul>
				<ul class="nav">
					<li><a href="<?php echo base_url();?>" target="_black">前台首页</a></li>
					<li><a href="<?php echo site_url('manage_login/change_pwd');?>" target="dialog" rel="chagepwd">密码修改</a></li>
					<li>欢迎您：<?php echo $this->session->userdata('rel_name');?></li>
					<li><a href="<?php echo site_url('manage_login/logout');?>">退出</a></li>
				</ul>
			</div>
		</div>

		<div id="leftside" style="top: 50px">
			<div id="sidebar_s">
				<div class="collapse">
					<div class="toggleCollapse"><div></div></div>
				</div>
			</div>
			<div id="sidebar">
				<div class="toggleCollapse"><h2>主菜单</h2><div>收缩</div></div>
				<div class="accordion" fillSpace="sidebar" >
					<?php if($this->session->userdata('permission_id')==1):?>
					<div class="accordionHeader">
						<h2><span>Folder</span>基础数据</h2>
					</div>
					<div class="accordionContent">
						<ul class="tree">
							<li><a href="<?php echo site_url('manage/list_role');?>" target="navTab" rel="list_role">职级信息</a></li>
							<li><a href="<?php echo site_url('manage/list_position');?>" target="navTab" rel="list_user">职务信息</a></li>
							<li><a href="<?php echo site_url('manage/list_activity_type');?>" target="navTab" rel="list_activity_type">行程选项</a></li>
							<li><a href="<?php echo site_url('manage/list_course');?>" target="navTab" rel="list_course">代办进程</a></li>
							<li><a href="<?php echo site_url('manage/list_xiaoqu');?>" target="navTab" rel="list_xiaoqu">小区列表</a></li>
							<li><a href="<?php echo site_url('manage/list_towns');?>" target="navTab" rel="list_towns">区镇列表</a></li>
							<li><a href="<?php echo site_url('manage/list_menu');?>" target="navTab" rel="list_menu">套餐列表</a></li>
						</ul>
					</div>
					<?php endif;?>
					<?php if($this->session->userdata('permission_id') <= 4):?>
					<div class="accordionHeader">
						<h2><span>Folder</span>经纪人管理</h2>
					</div>
					<div class="accordionContent">
						<ul class="tree">
							<li><a href="<?php echo site_url('manage/list_company');?>" target="navTab" rel="list_company">公司信息</a></li>
							<li><a href="<?php echo site_url('manage/list_subsidiary');?>" target="navTab" rel="list_subsidiary">分店信息</a></li>
							<li><a href="<?php echo site_url('manage/list_user');?>" target="navTab" rel="list_user">用户列表</a></li>
						</ul>
					</div>
					<?php endif;?>
					<?php if($this->session->userdata('permission_id')==1):?>
					<div class="accordionHeader">
						<h2><span>Folder</span>文档/新闻/题库管理</h2>
					</div>
					<div class="accordionContent">
						<ul class="tree">
							<li><a href="<?php echo site_url('manage/list_forum_type');?>" target="navTab" rel="list_forum_type">资料类别</a></li>
							<li><a href="<?php echo site_url('manage/list_ticket');?>" target="navTab" rel="list_ticket">资料列表</a></li>
							<li><a href="<?php echo site_url('manage/list_news');?>" target="navTab" rel="list_news">新闻列表</a></li>
							<li><a href="<?php echo site_url('manage/list_questions');?>" target="navTab" rel="list_questions">试题列表</a></li>
						</ul>
					</div>
						<div class="accordionHeader">
							<h2><span>Folder</span>记录管理</h2>
						</div>
						<div class="accordionContent">
							<ul class="tree">
								<li><a href="<?php echo site_url('manage/list_sum_log');?>" target="navTab" rel="list_sum_log">金额流水记录</a></li>
							</ul>
						</div>
						<div class="accordionHeader">
							<h2><span>Folder</span>代办事项</h2>
						</div>
						<div class="accordionContent">
							<ul class="tree">
								<li><a href="<?php echo site_url('manage/list_agenda');?>" target="navTab" rel="list_agenda">代办事项</a></li>
							</ul>
						</div>

					<?php endif;?>
					<?php if($this->session->userdata('permission_id')==1 || in_array(10,$this->session->userdata('position_id_array'))):?>
					<div class="accordionHeader">
						<h2><span>Folder</span>大成莲创信息</h2>
					</div>
					<div class="accordionContent">
						<ul class="tree">
							<li><a href="<?php echo site_url('manage/list_dclc');?>" target="navTab" rel="list_dclc">大成莲创信息</a></li>
						</ul>
					</div>
					<?php endif;?>
					<?php if($this->session->userdata('permission_id')==1 || in_array(11,$this->session->userdata('position_id_array'))):?>
						<div class="accordionHeader">
							<h2><span>Folder</span>房产评估管理</h2>
						</div>
						<div class="accordionContent">
							<ul class="tree">
								<li><a href="<?php echo site_url('manage/list_pg');?>" target="navTab" rel="list_pg">房屋评估信息列表</a></li>
								<li><a href="<?php echo site_url('manage/list_pg_qq');?>" target="navTab" rel="list_pg_qq">评估客服QQ列表</a></li>
								<li><a href="<?php echo site_url('manage/list_pg_msg');?>" target="navTab" rel="list_pg_msg">评估留言列表</a></li>
							</ul>
						</div>
					<?php endif;?>
					<?php if($this->session->userdata('permission_id')==1 || in_array(12,$this->session->userdata('position_id_array'))):?>
						<div class="accordionHeader">
							<h2><span>Folder</span>金融管理</h2>
						</div>
						<div class="accordionContent">
							<ul class="tree">
								<li><a href="<?php echo site_url('manage/list_fin');?>" target="navTab" rel="list_fin">金融信息列表</a></li>

							</ul>
						</div>
					<?php endif;?>
				</div>
			</div>
		</div>
		<div id="container" style="top: 50px">
			<div id="navTab" class="tabsPage">
				<div class="tabsPageHeader">
					<div class="tabsPageHeaderContent"><!-- 显示左右控制时添加 class="tabsPageHeaderMargin" -->
						<ul class="navTab-tab">
							<li tabid="main" class="main"><a href="javascript:;"><span><span class="home_icon">我的主页</span></span></a></li>
						</ul>
					</div>
					<div class="tabsLeft">left</div><!-- 禁用只需要添加一个样式 class="tabsLeft tabsLeftDisabled" -->
					<div class="tabsRight">right</div><!-- 禁用只需要添加一个样式 class="tabsRight tabsRightDisabled" -->
					<div class="tabsMore">more</div>
				</div>
				<ul class="tabsMoreList">
					<li><a href="javascript:;">我的主页</a></li>
				</ul>
				<div class="navTab-panel tabsPageContent layoutBox">
				<div class="page unitBox">
	<div class="accountInfo">
		<div class="alertInfo">
			<h2>使用手册</h2>
			<a>演示视频</a>
		</div>
		<p><span>个人主页内容</span></p>
		<p>个人主页内容</p>
	</div>
	<div class="pageFormContent" layoutH="80">
	</div>
</div>					
				</div>
			</div>
		</div>

	</div>

	<div id="footer">Copyright &copy; 2016 Funmall Co., Ltd. All rights reserved. 备案号：苏CP备13003602号-2</div>


</body>
</html>
