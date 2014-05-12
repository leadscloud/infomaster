<!DOCTYPE html>
<html lang="en"><head>
    <meta charset="utf-8">
    <title><?php echo esc_html(strip_tags(system_head('title')));?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Ray.">
    <meta name="robots" content="noindex, nofollow" />

    <!-- Le styles -->
    <?php 
		// 加载核心CSS
		loader_css('css/style');
		loader_css('css/responsive');
		// 加载模块CSS
		loader_css(system_head('styles'));
	  ?> 

    <link rel="prerender" href="<?php echo ROOT;?>admin/statistics.php">
    
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="<?php echo ROOT;?>common/assets/ico/favicon.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo ROOT;?>common/assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo ROOT;?>common/assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="<?php echo ROOT;?>common/assets/ico/apple-touch-icon-57-precomposed.png">
    <?php include COM_PATH."/system/analyticstracking.php"; ?> 
    </head>

    <body class="navbar-fixed <?php echo is_array(system_head('body_class'))?'':system_head('body_class');?>">
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">

          <a class="brand" href="<?php echo ADMIN;?>"><small><i class="icon-leaf"></i> 询盘管理系统</small></a>

          <ul class="nav pull-right nav-info">
            <li class="purple">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="icon-bell-alt icon-only icon-animated-bell"></i>
								<span class="badge badge-important badge-pm"></span>
							</a>

							<ul class="pull-right dropdown-navbar navbar-pink dropdown-menu dropdown-caret dropdown-closer">
								<li class="nav-header">
									<i class="icon-warning-sign"></i>
									<span class="pm-count"><?php echo message_count('unread');?></span>个消息
								</li>

								<li>
									<a href="<?php echo ADMIN?>message.php?type=inbox">
										<div class="clearfix" >
											<span class="pull-left">
												<i class="btn btn-mini no-hover btn-pink icon-comment"></i>
												查看新消息
											</span>
                                            <span class="pull-right badge badge-info pm-count"></span>
                                             
										</div>
									</a>
								</li>

								<li>
									<a href="<?php echo ADMIN?>message.php?type=sent">
                                     <div class="clearfix">
                                      <span class="pull-left">
										<i class="btn btn-mini btn-primary icon-upload-alt"></i>
										已发送过的消息
                                      </span>
                                        <?php
											if($sent_count = message_count('sent')) {
												echo '<span class="pull-right badge badge-info">+'.$sent_count.'</span>';
											}
										?>
                                      </div>
									</a>
								</li>



								<li>
									<a href="<?php echo ADMIN?>message.php">
										查看所有消息
										<i class="icon-arrow-right"></i>
									</a>
								</li>
							</ul>
						</li>
            <li class="light-blue"><a href="<?php echo ADMIN.'profile.php';?>" class="dropdown-toggle" data-toggle="dropdown"><img class="nav-user-photo" src="<?php echo get_user_avatar($_USER['userid']);?>" alt="" style="max-width:40px; max-height:40px;"> <?php echo $_USER['nickname']==null?$_USER['name']:$_USER['nickname'] ?><i class="icon-caret-down"></i></a>
            	<ul class="dropdown-menu dropdown-user">
                  <li>
                      <div class="media">
                          <a class="pull-left" href="#">
                              <img src="<?php echo get_user_avatar($_USER['userid']);?>" title="Gravatar" alt="Avatars" width="96" height="96" />
                          </a>
                          <div class="media-body description pull-left">
                              <p><strong class="login-nickname"><?php echo $_USER['nickname']==null?$_USER['name']:$_USER['nickname'] ?></strong></p>
                              <p class="muted"><?php echo $_USER['mail'];?></p>
                              <p class="action"><a class="link" href="<?php echo ADMIN.'user.php?method=edit&userid='.$_USER['userid'];?>"><?php echo $_USER['name'];?></a> - <a class="link" href="<?php echo ADMIN.'options-general.php';?>">设置</a></p>
                              <a href="<?php echo ADMIN.'profile.php';?>" class="btn btn-primary btn-medium">查看个人资料</a>
                          </div>
                      </div>
                  </li>
                  <li class="dropdown-footer">
                      <div class="clearfix">
                          <a class="btn btn-small pull-right" href="<?php echo ADMIN?>login.php?method=logout" onclick="return $(this).logout();">注销</a>
                          <?php
						  if(current_user_can('user-new',false)){
							  echo '<a class="btn btn-small" href="'.ADMIN.'user.php?method=new">添加账户</a>';
						  }
						  ?>
                         
                      </div>
                  </li>
              </ul>
            </li>
         </ul>
        </div>
      </div>
    </div>
    <div class="container-fluid" id="main-container">
		<a id="menu-toggler" href="#" class=""><span></span></a>
		<!--ST siderbar-->
		<div id="sidebar" class="fixed">
				<div id="sidebar-shortcuts">
					<div id="sidebar-shortcuts-large">
						<button class="btn btn-small btn-success" data-href="<?php echo ADMIN?>index.php" title="控制面板">
							<i class="icon-signal"></i>
						</button>

						<button class="btn btn-small btn-info" data-href="<?php echo ADMIN?>report.php?method=new" title="添加询盘信息">
							<i class="icon-pencil" ></i>
						</button>

						<button class="btn btn-small btn-warning" data-href="<?php echo ADMIN?>user.php" title="用户管理">
							<i class="icon-group"></i>
						</button>

						<button class="btn btn-small btn-danger"  data-href="<?php echo ADMIN?>options-general.php" title="系统设置">
							<i class="icon-cogs"></i>
						</button>
					</div>

					<div id="sidebar-shortcuts-mini">
						<span class="btn btn-success"></span>
						<span class="btn btn-info"></span>
						<span class="btn btn-warning"></span>
						<span class="btn btn-danger"></span>
					</div>
				</div><!--#sidebar-shortcuts-->

				<ul class="nav nav-list" id="admin-menu">
                	    <?php 
    /**
     * 系统菜单
     *
     */
    system_menu(array(

        'cpanel' => array('<i class="icon-dashboard"></i><span>控制面板</span>','index.php','a1',array(
            array('<i class="icon-dashboard"></i><span>控制面板</span>','index.php','cpanel'),
            array('<i class="icon-trophy"></i><span>询盘所属人概览</span>','statistics.php','cpanel'),
			      array('<i class="icon-user"></i><span>联系人</span>','contact.php','contact-list'),
            array('<i class="icon-user-md"></i><span>我的资料</span>','profile.php'),
        )),

        'customers' => array('<i class="icon-building"></i><span>询盘信息</span>','report.php','a2',array(
            array('<i class="icon-building"></i><span>所有信息</span>','report.php','post-list'),
			array('<i class="icon-plus"></i><span>添加信息</span>','report.php?method=new','post-new'),
			//array('','','post-list'),
            array('<i class="icon-flag"></i><span>分类（部门）</span>','categories.php','categories'),
        )),
		
		'domain' => array('<i class="icon-tags"></i><span>域名管理 ','domain.php','a2',array(
            array('<i class="icon-tags"></i><span>所有域名</span>','domain.php','domain-list'),
      array('<i class="icon-plus"></i><span>添加域名</span>','domain.php?method=new','domain-new'),
      array('<i class="icon-tags"></i><span>分组</span>','domain-group.php','domain-group-new'),
			//array('<i class="icon-tags"></i><span>添加分组</span>','domain-group.php?method=new','domain-group-new'),
        )),
        
        //'------------------------------------------------------------',
        
        'users' => array('<i class="icon-user"></i><span>用户管理</span>','user.php','a4',array(
            array('<i class="icon-user"></i><span>所有用户</span>','user.php','user-list'),
            array('<i class="icon-plus-sign-alt"></i><span>添加用户</span>','user.php?method=new','user-new'),
			//array('','','group-list'),
			array('<i class="icon-group"></i><span>用户组</span>','user-group.php','group-list'),
            array('<i class="icon-plus"></i><span>添加用户组</span>','user-group.php?method=new','group-new'),
        )),
        
        'tools' => array('<i class="icon-cogs"></i><span>工具</span>','tools/clean-cache.php','a8',array(
            array('<i class="icon-medkit"></i><span>清除缓存</span>','tools/clean-cache.php','clean-cache'),
			array('<i class="icon-food"></i><span>其它工具</span>','tools/other.php','tools'),
			array('<i class="icon-h-sign"></i><span>历史记录</span>','tools/history.php','history'),
        )),
		'options'  => array('<i class="icon-wrench"></i> <span>设置</span>','options-general.php','',array(
			 array('<i class="icon-wrench"></i> <span>基本设置</span>','options-general.php','option-general'),
			 array('<i class="icon-magic"></i> <span>匹配规则</span>','rules.php','rule-list'),
			 array('<i class="icon-tags"></i> <span>字段管</span>理','model.php','model-list')
		)),
		
		'message' => array('<i class="icon-envelope-alt"></i> <span>私信管理</span>','message.php'),
	
		'about' => array('<i class="icon-coffee"></i> <span>关于</span>','about.php'),
		'feeback' => array('<i class="icon-coffee"></i> <span>用户反馈<span class="badge badge-success">'.(issue_count(null,'open')==0?'':issue_count(null,'open')).'</span></span>','feedback.php')
    ));
    ?>
				</ul><!--/.nav-list-->
               

				<div id="sidebar-collapse">
					<i class="icon-double-angle-left"></i>
				</div>
			</div>
<!--ED siderbar-->
<div id="main-content">
  <div id="breadcrumbs">
      <ul class="breadcrumb">
          <li>
              <i class="icon-home"></i>
              <a href="<?php echo ADMIN?>">首页</a>

              <span class="divider">
                  <i class="icon-angle-right"></i>
              </span>
          </li>
          <li class="active"><?php echo system_head('title');?></li>
      </ul><!--.breadcrumb-->

      <div id="nav-search">
          <form class="form-search" _lpchecked="1">
              <span class="input-icon">
                  <input type="text" placeholder="搜索 ..." class="input-small search-query" id="nav-search-input" autocomplete="off">
                  <i class="icon-search" id="nav-search-icon"></i>
              </span>
          </form>
      </div><!--#nav-search-->
  </div>
      <div id="main" class="clearfix">
      <noscript><div class="alert alert-error">您的浏览器不支持或已经禁止网页脚本，您无法正常使用系统的各项功能。<a href="http://service.mail.qq.com/cgi-bin/help?subtype=1&&no=341&&id=7" title="了解网页脚本限制的更多信息" target="_blank"><i class="icon-question-sign"></i> 如何解除脚本限制</a></div></noscript>