<?php
// 加载公共文件
include dirname(__FILE__).'/admin/admin.php';
// 动作
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;
system_class('body','dashboard');
system_head('scripts',array('jquery.flot','jquery.flot.pie','jquery.flot.time','jquery.flot.resize'));
system_head('scripts',array('jquery.peity'));
//system_head('scripts',array('js/charts'));
system_head('scripts',array('js/cpanel'));

switch ($method) {
	default:
		$db = get_conn();
		// 设置标题
        system_head('title', '控制面板');
		// 加载头部
?>
<!DOCTYPE html>
<html lang="en"><head>
    <meta charset="utf-8">
    <title><?php echo esc_html(strip_tags(system_head('title')));?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Ray.">

    <!-- Le styles -->
    <?php 
		// 加载核心CSS
		loader_css('css/style');
		// 加载模块CSS
		loader_css(system_head('styles'));
	?> 
    

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="../lib/assets/ico/favicon.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="../lib/assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="../lib/assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="../lib/assets/ico/apple-touch-icon-57-precomposed.png">
    </head>

    <body<?php echo system_class('body');?>>
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="<?php echo ROOT;?>" style="font-size:13px; color:##fbf4f4">询盘管理系统</a>
          <div class="nav-collapse">
            <ul class="nav">
              <li class="active"><a href="#"><i class="icon-home"></i>首页</a></li>
              <li><a href="admin/login.php"><i class="icon-user"></i>登陆</a></li>
              <li><a href="#">关于</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>
<div class="container">
      <div id="main">
<?php
		
		echo '<div class="module-header">';
		echo	'<h3><i class="icon-github-alt"></i> 公共页面</h3>';
		echo	'</div>';
		echo '<div class="tabbable">';
		echo	'<ul class="nav nav-tabs">';
		echo		'<li class="active"><a href="#analytics" data-toggle="tab"><i class="icon-lemon"></i> 概览</a></li>';
		echo		'<li><a href="#system" data-toggle="tab"><i class="icon-flag"></i> 系统信息</a></li>';
		echo		'<li><a href="#hardware" data-toggle="tab"><i class="icon-stethoscope"></i> 硬件信息</a></li>';
		echo	'</ul>';
		echo	'<div class="tab-content">';
		echo		'<div class="tab-pane active in" id="analytics">';
		echo '<div class="widget alert alert-info adjusted">
													<button class="close" data-dismiss="alert">×</button>
													<i class="cus-exclamation"></i>
													<strong>小提示:</strong>在页面调整大小时如果想看到实时数据，请刷新当前页面。 这只是一个演示页面
												</div>';
		
		//echo '<div class="container-fluid">';
		
		echo   '<div class="row-fluid">';
		echo     '<div class="span12 center">';
		echo       '<ul class="stat-boxes">';
		echo         '<li class="popover-visits">';
		echo           '<div class="left peity_bar_good"><span>2,4,9,7,12,10,12</span>+10%</div>';
		echo           '<div class="right"><strong>36094</strong>Visits</div>';
		echo         '</li>';
		echo         '<li class="popover-users">';
		echo           '<div class="left peity_bar_neutral"><span>20,15,18,14,10,9,9,9</span>+10%</div>';
		echo           '<div class="right"><strong>36094</strong>Visits</div>';
		echo         '</li>';
		echo         '<li class="popover-orders">';
		echo           '<div class="left peity_bar_bad"><span>3,5,9,7,12,20,10</span>+10%</div>';
		echo           '<div class="right"><strong>36094</strong>Visits</div>';
		echo         '</li>';
		echo         '<li class="popover-tickets">';
		echo           '<div class="left peity_bar_good"><span>12,6,9,23,14,10,17</span>+10%</div>';
		echo           '<div class="right"><strong>36094</strong>Visits</div>';
		echo         '</li>';
		echo       '</ul>';
		echo     '</div>';
		echo   '</div>';
		
		echo   '<div class="row-fluid">';
		echo     '<div class="span12">';
		echo       '<div class="widget-box">';
		echo         '<div class="widget-title">';
		echo           '<span class="icon"><i class="icon-signal"></i></span>';
		echo           '<h5>Site Statistics</h5>';
		echo           '<div class="buttons"><a href="#" class="btn btn-mini"><i class="icon-refresh"></i> Update stats</a></div>';
		echo         '</div>';
		echo         '<div class="widget-content">';
		echo           '<div class="row-fluid">';
		echo             '<div class="span4">';
		echo               '<ul class="site-stats">';
		echo                 '<li><i class="icon-user"></i> <strong>1433</strong> <small>Total Users</small></li>';
		echo                 '<li><i class="icon-arrow-right"></i> <strong>16</strong> <small>New Users (last week)</small></li>';
		echo                 '<li class="divider"></li>';
		echo                 '<li><i class="icon-shopping-cart"></i> <strong>259</strong> <small>Total Shop Items</small></li>';
		echo                 '<li><i class="icon-tag"></i> <strong>8650</strong> <small>Total Orders</small></li>';
		echo                 '<li><i class="icon-repeat"></i> <strong>29</strong> <small>Pending Orders</small></li>';
		echo               '</ul>';
		echo             '</div>';
		echo             '<div class="span8">';
		echo               '<div class="chart"></div>';

		echo             '</div>';
		echo           '</div>';
		echo         '</div>';
		echo       '</div>';
		echo     '</div>';
		echo   '</div>';
		
		echo   '<div class="row-fluid">';
		echo     '<div class="span6">';
		echo       '<div class="widget-box">';
		echo         '<div class="widget-title">';
		echo           '<span class="icon"><i class="icon-signal"></i></span>';
		echo           '<h5>Bar chart</h5>';
		echo           '<div class="buttons"><a href="#" class="btn btn-mini"><i class="icon-refresh"></i> Update stats</a></div>';
		echo         '</div>';
		echo         '<div class="widget-content">';
		echo           '<div class="bars"></div>';
		echo         '</div>';
		echo       '</div>';
		echo     '</div>';
		echo     '<div class="span6">';
		echo       '<div class="widget-box">';
		echo         '<div class="widget-title">';
		echo           '<span class="icon"><i class="icon-signal"></i></span>';
		echo           '<h5>Pie chart</h5>';
		echo           '<div class="buttons"><a href="#" class="btn btn-mini"><i class="icon-refresh"></i> Update stats</a></div>';
		echo         '</div>';
		echo         '<div class="widget-content">';
		echo           '<div class="pie"></div>';
		echo           '<p id="hoverdata">Mouse position at (<span id="x">3.74</span>, <span id="y">-1.26</span>). <span id="clickdata"></span></p>';
		echo         '</div>';
		echo       '</div>';
		echo     '</div>';
		echo   '</div>';
		
		//echo   '</div>';
		?>
<div class="row-fluid">
					<div class="span4">
						<div class="widget-box">
							<div class="widget-title">
								<span class="icon">
									<i class="icon-eye-open"></i>
								</span>
								<h5>Browsers</h5>
							</div>
							<div class="widget-content nopadding">
								<table class="table table-bordered table-striped table-hover">
									<thead>
										<tr>
											<th>Browser</th>
											<th>Visits</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>Chrome</td>
											<td>8775</td>
										</tr>
										<tr>
											<td>Firefox</td>
											<td>5692</td>
										</tr>
										<tr>
											<td>Internet Explorer</td>
											<td>4030</td>
										</tr>
										<tr>
											<td>Opera</td>
											<td>1674</td>
										</tr>
										<tr>
											<td>Safari</td>
											<td>1166</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="span4">
						<div class="widget-box">
							<div class="widget-title">
								<span class="icon">
									<i class="icon-arrow-right"></i>
								</span>
								<h5>Refferers</h5>
							</div>
							<div class="widget-content nopadding">
								<table class="table table-bordered table-striped table-hover">
									<thead>
										<tr>
											<th>Site</th>
											<th>Visits</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td><a href="#">http://google.com</a></td>
											<td>12679</td>
										</tr>
										<tr>
											<td><a href="#">http://bing.com</a></td>
											<td>11444</td>
										</tr>
										<tr>
											<td><a href="#">http://yahoo.com</a></td>
											<td>8595</td>
										</tr>
										<tr>
											<td><a href="#">http://www.something.com</a></td>
											<td>4445</td>
										</tr>
										<tr>
											<td><a href="#">http://else.com</a></td>
											<td>2094</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="span4">
						<div class="widget-box">
							<div class="widget-title">
								<span class="icon">
									<i class="icon-file"></i>
								</span>
								<h5>Most Visited Pages</h5>
							</div>
							<div class="widget-content nopadding">
								<table class="table table-bordered table-striped table-hover">
									<thead>
										<tr>
											<th>Page</th>
											<th>Visits</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td><a href="#">Shopping cart</a></td>
											<td>9440</td>
										</tr>
										<tr>
											<td><a href="#">Blog</a></td>
											<td>6974</td>
										</tr>
										<tr>
											<td><a href="#">jQuery UI tips</a></td>
											<td>5377</td>
										</tr>
										<tr>
											<td><a href="#">100+ Free Icon Sets</a></td>
											<td>4990</td>
										</tr>
										<tr>
											<td><a href="#">How to use a Google Web Tools</a></td>
											<td>4834</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>

        <?php
		
		

		echo		'</div>';
		echo		'<div class="tab-pane" id="system">';
		echo			'<table class="table">';
		echo				'<thead>';
		echo					'<tr><th colspan="2">系统信息</th></tr>';
		echo				'</thead>';
		echo				'<tbody>';
		echo					'<tr><td style="width:130px">服务器时间:</td><td>'.date("Y-m-d H:i:s").'</td></tr>';
		echo					'<tr><td>服务器系统:</td><td>'.PHP_OS .' '. php_uname('r') .' On '. php_uname('m').'</td></tr>';
		echo					'<tr><td>服务器软件:</td><td>'.$_SERVER['SERVER_SOFTWARE'].'</td></tr>';
		echo					'<tr><td>PHP API:</td><td>'.PHP_SAPI.'</td></tr>';
		echo					'<tr><td>PHP 版本</td><td>'.PHP_VERSION.'</td></tr>';
		if (instr($db->scheme,'mysql,mysqli')) {
            $version = '4.1.0';
        } elseif (instr($db->scheme,'sqlite2,sqlite3,pdo_sqlite2,pdo_sqlite')) {
            $version = '2.8.0';
        }
		echo					'<tr><td>数据格式:</td><td>'.$db->scheme.'&nbsp;'.$db->version().'&nbsp; </td></tr>';
		echo					'<tr><td>服务器IP:</td><td>'.$_SERVER['SERVER_NAME'].' ('.@gethostbyname($_SERVER['SERVER_NAME']).')'.'&nbsp; </td></tr>';
		echo					'<tr><td>客户端IP:</td><td>'.$_SERVER['REMOTE_ADDR'].'&nbsp; </td></tr>';
		echo				'</tbody>';
		echo			'</table>';
		echo		'</div>';
		echo		'<div class="tab-pane" id="hardware">';
		echo			'<table class="table">';
		echo				'<thead>';
		echo					'<tr><th colspan="2">硬件信息</th></tr>';
		echo				'</thead>';
		$sysReShow ='';
		switch (PHP_OS)
		{
			case "Linux":
				$sysReShow = (false !== ($sysInfo = sys_linux()))?"show":"none";
				break;
			case "WINNT":
				$sysReShow = (false !== ($sysInfo = sys_windows()))?"show":"none";
			default:
			break;
		}
		echo				'<tbody>';
		echo					'<tr><td style="width:130px">服务器系统：</td><td>'.PHP_OS .' '. php_uname('r') .' On '. php_uname('m').'</td></tr>';
		if("show"==$sysReShow){
		if(PHP_OS == "WINNT") {
			echo  '<tr><td>操作系统版本：</td><td>'.$sysInfo['操作系统版本'].'</td></tr>';
			echo  '<tr><td>操作系统序列号：</td><td>'.$sysInfo['操作系统序列号'].'</td></tr>';
			echo  '<tr><td>最后重启时间：</td><td>'.$sysInfo['最后重启时间'].'</td></tr>';
		}
		echo					'<tr><td>服务器运行时间：</td><td>'.$sysInfo['uptime'].'</td></tr>';
		
		echo					'<tr><td>CPU核心：</td><td>';
		echo					'<table class="table table-condensed table-borderless table-layout"><tbody>';
		echo					'<tr>';
		echo						'<td colspan="2">共有'.$sysInfo['cpu']['num'].'个核心，型号如下:</td>';
		echo					'<tr>';
		echo					$sysInfo['cpu']['detail'];
		echo					'</tbody></table>';
		echo					'</td></tr>';
		
		echo					'<tr><td>系统平均负载：</td><td>'.$sysInfo['loadAvg'].'</td></tr>';
		echo					'<tr><td>内存大小：</td><td>';
		echo					'<table class="table table-condensed table-borderless table-layout"><tbody>';
		echo					'<tr>';
		echo						'<td style="width:80px">总大小:</td>';
		echo						'<td style="width:80px">'.$sysInfo['memTotal'].'M</td>';
		echo						'<td></td>';
		echo						'<td></td>';
		echo					'<tr>';
		echo					'<tr>';
		echo						'<td>已使用:</td>';
		echo						'<td>'.$sysInfo['memUsed'].'M</td>';
		echo						'<td></td>';
		echo						'<td><span class="label label-success">'.$sysInfo['memPercent'].'%</span></td>';
		echo					'<tr>';
		echo					'<tr>';
		echo						'<td>剩余可用:</td>';
		echo						'<td>'.$sysInfo['memFree'].'M</td>';
		echo						'<td></td>';
		echo						'<td></td>';
		echo					'<tr>';
		echo					'</tbody></table>';
		echo					'</td></tr>';
		
		echo					'<tr><td>交换空间：</td><td>';
		echo					'<table class="table table-condensed table-borderless table-layout"><tbody>';
		echo					'<tr>';
		echo						'<td style="width:80px">总大小</td>';
		echo						'<td style="width:80px">'.$sysInfo['swapTotal'].'M</td>';
		echo						'<td></td>';
		echo						'<td></td>';
		echo					'<tr>';
		echo					'<tr>';
		echo						'<td>已使用:</td>';
		echo						'<td>'.$sysInfo['swapUsed'].'M</td>';
		echo						'<td></td>';
		echo						'<td><span class="label label-success">'.$sysInfo['swapPercent'].'%</span></td>';
		echo					'<tr>';
		echo					'<tr>';
		echo						'<td>剩余可用:</td>';
		echo						'<td>'.$sysInfo['swapFree'].'M</td>';
		echo						'<td></td>';
		echo						'<td></td>';
		echo					'<tr>';
		echo					'</tbody></table>';
		echo					'</td></tr>';
		if(PHP_OS == "Linux") {
		echo					'<tr><td>储存空间：</td><td>';
		echo					'<table class="table table-condensed table-borderless table-layout"><tbody>';
		echo					'<tr>';
		echo						'<th style="width:80px">总大小:</td>';
		echo						'<th>已使用</td>';
		echo						'<th>剩余可用</td>';
		echo						'<th>使用率</td>';
		echo					'<tr>';
		echo					'<tr>';
		echo						'<td style="width:80px">'.$sysInfo['diskTotal'].'</td>';
		echo						'<td>'.$sysInfo['diskUsed'].'</td>';
		echo						'<td>'.$sysInfo['diskFree'].'</td>';
		echo						'<td><span class="label label-success">'.$sysInfo['diskPercent'].'%</span></td>';
		echo					'<tr>';
		}
		echo					'</tbody></table>';
		echo					'</td></tr>';
		}
		echo				'</tbody>';
		echo			'</table>';
		echo		'</div>';
		echo	'</div>';
		echo '</div>';

		// 加载尾部
        include ADMIN_PATH.'/admin-footer.php';
        break;
}
function sys_linux()
{
  // CPU
  if (false === ($str = @file("/proc/cpuinfo"))) return false;
  $str = implode("", $str);
  @preg_match_all("/model\s+name\s{0,}\:+\s{0,}([\w\s\)\(\@\-.]+)[\r\n]+/", $str, $model);
  //@preg_match_all("/cpu\s+MHz\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $mhz);
  @preg_match_all("/cache\s+size\s{0,}\:+\s{0,}([\d\.]+\s{0,}[A-Z]+[\r\n]+)/", $str, $cache);
  if (false !== is_array($model[1]))
	  {
	  $res['cpu']['num'] = sizeof($model[1]);
	  for($i = 0; $i < $res['cpu']['num']; $i++)
	  {
		  $res['cpu']['detail'][] = "<tr><td>".$model[1][$i]."</td><td> 缓存：".$cache[1][$i].'</td></tr>';
	  }
	  if (false !== is_array($res['cpu']['detail'])) $res['cpu']['detail'] = implode("", $res['cpu']['detail']);
	  }
   
   
  // UPTIME
  if (false === ($str = @file("/proc/uptime"))) return false;
  $str = explode(" ", implode("", $str));
  $str = trim($str[0]);
  $min = $str / 60;
  $hours = $min / 60;
  $days = floor($hours / 24);
  $hours = floor($hours - ($days * 24));
  $min = floor($min - ($days * 60 * 24) - ($hours * 60));
  if ($days !== 0) $res['uptime'] = $days."天";
  if ($hours !== 0) $res['uptime'] .= $hours."小时";
  $res['uptime'] .= $min."分钟";
   
  // MEMORY
  if (false === ($str = @file("/proc/meminfo"))) return false;
  $str = implode("", $str);
  preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);
   
  $res['memTotal'] = round($buf[1][0]/1024, 2);
  $res['memFree'] = round($buf[2][0]/1024, 2);
  $res['memUsed'] = ($res['memTotal']-$res['memFree']);
  $res['memPercent'] = (floatval($res['memTotal'])!=0)?round($res['memUsed']/$res['memTotal']*100,2):0;
   
  $res['swapTotal'] = round($buf[3][0]/1024, 2);
  $res['swapFree'] = round($buf[4][0]/1024, 2);
  $res['swapUsed'] = ($res['swapTotal']-$res['swapFree']);
  $res['swapPercent'] = (floatval($res['swapTotal'])!=0)?round($res['swapUsed']/$res['swapTotal']*100,2):0;
   
  // LOAD AVG
  if (false === ($str = @file("/proc/loadavg"))) return false;
  $str = explode(" ", implode("", $str));
  $str = array_chunk($str, 3);
  $res['loadAvg'] = implode(" ", $str[0]);
  
  $res['diskTotal'] = format_size(@disk_total_space(".")); //round((@disk_total_space(".")/1024*1024*1024),2);
  $res['diskFree'] = format_size(@disk_free_space(".")); //round((@disk_free_space(".")/1024*1024*1024),2);
  $res['diskUsed'] = $res['diskTotal'] - $res['diskFree'];
  $res['diskPercent'] = round(($res['diskUsed']/$res['diskTotal']*100),2);
  
   
  return $res;
}
function format_size($size) {
    $mod = 1024;
    $units = explode(' ','B KB MB GB TB PB');
    for ($i = 0; $size > $mod; $i++) {
        $size /= $mod;
    }
    return round($size, 2) . ' ' . $units[$i];
}
function sys_windows() {

$objLocator = new COM("WbemScripting.SWbemLocator");

$wmi = $objLocator->ConnectServer();

$prop = $wmi->get("Win32_PnPEntity");

//CPU

$cpuinfo = GetWMI($wmi,"Win32_Processor", array("Name","L2CacheSize","NumberOfCores"));

$res['cpu']['num'] = $cpuinfo[0]['NumberOfCores'];

if (null == $res['cpu']['num']) {

$res['cpu']['num'] = 1;

}

for ($i=0;$i<$res['cpu']['num'];$i++){
	
$res['cpu']['detail'][] = "<tr><td>".$cpuinfo[0]['Name']."</td><td> 二级缓存：".$cpuinfo[0]['L2CacheSize'].'</td></tr>';

//$res['cpu']['detail'] .= $cpuinfo[0]['Name']."<br>";

//$res['cpu']['detail'] .= '二级缓存：'.$cpuinfo[0]['L2CacheSize']."<br>";

}
if (false !== is_array($res['cpu']['detail'])) $res['cpu']['detail'] = implode("", $res['cpu']['detail']);

// SYSINFO

$sysinfo = GetWMI($wmi,"Win32_OperatingSystem", array('LastBootUpTime','TotalVisibleMemorySize','FreePhysicalMemory','Caption','CSDVersion','SerialNumber','InstallDate'));

$res['操作系统版本'] = $sysinfo[0]['Caption']." ".$sysinfo[0]['CSDVersion'];

$res['操作系统序列号'] = "{$sysinfo[0]['SerialNumber']} 于".date('Y年m月d日H:i:s',strtotime(substr($sysinfo[0]['InstallDate'],0,14)))."安装";

//UPTIME

$res['最后重启时间'] = $sysinfo[0]['LastBootUpTime'];

 

 

$sys_ticks = 3600*8 + time() - strtotime(substr($res['最后重启时间'],0,14));

$min = $sys_ticks / 60;

$hours = $min / 60;

$days = floor($hours / 24);

$hours = floor($hours - ($days * 24));

$min = floor($min - ($days * 60 * 24) - ($hours * 60));

if ($days !== 0) $ress['day'] = $days."天";

if ($hours !== 0) $ress['hours'] = $hours."小时";

$res['uptime'] = $ress['day'].$ress['hours'].$min."分钟";

 

//MEMORY

$res['memTotal'] = $sysinfo[0]['TotalVisibleMemorySize'];

$res['memFree'] = $sysinfo[0]['FreePhysicalMemory'];

$res['memUsed'] = $res['memTotal'] - $res['memFree'];

$res['memPercent'] = round($res['memUsed'] / $res['memTotal']*100,2);

 

$swapinfo = GetWMI($wmi,"Win32_PageFileUsage", array('AllocatedBaseSize','CurrentUsage'));

 

// TODO swp区获取

$res['swapTotal'] = $swapinfo[0]['AllocatedBaseSize'];

$res['swapUsed'] = $swapinfo[0]['CurrentUsage'];

$res['swapFree'] = $res['swapTotal'] - $res['swapUsed'];

$res['swapPercent'] = (floatval($res['swapTotal'])!=0)?round($res['swapUsed']/$res['swapTotal']*100,2):0;

 

// LoadPercentage

$loadinfo = GetWMI($wmi,"Win32_Processor", array("LoadPercentage"));

$res['loadAvg'] = $loadinfo[0]['LoadPercentage'];

 

return $res;

}

 

function GetWMI($wmi,$strClass, $strValue = array()) {

$arrData = array();

 

$objWEBM = $wmi->Get($strClass);

$arrProp = $objWEBM->Properties_;

$arrWEBMCol = $objWEBM->Instances_();

foreach($arrWEBMCol as $objItem) {

@reset($arrProp);

$arrInstance = array();

foreach($arrProp as $propItem) {

eval("\$value = \$objItem->" . $propItem->Name . ";");

if (empty($strValue)) {

$arrInstance[$propItem->Name] = trim($value);

} else {

if (in_array($propItem->Name, $strValue)) {

$arrInstance[$propItem->Name] = trim($value);

}

}

}

$arrData[] = $arrInstance;

}

return $arrData;

}
?>