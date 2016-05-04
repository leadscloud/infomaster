<?php
// 加载公共文件
include dirname(__FILE__).'/../admin/admin.php';
/*require_once 'DomainParser/Parser.php';
require_once 'WhoisParser/Parser.php';
$Parser = new Novutec\WhoisParser\Parser();
$result = $Parser->lookup("http://miningequipments.org");
echo $result->created;
echo $result->changed;
echo $result->expires;
print_r( $result->name);
print_r($result->rawdata);*/
//$result = $Parser->lookup("http://www.shibang-china.com");
//print_r($result->rawdata); // get raw output as array
// 退出登录
$method = isset($_GET['method'])?$_GET['method']:null;
$result ='';
$url	= isset($_GET['domain'])?$_GET['domain']:null;
$password = isset($_GET['password'])?$_GET['password']:null;
switch($method){
	case 'checkurl':
		$url	= isset($_POST['url'])?$_POST['url']:null;
		$type   = isset($_POST['type'])?$_POST['type']:null;
		$status = determine_url($url,$type);
		echo json_encode($status);
		exit;
		break;
	case 'whois':
	/**
		$w = stream_get_wrappers();
		echo 'openssl: ',  extension_loaded  ('openssl') ? 'yes':'no', "\n";
		echo 'http wrapper: ', in_array('http', $w) ? 'yes':'no', "\n";
		echo 'https wrapper: ', in_array('https', $w) ? 'yes':'no', "\n";
		echo 'wrappers: ', var_dump($w);
*/
		
		$domain	= isset($_POST['domain'])?$_POST['domain']:null;
		require_once 'DomainParser/Parser.php';
		require_once 'WhoisParser/Parser.php';
		error_reporting(0);
		/*
		$Parser1 = new Novutec\DomainParser\Parser();
		$result1 = $Parser1->parse($domain);
		print_r($result1);
		*/
		$Parser = new Novutec\WhoisParser\Parser();
		$whois = $Parser->lookup($domain);
		//print_r($whois);
		$result = array($whois->created,$whois->expires);
		echo json_encode($result);
		exit;
		break;
	case 'data-table':
		//require_once 'DomainParser/Parser.php';
		//require_once 'WhoisParser/Parser.php';
		//error_reporting(0);
		//$Parser = new Novutec\WhoisParser\Parser();
		//$Parser->useCache = true;
		$db = get_conn();
		$json_data = array('aaData'=>array());

        // 其它公司的域名显示，临时
        $other_aaData = array(
            '红星'=>array(
                'china-ftm.com',
                'hxjq-machine.com',
                'hxjqmining.com',
                'china-sand-maker.com',
                'crushing-mill.com',
                'cijpart.com',
                'hxjq-crusher.com',
                'china-sandmaker.com',
                'sstric.co.uk',
                'ftm-mac.com',
                'chinafote.com',
                'hnftm.com',
                'foteinfo.com',
                'sinoftm.com',
                'hx-jawcrusher.com',
                'hxjq-ballmill.com',
                'sell-ballmill.com',
                'fte-china.com',
                'china-cementmill.com',
                'bestjawcrusher.com',
                'china-ore-beneficiation.com',
                'china-jawcrusher.com',
                'crusher-made.com',
                'made-crusher.com'
            ),
            '山美' => array(
                'sanmecrusher.com', 
                '51conecrusher.com',
                'rockcrusher.mobi',
            ),
            '一帆' => array(
                'crushingmachine.net',
                'yifancrusher.net',
                'symonscrusher.net',
                'mobile-crusher.com',
                'stonecrusher.org'
            ),
            '维科' => array(
                'vipeakgroup.com',
                'mill-crushers.com'
            ),
            '申港' => array(
                'suncomachinery.com',
            ),
            '科利瑞克' => array(
                'sand-making.net',
                'raymond-mill.com'
            ),
            '建业' => array(
                'mineral-grinder.com',
            ),
            '明工' => array(
                'sievoequipments.com',
            ),
            '卓亚' => array(
                'orientalcrusher.com',
                'crusherinc.com',
                'joyalgrindingmill.com',
                'joyalcrusher.com'
            ),
            '鑫海舶' => array(
                'aggregatequip.com',
                'hbm-crusher.com',
            ),
            '郑州达威' => array(
                'daswellmining.com',
            ),
            '中信重工' => array(
                'citic-hic.com',
            ),
            '美卓' => array(
                'metso.com',
            ),
            '山特维克' => array(
                'sandvik.com',
            ),
            '特雷克斯' => array(
                'terex.com',
                'powerscreen.com'
            ),
            '特雷克斯Powerscreen' => array(
                'blue-group.com'
            ),
            'Lippmann Milwaukee, Inc.' => array(
                'lippmann-milwaukee.com'
            ),
            'Telsmith公司' => array(
                'telsmith.com',
            ),
            '麦克洛斯基国际' => array(
                'mccloskeyinternational.com',
            ),
            '日立建机' => array(
                'hitachicm.com.au',
                'hitachi-c-m.com',
                'hcme.com',
                'tatahitachi.co.in',
                'hcmm.com.my',
                'hitachi.ihub.ninemsn.com.au'
            ),
            '瑞典Tesab' => array(
                'tesab.com',
            ),
            'LCN.com Ltd' => array(
                'redrhinocrushers.com',
            ),
            '久益环球' => array(
                'joyglobal.com',
                'phmining.com',
                'coloradomining.org'
            ),
            '凯斯特' => array(
                'keestrack.com',
            ),
            '山启' => array(
                'stonecrusher.hk',
            ),
            '盖尔特' => array(
                'gatormachinery.com',
            ),

        );

        foreach ($other_aaData as $name => $urls) {
            foreach ($urls as $value) {
                $json_data['aaData'][] = array('http://'.$value, 'C'.$name, 0, null, null);
            }  
        }
        
        if($password != 'sbmcrusher^537%k'){
            echo json_encode($json_data);
            exit();
        }

		$rs= $db->query("SELECT `domain`, `result`,`subdomain` FROM `#@_rule` WHERE `type`='网站所属人' AND `domain`<>'' ORDER BY `result`;");
		while ($result = $db->fetch($rs)) {
			//$whois = $Parser->lookup($result['domain']);
            if($result['domain']){
			    $json_data['aaData'][] = array($result['domain'],$result['result'],$result['subdomain'],null,null);
            }
		}
        
		//临时增加域名管理里的域名
		$rs= $db->query("SELECT `domain`, `author` FROM `#@_domain` WHERE `status`='approved' ORDER BY `author`;");
		while ($result = $db->fetch($rs)) {
			//$whois = $Parser->lookup($result['domain']);
            if($result['domain']){
                $json_data['aaData'][] = array('http://'.$result['domain'],$result['author'],0,null,null);
            }
		}

        
        
		//print_r($json_data);
		echo json_encode($json_data);
		exit();
		break;
	default:
        echo 'error';
        exit();
		$db = get_conn();
		$count = $db->result("SELECT COUNT(`ruleid`) FROM `#@_rule` WHERE `type`='网站所属人' AND `domain`<>'' AND `subdomain`<>1");
		$count2 = $db->result("SELECT COUNT(`ruleid`) FROM `#@_rule` WHERE `type`='网站所属人' AND `domain`<>'' AND `subdomain`=1");

		$html = '';
		$html.= '<div class="">';
		$html.= '<div class="page-header">';
		$html.=   '<h1>所有域名列表 <small>共'.$count.'个域名, '.$count2.'个二级域名</small></h1>';
		$html.= '</div>';
		$html.=   '<table class="table table-striped table-hover table-bordered" id="all-domain">';
		$html.=     '<thead>';
		$html.=       '<th>域名</th>';
		$html.=       '<th>所属人</th>';
		$html.=       '<th>是否二级域名</th>';
		$html.=       '<th>域名创建时间</th>';
		$html.=       '<th>域名过期时间</th>';
		$html.=     '</thead>';
		$html.=     '<tbody>';
		$html.=     '</tbody>';
		$html.=     '<tfoot>';
		$html.=       '<th>域名</th>';
		$html.=       '<th>所属人</th>';
		$html.=       '<th>是否二级域名</th>';
		$html.=       '<th>域名创建时间</th>';
		$html.=       '<th>域名过期时间</th>';
		$html.=     '</tfoot>';
		$html.=   '</table>';
		$html.= '</div>';
		break;
}
if($url){
	if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
	$result = determine_url($url, '网站所属人');
	if(!$result) {
		$result = '此网址没有匹配！';
	}
}
get_domain_data();
function wrapper_head($htlm){
	echo '<div class="container-fluid">';
	echo   '<div class="row-fluid">';
	echo     '<div class="span5">';
	echo     '</div>';
	echo     '<div class="span5">';
	echo     '</div>';
	echo   '</div>';
	echo '</div>';       
}
// 查询页面
?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>查询页面</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">
        <meta name="author" content="Ray.">

        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="css/main.css">
        <link rel="stylesheet" href="css/style.css">
        <script src="js/vendor/modernizr-2.6.2.min.js"></script>
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->

        <div class="wrapper">
          <div class="google-header-bar">
            <div class="header content clearfix">
              <img class="logo" src="../common/img/sbm_logo_41.png" alt="Google">
              <div class="form-container">
                <form class="form-search">
                  <input type="text" class="input-medium search-query" name="domain" value="<?php echo $url;?>" placeholder="在此输入你的域名,将根据系统规则判定域名所有者." id="check-url">
                  <button type="submit" class="g-button g-button-submit input-small" id="btn-search"><i class="icon-search"></i> 查询</button>
                </form>
              </div>
              <span class="signup-button"> <a id="link-signup" class="g-button g-button-red" href="../index.php">登陆</a></span>
            </div>
          </div>
          
          <div class="main content clearfix">
          <?php
		  	echo $html;
		  ?>
          </div>
    	</div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.9.0.min.js"><\/script>')</script>
        <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
        <script src="js/jquery.dataTables.min.js"></script>
        <script src="js/plugins.js"></script>
        <script src="js/main.js"></script>
    </body>
</html>
