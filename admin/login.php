<?php
/*****************************************************************************************
 * PHP 集团营销数据平台                                                                  *
 * Copyright (C) 2013 by Ray Chang (张雷).    All rights reserved.                       *
 *                                                                                       *
 * 实现集团营销体系的数据及时共享和分析功能                                              *
 * Author Website: http://love4026.org                                                   *
 * Version: 1.1.Alpha1                                                                   *
 *****************************************************************************************/
// 定义管理后台路径
defined('ADMIN_PATH') or define('ADMIN_PATH',dirname(__FILE__));
// 加载公共文件
include ADMIN_PATH.'/admin.php';
// 退出登录
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;
$msg ='';
switch ($method) {
    //登出
    case 'logout':
        user_logout('login.php');
        break;
    //注册账号
    case 'register':
        $email     = isset($_POST['email'])?$_POST['email']:null;
        $username  = isset($_POST['username'])?$_POST['username']:null;
        $password  = isset($_POST['password1'])?$_POST['password1']:null;
        $password2 = isset($_POST['password2'])?$_POST['password2']:null;
        $agreement = isset($_POST['agreement'])?true:false;

        $is_exist = user_get_byname($username)?false:true;
        
        // 验证email
        validate_check(array(
            array('email',VALIDATE_EMPTY,'邮箱不能为空.'),
            array('email',VALIDATE_IS_EMAIL,'你必须提供一个正确的邮箱地址。')
        ));
        $allowed = array('shibangchina.com', 'shibangchina.net', 
            'shibangcrusher.com', 
            'sbjq.com',
            'sbjq.net',
            'sbmchina.com',
            'sbmchina.net',
            'sbmcrusher.com',
            'sbmcrusher.net',
            'zenithmining.com',
            'zenithmining.net'
        );
        $domain = array_pop(explode('@', $email));
        $is_allow = in_array($domain, $allowed)?true:false;

        // 验证email
        validate_check(array(
            array('email',VALIDATE_EMPTY,'请提供一个公司邮箱地址。'),
            array('email',VALIDATE_IS_EMAIL,'邮箱地址格式不正确。'),
            array('email',$is_allow,'请使用公司邮箱注册。')
        ));
        // 验证用户名
        validate_check(array(
            // 用户名不能为空
            array('username',VALIDATE_EMPTY,'用户名不能为空。'),
            // 用户名长度必须是2-30个字符
            array('username',VALIDATE_LENGTH,'用户名长度必须在 %d-%d 字符之间。',2,30),
            // 用户已存在
            array('username',$is_exist,'用户名已经存在。'), 
        ));
        // 验证密码
        validate_check(array(
            array('password1',VALIDATE_EMPTY,'你输入你的密码.'),
            array('password2',VALIDATE_EMPTY,'请再次输入你的密码.'),
            array('password1',VALIDATE_EQUAL,'你两次输入的密码不匹配,请重试.','password2'),
        ));
        // 验证通过
        if (validate_is_ok()) {
            $group = group_get_byname('Guest');
            if(!$group){
                $group = group_add(array(
                    'name'          => 'Guest',
                    'code'          => 'NULL',
                    'colour'        => '5667e1'
                ));
            }
            $groupid = $group['id'];
            $user_info = array(
                'Administrator' => 'Yes',
                'status' => 1, //默认账户是禁用的
                'nickname' => esc_html($username),
                'BanChangePassword' => null,
                'MultiPersonLogin'  => 'No',
                'usergroup'  => $groupid,
                'other_grps' => array(),
                'roles'     => null
            );
            user_add($username,$password,$email,$user_info);
            ajax_success('注册用户成功, 请等待管理员激活.');
        }
        break;
    //重置密码
    case 'resetpass':
        $email  = isset($_POST['reset_email'])?$_POST['reset_email']:null;
        $result = sendResetPassword($email);
        ajax_success($result);
        exit();
        break;
    //发送新密码
    case 'reset':
        $key = isset($_GET['reset'])?$_GET['reset']:null;
        if($key && strlen($key) == 40) {
            $db = get_conn();
            $sql = sprintf("SELECT `userid`, `mail`, `reset_timer` FROM `#@_user` WHERE `reset_key` = '%s' LIMIT 1",$key);
            $rs = $db->query($sql);
            if($row = $db->fetch($rs)) {
                $userid = $row['userid'];
                if((time() - $row['reset_timer']) > 7200){
                    return '<div class="alert alert-warning">
                                    <strong>错误</strong> 重置链接已经失效. 重置链接仅在重置后两小时内有效.
                                </div>';
                    exit;
                }
                $password_readable = substr(str_shuffle('abcdefghijklmnopqrstuvxyz1234567890ABCDEFGHIJKLMNOPQRSTUVXYZ0987654321'),0,12);
                //更改新密码
                if ($userid) {
                    $authcode = authcode($userid);
                    $user_info = array(
                        'pass'          => md5($password_readable.$authcode),
                        'authcode'      => $authcode,
                        'reset_key'     => "",
                        'reset_timer'   => "",
                    );
                    //$db->update('#@_user',$user_info,array('reset_key' => $key));
                    user_edit($row['userid'],$user_info);
                }

                //向邮箱发送新密码
                require_once(COM_PATH.'/system/templates/emails/tmpl_new_password.php');
                $variables = array('website_name' => '信息管理系统',
                                       'site_url' => C('System.home'),
                                       'username' => $row['name'],
                                       'email' => $row['mail'],
                                       'newpassword' => $password_readable,
                                       'visitor_ip' => $_SERVER['REMOTE_ADDR']
                                    );
                    
                $subject = render_email($variables, $email['title']);
                $body = render_email($variables, $email['body']);

                //error_log($body, 3, ABS_PATH.'/app.log');
                            
                send_mail($row['mail'], $subject, $body);
                    
                $msg = '<div class="alert alert-success">
                                <strong>重置成功</strong> 你的新密码已经发送到你的邮箱中,请等会检查你的邮箱.
                            </div>';
                
            }else{
                $msg = '<div class="alert alert-error">
                                <strong>错误</strong> 重置代码无效或者你的账户被禁用.
                            </div>';
            }
        }
        //exit();
        break;
    default:
        // 只验证 POST 方式提交
        if (validate_is_post()) {
            $username   = isset($_POST['username'])?$_POST['username']:null;
            $userpass   = isset($_POST['userpass'])?$_POST['userpass']:null;
            $rememberme = isset($_POST['rememberme'])?$_POST['rememberme']:null;
            // 验证用户名
            validate_check(array(
                // 用户名不能为空
                array('username',VALIDATE_EMPTY,'用户名不能为空.'),
                // 用户名长度必须是2-30个字符
                array('username',VALIDATE_LENGTH,'用户名长度必须在 be %d-%d 字符之间.',2,30)
            ));
            // 验证密码
            validate_check('userpass',VALIDATE_EMPTY,'密码不能为空.');
            // 验证通过
            if (validate_is_ok()) {
                // 提交到数据库验证用户名和密码
                if ($user = user_login($username,$userpass)) {
                    $expire   = $rememberme=='forever' ? 365*86400 : 0;
                    cookie_set('authcode',$user['authcode'],$expire);
                    if($user['usergroup']=='SEO技术人员'){
                        redirect('index.php?method=report');
                    }
                    if( $user['roles']=='ALL' || instr('cpanel', $user['roles']) || instr('cpanel', get_group_permissions(explode(',', $user['permissions']),$user['other_grps'])) )
                        redirect('index.php');
                    else
                        redirect('profile.php');
                } else {
                    $msg = '<div class="alert alert-error"><strong>登录失败</strong> 用户名或密码错误!</div>';
                    //ajax_alert('用户名或密码错误!');
                }
            }
        } else {
            if (user_current(false)) {
                redirect('index.php');
            }
        }
        break;
}

//system_head('scripts',array('js/jquery.validate'));
//system_head('scripts',array('js/login'));

// 登录页面
?>
<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="utf-8">
    <title>登陆 - 客户信息管理系统</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="客户信息管理系统！">
    <meta name="author" content="Ray">
    <meta name="robots" content="noindex, nofollow" />

    <!-- Le styles -->
    <?php loader_css('css/style');loader_css('css/responsive');?>

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="<?php echo ROOT;?>common/assets/ico/favicon.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo ROOT;?>common/assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo ROOT;?>common/assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="<?php echo ROOT;?>common/assets/ico/apple-touch-icon-57-precomposed.png">
    </head>

    <body class="login-layout">
      <div class="main-container container-fluid">
        <div class="main-content">
          <div class="row-fluid">
            <div class="span12">
              <div class="login-container">
                <div class="row-fluid">
                  <div class="center">
                    <h1>
                      <i class="icon-leaf green"></i>
                      <span class="red">SBM</span>
                      <span class="white">信息管理系统</span>
                    </h1>
                    <h4 class="blue">© 上海世邦机器有限公司</h4>
                  </div>
                </div>
                <div class="space-6"></div>
                <div class="row-fluid">
                  <div class="position-relative">
                    <div id="login-box" class="login-box widget-box no-border visible">
                        <div class="widget-body">
                            <div class="widget-main">
                                <?php
                                echo $msg;
                                ?>

                                <noscript>
                                    <div class="alert alert-error">
                                        您的浏览器不支持或已经禁止网页脚本，您无法正常登录。
                                        <a href="http://service.mail.qq.com/cgi-bin/help?subtype=1&&no=341&&id=7" title="了解网页脚本限制的更多信息" target="_blank">
                                            <i class="icon-question-sign"></i> 如何解除脚本限制
                                        </a>
                                    </div>
                                </noscript>
                                <div class="alert" id="alertNoCookie" style="display:none">
                                    你的浏览器不支持或已经禁止使用Cookie，导致无法正常登录。请
                                    <a href="http://service.mail.qq.com/cgi-bin/help?subtype=1&&id=7&&no=1001007#chrome" title="了解Cookie的更多信息" target="_blank">
                                        启用Cookie
                                        <i class="icon-question-sign"></i>
                                    </a>
                                    后重试。
                                </div>

                                <h4 class="header blue lighter bigger">
                                    <i class="icon-coffee green"></i>
                                    请填写以下信息以完成登录
                                </h4>

                                <div class="space-6"></div>

                                <form method="POST" _lpchecked="1" id="loginform">
                                    <fieldset>
                                        <label>
                                            <span class="block input-icon input-icon-right">
                                                <input type="text" name="username" class="span12" placeholder="用户名">
                                                <i class="icon-user"></i>
                                            </span>
                                        </label>

                                        <label>
                                            <span class="block input-icon input-icon-right">
                                                <input type="password" name="userpass" class="span12" placeholder="密码">
                                                <i class="icon-lock"></i>
                                            </span>
                                        </label>

                                        <div class="space"></div>

                                        <div class="clearfix">
                                            <label class="inline">
                                                <input type="checkbox" name="rememberme">
                                                <span class="lbl"> 记住我</span>
                                            </label>

                                            <button type="submit" class="width-35 pull-right btn btn-small btn-primary">
                                                <i class="icon-key"></i>
                                                登录
                                            </button>
                                        </div>

                                        <div class="space-4"></div>
                                    </fieldset>
                                </form>

                                <div class="alert-remember well">
                                    选择此项后，请确保浏览器的 cookies 是启用的：<br>
                                    * 默认记住时间为365天<br>
                                    * 请不要在公共电脑上选择此项<br>
                                </div>

                                
                            </div><!--/widget-main-->

                            <div class="toolbar clearfix">
                                <div>
                                    <a href="#" onclick="show_box('forgot-box'); return false;" class="forgot-password-link">
                                        <i class="icon-arrow-left"></i>
                                        忘记密码
                                    </a>
                                </div>

                                <div>
                                    <a href="#" onclick="show_box('signup-box'); return false;" class="user-signup-link">
                                        注册用户
                                        <i class="icon-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div><!--/widget-body-->
                    </div><!--/login-box-->

                    <div id="forgot-box" class="forgot-box widget-box no-border">
                        <div class="widget-body">
                            <div class="widget-main">
                                
                                <span class="block alertmsg"></span>

                                <h4 class="header red lighter bigger">
                                    <i class="icon-key"></i>
                                    找回密码
                                </h4>

                                <div class="space-6"></div>
                                <p>
                                    输入你的电子邮箱以接收重置步骤
                                </p>

                                <form method="POST" action="login.php?method=resetpass">
                                    <fieldset>
                                        <label>
                                            <span class="block input-icon input-icon-right">
                                                <input type="email" name="reset_email" class="span12" placeholder="输入你的电子邮箱">
                                                <i class="icon-envelope"></i>
                                            </span>
                                        </label>

                                        <div class="clearfix">
                                            <button class="width-35 pull-right btn btn-small btn-danger">
                                                <i class="icon-lightbulb"></i>
                                                发送给我!
                                            </button>
                                        </div>
                                    </fieldset>
                                </form>
                            </div><!--/widget-main-->

                            <div class="toolbar center">
                                <a href="#" onclick="show_box('login-box'); return false;" class="back-to-login-link">
                                    返回登录界面
                                    <i class="icon-arrow-right"></i>
                                </a>
                            </div>
                        </div><!--/widget-body-->
                    </div><!--/forgot-box-->

                    <div id="signup-box" class="signup-box widget-box no-border">
                        <div class="widget-body">
                            <div class="widget-main">

                                <span class="block alertmsg"></span>

                                <h4 class="header green lighter bigger">
                                    <i class="icon-group blue"></i>
                                    新用户注册
                                </h4>

                                <div class="space-6"></div>
                                <p> 输入你的详细资料以开始: </p>

                                <form _lpchecked="1" action="login.php?method=register" method="POST">
                                    <fieldset>
                                        <label>
                                            <span class="block input-icon input-icon-right">
                                                <input type="email" name="email" class="span12" placeholder="邮箱">
                                                <i class="icon-envelope"></i>
                                            </span>
                                        </label>

                                        <label>
                                            <span class="block input-icon input-icon-right">
                                                <input type="text" name="username" class="span12" placeholder="用户名">
                                                <i class="icon-user"></i>
                                            </span>
                                        </label>

                                        <label>
                                            <span class="block input-icon input-icon-right">
                                                <input type="password" name="password1" class="span12" placeholder="密码">
                                                <i class="icon-lock"></i>
                                            </span>
                                        </label>

                                        <label>
                                            <span class="block input-icon input-icon-right">
                                                <input type="password" name="password2" class="span12" placeholder="再次输入密码">
                                                <i class="icon-retweet"></i>
                                            </span>
                                        </label>

                                        <label>
                                            <input type="checkbox" name="agreement">
                                            <span class="lbl">
                                                我接受
                                                <a href="#">用户协议</a>
                                            </span>
                                        </label>

                                        <div class="space-24"></div>

                                        <div class="clearfix">
                                            <button type="reset" class="width-30 pull-left btn btn-small">
                                                <i class="icon-refresh"></i>
                                                重置
                                            </button>

                                            <button class="width-65 pull-right btn btn-small btn-success">
                                                注册
                                                <i class="icon-arrow-right icon-on-right"></i>
                                            </button>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>

                            <div class="toolbar center">
                                <a href="#" onclick="show_box('login-box'); return false;" class="back-to-login-link">
                                    <i class="icon-arrow-left"></i>
                                    返回登录界面
                                </a>
                            </div>
                        </div><!--/widget-body-->
                    </div><!--/signup-box-->
                </div>
                </div>
              </div>
            </div><!--/.span-->
          </div><!--/.row-fluid-->
        </div>
      </div><!--/.main-container-->
      <?php 
        echo '<script>var baseurl = "'.ROOT.'admin/", AccCheckIntval = 30000,logged = false;</script>';
        loader_js('js/jquery'); loader_js('js/login'); loader_js(system_head('scripts'));
      ?>
    <!--与本页相关的内联脚本-->
    <script type="text/javascript">
        function show_box(id) {
         $('.widget-box.visible').removeClass('visible');
         $('#'+id).addClass('visible');
        }
    </script>

    </body>
</html>