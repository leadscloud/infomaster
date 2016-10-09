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
	$name = $_USER['name'];
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
	case 'd3Data':
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
		echo '<div id="statisticrange" class="pull-left hidden-phone-comment hidden-tablet-comment">';
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
        echo               '<th>C+类询盘</th>';
		echo               '<th>C类询盘</th>';
        echo               '<th>C-类询盘</th>';
		// echo               '<th>D类询盘</th>';
		// echo               '<th>E类询盘</th>';
		echo             '</tr>';
		echo 			'</thead>';




		$where = 'WHERE `type`="inquiry" AND (`xp_status`="" OR `xp_status`="冲突") AND (`source`="商务通" OR `source`="网站留言")';

		// 郑州的询盘不显示
		// $where.= ' AND `infoclass` <> "郑州SEO技术人员" ';

		if ($startdate) {
			$conditions[] = sprintf("`addtime` > '%s'",esc_sql($startdate));
		}
		if ($enddate) {
			$conditions[] = sprintf("`addtime` < '%s'",esc_sql($enddate));
		}
		if($conditions) {
			$where.= ' AND '.implode(' AND ' , $conditions);
		}else{
			$where = "WHERE FROM_UNIXTIME(`addtime`) >= DATE_ADD(CURDATE(), INTERVAL -{$days} DAY) AND `type`='inquiry' AND (`xp_status`='' OR `xp_status`='冲突') AND (`source`='商务通' OR `source`='网站留言')";
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

                // if host is not empty, re_determine_url
                /**
                if(current_user_can('belong-detail',false)) {
                if(!empty($host) && empty($data['belong'])){
                    // print("test ");
                    // print($url);
                    $new = determine_url($url,'网站所属人');
                    // print('new '.$new. ' ');
                    if($new){
                        // print("have new belong");
                        post_edit($data['postid'],array('belong'=>$new));
                    }
                }
                }
                */


			}
		}

		if($where){
			$count_sql = "SELECT COUNT(`postid`) as post_count, `inforate`,`landingurl`, `belong` FROM `#@_post` {$where} GROUP BY `belong`,`inforate` order by post_count DESC, `inforate` DESC";
		}else{
			$count_sql = "SELECT COUNT(`postid`) as post_count, `inforate`,`landingurl`, `belong` FROM `#@_post` WHERE  FROM_UNIXTIME(`addtime`) >= DATE_ADD(CURDATE(), INTERVAL -{$days} DAY) AND `type`='inquiry' AND (`xp_status`='' OR `xp_status`='冲突') AND (`source`='商务通' OR `source`='网站留言') GROUP BY `belong`,`inforate` order by post_count DESC, `inforate` DESC";
		}

        $domain_conditions = array();
        $domain_where = 'WHERE `status`="approved" ';
        if ($startdate) {
			$domain_conditions[] = sprintf("`addtime` > FROM_UNIXTIME('%s')",esc_sql($startdate));
		}
		if ($enddate) {
			$domain_conditions[] = sprintf("`addtime` < FROM_UNIXTIME('%s')",esc_sql($enddate));
		}

        if($domain_conditions) {
			$domain_where.= ' AND '.implode(' AND ' , $domain_conditions);
		}else{
            $domain_where = "WHERE `status`='approved' AND `addtime` >= DATE_ADD(CURDATE(), INTERVAL -{$days} DAY)";
        }
        $domain_sql = "SELECT COUNT(`id`) as domain_count, `author` FROM `#@_domain` {$domain_where} GROUP BY `author`";
        $domain_result = $db->query($domain_sql);
        if ($domain_result) {
            while ($data = $db->fetch($domain_result)) {
				$collect[$data['author']]['count']['domain'] = $data['domain_count'];
			}
		}

        $domain_sql2 = "SELECT COUNT(`id`) as domain_count, `author` FROM `#@_domain` {$domain_where} AND (`domain` LIKE '%.ga%' OR `domain` LIKE '%.cf%' OR `domain` LIKE '%.tk%' OR `domain` LIKE '%.gq%') GROUP BY `author`";
        $domain_result2 = $db->query($domain_sql2);
        if ($domain_result2) {
            while ($data = $db->fetch($domain_result2)) {
				$collect[$data['author']]['count']['domain_free'] = $data['domain_count'];
			}
		}


		$count_result = $db->query($count_sql);
		if ($count_result) {
            while ($data = $db->fetch($count_result)) {
				$collect[$data['belong']]['count'][$data['inforate']] = $data['post_count'];
			}
		}
		$current_collect = current($collect);
		if(count($collect)==1 && key($collect)==null && empty($current_collect['host'])){
			$current_array = current($collect);
			if(empty($current_array['host'])){
				$collect[key($collect)]['host']=array();
			}
		}

        $disable_user_query = $db->query("select `name` FROM `#@_user` WHERE `status`=1 ");
        $disable_user = array();
        while ($data = $db->fetch($disable_user_query)) {
            $disable_user[] = $data['name'];
        }

		//处理数组，获得每个询盘等级的数量
		foreach($collect as $name=>$data) {
			$collect[$name]['count']['total'] = isset($data['host'])?count_recursive($data['host'],3):0; //所有询盘总数
			$count_a = isset($collect[$name]['count']['A'])?$collect[$name]['count']['A']:0;
			$count_b = isset($collect[$name]['count']['B'])?$collect[$name]['count']['B']:0;
            $count_c = isset($collect[$name]['count']['C'])?$collect[$name]['count']['C']:0;
            $count_c1 = isset($collect[$name]['count']['C+'])?$collect[$name]['count']['C+']:0;
			$count_c2 = isset($collect[$name]['count']['C-'])?$collect[$name]['count']['C-']:0;
			$collect[$name]['count']['totalvaild'] = $count_a + $count_b + $count_c + $count_c1; //有效询盘总数
		}

		if($orderby=='totalvaild')
			aasort($collect,"totalvaild");
		elseif($orderby=='total')
			aasort($collect,"total");


		$number = 1;
        $deny_name = array("竞价网站", "SNS推广", "邮件营销", "小语种", "西芝", "世邦", "岳静丽", "耿二轩", "张九杰", "马永红", "黄儒卿", "谢亚丹", "马芳", "刘鹏雁", "张扬涛", "高莹莹", "张文波", "牛永亮", "常文哲"); //, "张晓燕"
        $pm_name = array('岳静丽', '刘鹏雁', '张九杰', '马芳', '耿二轩', '谢亚丹', '黄儒卿', '马永红');

        $deny_name = array_merge($deny_name, $pm_name);

		$inquiryData=array();
        if(current_user_can('belong-detail',false)) {
            log_belong_details($_USER['userid']);
        }
        $style_count = 0;
		foreach($collect as $name=>$data) {
			$total_a = isset($data['count']['A'])?$data['count']['A']:null;
			$total_b = isset($data['count']['B'])?$data['count']['B']:null;
			$total_c = isset($data['count']['C'])?$data['count']['C']:null;
      $total_c1 = isset($data['count']['C+'])?$data['count']['C+']:null;
      $total_c2 = isset($data['count']['C-'])?$data['count']['C-']:null;
			$total_d = isset($data['count']['D'])?$data['count']['D']:null;
			$total_e = isset($data['count']['E'])?$data['count']['E']:null;

			$total = isset($data['count']['total'])?$data['count']['total']:0;
			$totalvaild = isset($data['count']['totalvaild'])?$data['count']['totalvaild']:0;

      $domain_count = isset($data['count']['domain'])?$data['count']['domain']:0;
      $domain_count_free = isset($data['count']['domain_free'])?$data['count']['domain_free']:0;


			if($total!=0) {
				$percentage = $totalvaild/$total;
			}else{
				$percentage = 0;
			}

			if(!empty($name) && $total > 10 && !in_array($name, $deny_name)){
				array_push($inquiryData, array('Name' => $name, 'data'=>array(
					'A' => intval($total_a),
					'B' => intval($total_b),
                    'C1' => intval($total_c1),
					'C' => intval($total_c),
                    'C2' => intval($total_c2),
					'D' => intval($total_d),
					'E' => intval($total_e)
				)));
			}




			$percent_friendly = number_format( $percentage * 100, 2 ) . '%';

			$cur_user = user_get_byname($name);
			$workplace = isset($cur_user['workplace'])?$cur_user['workplace']:null;

			echo '<thead class="data-summary" style="cursor:pointer;">';
            $style = '';
            $award_color = '';
            $special_icon = '';
            if($number > 0 && $number < 4 && !in_array($name, $deny_name) && $name !='' && $style_count < 3){
                $style = "color:red";
                if($style_count==0){
                    $award_color = 'color:#FFD700;';
                }else if($style_count==1){
                    $award_color = 'color:#C0C0C0;';
                }else{
                    $award_color = 'color:#B87333;';
                }
                $special_icon = '<i class="icon-trophy blink" style="'.$award_color.'"></i>';
                $style_count++;
            }



            if($total < 1){
                continue;
            }
            $contiue = false;
            foreach($disable_user as $du){
                if($name == $du){
                    $contiue = true;
                }
            }
            if($contiue) continue;

						// 针对郑州的设置，测试用
						$can_see_user = array("杨新鹏", "王国华");
						$dont_show_user = array("库亚飞", "朱少锋", "张晓燕", "赵双", "申庆", "牛永亮", "常文哲", "张雷", "冯建会", "孙冰", "欧本林", "岳静丽", "耿二轩", "张九杰", "马永红", "黄儒卿", "谢亚丹", "马芳", "刘鹏雁", "张扬涛", "高莹莹", "张文波");
						if (!current_user_can('ALL',false) && in_array($name, $dont_show_user) && !in_array($_USER["username"], $dont_show_user) && !in_array($_USER["username"], $can_see_user)) {
							continue;
						}

            $html_domain_count = '';
            $icon_html = '<i class="icon-circle" data-toggle="icon-circle-blank"></i> ';
            if(current_user_can('belong-detail',false) || $_USER['name'] == '朱海龙' || $_USER['workplace'] == '郑州') {
                $html_domain_count = ' - <span title="当前日期范围内新增域名数量，不包含免费域名">'.$domain_count.'-'.$domain_count_free.'='.($domain_count-$domain_count_free).'</span>';
            }
            if(current_user_can('belong-detail',false)){
                $icon_html = '<i class="icon-plus" data-toggle="icon-minus"></i> ';
            }


			echo '<tr data-name="'.$name.'" style="'.$style.'">';
			echo   '<td>'.(in_array($name, $deny_name)||$name==""?'':$number).'</td>';
      $score_total = $total_a*100 + $total_b*50 + ($total_c1 + $total_c)*10;
			echo   '<td><b title="'.$percent_friendly.'" score="'.$score_total.'">'.$icon_html.$special_icon.' <span>'.$name.'</span>  ('.$totalvaild.'/'.$total.')</b><small class="muted"> '.$workplace.'  (¥'.$score_total.$html_domain_count.')</small></td>';
			echo   '<td><b>'.$total_a.'</b></td>';
			echo   '<td><b>'.$total_b.'</b></td>';
      echo   '<td><b>'.$total_c1.'</b></td>';
			echo   '<td><b>'.$total_c.'</b></td>';
      echo   '<td><b>'.$total_c2.'</b></td>';
			// echo   '<td><b>'.$total_d.'</b></td>';
			// echo   '<td><b>'.$total_e.'</b></td>';
			echo '</tr>';
			echo '</thead>';

			echo '<tbody class="data-detail">';
            $permission_user = array('王国华', '杨新鹏', '张雷', "张晓燕", "朱少锋", "赵双", "申庆", "库亚飞", "岳静丽", "耿二轩", "牛永亮", "常文哲", "张扬涛", "高莹莹", "张文波");

            if(current_user_can('belong-detail',false) || $_USER['name'] == $name || empty($name) || $name=="竞价网站"
                ||in_array($_USER['name'], $permission_user)){
                $sub_index = 0;
    			foreach($data['host'] as $host=>$detail){
                    $sub_index++;
    				$count_a = isset($detail['A'])?count($detail['A']):null;
    				$count_b = isset($detail['B'])?count($detail['B']):null;
                    $count_c1 = isset($detail['C+'])?count($detail['C+']):null;
    				$count_c = isset($detail['C'])?count($detail['C']):null;
                    $count_c2 = isset($detail['C-'])?count($detail['C-']):null;
    				$count_d = isset($detail['D'])?count($detail['D']):null;
    				$count_e = isset($detail['E'])?count($detail['E']):null;
    				echo '<tr>';
    				echo   '<td> -- '.$sub_index.'</td>';
                    $score = $count_a*100+$count_b*50+($count_c+$count_c1)*10;
    				echo   '<td class="host" score="'.$score.'"><i>'.$host.' ('.($count_a+$count_b+$count_c+$count_c1).'/'.($count_a+$count_b+$count_c+$count_c1+$count_c2+$count_d+$count_e).')</i></td>';
    				echo   '<td>'.$count_a.'</td>';
    				echo   '<td>'.$count_b.'</td>';
                    echo   '<td>'.$count_c1.'</td>';
                    echo   '<td>'.$count_c.'</td>';
    				echo   '<td>'.$count_c2.'</td>';
    				// echo   '<td>'.$count_d.'</td>';
    				// echo   '<td>'.$count_e.'</td>';
    				echo '</tr>';
    			}
            }else{
               echo '<tr>';
               echo   '<td></td><td colspan="6" style="text-align:left;font-size:1em;">You need permission to view. ^_^ </td>';
               echo '</tr>';
            }
			echo '</tbody>';
            if (!in_array($name, $deny_name) && $name != '') {
                $number++;
            }

		}
		echo           '</table>';

		echo '<style>
path {  stroke: #fff; }
path:hover {  opacity:0.9; }
rect:hover {  fill:blue; }
.axis {  font: 10px sans-serif; }
.legend tr{    border-bottom:1px solid grey; }
.legend tr:first-child{    border-top:1px solid grey; }

.axis path,
.axis line {
  fill: none;
  stroke: #000;
  shape-rendering: crispEdges;
}

.x.axis path {  display: none; }
.legend{
    margin-bottom:76px;
    display:inline-block;
    border-collapse: collapse;
    border-spacing: 0px;
}
.legend td{
    padding:4px 5px;
    vertical-align:bottom;
}
.legendFreq, .legendPerc{
    align:right;
    width:50px;
}

.d3-tip {
  line-height: 1;
  font-weight: bold;
  padding: 12px;
  background: rgba(0, 0, 0, 0.8);
  color: #fff;
  border-radius: 2px;
}

/* Creates a small triangle extender for the tooltip */
.d3-tip:after {
  box-sizing: border-box;
  display: inline;
  font-size: 10px;
  width: 100%;
  line-height: 1;
  color: rgba(0, 0, 0, 0.8);
  content: "\25BC";
  position: absolute;
  text-align: center;
}

/* Style northward tooltips differently */
.d3-tip.n:after {
  margin: -1px 0 0 0;
  top: 100%;
  left: 0;
}

</style>';

		echo '<script src="http://d3js.org/d3.v3.min.js"></script>';
		echo '<script src="http://labratrevenge.com/d3-tip/javascripts/d3.tip.v0.6.3.js"></script>';
		echo "<script>var inquiryDataJson='" . json_encode($inquiryData). "';</script>";
		echo '<script>var inquiryData= JSON.parse(inquiryDataJson);</script>';
		echo '<script>function dashboard(id, fData){
    var barColor = "steelblue";
    //function segColor(c){ return {A:"#499675", B:"#61A977",C1:"#7EBC76", C:"#7EBC76", C2:"#7EBC76", D:"#C6DE72", E:"#F0ED73"}[c]; }
    function segColor(c){ return {A:"#109618", B:"#3366cc",C1:"#E81CAB", C:"#ff9900", C2:"#FFDE80", D:"#990099", E:"#dc3912"}[c]; }

    // compute total for each state.
    fData.forEach(function(d){d.total=d.data.A+d.data.B+d.data.C1+d.data.C+d.data.C2+d.data.D+d.data.E;});

    // function to handle histogram.
    function histoGram(fD){
        var hG={},    hGDim = {t: 60, r: 0, b: 30, l: 40};
        hGDim.w = 900 - hGDim.l - hGDim.r,
        hGDim.h = 500 - hGDim.t - hGDim.b;

        //create svg for histogram.
        var hGsvg = d3.select(id).append("svg")
            .attr("width", hGDim.w + hGDim.l + hGDim.r)
            .attr("height", hGDim.h + hGDim.t + hGDim.b).append("g")
            .attr("transform", "translate(" + hGDim.l + "," + hGDim.t + ")");

        // create function for x-axis mapping.
        var x = d3.scale.ordinal().rangeRoundBands([0, hGDim.w], 0.1)
                .domain(fD.map(function(d) { return d[0]; }));
        //.sort(function(a,b){return b[1] - a[1]})

        var y = d3.scale.linear().range([hGDim.h, 0])
        .domain([0, d3.max(fD, function(d) { return d[1]; })]);

        var tip = d3.tip()
		  .attr(\'class\', "d3-tip")
		  .offset([-10, 0])
		  .html(function(d) {
		  	console.log(d);
		    return "<span style=\'color:#fff\'>" + d + "</span>";
		  })

        // Add x-axis to the histogram svg.
        hGsvg.append("g").attr("class", "x axis")
            .attr("transform", "translate(0," + hGDim.h + ")")
            .call(d3.svg.axis().scale(x).orient("bottom"));
        // add y-axis
        hGsvg.append("g").attr("class", "y axis")
            .call(d3.svg.axis().scale(y).orient("left"))
            .append("text")
            .attr("transform", "rotate(-90)")
            .attr("y", 6)
            .attr("dy", ".71em")
            .style("text-anchor", "end")
            .text("询盘数");

        // Create function for y-axis map.
        var y = d3.scale.linear().range([hGDim.h, 0])
                .domain([0, d3.max(fD, function(d) { return d[1]; })]);

        // Create bars for histogram to contain rectangles and freq labels.
        var bars = hGsvg.selectAll(".bar").data(fD).enter()
                .append("g").attr("class", "bar");

        bars.call(tip);



        //create the rectangles.
        bars.append("rect")
            .attr("x", function(d) { return x(d[0]); })
            .attr("y", function(d) { return y(d[1]); })
            .attr("width", x.rangeBand())
            .attr("height", function(d) { return hGDim.h - y(d[1]); })
            .attr("fill",barColor)
            .on("mouseover",mouseover)// mouseover is defined below.
            .on("mouseout",mouseout);// mouseout is defined below.

        //Create the frequency labels above the rectangles.
        bars.append("text").text(function(d){ return d3.format(",")(d[1])})
            .attr("x", function(d) { return x(d[0])+x.rangeBand()/2; })
            .attr("y", function(d) { return y(d[1])-5; })
            .attr("text-anchor", "middle");


        function mouseover(d){ tip.show(d); // utility function to be called on mouseover.
            // filter for selected state.
            var st = fData.filter(function(s){ return s.Name == d[0];})[0],
                nD = d3.keys(st.data).map(function(s){ return {type:s, data:st.data[s]};});

            // call update functions of pie-chart and legend.
            pC.update(nD);
            leg.update(nD);
        }

        function mouseout(d){   tip.hide(); // utility function to be called on mouseout.
            // reset the pie-chart and legend.
            pC.update(tF);
            leg.update(tF);
        }

        // create function to update the bars. This will be used by pie-chart.
        hG.update = function(nD, color){
            // update the domain of the y-axis map to reflect change in frequencies.
            y.domain([0, d3.max(nD, function(d) { return d[1]; })]);

            // Attach the new data to the bars.
            var bars = hGsvg.selectAll(".bar").data(nD);

            // transition the height and color of rectangles.
            bars.select("rect").transition().duration(500)
                .attr("y", function(d) {return y(d[1]); })
                .attr("height", function(d) { return hGDim.h - y(d[1]); })
                .attr("fill", color);

            // transition the frequency labels location and change value.
            bars.select("text").transition().duration(500)
                .text(function(d){ return d3.format(",")(d[1])})
                .attr("y", function(d) {return y(d[1])-5; });
        }
        return hG;
    }

    // function to handle pieChart.
    function pieChart(pD){
        var pC ={},    pieDim ={w:250, h: 250};
        pieDim.r = Math.min(pieDim.w, pieDim.h) / 2;

        // create svg for pie chart.
        var piesvg = d3.select(id).append("svg")
            .attr("width", pieDim.w).attr("height", pieDim.h).append("g")
            .attr("transform", "translate("+pieDim.w/2+","+pieDim.h/2+")");

        // create function to draw the arcs of the pie slices.
        var arc = d3.svg.arc().outerRadius(pieDim.r - 10).innerRadius(0);

        // create a function to compute the pie slice angles.
        var pie = d3.layout.pie().sort(null).value(function(d) { return d.data; });

        // Draw the pie slices.
        piesvg.selectAll("path").data(pie(pD)).enter().append("path").attr("d", arc)
            .each(function(d) { this._current = d; })
            .style("fill", function(d) { return segColor(d.data.type); })
            .on("mouseover",mouseover).on("mouseout",mouseout);

        // create function to update pie-chart. This will be used by histogram.
        pC.update = function(nD){
            piesvg.selectAll("path").data(pie(nD)).transition().duration(500)
                .attrTween("d", arcTween);
        }
        // Utility function to be called on mouseover a pie slice.
        function mouseover(d){
            // call the update function of histogram with new data.
            hG.update(fData.map(function(v){
                return [v.Name,v.data[d.data.type]];}),segColor(d.data.type));
        }
        //Utility function to be called on mouseout a pie slice.
        function mouseout(d){
            // call the update function of histogram with all data.
            hG.update(fData.map(function(v){
                return [v.Name,v.total];}), barColor);
        }
        // Animating the pie-slice requiring a custom function which specifies
        // how the intermediate paths should be drawn.
        function arcTween(a) {
            var i = d3.interpolate(this._current, a);
            this._current = i(0);
            return function(t) { return arc(i(t));    };
        }
        return pC;
    }

    // function to handle legend.
    function legend(lD){
        var leg = {};

        // create table for legend.
        var legend = d3.select(id).append("table").attr("class","legend");

        // create one row per segment.
        var tr = legend.append("tbody").selectAll("tr").data(lD).enter().append("tr");

        // create the first column for each segment.
        tr.append("td").append("svg").attr("width", "16").attr("height", "16").append("rect")
            .attr("width", "16").attr("height", "16")
			.attr("fill",function(d){ return segColor(d.type); });

        // create the second column for each segment.
        tr.append("td").text(function(d){ return d.type;});

        // create the third column for each segment.
        tr.append("td").attr("class","legendFreq")
            .text(function(d){ return d3.format(",")(d.data);});

        // create the fourth column for each segment.
        tr.append("td").attr("class","legendPerc")
            .text(function(d){ return getLegend(d,lD);});

        // Utility function to be used to update the legend.
        leg.update = function(nD){
            // update the data attached to the row elements.
            var l = legend.select("tbody").selectAll("tr").data(nD);

            // update the frequencies.
            l.select(".legendFreq").text(function(d){ return d3.format(",")(d.data);});

            // update the percentage column.
            l.select(".legendPerc").text(function(d){ return getLegend(d,nD);});
        }

        function getLegend(d,aD){ // Utility function to compute percentage.
            return d3.format("%")(d.data/d3.sum(aD.map(function(v){ return v.data; })));
        }

        return leg;
    }

    // calculate total frequency by segment for all state.
    var tF = ["A","B","C1","C","C2","D","E"].map(function(d){
        return {type:d, data: d3.sum(fData.map(function(t){ return t.data[d];}))};
    });

    // calculate total frequency by state for all segment.
    var sF = fData.map(function(d){return [d.Name,d.total];});

    var hG = histoGram(sF), // create the histogram.
        pC = pieChart(tF), // create the pie-chart.
        leg= legend(tF);  // create the legend.
}

</script>';



		echo         '</div>';
		echo       '</div>';
		echo '<div id="dashboard" class="hidden-phone hidden-tablet"></div>';
		echo '<script>dashboard("#dashboard",inquiryData);</script>';
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

$someSentence = '域名请添加到域名管理里，不要添加到规则中，二级域名属于自己的话不用添加到规则中。
询盘数目统计日期以询盘日期计算，重复及冲突询盘不计数。
统计来源，只统计商务通和网站留言，直接来信等其它方式来的询盘不计数。';

	$someSentence = explode("\n", $someSentence);
	$somechosen = $someSentence[mt_rand(0, (count($someSentence)-1) ) ];
	return $somechosen;
}
