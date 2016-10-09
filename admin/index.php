<?php
// 加载公共文件
include dirname(__FILE__).'/admin.php';
// 查询管理员信息
$_USER = user_current();
// 动作
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;
system_class('body','dashboard');

$yourname = isset($_USER['nickname'])?$_USER['nickname']:$_USER['name'];
function your_inqiury_count($rate=null){
	global $_USER;
	$db = get_conn();
	$name = $_USER['name'];
	$where = "WHERE `belong`<>'' AND `belong`='{$name}'";
	$where .= " AND date_format(FROM_UNIXTIME(`addtime`),'%Y-%m')=date_format(now(),'%Y-%m')"; //当前月数据
	if($rate)
		$where .= " AND `inforate`='{$rate}'";
	$count = $db->result("SELECT COUNT(`postid`) FROM `#@_post` {$where};");
	return $count;
}

switch ($method) {
	default:
		if($_USER['usergroup']=='SEO技术人员' || $_USER['usergroup']=='郑州SEO技术人员'){
			redirect('profile.php');
		}
		current_user_can('cpanel');
		$db = get_conn();
		// 设置标题
        system_head('title', '控制面板');
		system_head('scripts',array('jquery.flot','jquery.flot.pie','jquery.flot.time','jquery.flot.resize','jquery.flot.categories'));
		system_head('scripts',array('jquery.peity'));
		system_head('scripts',array('jvectormap','jvectormap.world'));
		system_head('scripts',array('js/cpanel'));
		// 加载头部
        include ADMIN_PATH.'/admin-header.php';

		echo '<div class="module-header">';
		echo	'<h3><i class="icon-dashboard"></i> 欢迎</h3>';
		echo	'</div>';
		echo '<div class="tabbable">';
		echo	'<ul class="nav nav-tabs">';
		echo		'<li class="active"><a href="#analytics" data-toggle="tab"><i class="icon-lemon"></i> 概览</a></li>';
		echo		'<li><a href="#system" data-toggle="tab"><i class="icon-flag"></i> 系统信息</a></li>';
		echo		'<li><a href="#hardware" data-toggle="tab"><i class="icon-stethoscope"></i> 服务器信息</a></li>';
		echo	'</ul>';
		echo	'<div class="tab-content">';
		echo		'<div class="tab-pane active in" id="analytics">';
		//echo '<div class="widget alert alert-info adjusted"><button class="close" data-dismiss="alert">×</button><i class="cus-exclamation"></i><strong>小提示:</strong>在页面调整大小时如果想看到实时数据，请刷新当前页面。 这只是一个演示页面</div>';

		//echo '<div class="container-fluid">';

		echo   '<div class="row-fluid">';
		echo     '<div class="span12 center">';
		echo       '<ul class="stat-boxes">';
		echo         '<li class="popover-visits">';
		echo           '<div class="left peity_bar_good"><span>2,4,9,7,12,10,12</span>+10%</div>';
		echo           '<div class="right"><strong>'.post_count('inquiry').'</strong>询盘</div>';
		echo         '</li>';
		echo         '<li class="popover-users">';
		echo           '<div class="left peity_bar_neutral"><span>20,15,18,14,10,9,9,9</span>+10%</div>';
		echo           '<div class="right"><strong>'.user_count().'</strong>用户</div>';
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
		echo         '<div class="widget-title"><a href="#collapseStats" data-toggle="collapse">';
		echo           '<span class="icon"><i class="icon-signal"></i></span>';
		echo           '<h5>询盘信息概览</h5></a>';
		echo           '<div class="buttons"><a href="#" class="btn btn-mini"><i class="icon-refresh"></i> Update stats</a></div>';
		echo         '</div>';
		echo        '<div id="collapseStats" class="accordion-body collapse in">';
		echo         '<div class="widget-content">';
		echo           '<div class="row-fluid">';
		echo             '<div class="span4">';
		echo               '<ul class="site-stats">';
		echo                 '<li><div class="cc"><i class="icon-user"></i> <strong>'.user_count().'</strong> <small>全部用户</small></div></li>';
		echo                 '<li><div class="cc"><i class="icon-remove-circle"></i> <strong>'.user_count(1).'</strong> <small>禁用的用户</small></div></li>';
		echo                 '<li class="divider"></li>';
		echo                 '<li><div class="cc"><i class="icon-tags"></i> <strong>'.post_count('inquiry').'</strong> <small>所有询盘信息</small></div></li>';
		echo                 '<li><div class="cc"><i class="icon-bookmark"></i> <strong>'.post_count('inquiry','A').'</strong> <small>A类信息</small></div></li>';
		echo                 '<li><div class="cc"><i class=" icon-bookmark-empty"></i> <strong>'.post_count('inquiry','B').'</strong> <small>B类信息</small></div></li>';
		echo               '</ul>';
		echo             '</div>';
		echo             '<div class="span8">';
		echo               '<div class="chart"></div>';
		echo             '</div>';
		echo           '</div>';
		echo         '</div>';
		echo        '</div>'; // end collapse
		echo       '</div>';
		echo     '</div>';
		echo   '</div>';

		echo   '<div class="row-fluid">';
		echo     '<div class="span6">';
		echo       '<div class="widget-box">';
		echo         '<div class="widget-title"><a href="#collapseBars" data-toggle="collapse">';
		echo           '<span class="icon"><i class="icon-bar-chart"></i></span>';
		echo           '<h5>信息员录入信息数</h5></a>';
		echo           '<div class="buttons"><a href="#" class="btn btn-mini"><i class="icon-refresh"></i> Update stats</a></div>';
		echo         '</div>';
		echo        '<div id="collapseBars" class="accordion-body collapse in">';
		echo         '<div class="widget-content">';
		echo           '<div class="bars"></div>';
		echo         '</div>';
		echo        '</div>'; // end collapse
		echo       '</div>';
		echo     '</div>';
		echo     '<div class="span6">';
		echo       '<div class="widget-box">';
		echo         '<div class="widget-title"><a href="#collapseStates" data-toggle="collapse">';
		echo           '<span class="icon"><i class="icon-signal"></i></span>';
		echo           '<h5>询盘信息各大洲分布</h5></a>';
		echo           '<div class="buttons"><a href="#" class="btn btn-mini"><i class="icon-refresh"></i> Update stats</a></div>';
		echo         '</div>';
		echo        '<div id="collapseStates" class="accordion-body collapse in">';
		echo         '<div class="widget-content">';
		echo           '<div class="pie"></div>';
		echo           '<p id="hoverdata">Mouse position at (<span id="x">3.74</span>, <span id="y">-1.26</span>). <span id="clickdata"></span></p>';
		echo         '</div>';
		echo        '</div>'; // end collapse
		echo       '</div>';
		echo     '</div>';
		echo   '</div>';

		echo   '<div class="row-fluid">';
		echo     '<div class="span12 center">';
		echo       '<div class="widget-box">';
		echo         '<div class="widget-title"><a href="#collapseGlobal" data-toggle="collapse">';
		echo           '<span class="icon"><i class="icon-globe"></i></span>';
		echo           '<h5>询盘在各个国家的分布</h5></a>';
		echo           '<div class="buttons"><a href="#" class="btn btn-mini"><i class="icon-refresh"></i> Update stats</a></div>';
		echo         '</div>';
		echo        '<div id="collapseGlobal" class="accordion-body collapse in">';
		echo         '<div class="widget-content">';
		echo           '<div id="world-map" style="width: 100%; height: 400px"></div>';
		echo         '</div>';
		echo        '</div>'; // end collapse
		echo       '</div>';
		echo     '</div>';
		echo   '</div>';




		echo		'</div>';
		echo		'<div class="tab-pane" id="system">';
		echo			'<table class="table">';
		echo				'<thead>';
		echo					'<tr><th colspan="2">服务器信息</th></tr>';
		echo				'</thead>';
		echo				'<tbody>';
		echo					'<tr><td style="width:130px">服务器时间:</td><td>'.date("Y-m-d H:i:s").'</td></tr>';
		echo					'<tr><td>服务器系统:</td><td>'.PHP_OS .' '. php_uname('r') .' On '. php_uname('m').'</td></tr>';
		echo					'<tr><td>服务器软件:</td><td>'.$_SERVER['SERVER_SOFTWARE'].'</td></tr>';
		echo					'<tr><td>PHP API</td><td>'.PHP_SAPI.'</td></tr>';
		echo					'<tr><td>PHP 版本</td><td>'.PHP_VERSION.'</td></tr>';
		if (instr($db->scheme,'mysql,mysqli')) {
            $version = '4.1.0';
        } elseif (instr($db->scheme,'sqlite2,sqlite3,pdo_sqlite2,pdo_sqlite')) {
            $version = '2.8.0';
        }
		echo					'<tr><td>数据格式:</td><td>'.$db->scheme.'&nbsp;'.$db->version().'&nbsp; </td></tr>';
		echo					'<tr><td>服务器IP:</td><td>'.$_SERVER['SERVER_NAME'].' ('.@gethostbyname($_SERVER['SERVER_NAME']).')'.'&nbsp; </td></tr>';
		echo					'<tr><td>客户端IP:</td><td>'.$_SERVER['REMOTE_ADDR'].'&nbsp; </td></tr>';
		echo					'<tr><td>占用内存:</td><td>'.format_size(memory_get_usage()).'&nbsp; </td></tr>';
		echo					'<tr><td>网站根目录:</td><td>'.$_SERVER['DOCUMENT_ROOT'].'&nbsp; </td></tr>';
		echo					'<tr><td>最大上值:</td><td>'.@ini_get('upload_max_filesize').'&nbsp; </td></tr>';
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
			default:
			break;
		}
		echo				'<tbody>';
		echo					'<tr><td style="width:130px">服务器系统：</td><td>'.PHP_OS .' '. php_uname('r') .' On '. php_uname('m').'</td></tr>';
		if("show"==$sysReShow){
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
?>
