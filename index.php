<?php
if ( !file_exists( dirname(__FILE__) . '/config.php') ) {
  $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
  $html.= '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
  $html.= '<meta http-equiv="refresh" content="0;url=admin/" />';
  $html.= '<title>询盘信息管理系统</title>';
  $html.= '<script type="text/javascript">location.replace("admin/");</script>';
  $html.= '</head><body>';
  $html.= '</body></html>';
  exit($html);
}
// 加载公共文件
include dirname(__FILE__).'/admin/admin.php';
//是否404页面
if(isset($_SERVER['REQUEST_URI'])){
	$request_uri = preg_replace('/(.*)\?(.*)/i','$1',$_SERVER['REQUEST_URI']);
	$file = $_SERVER['DOCUMENT_ROOT'].$request_uri;
	if ( !is_file( $file ) && !is_dir( $file )) {
		status_header( 404 );
		nocache_headers();
		echo error_page('404 页面未找到','找不到你所请求的页面 '.HTTP_HOST.$_SERVER['REQUEST_URI'],true);
		exit();
	}
/*	$file_headers = get_headers(HTTP_HOST.$_SERVER['REQUEST_URI']);
	if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
    	$is_404 = true;
	}*/
}

function remove_querystring_var($url, $key) { 
	$url = preg_replace('/(.*)(?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&'); 
	$url = substr($url, 0, -1); 
	return $url; 
}

// 退出登录
$method = isset($_GET['method'])?$_GET['method']:null;
if ($method=='logout') {
    cookie_delete('authcode');
    redirect('index.php');
} else {
	//$language = language();
}
$is_login = false;

// 只验证 POST 方式提交
if (validate_is_post()) {
    $username   = isset($_POST['username'])?$_POST['username']:null;
    $userpass   = isset($_POST['userpass'])?$_POST['userpass']:null;
    $rememberme = isset($_POST['rememberme'])?$_POST['rememberme']:null;
    // 验证用户名
    validate_check(array(
        // 用户名不能为空
        array('username',VALIDATE_EMPTY,'用户名不能为空。'),
        // 用户名长度必须是2-30个字符
        array('username',VALIDATE_LENGTH,'用户名长度必须在 %d-%d 之间。',2,30)
    ));
    // 验证密码
    validate_check('userpass',VALIDATE_EMPTY,'密码不能为空。');
    // 验证通过
    if (validate_is_ok()) {
        // 提交到数据库验证用户名和密码
        if ($user = user_login($username,$userpass)) {
            $expire   = $rememberme=='forever' ? 365*86400 : 0;
            cookie_set('authcode',$user['authcode'],$expire);
			if($user['usergroup']=='SEO技术人员'){
				redirect('admin/index.php?method=report');
			}
			if( $user['roles']=='ALL' || instr('cpanel', $user['roles']) || instr('cpanel', get_group_permissions(explode(',', $user['permissions']),$user['other_grps'])) ){
            	redirect('admin/index.php');
			}
			else{
				redirect('admin/profile.php');
			}
        } else {
            ajax_alert('您输入的用户名或密码不正确。');
        }
    }
} else {
    if ($_USER=user_current(false)) {
		$is_login = true;
			//redirect('admin/index.php');
		}
}
// 登录页面
?>
<!DOCTYPE html>
<html lang="en"><head>
    <meta charset="utf-8">
    <title>询盘管理</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Ray.">
    <meta name="robots" content="noindex, nofollow" />
    <meta name="robots" content="noindex, nofollow" />

    <!-- Le styles -->
    <link href="common/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <style type="text/css">
	html, body, div, h1, h2, h3, h4, h5, h6, p, img, dl,
  dt, dd, ol, ul, li, table, tr, td, form, object, embed,
  article, aside, canvas, command, details, fieldset,
  figcaption, figure, footer, group, header, hgroup, legend,
  mark, menu, meter, nav, output, progress, section, summary,
  time, audio, video {
  margin: 0;
  padding: 0;
  border: 0;
  }
  html {
  font: 81.25% arial, helvetica, sans-serif;
  background: #fff;
  color: #333;
  line-height: 1;
  direction: ltr;
  }
  a {
  color: #15c;
  text-decoration: none;
  }
  a:active {
  color: #d14836;
  }
  a:hover {
  text-decoration: underline;
  }
  h1, h2, h3, h4, h5, h6 {
  color: #222;
  font-size: 1.54em;
  font-weight: normal;
  line-height: 24px;
  margin: 0 0 .46em;
  }
  p {
  line-height: 17px;
  margin: 0 0 1em;
  }
  ol, ul {
  list-style: none;
  line-height: 17px;
  margin: 0 0 1em;
  }
  li {
  margin: 0 0 .5em;
  }
  table {
  border-collapse: collapse;
  border-spacing: 0;
  }
  strong {
  color: #222;
  }
  
  html, body {
  position: absolute;
  height: 100%;
  min-width: 100%;
  }
  ::-webkit-scrollbar-track-piece{background-color:#f5f5f5;border-left:1px solid #d2d2d2}
  ::-webkit-scrollbar{width:13px;height:13px}
  ::-webkit-scrollbar-thumb{background-color:#c2c2c2;background-clip:padding-box;border:1px solid #979797;min-height:28px}
  ::-webkit-scrollbar-thumb:hover{border:1px solid #636363;background-color:#929292}
  .wrapper {
  position: relative;
  min-height: 100%;
  }
  .content {
  padding: 0 44px;
  }
  .google-header-bar {
  height: 71px;
  background: #f1f1f1;
  border-bottom: 1px solid #e5e5e5;
  overflow: hidden;
  }
  .header .logo {
  margin: 17px 0 0;
  float: left;
  }
  .header .signin,
  .header .signup {
  margin: 28px 0 0;
  float: right;
  font-weight: bold;
  }
  .header .signin-button,
  .header .signup-button {
  margin: 22px 0 0;
  float: right;
  }
  .header .signin-button a {
  font-size: 13px;
  font-weight: normal;
  }
  .header .signup-button a {
  position: relative;
  top: -1px;
  margin: 0 0 0 1em;
  }
	</style>
<style type="text/css">
  button, input, select, textarea {
  font-family: inherit;
  font-size: inherit;
  }
  button::-moz-focus-inner,
  input::-moz-focus-inner {
  border: 0;
  }
  input[type=email],
  input[type=number],
  input[type=password],
  input[type=tel],
  input[type=text],
  input[type=url] {
  -webkit-appearance: none;
  appearance: none;
  display: inline-block;
  height: 29px;
  margin: 0;
  padding: 0 8px;
  background: #fff;
  border: 1px solid #d9d9d9;
  border-top: 1px solid #c0c0c0;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-border-radius: 1px;
  -moz-border-radius: 1px;
  border-radius: 1px;
  }
  input[type=email]:hover,
  input[type=number]:hover,
  input[type=password]:hover,
  input[type=tel]:hover,
  input[type=text]:hover,
  input[type=url]:hover {
  border: 1px solid #b9b9b9;
  border-top: 1px solid #a0a0a0;
  -webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
  -moz-box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
  box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
  }
  input[type=email]:focus,
  input[type=number]:focus,
  input[type=password]:focus,
  input[type=tel]:focus,
  input[type=text]:focus,
  input[type=url]:focus {
  outline: none;
  border: 1px solid #4d90fe;
  -webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,0.3);
  -moz-box-shadow: inset 0 1px 2px rgba(0,0,0,0.3);
  box-shadow: inset 0 1px 2px rgba(0,0,0,0.3);
  }


  input[type=checkbox].form-error,
  input[type=email].form-error,
  input[type=number].form-error,
  input[type=password].form-error,
  input[type=text].form-error,
  input[type=tel].form-error,
  input[type=url].form-error {
  border: 1px solid #dd4b39;
  }
  input[type=checkbox]{
  -webkit-appearance: none;
  appearance: none;
  width: 13px;
  height: 13px;
  margin: 0;
  cursor: pointer;
  vertical-align: bottom;
  background: #fff;
  border: 1px solid #dcdcdc;
  -webkit-border-radius: 1px;
  -moz-border-radius: 1px;
  border-radius: 1px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  position: relative;
  }
  input[type=checkbox]:active{
  border-color: #c6c6c6;
  background: #ebebeb;
  }
  input[type=checkbox]:hover {
  border-color: #c6c6c6;
  -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,0.1);
  -moz-box-shadow: inset 0 1px 1px rgba(0,0,0,0.1);
  box-shadow: inset 0 1px 1px rgba(0,0,0,0.1);
  }
  input[type=checkbox]:checked{
  background: #fff;
  }
  input[type=checkbox]:checked::after {
  content: url(//ssl.gstatic.com/ui/v1/menu/checkmark.png);
  display: block;
  position: absolute;
  top: -6px;
  left: -5px;
  }
  input[type=checkbox]:focus {
  outline: none;
  border-color:#4d90fe;
  }
  .g-button {
  display: inline-block;
  min-width: 46px;
  text-align: center;
  color: #444;
  font-size: 11px;
  font-weight: bold;
  height: 27px;
  padding: 0 8px;
  line-height: 27px;
  -webkit-border-radius: 2px;
  -moz-border-radius: 2px;
  border-radius: 2px;
  -webkit-transition: all 0.218s;
  -moz-transition: all 0.218s;
  -ms-transition: all 0.218s;
  -o-transition: all 0.218s;
  transition: all 0.218s;
  border: 1px solid #dcdcdc;
  background-color: #f5f5f5;
  background-image: -webkit-gradient(linear,left top,left bottom,from(#f5f5f5),to(#f1f1f1));
  background-image: -webkit-linear-gradient(top,#f5f5f5,#f1f1f1);
  background-image: -moz-linear-gradient(top,#f5f5f5,#f1f1f1);
  background-image: -ms-linear-gradient(top,#f5f5f5,#f1f1f1);
  background-image: -o-linear-gradient(top,#f5f5f5,#f1f1f1);
  background-image: linear-gradient(top,#f5f5f5,#f1f1f1);
  -webkit-user-select: none;
  -moz-user-select: none;
  user-select: none;
  cursor: default;
  }
  *+html .g-button {
  min-width: 70px;
  }
  button.g-button,
  input[type=submit].g-button {
  height: 29px;
  line-height: 29px;
  vertical-align: bottom;
  margin: 0;
  }
  *+html button.g-button,
  *+html input[type=submit].g-button {
  overflow: visible;
  }
  .g-button:hover {
  border: 1px solid #c6c6c6;
  color: #333;
  text-decoration: none;
  -webkit-transition: all 0.0s;
  -moz-transition: all 0.0s;
  -ms-transition: all 0.0s;
  -o-transition: all 0.0s;
  transition: all 0.0s;
  background-color: #f8f8f8;
  background-image: -webkit-gradient(linear,left top,left bottom,from(#f8f8f8),to(#f1f1f1));
  background-image: -webkit-linear-gradient(top,#f8f8f8,#f1f1f1);
  background-image: -moz-linear-gradient(top,#f8f8f8,#f1f1f1);
  background-image: -ms-linear-gradient(top,#f8f8f8,#f1f1f1);
  background-image: -o-linear-gradient(top,#f8f8f8,#f1f1f1);
  background-image: linear-gradient(top,#f8f8f8,#f1f1f1);
  -webkit-box-shadow: 0 1px 1px rgba(0,0,0,0.1);
  -moz-box-shadow: 0 1px 1px rgba(0,0,0,0.1);
  box-shadow: 0 1px 1px rgba(0,0,0,0.1);
  }
  .g-button:active {
  background-color: #f6f6f6;
  background-image: -webkit-gradient(linear,left top,left bottom,from(#f6f6f6),to(#f1f1f1));
  background-image: -webkit-linear-gradient(top,#f6f6f6,#f1f1f1);
  background-image: -moz-linear-gradient(top,#f6f6f6,#f1f1f1);
  background-image: -ms-linear-gradient(top,#f6f6f6,#f1f1f1);
  background-image: -o-linear-gradient(top,#f6f6f6,#f1f1f1);
  background-image: linear-gradient(top,#f6f6f6,#f1f1f1);
  -webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
  -moz-box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
  box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
  }
  .g-button:visited {
  color: #666;
  }
  .g-button-submit {
  border: 1px solid #3079ed;
  color: #fff;
  text-shadow: 0 1px rgba(0,0,0,0.1);
  background-color: #4d90fe;
  background-image: -webkit-gradient(linear,left top,left bottom,from(#4d90fe),to(#4787ed));
  background-image: -webkit-linear-gradient(top,#4d90fe,#4787ed);
  background-image: -moz-linear-gradient(top,#4d90fe,#4787ed);
  background-image: -ms-linear-gradient(top,#4d90fe,#4787ed);
  background-image: -o-linear-gradient(top,#4d90fe,#4787ed);
  background-image: linear-gradient(top,#4d90fe,#4787ed);
  }
  .g-button-submit:hover {
  border: 1px solid #2f5bb7;
  color: #fff;
  text-shadow: 0 1px rgba(0,0,0,0.3);
  background-color: #357ae8;
  background-image: -webkit-gradient(linear,left top,left bottom,from(#4d90fe),to(#357ae8));
  background-image: -webkit-linear-gradient(top,#4d90fe,#357ae8);
  background-image: -moz-linear-gradient(top,#4d90fe,#357ae8);
  background-image: -ms-linear-gradient(top,#4d90fe,#357ae8);
  background-image: -o-linear-gradient(top,#4d90fe,#357ae8);
  background-image: linear-gradient(top,#4d90fe,#357ae8);
  }
  .g-button-submit:active {
  background-color: #357ae8;
  background-image: -webkit-gradient(linear,left top,left bottom,from(#4d90fe),to(#357ae8));
  background-image: -webkit-linear-gradient(top,#4d90fe,#357ae8);
  background-image: -moz-linear-gradient(top,#4d90fe,#357ae8);
  background-image: -ms-linear-gradient(top,#4d90fe,#357ae8);
  background-image: -o-linear-gradient(top,#4d90fe,#357ae8);
  background-image: linear-gradient(top,#4d90fe,#357ae8);
  -webkit-box-shadow: inset 0 1px 2px rgb	a(0,0,0,0.3);
  -moz-box-shadow: inset 0 1px 2px rgba(0,0,0,0.3);
  box-shadow: inset 0 1px 2px rgba(0,0,0,0.3);
  }
  .g-button-red {
  border: 1px solid transparent;
  color: #fff;
  text-shadow: 0 1px rgba(0,0,0,0.1);
  text-transform: uppercase;
  background-color: #d14836;
  background-image: -webkit-gradient(linear,left top,left bottom,from(#dd4b39),to(#d14836));
  background-image: -webkit-linear-gradient(top,#dd4b39,#d14836);
  background-image: -moz-linear-gradient(top,#dd4b39,#d14836);
  background-image: -ms-linear-gradient(top,#dd4b39,#d14836);
  background-image: -o-linear-gradient(top,#dd4b39,#d14836);
  background-image: linear-gradient(top,#dd4b39,#d14836);
  }
  .g-button-red:hover {
  border: 1px solid #b0281a;
  color: #fff;
  text-shadow: 0 1px rgba(0,0,0,0.3);
  background-color: #c53727;
  background-image: -webkit-gradient(linear,left top,left bottom,from(#dd4b39),to(#c53727));
  background-image: -webkit-linear-gradient(top,#dd4b39,#c53727);
  background-image: -moz-linear-gradient(top,#dd4b39,#c53727);
  background-image: -ms-linear-gradient(top,#dd4b39,#c53727);
  background-image: -o-linear-gradient(top,#dd4b39,#c53727);
  background-image: linear-gradient(top,#dd4b39,#c53727);
  -webkit-box-shadow: 0 1px 1px rgba(0,0,0,0.2);
  -moz-box-shadow: 0 1px 1px rgba(0,0,0,0.2);
  -ms-box-shadow: 0 1px 1px rgba(0,0,0,0.2);
  -o-box-shadow: 0 1px 1px rgba(0,0,0,0.2);
  box-shadow: 0 1px 1px rgba(0,0,0,0.2);
  }
  .g-button-red:active {
  border: 1px solid #992a1b;
  background-color: #b0281a;
  background-image: -webkit-gradient(linear,left top,left bottom,from(#dd4b39),to(#b0281a));
  background-image: -webkit-linear-gradient(top,#dd4b39,#b0281a);
  background-image: -moz-linear-gradient(top,#dd4b39,#b0281a);
  background-image: -ms-linear-gradient(top,#dd4b39,#b0281a);
  background-image: -o-linear-gradient(top,#dd4b39,#b0281a);
  background-image: linear-gradient(top,#dd4b39,#b0281a);
  -webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,0.3);
  -moz-box-shadow: inset 0 1px 2px rgba(0,0,0,0.3);
  box-shadow: inset 0 1px 2px rgba(0,0,0,0.3);
  color: #fff
  }
	.g-button-red:visited, .g-button-share:visited, .g-button-submit:visited {
color: #fff;
}
    </style>
    <style type="text/css">
	.main {
margin: 0 auto;
width: 650px;
padding-top: 23px;
padding-bottom: 100px;
}
	 .main {
  width: auto;
  max-width: 1000px;
  min-width: 780px;
  }
	.product-info {
  margin: 0 385px 0 0;
  }
  .product-info h3 {
  font-size: 1.23em;
  font-weight: normal;
  }
	.product-info a:visited {
  color: #61c;
  }
  .product-info .g-button:visited {
  color: #666;
  }
	.product-headers {
  margin: 0 0 1.5em;
  }
  .product-headers h1 {
  font-size: 25px;
  margin: 0 !important;
  }
  .product-headers h2 {
  font-size: 16px;
  margin: .4em 0 0;
  }
  .features {
  overflow: hidden;
  margin: 2em 0 0;
  }
  .features li {
  margin: 3px 0 2em;
  }
  .features img {
  float: left;
  margin: -3px 0 0;
  }
  .features p {
  margin: 0 0 0 68px;
  }
  .features .title {
  font-size: 16px;
  margin-bottom: .3em;
  }
  .features.no-icon p {
  margin: 0;
  }
  .features .small-title {
  font-size: 1em;
  font-weight: bold;
  }
	ul.mail-links li {
  display: inline-block;
  margin-right: 20px;
  *display: inline; /*ie7*/
  }
	.sign-in {
  width: 335px;
  float: right;
  }
  .signin-box {
  margin: 12px 0 0;
  padding: 20px 25px 15px;
  background: #f1f1f1;
  border: 1px solid #e5e5e5;
  }
  .signin-box h2 {
  font-size: 16px;
  line-height: 17px;
  height: 16px;
  margin: 0 0 1.2em;
  position: relative;
  }
  .signin-box h2 strong {
  display: inline-block;
  position: absolute;
  right: 0;
  top: 1px;
  height: 19px;
  width: 52px;
  background: transparent url(common/img/sbm-signin-flat.png) no-repeat;
  }
  @media only screen and (-webkit-device-pixel-ratio: 2){
  .signin-box h2 strong {
  background: transparent url(common/img/sbm-signin-flat_2x.png) no-repeat;
  background-size: 52px 19px;
  }
  }
  .signin-box div {
  margin: 0 0 1.5em;
  }
  .signin-box label {
  display: block;
  }
  .signin-box input[type=email],
  .signin-box input[type=text],
  .signin-box input[type=password] {
  width: 100%;
  height: 32px;
  font-size: 15px;
  direction: ltr;
  }
  .signin-box .user-label,
  .signin-box .passwd-label {
  font-weight: bold;
  margin: 0 0 .5em;
  display: block;
  -webkit-user-select: none;
  -moz-user-select: none;
  user-select: none;
  }
  .signin-box .reauth {
  display: inline-block;
  font-size: 15px;
  height: 29px;
  line-height: 29px;
  margin: 0;
  }
  .signin-box label.remember {
  display: inline-block;
  vertical-align: top;
  margin: 9px 0 0;
  }
  .signin-box .remember-label {
  font-weight: normal;
  color: #666;
  line-height: 0;
  padding: 0 0 0 .4em;
  -webkit-user-select: none;
  -moz-user-select: none;
  user-select: none;
  }
  .signin-box input[type=submit] {
  margin: 0 1.5em 1.2em 0;
  height: 32px;
  font-size: 13px;
  }
  .signin-box ul {
  margin: 0;
  }
  .signin-box .training-msg {
  padding: .5em 8px;
  background: #f9edbe;
  }
  .signin-box .training-msg p {
  margin: 0 0 .5em;
  }
</style>
<style type="text/css">
  .mail .mail-promo {
  border: 1px solid #ebebeb;
  margin: 30px 0 0;
  padding: 20px;
  overflow: hidden;
  }
  .mail-promo-64 h4 {
  padding-top: 12px;
  }
  .mail .mail-promo h3,
  .mail .mail-promo p {
  margin-left: 60px;
  }
  .mail .mail-promo img {
  width: 42px;
  margin: 3px 0 0;
  float: left;
  }
  .mail .mail-promo h3 {
  font-size: 16px;
  margin-bottom: .3em;
  }
  .mail .mail-promo p {
  margin-bottom: 0;
  }
  .mail .mail-promo p:last-of-type {
  margin-bottom: 0;
  }
  .mail .mail-promo a {
  white-space: nowrap;
  }
  .mail .mail-promo-64 {
  padding: 5px 18px 5px 10px;
  }
  .mail .mail-promo-64 img {
  width: 64px;
  height: 64px;
  }
  .mail .mail-promo-64 h3,
  .mail .mail-promo-64 p {
  margin-left: 76px;
  }
  .mail .mail-promo-64 h3 {
  padding-top: .6em;
  }
  .mail h3.mail-hero-heading {
  font-family: 'open sans', arial, sans-serif;
  font-size: 24px;
  font-weight: 300;
  }
  .mail h4.mail-hero-heading {
  color: #565656;
  font-size: 15px;
  font-weight: normal;
  line-height: 22px;
  margin-top: 15px;
  width: 270px;
  }
  .mail h5.mail-about-heading {
  color: #565656;
  font-size: 15px;
  font-weight: bold;
  }
  .mail ul.mail-links {
  margin: 0;
  overflow: hidden;
  }
  .mail ul.mail-links li {
  display: inline-block;
  margin-right: 20px;
  *display: inline; /*ie7*/
  }
  .mail .mail-hero {
  background-image:url("//ssl.gstatic.com/accounts/services/mail/gradient.png");
  background-repeat: no-repeat;
  background-position: 0 137px;
  height: 317px;
  margin-top: -20px;
  width: 100%;
  }
  .mail .mail-hero-left {
  display: block;
  float: left;
  width: 55%;
  }
  .mail .mail-hero-right {
  float: left;
  width: 45%;
  }
  .mail .mail-about-section {
  padding-top: 60px;
  width: 100%;
  }
  .mail .mail-about-col-left {
  display: block;
  float: left;
  width: 55%;
  }
  .mail .mail-about-col-right {
  display: block;
  float: left;
  width: 45%;
  }
  .mail .mail-about-col-space {
  display: block;
  float: left;
  width: 40px;
  }
  .mail .mail-buttons {
  vertical-align: top;
  margin-top: 90px;
  width: 300px;
  }
  .mail .mail-button-google-play {
  margin-bottom: 1px;
  }
  .mail .mail-button-unit {
  display: inline-block;
  padding-right: 10px;
  float: left;
  }
  .mail .mail-hero-img {
  padding-left: 75px;
  }
  .mail p.mail-about-text,
  .mail p.mail-account-text {
  color: #575757;
  line-height: 20px;
  }
  .mail p.mail-about-text {
  width: 80%;
  }
</style>
<style type="text/css">
.errormsg {
margin: .5em 0 0;
display: block;
color: #dd4b39;
line-height: 17px;
}
.text-info{color: #3a87ad;}
.footer {
border-top: 1px solid #f5f5f5;
background: #f5f5f5;
}
#footer-local {
background: #f9f9f9;
border-top: 1px solid #f5f5f5;
}
.footer-links {
margin: 10px 0;
}
.footer ul {
margin: 0;
}
.footer-links li:first-child {
padding-left: 0;
}
.footer li {
display: inline;
padding: 0 2px;
}
#footer-global {
border-top: 1px solid #eee;
font-size: 11px;
line-height: 2.19;
list-style: none;
}
#footer-global, #footer-local {
padding: 10px 15px;
}
.maia-aux {
margin: auto;
max-width: 978px;
}
.suez-sitemap {
padding-top: 10px;
}
.suez-sitemap h3 {
font-weight: bold;
font-size: 1em;
margin-top: 0;
}
.suez-sitemap ul {
list-style: none;
margin-left: 0;
margin-right: 0 !important;
overflow: hidden;
}
.suez-sitemap-compact li {
float: left;
margin-right: 1em;
padding-right: 1em;
position: relative;
}
.suez-sitemap ul a {
color: #15c;
display: block;
}
</style>
<style type="text/css">
@media (min-width: 1200px) {
}
@media (min-width: 768px) and (max-width: 979px) {
  .main{min-width: 680px;}
}
@media (max-width: 767px) {
  .main{min-width: 0;}
}
@media (max-width: 767px) {
}
@media (max-width: 480px) {
	.product-info{ margin:0;}
	.signin-box,.product-info{ margin-top:20px;}
	.sign-in{ width:100%; float:none;}
	.content {padding: 0 22px;}
	.product-info {padding-bottom: 20px;}
}
</style>
    

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="common/assets/ico/favicon.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="common/assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="common/assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="common/assets/ico/apple-touch-icon-57-precomposed.png">
    </head>

    <body>
    
    <div class="wrapper">
      <div class="google-header-bar">
        <div class="header content clearfix">
          <img class="logo" src="common/img/sbm_logo_41.png" alt="Google">
          <span class="signup-button"> <a id="link-signup" class="g-button g-button-red" href="pages/">查询页面</a></span>
        </div>
      </div>
      <div class="main content clearfix">
        <div class="sign-in">
          <div class="signin-box">
            <h2>登录 <strong></strong></h2>
              <form novalidate="" id="loginform" action="index.php" method="post">
                <div class="user-div">
                  <label for="username"><strong class="user-label">用户名</strong></label>
                  <?php
				  if($is_login) {
					  echo '<input type="hidden" name="username" id="username" value="'.$_USER['name'].'">';
					  echo '<span id="reauthName" class="reauth">'.$_USER['name'].'</span>';
				  } else {
					  echo '<input type="text" spellcheck="false" name="username" id="username">';
				  }
				  ?>
                </div>
                <div class="passwd-div">
                  <label for="Passwd"><strong class="passwd-label">密码</strong></label>
                  <input type="password" name="userpass" id="userpass">
                </div>
                <input type="submit" class="g-button g-button-submit" name="signIn" id="signIn" value="登录">
                <label class="remember" onclick="">
                <input type="checkbox" name="rememberme" id="rememberme" value="forever">
                <strong class="remember-label">保持登录状态</strong>
                </label>
              </form>
              <ul>
                <li><a id="link-forgot-passwd" href="#" target="_top">无法访问您的帐户？</a></li>
                <?php
				if($is_login) echo '<li><a id="link-force-reauth" href="'.PHP_FILE.'?method=logout">退出，然后以另一用户身份登录</a></li>';
				$is_login?'':'';
				?>
              </ul>
            </div>
          </div>
        <div class="product-info mail">
          <div class="product-headers">
            <h1 class="redtext">询盘信息管理系统</h1>
            <h2>SBM 提供的信息管理服务。</h2>
          </div>
          <p>系统目前不提供账号注册功能. <span class="text-info">
          <?php //echo get_ip_address(); echo "  ".get_ip_place();?>
          </span></p>
          <ul class="features">
            <li>
              <img src="common/img/bootstrap42.png" alt="">
              <p class="title">基于Bootstrap</p>
              <p>简洁、直观、强悍的前端开发框架， 适应更多设备的浏览。</p>
            </li>
            <li>
              <img src="common/img/html42.png" alt="">
              <p class="title">HTM5 + CSS3 + AJAX</p>
              <p>更直观、友好的交互体验， 让你事半功倍。</p>
            </li>
            <li>
              <img src="common/img/data42.png" alt="">
              <p class="title">数据可视化</p>
              <p>询盘信息增长与各地区、国家分布情况可以直观化感受。</p>
            </li>
          </ul>
          <ul class="mail-links">
            <li><a href="#1">关于 InfoMaster</a></li>
            <li><a href="#2">新增功能！</a></li>
            <li><a href="admin/login.php">创建帐户</a></li>
            <li><a href="http://sysach.com/circle-game/" target="_blank">玩下游戏</a></li>
          </ul>
          <div class="mail-promo">
            <img src="common/img/chrome42.png" alt="">
            <h3>让 InfoMaster 与 Google Chrome 珠联璧合</h3>
            <p>如果使用本系统，请使用Google Chrome或Firefox浏览器， 从而得到更佳操作体验。</p>
            <p>在IE浏览器下浏览此系统，可能会影响你的心情。 <a href="https://www.google.com/intl/zh-CN/chrome/browser/">了解更多信息</a></p>
          </div>
        </div>
      </div>
	  <div id="footer" class="footer">
	    <div id="footer-local">
		  <div class="maia-aux">
			  <div class="suez-sitemap suez-sitemap-compact">
				<h3>相关资源</h3>
				<ul>
				  <li>
					<a href="/pages/all-domain.php">查看所有域名</a>
				  </li>
				  <li>
					<a href="http://coffeescript.org/">CoffeeScript</a>
				  </li>
				  <li>
					<a href="http://www.lesscss.net/">LESS</a>
				  </li>
				  <li>
					<a href="http://www.acfun.tv/v/ac500043">BBC：地平线</a>
				  </li>
				  <li>
					<a href="https://docs.google.com/forms/d/1rhRenrd16MDSgAOwnMVx9KQbp--0JoY9vKiJdIcMe44/viewform">Penguin Spam Report</a>
				  </li>
				  <li><a href="http://sharismlab.com/pipeline/home/">SBF2G</a></li>
				</ul>
			  </div>
			  <div id="suez-footer-univ" class="suez-sitemap suez-sitemap-compact">
			    <h3>友情链接</h3>
			    <ul>
				  <li><a href="//bootcss.com">Bootstrap中文网</a></li>
				  <li><a href="http://bootsnipp.com/">Bootsnipp</a></li>
			      <li><a href="http://gitlab.org/">Gitlab</a></li>
				  <li><a href="http://github.com/">Github</a></li>
				  <li><a href="http://angularjs.org/">AngularJS</a></li>
				  <li><a href="http://www.codecademy.com/">Codecademy</a></li>
				  <li><a href="http://www.laruence.com/">风雪之隅</a></li>
				  <li><a href="http://www.douban.com/group/topic/26744328/">兵马俑BBS</a></li>
				  <li><a href="http://www.zhihu.com/question/19926207/answer/15236333">知乎</a></li>
				  <li><a href="http://bbs.csdn.net/topics/280083937">职业规划</a></li> 
				</ul>
			  </div>
			</div>
		</div>
		
		<div id="footer-global">
		  <div class="maia-aux">© 2013 SBM, Inc. All rights reserved.
		    Powered by Ray.
		  </div>
	    </div>
	  </div>
    </div>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
<script type="text/javascript">
function getCookie(c_name)
{
var c_value = document.cookie;
var c_start = c_value.indexOf(" " + c_name + "=");
if (c_start == -1)
  {
  c_start = c_value.indexOf(c_name + "=");
  }
if (c_start == -1)
  {
  c_value = null;
  }
else
  {
  c_start = c_value.indexOf("=", c_start) + 1;
  var c_end = c_value.indexOf(";", c_start);
  if (c_end == -1)
  {
c_end = c_value.length;
}
c_value = unescape(c_value.substring(c_start,c_end));
}
return c_value;
}
function setCookie(c_name,value,exdays)
{
var exdate=new Date();
exdate.setDate(exdate.getDate() + exdays);
var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
document.cookie=c_name + "=" + c_value;
}
$(document).ready(function(){
	var visited = getCookie("R_Visited")
	if (visited == null) {
		setCookie('R_Visited', 'yes', 360);
		$('.signup-button').prepend('第一次使用？');
  }
  setCookie('R_Visited', 'yes', 360);
	
	$("input").focus(function () {
		$(this).removeClass('form-error');
		$(this).siblings('.errormsg').remove();
  });
	
  $("input").blur(function () {
		if($(this).val().length == 0) {
			$(this).addClass('form-error').closest('div').append('<span role="alert" class="errormsg" id="errormsg_0_LastName" style="">此处不能留空。</span>');;
		}
  });
	//表单提交
	$('#loginform').submit(function() {
		var _this = $(this);
  	var button = $('input[type=submit]',this).attr('disabled',true);
		$.ajax({
				cache: false, 
				url: self.location.href, 
				dataType:'json',
				type: 'POST',
				data: _this.serializeArray(),
				success: function(data, status, xhr){
				  if(status=='success') {
						var code = xhr.getResponseHeader('X-InfoMaster-Code');
						switch (code) {
							case 'Validate':
								$.each(data,function(i){
									elm = $('#'+this.id);
									if (elm.length > 0 ) {
										msg = elm.siblings('.errormsg');
										if(msg.is('span')) 
											elm.removeClass('form-error').closest('.errormsg').text(this.text);
										else
											elm.addClass('form-error').closest('div').append('<span class="errormsg">'+this.text+'</span>');
									}
								});
								break;
							case 'Success': case 'Error': case 'Alert':
								$('.passwd-div .errormsg').remove();
								$('.passwd-div').append('<span class="errormsg">'+data+'</span>');
								$('.redtext').css('color','#dd4b39');
								break;
							case 'Redirect':
								window.location.replace(data.Location);
								break;
						}
					} else {
						$('.passwd-div').append('<span class="errormsg">'+data+'</span>');
					}
				},
				complete: function(){
						button.attr('disabled',false); 
				}
		});
  	return false;
	});
});
</script>
<!--
Name: zhanglei
Website: http://www.love4026.org
Email: sbmzhcn@gmail.com
Last update: 2014/06/25
-->
</body>
</html>