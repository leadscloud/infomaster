<?php
// 加载公共文件
include dirname(__FILE__).'/admin.php';
// 查询管理员信息
$_USER = user_current();
current_user_can('cpanel');
// 动作
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;
system_class('body','dashboard');

//system_head('scripts',array('js/charts'));

//print_r(get_inqiury_info('inquiry',null,30));
//echo json_encode(get_world_info());
//print_r(get_world_info());
function your_inqiury_count($rate=null){
	global $_USER;
	$db = get_conn();
	$name = $_USER['nickname'];
	$where = "WHERE `belong`<>'' AND `belong`='{$name}'";
	if($rate)
		$where .= " AND `inforate`='{$rate}'";
	$count = $db->result("SELECT COUNT(`postid`) FROM `#@_post` {$where};");
	return $count;
}

$days = isset($_REQUEST['days'])?$_REQUEST['days']:7;
$days = intval($days);
if($days==0) $days = 7;

switch ($method) {
	case 'getwords':
		$helloWords = getSomeSentence();
		ajax_success($helloWords);
	break;

	default:
		system_head('title', '询盘所属人概览');
		//system_head('scripts',array('jquery.flot','jquery.flot.pie','jquery.flot.time','jquery.flot.resize','jquery.flot.categories'));
		system_head('styles', array('css/daterangepicker'));
		system_head('styles',array('css/statistics'));
		system_head('scripts',array('js/date'));
		system_head('scripts',array('js/daterangepicker'));
		system_head('scripts',array('js/statistics'));
		// 加载头部
        include ADMIN_PATH.'/admin-header.php';

        $orderby = isset($_GET['orderby'])?$_GET['orderby']:'totalvaild';

        $startdate = isset($_REQUEST['startdate'])?$_REQUEST['startdate']:null;
		$enddate = isset($_REQUEST['enddate'])?$_REQUEST['enddate']:null;
		$conditions = array();


		echo '<div class="module-header">';
		echo	'<h3><i class="icon-trophy"></i> 询盘所属人概览</h3>';
		echo '</div>';
		
		echo '<div class="alert alert-warning hidden-print"><i class="icon-bullhorn"></i> <span id="somewords">友情提醒：本数据仅供参考，如果询盘数高于实际发布，建议以实际发布为准(也许计算错了)， 以下排序按照总有效询盘数。</span></div>';

		
		echo   '<div class="row-fluid printable">';
		echo     '<div class="span12">';
		echo       '<div class="widget-box">';
		echo         '<div class="widget-title">';

		

		echo           '<span class="icon"><i class="icon-bar-chart"></i></span>';
		//echo           '<h5>最近'.$days.'天询盘情况</h5>';
		echo '<div id="statisticrange" class="pull-left hidden-phone hidden-tablet">';
    	echo   '<i class="icon-calendar icon-large"></i> ';
    	echo   '<span>'.($startdate==null?date_gmt("m/d/Y", strtotime('-7 day')):date_gmt('m/d/Y', $startdate)) .' - '. ($enddate==null?date_gmt("m/d/Y"):date_gmt('m/d/Y', $enddate)) .'</span> <b class="caret"></b> ';
		echo '</div>';
		echo           '<div class="buttons hidden-print"><a href="#" class="btn btn-mini btn-expandall hidden-phone"><i class="icon-double-angle-down"></i> 展开所有</a>';

		

		//echo             '<a href="'.add_query_arg('days','30').'" class="btn btn-mini"><i class="icon-refresh"></i> 最近30天</a>';
		if($orderby=='totalvaild')
			echo         '<a href="'.add_query_arg('orderby','total').'" class="btn btn-mini hidden-phone"><i class="icon-reorder"></i> 按总询盘数排序</a>';
		elseif($orderby=='total')
			echo         '<a href="'.add_query_arg('orderby','totalvaild').'" class="btn btn-mini hidden-phone"><i class="icon-reorder"></i> 按总有效询盘数排序</a>';

		echo             '<a href="javascript:window.print();" class="btn btn-mini hidden-phone">打印</a>';
		echo           '</div>';
		echo '<div class="buttons"><a class="btn go-full-screen"><i class="icon-resize-full"></i></a></div>';
		echo         '</div>';
		echo         '<div class="widget-content nopadding">';
		echo           '<table class="table table-bordered table-striped table-hover responsive">';
		echo 			'<thead>';
		echo             '<tr>';
		echo               '<th width="30px">编号</th>';
		echo               '<th width="30%">所属人</th>';
		echo               '<th data-sortBy="numeric">A类询盘</th>';
		echo               '<th>B类询盘</th>';
		echo               '<th>C类询盘</th>';
		echo               '<th>D类询盘</th>';
		echo               '<th>E类询盘</th>';
		echo             '</tr>';
		echo 			'</thead>';


		

		$where = 'WHERE `type`="inquiry" AND `xp_status`="" AND (`source`="商务通" OR `source`="网站留言")';

		if ($startdate) {
			$conditions[] = sprintf("`addtime` > '%s'",esc_sql($startdate));
		}
		if ($enddate) {
			$conditions[] = sprintf("`addtime` < '%s'",esc_sql($enddate));
		}
		if($conditions) {
			$where.= ' AND '.implode(' AND ' , $conditions);
		}else{
			$where = "WHERE FROM_UNIXTIME(`addtime`) >= DATE_ADD(CURDATE(), INTERVAL -{$days} DAY) AND `type`='inquiry' AND `xp_status`='' AND (`source`='商务通' OR `source`='网站留言')";
		}

		$sql = "SELECT `postid`, `inforate`,`referer`,`landingurl`, `belong` FROM `#@_post` {$where} ORDER BY `belong` ASC, `inforate` ASC, `landingurl` ASC";


		$db = get_conn();

		//echo $sql;

		//$sql = "SELECT `postid`, `inforate`,`referer`,`landingurl`, `belong` FROM `#@_post` WHERE  FROM_UNIXTIME(`addtime`) >= DATE_ADD(CURDATE(), INTERVAL -{$days} DAY) AND `type`='inquiry' AND `xp_status`='' order by belong ASC, `inforate` ASC, `landingurl` ASC";

		$result = $db->query($sql);
		$collect = array();
		if ($result) {
            while ($data = $db->fetch($result)) {
            	$url = empty($data['landingurl'])?$data['referer']:$data['landingurl'];
            	$url = trim($url);
            	//增加一个判断google重写向的地址
            	if($url!='' && strpos($url,'google')!==false){
	            	$url_parse = parse_url($url);
				    if(strrpos($url_parse['host'],"google")!==false){
				        if(isset($url_parse['query'])){
				            parse_str($url_parse['query'],$path_array);
				            if(isset($path_array['sa']) && $path_array['sa']=='t' && isset($path_array['url'])){
				                $url = $path_array['url'];
				            }
				        } 
				    }
				}

            	$parser = new parseURL($url);
            	$host 	= $parser->get_host();
            	$host 	= preg_replace('/^www./','',$host); //www开头的都替换掉。
            	$collect[$data['belong']]['host'][$host][$data['inforate']][] = $data['landingurl'];
			}
		}
		//print_r($collect);
		//
		if($where){
			$count_sql = "SELECT COUNT(`postid`) as post_count, `inforate`,`landingurl`, `belong` FROM `#@_post` {$where} GROUP BY `belong`,`inforate` order by post_count DESC, `inforate` DESC";
		}else{
			$count_sql = "SELECT COUNT(`postid`) as post_count, `inforate`,`landingurl`, `belong` FROM `#@_post` WHERE  FROM_UNIXTIME(`addtime`) >= DATE_ADD(CURDATE(), INTERVAL -{$days} DAY) AND `type`='inquiry' AND `xp_status`='' AND (`source`='商务通' OR `source`='网站留言') GROUP BY `belong`,`inforate` order by post_count DESC, `inforate` DESC";
		}

		
		$count_result = $db->query($count_sql);
		if ($count_result) {
            while ($data = $db->fetch($count_result)) {
				$collect[$data['belong']]['count'][$data['inforate']] = $data['post_count'];
			}
		}
		

		//处理数组，获得每个询盘等级的数量
		foreach($collect as $name=>$data) {
			$collect[$name]['count']['total'] = count_recursive($data['host'],3); //所有询盘总数
			$count_a = isset($collect[$name]['count']['A'])?$collect[$name]['count']['A']:0;
			$count_b = isset($collect[$name]['count']['B'])?$collect[$name]['count']['B']:0;
			$count_c = isset($collect[$name]['count']['C'])?$collect[$name]['count']['C']:0;
			$collect[$name]['count']['totalvaild'] = $count_a + $count_b + $count_c; //有效询盘总数			
		}

		if($orderby=='totalvaild')
			aasort($collect,"totalvaild");
		elseif($orderby=='total')
			aasort($collect,"total");
		

		$number = 0;
		foreach($collect as $name=>$data) {
			$total_a = isset($data['count']['A'])?$data['count']['A']:null;
			$total_b = isset($data['count']['B'])?$data['count']['B']:null;
			$total_c = isset($data['count']['C'])?$data['count']['C']:null;
			$total_d = isset($data['count']['D'])?$data['count']['D']:null;
			$total_e = isset($data['count']['E'])?$data['count']['E']:null;

			$total = isset($data['count']['total'])?$data['count']['total']:0;
			$totalvaild = isset($data['count']['totalvaild'])?$data['count']['totalvaild']:0;
			//if($total==0) break;
			$percentage = $totalvaild/$total;
			$percent_friendly = number_format( $percentage * 100, 2 ) . '%';

			$cur_user = user_get_byname($name);
			$workplace = isset($cur_user['workplace'])?$cur_user['workplace']:null;

			echo '<thead class="data-summary" style="cursor:pointer;">';
			echo '<tr data-name="'.$name.'">';
			echo   '<td>'.$number.'</td>';
			echo   '<td><b title="'.$percent_friendly.'"><i class="icon-plus"></i> '.$name.'  ('.$totalvaild.'/'.$total.')</b><small class="muted"> '.$workplace.'</small></td>';
			echo   '<td><b>'.$total_a.'</b></td>';
			echo   '<td><b>'.$total_b.'</b></td>';
			echo   '<td><b>'.$total_c.'</b></td>';
			echo   '<td><b>'.$total_d.'</b></td>';
			echo   '<td><b>'.$total_e.'</b></td>';
			echo '</tr>';
			echo '</thead>';
			//
			echo '<tbody class="data-detail">';
			foreach($data['host'] as $host=>$detail){
				$count_a = isset($detail['A'])?count($detail['A']):null;
				$count_b = isset($detail['B'])?count($detail['B']):null;
				$count_c = isset($detail['C'])?count($detail['C']):null;
				$count_d = isset($detail['D'])?count($detail['D']):null;
				$count_e = isset($detail['E'])?count($detail['E']):null;
				echo '<tr>';
				echo   '<td></td>';
				echo   '<td class="host"><i>'.$host.' ('.($count_a+$count_b+$count_c).'/'.($count_a+$count_b+$count_c+$count_d+$count_e).')</i></td>';
				echo   '<td>'.$count_a.'</td>';
				echo   '<td>'.$count_b.'</td>';
				echo   '<td>'.$count_c.'</td>';
				echo   '<td>'.$count_d.'</td>';
				echo   '<td>'.$count_e.'</td>';
				echo '</tr>';
			}
			echo '</tbody>';
			$number++;

		}

		echo           '</table>';
		echo         '</div>';
		echo       '</div>';
		echo     '</div>';
		echo   '</div>';
		
		// 加载尾部
        include ADMIN_PATH.'/admin-footer.php';
		break;
}

function count_recursive ($array, $limit) { 
    $count = 0; 
    if(!is_array ($array)) return $count; 
    foreach ($array as $id => $_array) { 
        if (is_array ($_array) && $limit > 0) { 
            $count += count_recursive ($_array, $limit - 1); 
        } else { 
            $count += 1; 
        } 
    } 
    return $count; 
}

function aasort (&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va['count'][$key];
    }
    arsort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
}

function getSomeSentence(){

$someSentence = '请大家在域名信息界面，添加你的域名信息。如果无法获取注册时间请自己填写。
询盘数目统计日期以询盘日期计算，重复及冲突询盘不计数。
统计来源，只统计商务通和网站留言，直接来信等其它方式来的询盘不计数。';

	$someSentence = explode("\n", $someSentence);
	$somechosen = $someSentence[mt_rand(0, (count($someSentence)-1) ) ];
	return $somechosen;
}