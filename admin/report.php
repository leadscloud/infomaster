<?php
// 文件名
$php_file = isset($php_file) ? $php_file : 'report.php';
// 加载公共文件
include dirname(__FILE__).'/admin.php';

// 查询管理员信息
$_USER = user_current();
// 方法
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;
$customerid2 = isset($_REQUEST['customerid'])?$_REQUEST['customerid']:null;

switch ($method) {
    // 强力插入
    case 'new':
        system_head('title', '添加新信息');
        current_user_can('post-new');
		system_head('styles', array('css/bootstrap.datepicker'));
		system_head('scripts',array('js/datetimepicker'));
		system_head('styles', array('css/chosen'));
		system_head('scripts',array('js/jquery.chosen'));
		system_head('scripts',array('js/shortcuts'));
		system_head('scripts',array('js/post'));
	    system_head('loadevents','post_manage_init');
		//system_head('loadevents','datepicker');
	    include ADMIN_PATH.'/admin-header.php';
	    post_manage_page('add');	    
	    include ADMIN_PATH.'/admin-footer.php';
	    break;
	// 编辑
	case 'edit':
		$postid = isset($_GET['postid'])?$_GET['postid']:0;
        if ('page.php' == $php_file) {
            // 所属
            $parent_file = 'page.php';
            // 重置标题
            system_head('title', '编辑询盘信息');
            // 权限检查
            current_user_can('page-edit');
        } else {
            // 所属
            $parent_file = 'report.php';
            // 重置标题
            system_head('title', '编辑询盘信息');
            // 权限检查
			if(!current_user_can('post-edit',false)) {
				if(current_user_can('post-view-all',false))
					redirect(PHP_FILE.'?method=view&postid='.$postid);
				else
					current_user_can('post-edit');
			}
        }
        //system_head('styles', array('css/post'));
        //system_head('scripts',array('js/xheditor'));
		
		system_head('styles', array('css/bootstrap.datepicker'));
		system_head('scripts',array('js/datetimepicker'));
		system_head('styles', array('css/chosen'));
		system_head('scripts',array('js/jquery.chosen'));
		system_head('scripts',array('js/shortcuts'));
		system_head('scripts',array('js/post'));
	    system_head('loadevents','post_manage_init');
		//system_head('loadevents','datepicker');
	    include ADMIN_PATH.'/admin-header.php';
	    post_manage_page('edit');	    
	    include ADMIN_PATH.'/admin-footer.php';
	    break;
	// 查看/浏览
	case 'view':
        // 所属
        $parent_file = 'report.php';
        // 重置标题
        system_head('title', '查看询盘信息');
        // 权限检查
		current_user_can('post-view-all');

		system_head('scripts',array('js/post'));
	    system_head('loadevents','post_view_init');
		//system_head('loadevents','datepicker');
	    include ADMIN_PATH.'/admin-header.php';
	    post_view_page('view');	    
	    include ADMIN_PATH.'/admin-footer.php';
	    break;
    // 批量动作
	case 'delete':
	    $action  = isset($_POST['action'])?$_POST['action']:null;
	    $listids = isset($_POST['listids'])?$_POST['listids']:null;
	    if (empty($listids)) {
	    	ajax_error('你没有选择任何项目。');
	    }
		current_user_can('post-delete');
		foreach ($listids as $postid) {
			post_delete($postid);
		}
		ajax_success('信息已删除',"InfoSYS.redirect('".referer()."');");
		break;
	// 导出数据
	case 'export':
		break;
	// 保存
	case 'save':
		$postid = isset($_POST['postid'])?$_POST['postid']:0;
	    current_user_can($postid?'post-edit':'post-new');
        if (validate_is_post()) {
            $referer  = referer(PHP_FILE,false);
			
			$sortid				= isset($_POST['sortid'])?$_POST['sortid']:0;
			$type				= isset($_POST['type'])?$_POST['type']:'inquiry';
			$operational		= isset($_POST['operational'])?$_POST['operational']:null;
			$source				= isset($_POST['source'])?$_POST['source']:null;
			$language			= isset($_POST['language'])?$_POST['language']:null;
			$keywords			= isset($_POST['keywords'])?$_POST['keywords']:null;
			$refererurl			= isset($_POST['refererurl'])?$_POST['refererurl']:null;
			$landingurl			= isset($_POST['landingurl'])?$_POST['landingurl']:null;
			$sesource			= isset($_POST['sesource'])?$_POST['sesource']:null;
			$auction			= isset($_POST['auction'])?$_POST['auction']:null;
			$producttype		= isset($_POST['producttype'])?$_POST['producttype']:null;
			$country			= isset($_POST['country'])?$_POST['country']:null;
			$customer_country	= isset($_POST['customer_country'])?$_POST['customer_country']:null;
			$province			= isset($_POST['province'])?$_POST['province']:null;
			$email				= isset($_POST['email'])?$_POST['email']:null;
			$phone				= isset($_POST['phone'])?$_POST['phone']:null;
			$inforate			= isset($_POST['inforate'])?$_POST['inforate']:null;
			$remarks			= isset($_POST['remarks'])?$_POST['remarks']:null;
			//$identifier			= isset($_POST['identifier'])?$_POST['identifier']:null;
			$infoclass			= isset($_POST['infoclass'])?$_POST['infoclass']:$_USER['usergroup'];
			$infomember			= isset($_POST['infomember'])?$_POST['infomember']:$_USER['nickname'];
			$chatlog			= isset($_POST['chatlog'])?$_POST['chatlog']:null;
			$datetime			= isset($_POST['add_date'])?$_POST['add_date']:time();
			$addtime			= isset($_POST['add_date'])?$_POST['add_date']:time();
			$serial				= 1;
			

			$xp_status			= isset($_POST['xp_status'])?$_POST['xp_status']:null;
			$agency				= isset($_POST['agency'])?$_POST['agency']:null;
			$device				= isset($_POST['device'])?$_POST['device']:null;

			$category = isset($_POST['category'])?$_POST['category']:array();
			
			//询盘等级字段
			$materials			= isset($_POST['materials'])?$_POST['materials']:null;
			$output				= isset($_POST['output'])?$_POST['output']:null;
			$have_phone			= isset($_POST['have_phone'])?$_POST['have_phone']:null;
			$have_email			= isset($_POST['have_email'])?$_POST['have_email']:null;
			$have_name			= isset($_POST['have_name'])?$_POST['have_name']:null;
			$come_to_visit		= isset($_POST['come_to_visit'])?$_POST['come_to_visit']:null;
			$old_customer		= isset($_POST['old_customer'])?$_POST['old_customer']:null;
			$own_use			= isset($_POST['own_use'])?$_POST['own_use']:null;
			$specific_product	= isset($_POST['specific_product'])?$_POST['specific_product']:null;
			$manual_rate		= isset($_POST['manual_rate'])?$_POST['manual_rate']:null;

			$i1			= isset($_POST['i1'])?$_POST['i1']:null;
			$i2			= isset($_POST['i2'])?$_POST['i2']:null;
			$i3			= isset($_POST['i3'])?$_POST['i4']:null;
			$i4			= isset($_POST['i4'])?$_POST['i4']:null;
			$i5			= isset($_POST['i5'])?$_POST['i5']:null;
			$i6			= isset($_POST['i6'])?$_POST['i6']:null;
			$i7			= isset($_POST['i7'])?$_POST['i7']:null;
			$p1			= isset($_POST['p1'])?$_POST['p1']:null;
			$p2			= isset($_POST['p2'])?$_POST['p2']:null;
			$p3			= isset($_POST['p3'])?$_POST['p3']:null;
			$p4			= isset($_POST['p4'])?$_POST['p4']:null;
			$p5			= isset($_POST['p5'])?$_POST['p5']:null;
			$p6			= isset($_POST['p6'])?$_POST['p6']:null;
			$p7			= isset($_POST['p7'])?$_POST['p7']:null;
			$l1			= isset($_POST['l1'])?$_POST['l1']:null;
			$l2			= isset($_POST['l2'])?$_POST['l2']:null;
			$l3			= isset($_POST['l3'])?$_POST['l3']:null;
			$l4			= isset($_POST['l4'])?$_POST['l4']:null;
			$l5			= isset($_POST['l5'])?$_POST['l5']:null;
			$l6			= isset($_POST['l6'])?$_POST['l6']:null;			
			$hand_v		= isset($_POST['hand_v'])?$_POST['hand_v']:null;
			$is_complete= isset($_POST['is_complete'])?$_POST['is_complete']:null;
			
			$survey = array(
				'material'	=> $materials,
				'output'	=> $output,
				'visit'		=> $i1,
				'exist'		=> $i2,
				'have_customer'	=> $i6,
				'have_name'	=> $i7,
				'p_sale'	=> $p1,
				'p_high'	=> $p2,
				'p_opt'		=> $p3,
				'p_used'	=> $p4,
				'p_rival'	=> $p5,
				'p_ouropt'	=> $p6,
				'p_non'		=> $p7,
				'l_stone'	=> $l1,
				'l_process'	=> $l2,
				'l_mill'	=> $l3,
				'l_sand'	=> $l4,
				'l_mining'	=> $l5,
				'l_industry'=> $l6,
				'hand_v'	=> $hand_v,
				'is_complete'=> $is_complete
			);
			$description = serialize($survey); 
			
			
            if ('report.php' == $php_file) {
				//必须选择一个分类目录
                validate_check('sortid',VALIDATE_EMPTY,'请选择一个主分类(主部门)');
            }
			
			if($producttype==null)
				validate_check(array('producttype',VALIDATE_EMPTY,'请选择产品类型。'));

            validate_check(array(
				array('source',VALIDATE_EMPTY,'请选择询盘来源。'),
				array('language',VALIDATE_EMPTY,'请选择询盘所属语种。'),
				array('agency',VALIDATE_LENGTH,'你输入的媒介内容长度最多 %d 字符。',0,4),
				//array('phone',VALIDATE_IS_NUMERIC,'电话号码必须是数字。'),
				//array('email',VALIDATE_IS_EMAIL,'请输入正确的邮箱地址。'),
				array('refererurl',VALIDATE_IS_URL,'请输入正确的网址。'),
				array('landingurl',VALIDATE_IS_URL,'请输入正确的网址。'),
				array('infoclass',VALIDATE_EMPTY,'信息组无法获取，请先设置你所在用户组。'),
				array('infomember',VALIDATE_EMPTY,'信息员名称获取失败，请设置你的昵称。')

            )); 

            //业务人员必须是联系人列表中的。
            if(!is_operational($operational)){
            	ajax_alert('业务人员必须是联系人列表中的。');
            }
			
			//检测网站类型
			if($landingurl!=null) {
				$auction = determine_url($landingurl,'网站类型','优化站');
			}
			
			//检测搜索引擎来源
			if($refererurl!=null) {
				//$sesource = determine_url($refererurl,'搜索引擎来源'); 2013-12-14修改成以下
				$sesource = detect_se($refererurl); 
			}
			
			//计算当前询盘信息序号
			/*
			$prepost = user_prepost($_USER['userid'],$postid);
			if(isset($prepost['serial'])) {
				$serial = $prepost['serial'] + 1;
			}
			*/
			//2013-8-2修改如下
			$userid				= isset($_POST['userid'])?$_POST['userid']:$_USER['userid'];
			//$serial = post_pre_count($userid,$postid) + 1;
			
			$serial = post_count_curdate($userid,$postid);
			
			
			//检测国家所属大洲
			$continent	= check_continent($country);
			//检测网站所属人
			$belong	= determine_url($landingurl,'网站所属人');
			if(!$belong)
				$belong = determine_url($refererurl,'网站所属人');
			
			/*
			* 计算询盘等级
			*/
			$inforate = 'D';
			//符合D等级的
			/*
			if( $p7 ) {//二手破磨设备 or 竞争对手产品
				$inforate = 'D';
			}
			*/
			//符合C等级的
			if( $p4 || $p5 ) {
				$inforate = 'C';
			}
			//B等级
			if(instr('代理',$producttype)) { //产品类型=代理
				if( ($p1 || $p2 || $p3 || $p6) && $i6 ) //如果是否有客户=有，产品=可售单机或者辅助设备或者我司配件，等级为B；
					$inforate = 'B';
			}else {
			    if($materials && $materials!='不适合' && ($p1 || $p2 || $p3 || $p6) )
					$inforate = 'B';
			}

			if($materials && $materials!='不适合' && $is_complete && ($p1 || $p2 || $p3 || $p6)) {
				$inforate = 'B';
			}
			/*
			if($i5){
				if($materials && ($p1 || $p2 || $p3 || $p6) )
					$inforate = 'B';
			}elseif($i7){
				if( $p1 || $p2 || $p3 || $p6 )
					$inforate = 'B';
			}
			*/
			
			if( ($p1 || $p2 || $p3 || $p6) && ($i1 || $i2) ) { //可售单机或者辅助设备或者我司配件 AND 老客户 或 意向来访 AND 自用
				$inforate = 'A';
			}

			//针对代理和其它物料的,最高等级为B
			if((instr('代理',$producttype) || $i6) || $materials=='其它' && $inforate == 'A')
				$inforate = 'B';

			if($country=='印度') {
				switch($inforate){
					case  'B';
						$inforate = 'C';
						break;
					case  'C';
						$inforate = 'D';
						break;
					case  'D';
						$inforate = 'E';
						break;
				}
			}

			if($i7 && $materials && $is_complete) {
				if($p2) $inforate = 'A';
				if($p1 && instr('砂石',$producttype) && $output>=50 && $country!='印度') $inforate = 'A';
				if($p1 && instr('矿山',$producttype) && $output>=30) $inforate = 'A';
				if($p1 && instr('磨机',$producttype) && $output>=1) $inforate = 'A';
				if($p1 && instr('制砂机',$producttype) && $output>=10) $inforate = 'A';
				if($p1 && instr('砂石',$producttype) && $output>=150) $inforate = 'A';

				//if($p1 && instr('砂石',$producttype) && $output>=50 && $country=='印度') $inforate = 'B';
			}
			
			//不合适的物料一般为D，印度的为E，2013-8-5添加，根据邵琳提的要求
			if($materials=='不适合') {
				$inforate = 'D';
				if($country=='印度') {
					$inforate = 'E';
				}
			}
			
			
			if(!empty($hand_v))
				$inforate = $hand_v;
			//等级判定结束
			
			/**
			 * 新的等级判定标准
			 * $output  产量
			 * $have_phone 是否有电话
			 * $have_email 是否有邮箱
			 * $have_name 有否有客户姓名
			 * $come_to_visit 是否意向来访
			 * $old_customer  是否为老客户
			 * $own_use 是否自用
			 * $specific_product 具体产品
			 * $producttype 产品类型
			 */
			$inforate = 'E';
			$can_sale = true; // 公司允许销售的产品
			$is_india = $country=='印度'?true:false;
			$b_materials = ($materials && $materials!='不适合')?true:false;
			if(trim($specific_product)=='二手破磨设备' || trim($specific_product)=='竞争对手产品' || trim($specific_product)=='非我司配件')
				$can_sale = false;
			//如果客户意向来访或者客户是老客户，产品为公司允许销售的产品，联系方式只要电话和邮箱有其中一个即可判定为A类询盘
			if(($come_to_visit || $old_customer) && $can_sale && ($have_phone || $have_email))
				$inforate = rating_review($inforate,'A');
			//1.如果产品为我司配件，国家不为空，产品类型非代理（即为建筑破碎、磨机、矿山破碎等），联系方式电话邮箱有一个即可，物料符合要求，且是公司允许销售的产品，为B类询盘
			//2.如果产品为我公司可售的配套辅助设备，国家不为空，产品类型非代理（即为建筑破碎、磨机、矿山破碎等），联系方式电话邮箱有一个即可，物料符合要求，且是公司允许销售的产品，为B类询盘
			if(($specific_product=='我司配件' || $specific_product=='配套辅助设备') && $country && $producttype!='代理' && ($have_phone || $have_email) && $b_materials && $can_sale)
				$inforate = rating_review($inforate,'B');
			

			// 机器自用，非印度国家
			if(($own_use||(!instr('代理',$producttype))) && !$is_india){
				if($have_name && $specific_product=='高附加值产品' && $have_email && $have_phone && $b_materials)
					if((instr('建筑破碎',$producttype) && $output>=50) || (instr('矿山用破碎',$producttype) && $output>=30) || (instr('磨机',$producttype) && $output>=1) || (instr('制砂机',$producttype) && $output>=10)  || (instr('球磨',$producttype) && $output>=5))
						$inforate = rating_review($inforate,'A');

				if($can_sale && ($have_phone || $have_email) && $b_materials)
					if((instr('建筑破碎',$producttype) && $output>=10) || (instr('矿山用破碎',$producttype) && $output>=10) || (instr('磨机',$producttype) && $output<1) || (instr('制砂机',$producttype) && $output<10)  || (instr('球磨',$producttype) && $output<5))
						$inforate = rating_review($inforate,'B');
				if($have_name && $specific_product=='水泥生产线' && (!instr('代理',$producttype)) && ($have_phone || $have_email) && $b_materials && $output >= 1)
					$inforate = rating_review($inforate,'B');
				if($specific_product=='二手破磨设备' && (!instr('代理',$producttype)) && ($have_phone || $have_email))
					$inforate = rating_review($inforate,'C');
				if($specific_product=='非我司配件' && (!instr('代理',$producttype)) && ($have_phone || $have_email))
					$inforate = rating_review($inforate,'D');
				if($have_phone || $have_email)
					$inforate = rating_review($inforate,'D');
			} else {
				if($have_name && $specific_product=='高附加值产品' && $have_email && $have_phone && $b_materials)
					if((instr('建筑破碎',$producttype) && $output>=50) || (instr('矿山用破碎',$producttype) && $output>=30) || (instr('磨机',$producttype) && $output>=1) || (instr('制砂机',$producttype) && $output>=10)  || (instr('球磨',$producttype) && $output>=5))
						$inforate = rating_review($inforate,'A');
				if($have_name && $can_sale && ($have_phone || $have_email) && $b_materials)
					if((instr('建筑破碎',$producttype) && $output>=100) || (instr('矿山用破碎',$producttype) && $output>=10) || (instr('磨机',$producttype) && $output>=0.5) || (instr('制砂机',$producttype) && $output<10)  || (instr('球磨',$producttype) && $output<5))
						$inforate = rating_review($inforate,'B');
				if($specific_product=='水泥生产线' && (!instr('代理',$producttype)) && ($have_phone || $have_email) && $b_materials)
					$inforate = rating_review($inforate,'C');
				if($specific_product=='二手破磨设备' && (!instr('代理',$producttype)) && ($have_phone || $have_email))
					$inforate = rating_review($inforate,'D');
				if($specific_product=='非我司配件' && (!instr('代理',$producttype)) && ($have_phone || $have_email))
					$inforate = rating_review($inforate,'E');
				if($have_phone || $have_email)
					$inforate = rating_review($inforate,'E');
			}

			if($country && $specific_product=='高附加值产品' && (!instr('代理',$producttype)) && ($have_phone || $have_email))
				$inforate = rating_review($inforate,'C');
			if($country && $specific_product=='竞争对手产品' && (!instr('代理',$producttype)) && ($have_phone || $have_email))
				$inforate = rating_review($inforate,'C');
			//针对印度的处理
			if(instr('代理',$producttype) && !$is_india){
				if($inforate=='A'||$inforate=='B')
					$inforate = 'B';
			}
			if(instr('代理',$producttype) && $is_india){
				if($inforate=='A'||$inforate=='B')
					$inforate = 'B';
				if($inforate == 'C')
					$inforate = 'D';
			}
			//手动判定优先级最高
			if(!empty($manual_rate))
				$inforate = $manual_rate;
			
			
            // 安全有保证，做爱做的事吧！
            if (validate_is_ok()) {
                // 添加主分类
                if ($sortid > 0) {
                    array_unshift($category,$sortid);
                }
				
				$term_names = taxonomy_get_names($category);

				//处理询盘日期
				$addtime = strtotime($addtime);
				$addtime = $addtime - ( C( '.gmt_offset' ) * 60 * 60 );
				
                // 获取数据
                $data = array(
					'sortid'   		=> $sortid,
                    'type'     		=> $type,
                    'category' 		=> $category,
                    'keywords' 		=> esc_html(trim($keywords)),
                    'operational' 	=> $operational,
                    'remarks' 		=> esc_html($remarks),
					'source'		=> $source,
					'language'		=> $language,
					'referer'		=> $refererurl,
					'landingurl'	=> $landingurl,
					'sesource'		=> $sesource,
					'auction'		=> $auction,
					'producttype'	=> implode(",", $producttype),
					'country'		=> $country,
					'continent'		=> $continent,
					'province'		=> $province,
					'email'			=> $email,
					'phone'			=> $phone,
					'inforate'		=> $inforate,
					//'serial'		=> $serial,
					'infoclass'		=> $infoclass,
					'infomember'	=> $infomember,
					'saleunit'		=> $term_names[0],
					//'salesubunit'	=> $term_names[1],
					'chatlog'		=> $chatlog,
					'belong'		=> $belong,
					'addtime'		=> $addtime,
					//'datetime'		=> strtotime($datetime),
					'description'	=> $description,
					'xp_status'		=> $xp_status
					
				);

				if(isset($_POST['agency'])){
					$data['meta']['agency'] = $agency;
				}
				//额外字段
				if(isset($_POST['customer_country']))
					$data['meta']['customer_country'] = $customer_country;
				if(isset($_POST['output']))
					$data['meta']['output'] = $output;
				if(isset($_POST['manual_rate']))
					$data['meta']['manual_rate'] = $manual_rate;

				if(isset($_POST['device']))
					$data['meta']['device'] = $device;

				$data['meta']['have_phone'] = $have_phone;
				$data['meta']['have_email'] = $have_email;
				$data['meta']['have_name'] = $have_name;
				$data['meta']['come_to_visit'] = $come_to_visit;
				$data['meta']['old_customer'] = $old_customer;
				$data['meta']['own_use'] = $own_use;

				if(isset($_POST['specific_product']))
					$data['meta']['specific_product'] = $specific_product;

                // 更新
                if ($postid) {
					$data['remarks']	= $remarks;
					$data['serial']		= $serial;
                    post_edit($postid,$data);
                    $result = '询盘信息更新完成.';
                }
                // 强力插入
                else {
                    $data['author'] = $_USER['name'];
                    $data['userid'] = $_USER['userid'];
                    if ($post = post_add($serial,$remarks,$data)) {
                        $postid = $post['postid'];
                    }
                    $result = '信息添加成功.';
                }
				//更新询盘ID, 对于以前的信息组无法对应现在的，询盘ID不会更新。
				generate_identifier($postid);
				//
				ajax_success($result, "InfoSYS.redirect('".$referer."');");
            }
        }
	    break;
    default:
		system_head('styles', array('css/daterangepicker'));
		system_head('scripts',array('js/date'));
		system_head('scripts',array('js/daterangepicker'));
		system_head('scripts',array('js/ZeroClipboard'));
		system_head('scripts',array('js/shortcuts'));
		system_head('scripts',array('js/post'));
		
        system_head('title',  '询盘信息管理');
        current_user_can('post-list');
	    system_head('loadevents','post_list_init');
	    $model    = isset($_REQUEST['model'])?$_REQUEST['model']:'';
        $search   = isset($_REQUEST['query'])?$_REQUEST['query']:'';
		$fields	  = isset($_REQUEST['fields'])?$_REQUEST['fields']:array();
        $category = isset($_REQUEST['category'])?$_REQUEST['category']:null;
		
		$is_grp_admin = isset($_USER['GroupAdmin'])?$_USER['GroupAdmin']==0:false;
		
		$startdate = isset($_REQUEST['startdate'])?$_REQUEST['startdate']:null;
		$enddate = isset($_REQUEST['enddate'])?$_REQUEST['enddate']:null;
		
        $query    = array('page' => '$');
        $add_args = array('method' => 'new');
		
		//动作
		$action  = isset($_REQUEST['action'])?$_REQUEST['action']:null;
        // 排序方式
        //$order = 'page.php'==$php_file ? 'ASC' : 'DESC';
		$order  = isset($_REQUEST['order'])?$_REQUEST['order']:'desc';
		$orderby  = isset($_REQUEST['orderby'])?$_REQUEST['orderby']:null;
		
		//所属人
		$belong  = isset($_REQUEST['belong'])?$_REQUEST['belong']:null;
		$inforate  = isset($_REQUEST['inforate'])?$_REQUEST['inforate']:null;
		
		if($orderby)  $query['orderby'] = $orderby;
		if($order) $query['order'] = $order;
		
		list( $columns, $hidden, $sortable ) = get_column_info();

		switch ($orderby) {
			case 'adddate':
				$orderby ='datetime';
				break;
			case 'editdate':
				$orderby ='edittime';
				break;
			case 'inquirydate':
				$orderby ='addtime';
				break;
			case 'rete':
				$orderby ='info_rate';
				break;
			case 'status':
				$orderby ='xp_status';
				break;
			case 'inforate':
				$orderby ='inforate';
				break;
			default:
				if(!isset($sortable[$orderby]))
					$orderby ='postid';
				break;
		}
		
		

        $conditions = $date_conditions = array();
		// 根据时间筛选
		if ($startdate) {
			$query['startdate'] = $startdate;
			switch ($orderby) {
				case 'datetime':
					$orderdate ='datetime';
					break;
				case 'edittime':
					$orderdate ='edittime';
					break;
				case 'addtime':
					$orderdate ='addtime';
					break;
				default:
					$orderdate ='datetime';
					break;
			}
			$date_conditions[] = sprintf("`{$orderdate}` > '%s'",esc_sql($startdate));
		}
		
		if ($enddate) {
			$query['enddate'] = $enddate;
			switch ($orderby) {
				case 'datetime':
					$orderdate ='datetime';
					break;
				case 'edittime':
					$orderdate ='edittime';
					break;
				case 'addtime':
					$orderdate ='addtime';
					break;
				default:
					$orderdate ='datetime';
					break;
			}
			$date_conditions[] = sprintf("`{$orderdate}` < '%s'",esc_sql($enddate));
		}
			
        // 根据分类筛选
        if ($search || $category) {
            if ('page.php' == $php_file) {
                $where = "WHERE `p`.`type`='page'";
            } else {
                $where = "WHERE `p`.`type`='inquiry'";
            }
            if ($category) {
                $query['category'] = $category; $add_args['category'] = $category;
                $where.= sprintf(" AND (`tr`.`taxonomyid`=%d)", esc_sql($category));
            }
            if ($search) {
                $query['query'] = $search;
				$fields = array('identifier','source','infoclass','infomember','inforate','saleunit','belong','operational','language','keywords','referer','landingurl','email','phone','country','province','producttype','auction','sesource','remarks');
				if(isset($_REQUEST['fields']) && is_array($_REQUEST['fields']))
					$fields = $_REQUEST['fields'];
                foreach($fields as $field) {
                    $conditions[] = sprintf("BINARY UCASE(`p`.`%s`) LIKE UCASE('%%%%%s%%%%')",$field,esc_sql($search));
                }
				if(!empty($date_conditions))
                	$where.= ' AND ('.implode(' OR ', $conditions).')'.' AND '.implode(' AND ' , $date_conditions);
				else
					$where.= ' AND ('.implode(' OR ', $conditions).')';
            }
			//只能查看属于自己的内容,除了超级管理员
			if(!current_user_can('ALL',false) && !$is_grp_admin && !current_user_can('post-view-all',false))
				$condition_limit = " AND (`userid`='".$_USER['userid']."')";
			elseif($is_grp_admin && !current_user_can('post-view-all',false)){
				$userids = get_group_users($_USER['primary_grp']);
				$userids = implode(",",$userids);
				if($userids)
					$condition_limit = sprintf(" AND ( `userid` in (%s) )",esc_sql($userids));
			}
			else
				$condition_limit = '';
			
			$where.= $condition_limit;
			
			//所属人
			if($belong){
				$query['belong'] = $belong;
				$where.= sprintf(" AND `p`.`belong` = '%s'",esc_sql($belong));
			}
			if($inforate){
				$query['inforate'] = $inforate;
				$where.= sprintf(" AND `p`.`inforate` = '%s'",esc_sql($inforate));
			}
					
					
            $sql = "SELECT DISTINCT(`p`.`postid`) FROM `#@_post` AS `p` LEFT JOIN `#@_term_relation` AS `tr` ON `p`.`postid`=`tr`.`objectid`  LEFT JOIN `#@_post_meta` as m ON m.postid = p.postid {$where} ORDER BY `p`.`{$orderby}` {$order}";
			$count_sql = "SELECT COUNT(DISTINCT(`p`.`postid`)) FROM `#@_post` AS `p` LEFT JOIN `#@_term_relation` AS `tr` ON `p`.`postid`=`tr`.`objectid`  LEFT JOIN `#@_post_meta` as m ON m.postid = p.postid {$where} ORDER BY `p`.`{$orderby}` {$order}";
        } else {
			if ('domain.php' == $php_file) {
                $conditions[] = "`type`='domain'";
            } else {
                $conditions[] = "`type`='inquiry'";
            }
			
			if(!current_user_can('ALL',false)&&!$is_grp_admin && !current_user_can('post-view-all',false))
				$conditions[] = sprintf("`userid` = '%d'",esc_sql($_USER['userid']));
			
			
			
			if ($startdate) {
				//$conditions[] = sprintf("`datetime` > '%s'",esc_sql($startdate));
			}
			if ($enddate) {
				//$conditions[] = sprintf("`datetime` < '%s'",esc_sql($enddate));
			}
			if($is_grp_admin && !current_user_can('post-view-all',false)){
				$userids = get_group_users($_USER['primary_grp']);
				$userids = implode(",",$userids);
				if($userids)
					$conditions[] = sprintf("`userid` in (%s)",esc_sql($userids));
			}
            // 没有任何筛选条件
            $where = ' WHERE '.implode(' AND ' , $conditions);		
			if(!empty($date_conditions))
                $where.= ' AND '.implode(' AND ' , $date_conditions);
			if($belong){
				$query['belong'] = $belong;
				$where.= sprintf(" AND `belong` = '%s'",esc_sql($belong));
			}
			if($inforate){
				$query['inforate'] = $inforate;
				$where.= sprintf(" AND `inforate` = '%s'",esc_sql($inforate));
			}
				
			
            $sql = "SELECT DISTINCT(`postid`) FROM `#@_post` {$where} ORDER BY `{$orderby}` {$order}";
			$count_sql = "SELECT COUNT(DISTINCT(`postid`)) FROM `#@_post` {$where} ORDER BY `{$orderby}` {$order}";
        }
		// 导出数据
		if($action=='export') {
			set_time_limit(0);
			ini_set("memory_limit","-1");
			$db = get_conn();
			$result = $db->query($sql);
			if ($result) {
				while ($data = pages_fetch($result)) {
					$post = post_get($data['postid']);
					$post['addtime'] = date_gmt('Y-m-d H:i:s',$post['addtime']);
					$post['datetime'] = date_gmt('Y-m-d H:i:s',$post['datetime']);
					$post['edittime'] = date_gmt('Y-m-d H:i:s',$post['edittime']);
					unset ($post['type'],$post['comments'],$post['sortid'],$post['postid'],$post['description'],$post['userid']);
					unset ($post['userid'],$post['grpid'],$post['otheruser'],$post['model'],$post['author'],$post['category']);

					//把额外字段加到数组中
					$post['agency'] = isset($post['meta']['agency'])?$post['meta']['agency']:null;
					$post['device'] = isset($post['meta']['device'])?$post['meta']['device']:null;
					$rows[] = $post;
					unset($post);
				}
			}
			
			//导出数据
			//excel_export($rows);exit();
			//print_r($rows);
			//new method to export data
			include_file(COM_PATH.'/system/php-export-data.class.php');
			$excel_name = 'DS基础数据表'.date('Ymd',time());
			$exporter = new ExportDataExcel('browser', $excel_name.'.xls');
			$exporter->initialize(); // starts streaming data to web browser
			$sheet_head = array('identifier'=>'询盘ID','source'=>'来源','device'=>'转化端口','infoclass'=>'信息组部门','infomember'=>'信息员','addtime'=>'询盘日期','datetime'=>'时间','xp_status'=>'询盘状态','inforate'=>'信息评级','belong'=>'所属人','operational'=>'业务人员','saleunit'=>'销售部门','salesubunit'=>'销售组别','language'=>'语种','keywords'=>'关键词','agency'=>'媒介','referer'=>'来源网址','landingurl'=>'到访网址','country'=>'所属国家','continent'=>'大洲','producttype'=>'产品类型','auction'=>'是否竞价','sesource'=>'搜索引擎来源','remarks'=>'备注');
			$exporter->addRow($sheet_head);
			foreach($rows as $row) {
				unset($row['meta']);
				$newrow = array();
				foreach($sheet_head as $index=>$value) {
					$newrow[] = $row[$index];
				}
				$exporter->addRow($newrow);
			}
			$exporter->finalize(); // writes the footer, flushes remaining data to browser.
			exit(); // all done
			
			//print_r($rows);
			exit();
		}
		//询盘数目
		$db = get_conn();
		$post_count = $db->result($count_sql);
		//设置每页显示数目
		$posts_per_page = C($_USER['name'].'.posts_per_page');
		if ($posts_per_page) {
			pages_init($posts_per_page);
		}
        $result = pages_query($sql);
        // 分页地址
        $page_url   = PHP_FILE.'?'.http_build_query($query);

		// 加载头部
        include ADMIN_PATH.'/admin-header.php';
		
		echo '<div class="module-header">';
		echo	'<h3><i class="icon-building"></i> 询盘信息</h3>';
		echo	'</div>';
		echo '<div class="alert alert-info fade in"><button type="button" class="close" data-dismiss="alert">×</button> <i class="icon-info-sign"></i> 双击每行以编辑该信息。</div>';
		echo '<div class="tabbable">';
		echo	'<ul class="nav nav-tabs">';
		echo		'<li class="active"><a href="#customers" data-toggle="tab">所有信息 ('.$post_count.')</a></li>';
		echo	'</ul>';
		echo	'<div class="tab-content">';
		echo		'<div class="tab-pane fade active in" id="customers">';
		
		table_nav('top',$page_url);
		
		echo			'<div class="widget widget-table">';
		echo				'<div class="widget-header">';
		echo					'<h3><i class="icon- icon-border">&#xf03a;</i> 询盘列表</h3>';
		
		echo					'<div class="pull-right" style="margin-right:10px">';
		
		if(current_user_can('post-new',false)){
			echo					'<a class="btn btn-small" href="'.PHP_FILE.'?'.http_build_query($add_args).'"><i class="icon-plus"></i> 添加询盘信息</a>';
		}
		echo					'</div>';
		echo '<div id="reportrange" class="btn btn-small pull-right hidden-phone hidden-tablet" title="按添加时间过滤">';
    	echo   '<i class="icon-calendar icon-large"></i> ';
    	echo   '<span>'.($startdate==null?date_gmt("F j, Y", strtotime('-30 day')):date_gmt('m/d/Y', $startdate)) .' - '. ($enddate==null?date_gmt("F j, Y"):date_gmt('m/d/Y', $enddate)) .'</span> <span class="caret"></span>';
		echo '</div> ';

		echo				'</div>';
		
		echo			'<div class="widget-content" id="scroller">';
		echo				'<table class="table-report table table-striped table-hover table-bordered">';
		echo					'<thead>';
		echo						'<tr>';
		//显示表格头部列
		$current_url = PHP_FILE.'?'.http_build_query($query);
		$paged = isset($_GET['page'])?$_GET['page']:1;
		if (strpos($current_url,'%24') !==false )
            $current_url = str_replace('%24',$paged,$current_url);
		$current_orderby	= isset($_GET['orderby'])?$_GET['orderby']:null;
		$current_order		= isset($_GET['order'])?$_GET['order']:null;
		//list( $columns, $hidden, $sortable ) = get_column_info();
		//循环显示所有列
		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );
			$style = '';
			if ( !in_array( $column_key, $hidden ) && $column_key!='cb')
				$style = 'display:none;';

			$style = ' style="' . $style . '"';
			
			if ( 'cb' == $column_key )
				$class[] = 'check-column';
			elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) )
				$class[] = 'num';
				
			if ( isset( $sortable[$column_key] ) ) {
				list( $orderby, $desc_first ) = $sortable[$column_key];

				if ( $current_orderby == $orderby ) {
					$order = 'asc' == $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}

				$column_display_name = '<a href="' . add_query_arg( compact( 'orderby', 'order' ), $current_url )  . '"><span>' . $column_display_name . '</span> <i class="icon-angle-down"></i></a>';
			}
							
			$id = "id='$column_key'";
			if ( !empty( $class ) )
				$class = "class='" . join( ' ', $class ) . "'";
				
			echo "<th scope='col' $id $class $style>$column_display_name</th>";
		}
		echo						'</tr>';
		echo					'</thead>';
		echo					'<tbody>';
		if ($result) {		
			while ($data = pages_fetch($result)) {
				$post     = post_get($data['postid']);
				
				$edit_url = PHP_FILE.'?method=edit&postid='.$post['postid'];
				echo '<tr class="inquiry-'.$post['postid'].'">';
				//循环显示所有数据行
				foreach ( $columns as $column_name => $column_display_name ) {
					$class = "class='column-$column_name'";
					$style = '';
					if ( !in_array( $column_name, $hidden ) && $column_name!='cb')
						$style = ' style="display:none;"';
					$attributes = $class . $style;
					switch ( $column_name ) {
						case 'cb':
							echo '<td class="check-column"><input type="checkbox" name="listids[]" value="'.$post['postid'].'" /></td>';
							break;
						case 'id':
							echo '<td ' . $attributes . '>'.$post['postid'].'</td>';
							break;
						case 'identifier':
							echo '<td ' . $attributes . '> <a class="black clipboard" href="'.$edit_url.'" data-clipboard-text="'.$post['identifier'].'" title="'.$post['identifier'].'"> '.$post['identifier'].'</a> </td>';
						break;
						case 'addtime':
							echo '<td ' . $attributes . ' title="'.date_gmt('Y年m月d日 H:i:s',$post['addtime']).'">'.date_gmt('Y-m-d',$post['addtime']).'</td>';
							break;
						case 'edittime':
							echo '<td ' . $attributes . ' title="'.date_gmt('Y年m月d日 H:i:s',$post['edittime']).'">'.date_gmt('Y-m-d',$post['edittime']).'</td>';
							break;
						case 'datetime':
							echo '<td ' . $attributes . ' title="'.date_gmt('Y年m月d日 H:i:s',$post['datetime']).'">'.date_gmt('Y-m-d',$post['datetime']).'</td>';
							break;
						case 'inforate':
							echo '<td ' . $attributes . ' style="text-align:center">'.badge_rate($post['inforate']).'</td>';
							break;
						default:
							echo '<td ' . $attributes . ' title="'.$post[$column_name].'"> '.$post[$column_name].'</td>';
							break;
					}
				}
                echo '</tr>';
			}
		}else {
			echo						'<tr>';
			echo							'<td colspan="'.count($hidden).'">无记录！</td>';
			echo						'</tr>';
		}
		echo					'</tbody>';
		echo				'</table>';
		echo			'</div>';
		echo			'</div>';//end.widget
		table_nav('bottom',$page_url);
		echo		'</div>';
		echo		'<div class="tab-pane fade" id="customers2">';
		echo			'<p class="text-info" style="margin:20px">test</p>';
		echo		'</div>';
		echo		'<div class="tab-pane fade" id="customers3">';
		echo			'<p>test</p>';
		echo		'</div>';
		echo	'</div>';
		echo '</div>';
		?>
  <div class="modal hide draggable" id="ImportDataBox">
          <div class="modal-header">
              <h3>从Excel导入数据</h3>
          </div>
          <div class="modal-body">
          <?php
		  	$bytes = max_upload_size();
			$size = convert_bytes_to_hr( $bytes );
		  ?>
              <form enctype="multipart/form-data" id="import-upload-form" method="post" action="" class="">
              	<fieldset>
                	
                    <div class="control-group">
                    	
                        <div class="controls">
                        	<input class="input-file" name="importFile" id="importFile" type="file">
                            <p class="help-block">从你的电脑中选择一个文件 (最大值: <?php echo $size;?>)，文件扩展名为xls,格式与导出文件格式一致</p>
                            <input type="hidden" name="max_file_size" value="<?php echo $bytes;?>">
                        </div>
                   </div>
               </fieldset>
              </form>
          </div>
          <div class="modal-footer">
              <a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>
              <a href="#" class="btn btn-success" id="ImportExcel">确定导入</a>
          </div>
  </div>
        <?php

		// 加载尾部
        include ADMIN_PATH.'/admin-footer.php';
        break;
}

/**
 * 批量操作
 *
 * @param  $side    top|bottom
 * @param  $url
 * @return void
 */
function table_nav($side,$url) {
    global $php_file, $category, $search, $fields, $inforate, $belong, $_USER;
	$referer = referer(PHP_FILE);
	$startdate = isset($_REQUEST['startdate'])?$_REQUEST['startdate']:null;
	$enddate = isset($_REQUEST['enddate'])?$_REQUEST['enddate']:null;
    echo '<div class="table-nav pull-left">';
	echo	'<div class="btn-group">';
	echo		'<button class="btn" data-toggle="tooltip" data-original-title="返回上级URL" onclick="javascript:;InfoSYS.redirect(\''.$referer.'\')"><i class="icon-arrow-up"></i> 返回</button>';
	//echo		'<button class="btn" id="select" onclick="javascript:;"  data-toggle="button"><i class="icon-check"></i>全选</button>';
	if(current_user_can('post-delete',false)){
		echo	'<button class="btn" name="delete" data-toggle="tooltip" data-original-title="请选择后再删除"><i class="icon-remove"></i> 删除</button>';
	}
	//echo		'<button class="btn"><i class="icon-trash"></i>移到垃圾箱</button>';
	echo	'</div>';
	echo	'<div class="btn-group">';
	if(current_user_can('data-export',false)){
	echo		'<a data-toggle="tooltip" data-original-title="导出为Excel (xls)格式" class="btn ExportData" href="#"><i class="icon-download"></i> 导出数据</a> ';
	}
	if(current_user_can('data-import',false)){
	echo		'<a data-toggle="tooltip" data-original-title="导入的格式为Excel2003格式" class="btn" id="ImportData"><i class="icon-upload"></i> 导入数据</a> ';
	}
	echo	'</div>';
	
	echo	'<div class="btn-group">';
	echo		'<button class="btn" name="refresh" data-original-title="刷新当前页面"> <i class="icon-refresh"></i> 刷新</button> ';
	echo	'</div>';
	
	if($_USER['usergroup']=='SEO技术人员'){
		$query = array('belong'=>$_USER['nickname']);
		if(isset($_GET['inforate']))
			$query['inforate'] = $_GET['inforate'];
		if(isset($_GET['page']))
			$query['page'] = $_GET['page'];
		//$args = array_merge($_GET, array('belong'=>$_USER['nickname']));
		echo	'<div class="btn-group">';
		echo		'<a class="btn btn-success" href="'.PHP_FILE.'?'.http_build_query($query).'" data-original-title="只查看自己的询盘信息"> <i class="icon-user"></i> 只看自己的</a> ';
		echo	'</div>';
	}
	
	echo		'<input type="hidden" name="referer" value="'.referer().'"> ';
	
	echo	'</div>';
			
    if ($side == 'top') {
        echo '<div class="pull-right"><form class="form-inline" id="formSearch" method="GET" action="'.PHP_FILE.'">';
		
		echo '<select name="category" class="span2">';
        echo     '<option value="">查看所有分类（部门）</option> ';
        echo     dropdown_categories(null,$category);
        echo '</select> ';
			

		echo	'<div class="input-append"> ';
		echo		'<input class="span2" name="query" type="text" value="'.esc_html($search).'">';
		echo		'<input name="startdate" type="hidden" value="'.esc_html($startdate).'">';
		echo		'<input name="enddate" type="hidden" value="'.esc_html($enddate).'">';
		echo		'<input name="inforate" type="hidden" value="'.esc_html($inforate).'">';
		echo		'<input name="belong" type="hidden" value="'.esc_html($belong).'">';
		echo		'<button class="btn" type="submit">搜索</button>';
		echo		'<a href="#" data-toggle="button" class="btn" rel="popover" onclick="javascript:;return false;">选项 <i class="icon-caret-down"></i></a>';
		//echo		'<a href="search.php?method=search&adv=yes" class="btn"> 高级</a>';
		echo	'</div> ';
		
		//暂时这样
		$field_array = array(
			'identifier'=>'询盘ID',
			'source'=>'来源',
			'infoclass'=>'信息组',
			'infomember'=>'信息员',
			'inforate'=>'信息评级',
			'saleunit'=>'销售部门',
			'belong'=>'网站所属人',
			'operational'=>'业务人员',
			'language'=>'语种',
			'keywords'=>'关键词',
			'referer'=>'来源网址',
			'landingurl'=>'到访网址',
			'email'=>'邮箱',
			'phone'=>'电话',
			'country'=>'国家',
			'continent'=>'大洲',
			'province'=>'省、州',
			'producttype'=>'产品类型',
			'auction'=>'是否竞价',
			'sesource'=>'搜索引擎',
			'remarks'=>'备注'
		);
		echo '<div class="popover bottom">';
		echo   '<div class="arrow"></div>';
		echo   '<h3 class="popover-title">请选择你要搜索的字段 （列）</h3>';
		echo   '<div id="fields_content" class="popover-content">';
		echo	'<div class="btn-group" data-toggle="buttons-radio">';
		echo	'<a class="btn" id="selectAll">全选</a>';
		echo	'<a class="btn active" id="unSelect">全不选</a>';
		echo	'<a class="btn" id="reverse">反选</a>';
		echo	'</div>';
		//echo '<label class="checkbox"><input type="checkbox" name="selectf" value="all">全选/反选</label> ';
		foreach($field_array as $key => $val) {
			$checked = instr($key,$fields) ? ' checked="checked"' : '';
			echo '<label class="checkbox inline"><input type="checkbox" name="fields[]" value="'.$key.'"'.$checked.'>'.$val.'</label> ';
		}
		echo   '</div>';
		echo '</div>';
        echo '</form></div>';
    }
	
    if ($side == 'bottom') {
        echo pages_list($url);
    }
	//echo '</div>';
	
 
}

/**
 * 管理页面
 *
 * @param string $action
 */
function post_manage_page($action) {
    global $php_file; $trees = null; global $_USER;
	//print_r($_USER);
    $referer = referer(PHP_FILE);
    if ('report.php' == $php_file) {
        $trees = taxonomy_get_trees();
        if (empty($trees)) {
			echo '<div class="wrap">';
            echo   '<h2>'.system_head('title').'</h2>';
            echo   '<div class="well">';
            echo       '<div class="control-group">';
            echo               '<label class="control-label">请添加一个分类（部门）</label>';
            echo               '<button type="button" class="btn btn-primary" onclick="InfoSYS.redirect(\''.ADMIN.'categories.php?method=new\')">添加分类（部门）</button> <button type="button" class="btn" onclick="InfoSYS.redirect(\''.$referer.'\')">返回</button>';
            echo       '</div>';
            echo   '</div>';
            echo '</div>';
			return true;
        }
    }
    
    $postid  = isset($_GET['postid'])?$_GET['postid']:0;
	//echo post_pre_count($_USER['userid'],$postid);
	
    if ($action=='add') {
        $sortid = isset($_GET['category'])?$_GET['category']:null;
    } else {
        $_DATA  = post_get($postid);
        $sortid = isset($_DATA['sortid'])?$_DATA['sortid']:null;
    }
	$userid 			= isset($_DATA['userid'])?$_DATA['userid']:$_USER['userid'];
	$operational		= isset($_DATA['operational'])?$_DATA['operational']:null;
	$source				= isset($_DATA['source'])?$_DATA['source']:null;
	$language			= isset($_DATA['language'])?$_DATA['language']:null;
	$keywords			= isset($_DATA['keywords'])?$_DATA['keywords']:null;
	$refererurl			= isset($_DATA['referer'])?$_DATA['referer']:null;
	$landingurl			= isset($_DATA['landingurl'])?$_DATA['landingurl']:null;
	$sesource			= isset($_DATA['sesource'])?$_DATA['sesource']:null;
	$auction			= isset($_DATA['auction'])?$_DATA['auction']:null;
	$producttype		= isset($_DATA['producttype'])?$_DATA['producttype']:null;
	$continent			= isset($_DATA['continent'])?$_DATA['continent']:null;
	$country			= isset($_DATA['country'])?$_DATA['country']:null;
	$province			= isset($_DATA['province'])?$_DATA['province']:null;
	$email				= isset($_DATA['email'])?$_DATA['email']:null;
	$phone				= isset($_DATA['phone'])?$_DATA['phone']:null;
	$inforate			= isset($_DATA['inforate'])?$_DATA['inforate']:null;
	$remarks			= isset($_DATA['remarks'])?$_DATA['remarks']:null;
	$identifier			= isset($_DATA['identifier'])?$_DATA['identifier']:null;
	$infoclass			= isset($_DATA['infoclass'])&&$_DATA['infoclass']!=null?$_DATA['infoclass']:$_USER['usergroup'];
	$infomember			= isset($_DATA['infomember'])&&$_DATA['infomember']!=null?$_DATA['infomember']:$_USER['nickname'];
	$serial				= isset($_DATA['serial'])?$_DATA['serial']:1;
	$chatlog			= isset($_DATA['chatlog'])?$_DATA['chatlog']:null;
	$belong				= isset($_DATA['belong'])?$_DATA['belong']:null;
	
	$saleunit			= isset($_DATA['saleunit'])?$_DATA['saleunit']:null;
	//销售中心、销售部门合并为一个字段, 删除销售部门
	//$salesubunit		= isset($_DATA['salesubunit'])?$_DATA['salesubunit']:null;
	$description		= isset($_DATA['description'])?$_DATA['description']:null;
	$author				= isset($_DATA['author'])?$_DATA['author']:null;
	//$datetime			= isset($_DATA['datetime'])?$_DATA['datetime']:0;
	$addtime			= isset($_DATA['addtime'])?$_DATA['addtime']:0;
	$xp_status			= isset($_DATA['xp_status'])?$_DATA['xp_status']:null;

	//自定义字段
	$customer_country   = isset($_DATA['meta']['customer_country'])?$_DATA['meta']['customer_country']:null;
	$agency				= isset($_DATA['meta']['agency'])?$_DATA['meta']['agency']:null; //媒介
	$device				= isset($_DATA['meta']['device'])?$_DATA['meta']['device']:null; //是否移动设备

	//询盘等级所用字段
	$output				= isset($_DATA['meta']['output'])?$_DATA['meta']['output']:null;
	$have_phone   		= isset($_DATA['meta']['have_phone'])?$_DATA['meta']['have_phone']:null;
	$have_email   		= isset($_DATA['meta']['have_email'])?$_DATA['meta']['have_email']:null;
	$have_name   		= isset($_DATA['meta']['have_name'])?$_DATA['meta']['have_name']:null;
	$come_to_visit  	= isset($_DATA['meta']['come_to_visit'])?$_DATA['meta']['come_to_visit']:null;
	$old_customer   	= isset($_DATA['meta']['old_customer'])?$_DATA['meta']['old_customer']:null;
	$own_use   			= isset($_DATA['meta']['own_use'])?$_DATA['meta']['own_use']:null;
	$specific_product	= isset($_DATA['meta']['specific_product'])?$_DATA['meta']['specific_product']:null;
	$manual_rate		= isset($_DATA['meta']['manual_rate'])?$_DATA['meta']['manual_rate']:null;

	
	
	//获取用户组信息
	//if(!current_user_can('all-category',false)){
	$newtrees = null;
	$group  = group_get($_USER['primary_grp']);
	$group['category'] = unserialize($group['category']);
	if(!empty($group['category'])){
		$newtrees = array();
		foreach($group['category'] as $catid) {
			$trees	= taxonomy_get_trees($catid);
			$newtrees[] = $trees;
		}
	}
	//}
	

    $categories  = isset($_DATA['category'])?$_DATA['category']:array();

    $pre_post_count = post_pre_count($userid,$postid);
    $serial 		= $pre_post_count + 1;
	
	//$prepost = user_prepost($userid,$postid);
	// if(isset($prepost['serial'])) {
	// 	$serial = $prepost['serial'] + 1;
	// }
	$description = unserialize($description);
	
	echo	'<div class="module-header">';
	echo		'<h3>';
	if ($action=='add') {
		echo		'<i class="icon-plus-sign-alt"></i> 添加询盘信息';
	}else{
		echo		'<i class="icon-edit icon-large"></i> 编辑询盘信息';
	}
	echo		'</h3>';
	echo	'</div>';
	
	/**
	echo	'<div class="alert alert-warning"><a class="close" data-dismiss="alert" href="#">&times;</a>';
	if ($action=='add') {
		echo		' <i class="icon-info-sign"></i> 添加时请尽量把所有字段填完。';
	}else{
		echo		'<strong><i class="icon-info-sign"></i> 现在是编辑模式</strong>';
	}
	echo	'</div>';
	**/

	echo	'<div class="form-horizontal form-horizontal-small">';
	echo	'<form action="'.PHP_FILE.'?method=save" method="post" name="postmanage" id="postmanage">';
	echo		'<div class="widget">';
	echo			'<div class="widget-header">';
	echo				'<i class="icon-cog"></i><h3>基本信息</h3>';
	echo			'</div>';
	echo			'<div class="widget-content">';
	echo				'<table class="table table-button">';
	echo					'<tbody>';
	$hidden = '';
    if ('page.php' == $php_file) {
        $hidden = '<input type="hidden" name="type" value="page" />';
    } else {
        $hidden = '<input type="hidden" name="type" value="inquiry" />';
    }
	echo						'<tr>';
	echo							'<th class="td-borderless" style="width:80px;">销售中心</th>';
	echo							'<td colspan="2"  class="td-borderless">';
	echo								'<select name="sortid">';
	echo								'<option value=""> 查看所有部门 </option>';
	echo								dropdown_categories(0,$sortid,$newtrees);
	echo								'</select> ';
	if(current_user_can('categories',false)){
	echo							'<a class="btn btn-small" href="categories.php?method=new" title="添加一个部门"><i class="icon-plus"></i></a> ';
	
	echo							' <a class="btn btn-small" href="categories.php" title="查看所有部门"><i class="icon-align-justify"></i></a> ';
	}
	echo							'</td>';
	echo						'</tr>';
/*	echo           				'<tr>';
	echo           					'<th class="td-borderless">销售部门</th>';
	echo           					'<td class="td-borderless" style="white-space:nowrap">';
	echo								'<select name="category[]">';
	echo								'<option value=""> -- 查看所有部门 -- </option>';
	echo								dropdown_other_categories($sortid,$categories,$newtrees);
	echo								'</select> ';
	echo           					'</td>';
	echo           				'</tr>';*/
	echo           				'<tr>';
	echo           					'<th class="td-borderless">销售人员</th>';
	echo           					'<td class="td-borderless" style="white-space:nowrap">';
	echo							  '<div class="input-prepend"><span class="add-on ad-on-icon"><i class="icon-user"></i></span>';
	echo								'<input type="text" name="operational" id="operational" class="span2" placeholder="英文或中文逗号隔开" value="'.$operational.'" data-provide="typeahead" autocomplete="off">';
	echo								'<span class="help-inline"><i class="icon-question-sign" data-toggle="tooltip" data-title="你可以输入多个销售人员名字，请以英文或中文逗号隔开，如果你添加了联系人，你输入姓名会自动为你匹配。"></i></span>';
	echo							  '</div>';
	echo ' <div class="xp_status" style="padding-top:8px"><label class="checkbox inline"><input type="checkbox" class="unique" name="xp_status" value="重复" '.($xp_status=='重复'?'checked="checked"':'').'> 重复询盘</label> <label class="checkbox inline"><input type="checkbox" class="unique" name="xp_status" value="冲突" '.($xp_status=='冲突'?'checked="checked"':'').'> 冲突询盘</label></div>';
	echo           					'</td>';
	echo           				'</tr>';
	echo           				'<tr>';
	echo           					'<th class="td-borderless">询盘日期</th>';
	echo           					'<td class="td-borderless input-append date" style="white-space:nowrap" id="datepicker">';
	echo								'<input type="text" class="span2" name="add_date" value="'.date_gmt('Y-m-d H:i:s',($addtime?$addtime:time())).'" readonly>';
	echo								'<span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar" class="icon-calendar"></i></span>';
	echo								'<span class="help-inline"><i class="icon-question-sign" data-toggle="tooltip" data-title="默认为当前时间，请填写准确的日期。"></i></span>';
	echo           					'</td>';
	echo           				'</tr>';

	echo						'<tr>';
	echo							'<th class="td-borderless">基本信息</th>';
	echo							'<td class="td-borderless">';
	echo								'<div class="tabbable">';
	echo									'<ul class="nav nav-tabs">';
	echo										'<li class="active"><a href="#general" data-toggle="tab"><i class="icon-list-ul"></i> 一般信息</a></li>';
	echo										'<li><a href="#other" data-toggle="tab"><i class="icon-user"></i> 其它信息</a></li>';
	echo										'<li><a href="#tab-rate" data-toggle="tab"><i class="icon-legal"></i> 等级判定</a></li>';
	echo										'<li><a href="#chatlog" data-toggle="tab"><i class="icon-comments"></i> 聊天记录</a></li>';
	echo									'</ul>';
	echo									'<div class="tab-content">';
	echo										'<div class="tab-pane active" id="general">';
	echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">来源</label>';
	echo												'<div class="controls">';
	echo													'<select name="source">';
	echo													'<option value=""> --选择来源-- </option>';
	echo													display_select($source,1);
	echo													'</select> ';
	echo												'</div>';
	echo											'</div>';
	echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">转化端口</label>';
	echo												'<div class="controls">';
	echo													'<select name="device">';
	echo													'<option value=""> --选择转化端口-- </option>';
	echo													'<option value="PC端"'.($device=="PC端"?' selected="selected"':null).'>PC端</option>';
	echo													'<option value="移动端"'.($device=="移动端"?' selected="selected"':null).'>移动端</option>';
	echo													'</select> ';
	echo												'</div>';
	echo											'</div>';
	echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">语种</label>';
	echo												'<div class="controls">';
	echo													'<select name="language">';
	echo													'<option value=""> --选择语种-- </option>';
	echo													display_select($language,2);
	echo													'</select> ';
	echo												'</div>';
	echo											'</div>';
	echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">关键词</label>';
	echo												'<div class="controls">';
	echo													'<input type="text" name="keywords" class="span3" placeholder="" value="'.$keywords.'">';
	echo												'</div>';
	echo											'</div>';
		echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">媒介</label>';
	echo												'<div class="controls">';
	echo													'<input type="text" name="agency" class="span1" placeholder="最多四字" value="'.$agency.'">';
	echo												'</div>';
	echo											'</div>';
	echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">来源网址</label>';
	echo												'<div class="controls">';
	echo													'<input type="text" id="refererurl" name="refererurl" class="span5" placeholder="格式：http://" value="'.$refererurl.'">';
	echo													'<span class="help-inline"><i class="icon-question-sign" data-toggle="tooltip" data-title="请输入正确格式网址，不要忘了开头的http://，如果已经设置好对应关系，系统会自动判断搜索引擎来源。"></i></span>';
	echo												'</div>';
	echo											'</div>';
	echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">到访网址</label>';
	echo												'<div class="controls">';
	echo													'<input type="text" id="landingurl" name="landingurl" class="span5" placeholder="格式：http://" value="'.$landingurl.'">';
	echo													'<span class="help-inline"><i class="icon-question-sign" data-toggle="tooltip" data-title="请输入正确格式网址，不要忘了开头的http:// ; 系统会根据设置，自动判定是否属于竞价站。"></i></span>';
	echo												'</div>';
	echo											'</div>';
	
	echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">网站所属人</label>';
	echo												'<div class="controls">';
	echo												  '<input type="text" name="belong" class="span2" value="'.$belong.'" placeholder="根据规则，自动判断" disabled>';
	echo												  '<span class="help-inline"><i class="icon-question-sign" data-toggle="tooltip" data-title="请先在设置里的网址规则里添加判断规则。添加规则时请尽量避免重复规则。"></i></span>';
	echo												'</div>';
	echo											'</div>';
	
	echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">搜索引擎</label>';
	echo												'<div class="controls">';
	echo												  '<input type="text" name="sesource" class="span1" value="'.$sesource.'" disabled>';
	echo												  '<span class="help-inline"><i class="icon-question-sign" data-toggle="tooltip" data-title="请先在设置里设置好各个搜索引擎对应关系。否则，无法自动判断。"></i></span>';
	echo												'</div>';
	echo											'</div>';
	echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">是否竞价</label>';
	echo												'<div class="controls">';
	echo												  '<input type="text" name="auction" class="span1" value="'.$auction.'" disabled>';
	echo												  '<span class="help-inline"><i class="icon-question-sign" data-toggle="tooltip" data-title="请先在设置里设置竞价网站列表。"></i></span>';
	echo												'</div>';
	echo											'</div>';
	
	echo										'</div>';
	
	echo										'<div class="tab-pane" id="other">';
	echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">物料</label>';
	echo												'<div class="controls">';
	echo													'<select name="materials" class="material-select">';
	echo													  '<option value=""> -- 请选择物料 --</option>';
	echo													  dropdown_materials($description['material']);
	echo													'</select> ';
	echo												'</div>';
	echo											'</div>';
	echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">产品类型</label>';
	echo												'<div class="controls">';
	echo													'<select name="producttype[]" multiple class="product-select" data-placeholder="请选择产品类型">';
	echo													'<option value=""></option>';
	echo													display_select($producttype,5);
	echo													'</select> ';
	echo												'</div>';
	echo											'</div>';

	echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">客户所在国</label>';
	echo												'<div class="controls">';
	echo													'<select name="customer_country" class="country-select" data-placeholder="请选择客户所在国家">';
	echo													  '<option value=""></option>';
	echo													  dropdown_country($customer_country);
	echo													'</select> ';
	echo												  '<span class="help-inline"><i class="icon-question-sign" data-toggle="tooltip" data-title="默认的大洲依据为设备使用国家。"></i></span>';
	echo												'</div>';
	echo											'</div>';

	echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">设备使用国</label>';
	echo												'<div class="controls">';
	echo													'<select name="country" class="country-select" data-placeholder="请选择设备使用国家">';
	echo													  '<option value=""></option>';
	echo													  dropdown_country($country);
	echo													'</select> ';
	//echo													'<input type="text" name="country" id="country" class="input-large" value="'.$country.'" autocomplete="off" placeholder="输入国家中文名字">';
	echo												  '<span class="help-inline"><i class="icon-question-sign" data-toggle="tooltip" data-title="此处为必填项，可以根据你的输入自动匹配国家名字。"></i></span>';
	echo												'</div>';
	echo											'</div>';
	if ($action!='add') {
		echo										'<div class="control-group control-group-mini">';
		echo											'<label class="control-label">大洲</label>';
		echo											'<div class="controls">';
		echo												'<input type="text" name="continent" id="continent" class="input-large" value="'.$continent.'" disabled="disabled">';
		echo											'</div>';
		echo										'</div>';
	}
	echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">省/州</label>';
	echo												'<div class="controls">';
	echo													'<input type="text" name="province" class="input-large" value="'.$province.'">';
	echo												'</div>';
	echo											'</div>';
	echo											'<div class="control-group control-group-mini hide">';
	echo												'<label class="control-label">邮箱</label>';
	echo												'<div class="controls">';
	echo													'<input type="text" name="email" class="input-large" value="'.$email.'">';
	echo												'</div>';
	echo											'</div>';
	echo											'<div class="control-group control-group-mini hide">';
	echo												'<label class="control-label">电话</label>';
	echo												'<div class="controls">';
	echo													'<input type="text" name="phone"  id="phone" class="input-large" value="'.$phone.'">';
	echo												'</div>';
	echo											'</div>';

	echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">备注信息</label>';
	echo												'<div class="controls">';
	echo													'<textarea class="text span5" rows="6" id="remarks" name="remarks">'.$remarks.'</textarea>';
	//echo													editor('content',$description);
	echo												'</div>';
	echo											'</div>';
	echo										'</div>';
	//询盘等级判定字段（2013-9-22添加，与之前使用的方式不同，老的判定规则会失效。）
	echo										'<div class="tab-pane" id="tab-rate">';
	echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">联系方式</label>';
	echo												'<div class="controls">';
	echo													'<div class="checkbox inline"><input type="checkbox" name="have_phone" '.("on"===$have_phone?' checked="checked"':null).'> 有电话</div>';
	echo													'<div class="checkbox inline"><input type="checkbox" name="have_email" '.("on"===$have_email?' checked="checked"':null).'> 有邮箱</div>';
	echo												'</div>';
	echo											'</div>';
	
	echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">产量</label>';
	echo												'<div class="controls ">';
	echo												  '<input type="text" name="output" class="span2" placeholder="留空为未知产量" value="'.$output.'"> <span class="help-inline">TPH </span> ';
	echo												'</div>';
	echo											'</div>';
	echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">客户调查</label>';
	echo												'<div class="controls">';
	echo													'<div class="checkbox"><input type="checkbox" name="have_name" '.("on"===$have_name?' checked="checked"':null).'> 是否有客户姓名</div>';
	echo													'<div class="checkbox"><input type="checkbox" name="come_to_visit" '.("on"===$come_to_visit?' checked="checked"':null).'> 是否意向来访</div>';
	echo													'<div class="checkbox"><input type="checkbox" name="old_customer" '.("on"===$old_customer?' checked="checked"':null).'> 是否为老客户</div>';
	echo													'<div class="checkbox"><input type="checkbox" name="own_use" '.("on"===$own_use?' checked="checked"':null).'> 是否自用（非自用一般为代理）</div>';
	echo 											   '</div>';
	echo											'</div>';
	echo											'<div class="control-group control-group-mini">';
	echo												'<label class="control-label">具体产品</label>';
	echo												'<div class="controls">';
	echo													'<select name="specific_product">';
	echo													'<option value="" > --请选择产品类型-- </option>';
	echo													'<option value="高附加值产品"'.(trim($specific_product)==="高附加值产品"?' selected="selected"':null).'> 生产线/可售单机/高附加值产品 </option>';
	echo													'<option value="配套辅助设备"'.(trim($specific_product)==="配套辅助设备"?' selected="selected"':null).'> 配套辅助设备 </option>';
	echo													'<option value="我司配件"'.(trim($specific_product)==="我司配件"?' selected="selected"':null).'> 我司配件 </option>';
	echo													'<option value="二手破磨设备"'.(trim($specific_product)==="二手破磨设备"?' selected="selected"':null).'> 二手破磨设备 </option>';
	echo													'<option value="水泥生产线"'.(trim($specific_product)==="水泥生产线"?' selected="selected"':null).'> 水泥生产线/熟料 </option>';
	echo													'<option value="竞争对手产品"'.(trim($specific_product)==="竞争对手产品"?' selected="selected"':null).'> 竞争对手产品 </option>';
	echo													'<option value="非我司配件"'.(trim($specific_product)==="非我司配件"?' selected="selected"':null).'> 上下游产品/非我司配件 </option>';
	echo													'</select> ';
	echo												'</div>';
	echo											'</div>';

	echo '<hr>';

	echo											'<div class="control-group control-group-mini" style="color:#c6322a">';
	echo												'<label class="control-label">自己评级</label>';
	echo												'<div class="controls">';
	
	echo													'<select name="manual_rate" class="inline" style="color:#c6322a">';
	echo													'<option value=""> -- 请选择信息评级 -- </option>';
	echo	display_select($manual_rate,6);
	echo													'</select> ';
	echo												'</div>';
	echo											'</div>';
	echo										'</div>';
	
	echo										'<div class="tab-pane" id="chatlog">';
	echo											'<div class="control-group control-group-mini">';
	echo													'<textarea class="text span6" rows="15" id="chatlog" name="chatlog">'.$chatlog.'</textarea>';
	//echo													editor('content',$description);
	echo											'</div>';
	echo										'</div>';
	
	echo									'</div>';
	echo								'</div>';
	echo							'</td>';
	echo						'</tr>';
	echo					'</tbody>';
	echo				'</table>';
	echo			'</div>';
	echo		'</div>';
	
	echo		'<div style="height:30px">';
	echo			'<a class="btn btn-mini" data-toggle="collapse" data-target="#demo"><i class="icon-chevron-down"></i>其它信息</a>';
	echo		'</div>';
	
	echo		'<div class="widget collapse" style="margin-top: 10px;" id="demo">';
	echo			'<div class="widget-header">';
	echo				'<i class="icon-cog"></i><h3>其它信息</h3>';
	echo			'</div>';
	echo			'<div class="widget-content">';
	echo				'<table class="table table-button">';
	echo					'<tbody>';
	echo						'<tr>';
	echo							'<th class="td-borderless" style="width:80px;">询盘ID</th>';
	echo							'<td class="td-borderless">';
	echo								'<input type="text" class="span5" name="identifier" placeholder="系统自动生成..." value="'.$identifier.'" disabled>';
	echo							'</td>';
	echo						'</tr>';
	echo						'<tr>';
	echo							'<th class="td-borderless">信息组</th>';
	echo							'<td class="td-borderless">';
	echo								'<input type="text" class="span2" name="infoclass" value="'.$infoclass.'" readonly>';
	echo								'<span class="help-inline"><i class="icon-question-sign" data-toggle="tooltip" data-title="信息组为用户所在的【用户组】，添加用户时已经被选定，不需要设置。"></i></span>';
	echo							'</td>';
	echo						'</tr>';
	echo						'<tr>';
	echo							'<th class="td-borderless">信息员</th>';
	echo							'<td class="td-borderless">';
	echo								'<input type="text" class="span2" name="infomember" value="'.$infomember.'" readonly>';
	echo 								'<input type="hidden" name="userid" value="'.$userid.'">';
	echo								'<span class="help-inline"><i class="icon-question-sign" data-toggle="tooltip" data-title="信息员为用户的昵称，建议注册用户时直接使用中文名字作为用户名（即用户名与昵称是一样的）。"></i></span>';
	echo							'</td>';
	echo						'</tr>';
	echo						'<tr>';
	echo							'<th class="td-borderless">序号</th>';
	echo							'<td class="td-borderless">';
	echo								'<input type="text" class="span1" name="serial" value="'.$serial.'" disabled>';
	echo							'</td>';
	echo						'</tr>';
	
	echo					'</tbody>';
	echo				'</table>';
	echo			'</div>';
	echo		'</div>';

	echo	'<div style="margin-top:30px">';
	echo		'<p>';
	if ($action=='add') {
        echo   '<button type="submit"  class="btn btn-primary"><i class="icon-plus-sign"></i> 添加询盘信息</button>'.$hidden;
    } else {
        $hidden.= '<input type="hidden" name="postid" value="'.$postid.'" />';
        $hidden.= '<input type="hidden" name="referer" value="'.referer().'" />';
        echo   '<button type="submit" class="btn btn-primary"><i class="icon-ok-sign"></i> 更新询盘信息</button>'.$hidden;
    }
	echo       ' <button type="button" class="btn" onclick="InfoSYS.redirect(\''.$referer.'\')"><i class="icon-backward"></i> 返回 </button>';
	echo		'</p>';
	echo	'</div>';
	echo	'</form>';
	echo   '</div>';
}

/**
 * 查看页面
 *
 * @param string $action
 */
function post_view_page($action){
	$postid  = isset($_GET['postid'])?$_GET['postid']:0;
	$referer = referer(PHP_FILE);
    $_DATA  = post_get($postid);
    $sortid = isset($_DATA['sortid'])?$_DATA['sortid']:null;
		
	$userid 			= isset($_DATA['userid'])?$_DATA['userid']:0;
	$operational		= isset($_DATA['operational'])?$_DATA['operational']:null;
	$source				= isset($_DATA['source'])?$_DATA['source']:null;
	$language			= isset($_DATA['language'])?$_DATA['language']:null;
	$keywords			= isset($_DATA['keywords'])?$_DATA['keywords']:null;
	$refererurl			= isset($_DATA['referer'])?$_DATA['referer']:null;
	$landingurl			= isset($_DATA['landingurl'])?$_DATA['landingurl']:null;
	$sesource			= isset($_DATA['sesource'])?$_DATA['sesource']:null;
	$auction			= isset($_DATA['auction'])?$_DATA['auction']:null;
	$producttype		= isset($_DATA['producttype'])?$_DATA['producttype']:null;
	$continent			= isset($_DATA['continent'])?$_DATA['continent']:null;
	$country			= isset($_DATA['country'])?$_DATA['country']:null;
	$province			= isset($_DATA['province'])?$_DATA['province']:null;
	$email				= isset($_DATA['email'])?$_DATA['email']:null;
	$phone				= isset($_DATA['phone'])?$_DATA['phone']:null;
	$inforate			= isset($_DATA['inforate'])?$_DATA['inforate']:null;
	$remarks			= isset($_DATA['remarks'])?$_DATA['remarks']:null;
	$identifier			= isset($_DATA['identifier'])?$_DATA['identifier']:null;
	$infoclass			= isset($_DATA['infoclass'])?$_DATA['infoclass']:null;
	$infomember			= isset($_DATA['infomember'])?$_DATA['infomember']:null;
	$serial				= isset($_DATA['serial'])?$_DATA['serial']:1;
	$chatlog			= isset($_DATA['chatlog'])?$_DATA['chatlog']:null;
	$belong				= isset($_DATA['belong'])?$_DATA['belong']:null;
	
	$saleunit			= isset($_DATA['saleunit'])?$_DATA['saleunit']:null;
	$description		= isset($_DATA['description'])?$_DATA['description']:null;
	$author				= isset($_DATA['author'])?$_DATA['author']:null;
	$addtime			= isset($_DATA['addtime'])?$_DATA['addtime']:0;

	//自定义字段
	$customer_country   = isset($_DATA['meta']['customer_country'])?$_DATA['meta']['customer_country']:null;
	$xp_status			= isset($_DATA['xp_status'])?$_DATA['xp_status']:null;
	$manual_rate		= isset($_DATA['meta']['manual_rate'])?$_DATA['meta']['manual_rate']:null;

	$description = unserialize($description);
	
	echo '<div class="row-fluid">';
	echo   '<div class="span10 offset1">';
	echo      '<div class="widget-box transparent invoice-box">';
	echo        '<div class="widget-header widget-header-large">';
	
	echo          '<h3 class="grey lighter pull-left position-relative"><span class="badge badge-warning">'.$inforate.'</span>详细信息 #'.$postid.'</h3>';
	echo          '<div class="widget-toolbar no-border invoice-info">';
	echo            '<span class="invoice-info-label">询盘ID: </span>';
	echo            '<span class="red">'.$identifier.'</span>';
	echo            '<br>';
	echo            '<span class="invoice-info-label">询盘日期: </span>';
	echo            '<span class="blue">'.date_gmt('Y-m-d H:i:s',$addtime).'</span>';
	echo          '</div>';
	echo          '<div class="widget-toolbar hidden-480">';
	echo            '<a href="#" onClick="window.print(); return false;"><i class="icon-print"></i></a>';
	echo          '</div>';
	
	echo        '</div>';
	
	
	echo        '<div class="widget-body">';
	echo          '<div class="widget-main padding-24">';
	echo            '<div class="row-fluid">';
	echo              '<div class="span6">';
	echo                '<div class="row-fluid">';
	echo                  '<div class="span12 label label-large label-info arrowed-in arrowed-right"><b>基础信息</b></div>';
	echo                '</div>';
	echo                '<div class="row-fluid">';
	echo                  '<ul class="unstyled spaced">';
	echo                    '<li><i class="icon-caret-right blue"></i><span class="blue">销售中心: </span>'.$saleunit.'</li>';
	echo                    '<li><i class="icon-caret-right blue"></i><span class="blue">来源: </span>'.$source.'</li>';
	
	echo                    '<li><i class="icon-caret-right blue"></i><span class="blue">产品类型:</span> '.$producttype.'</li>';
	echo                    '<li><i class="icon-caret-right blue"></i><span class="blue">国家:</span> '.$country.' ('.$continent.')</li>';
	echo                    '<li><i class="icon-caret-right blue"></i><span class="blue">设备使用国家:</span> '.$customer_country.'</li>';
	echo                    '<li><i class="icon-caret-right blue"></i><span class="blue">询盘状态: </span>'.$xp_status.'</li>';
	echo                    '<li><i class="icon-caret-right blue"></i><span class="blue">业务人员: </span>'.$operational.'</li>';
	echo                    '<li><i class="icon-caret-right blue"></i><span class="blue">信息员: </span>'.$infomember.' ('.$infoclass.')</li>';
	echo                    '<li class="divider"></li>';
	echo                    '<li><i class="icon-caret-right blue"></i><span class="blue">备注</span></li>';
	echo                  '</ul>';
	echo                '</div>';
	echo              '</div><!--/span-->';
	echo              '<div class="span6">';
	echo                '<div class="row-fluid">';
	echo                  '<div class="span12 label label-large label-success arrowed-in arrowed-right"><b>其它信息</b></div>';
	echo                '</div>';
	echo                '<div class="row-fluid">';
	echo                  '<ul class="unstyled spaced">';
	echo                    '<li><i class="icon-caret-right green"></i><span class="green">语种: </span>'.$language.'</li>';
	echo                    '<li><i class="icon-caret-right green"></i><span class="green">是否竞价: </span>'.$auction.'</li>';
	echo                    '<li><i class="icon-caret-right green"></i><span class="green">搜索引擎:</span> '.$sesource.'</li>';
	echo                    '<li><i class="icon-caret-right green"></i><span class="green">关键词: </span>'.$keywords.'</li>';
	echo                    '<li><i class="icon-caret-right green"></i><span class="green">来源网址: </span><a href="'.$refererurl.'" target="_blank">'.$refererurl.'</a></li>';
	echo                    '<li><i class="icon-caret-right green"></i><span class="green">到访网址: </span><a href="'.$landingurl.'" target="_blank">'.$landingurl.'</a></li>';
	echo                    '<li><i class="icon-caret-right green"></i><span class="green">网站所属人: </span>'.$belong.'</li>';
	echo                  '</ul>';
	echo                '</div>';
	echo              '</div><!--/span-->';
	echo            '</div><!--/row-->';
	echo            '<div class="space"></div>';
	echo            '<div class="row-fluid">';
	echo              '<p>'.$remarks.'</p>';
	echo            '</div>';
	echo            '<div class="hr hr8 hr-double hr-dotted"></div>';
	echo            '<div class="row-fluid">';
	echo              '<div class="span5 pull-right">';
	echo                '<h4 class="pull-right">询盘等级 : <span class="red">'.$inforate.'</span></h4>';
	echo              '</div>';
	//print_r($description);
	echo              '<div class="span7 pull-left"> '.(!empty($manual_rate)?'由信息员自己评级':'系统自动评级').' </div>';
	echo            '</div>';
	echo            '<div class="space-6"></div>';
	echo            '<div class="row-fluid">';
	echo              '<div class="span12 well">询盘等级的判定,请依据信息组给定的规则. <a href="'.$referer.'">返回</a></div>';
	echo            '</div>';
	echo          '</div>';
	echo        '</div>';
	
	echo     '</div>';
	echo   '</div>';
	echo '</div>';
}


/**
 * 显示分类数
 *
 * @param int $sortid
 * @param array $categories
 * @param array $trees
 * @return string
 */
function display_ul_categories($sortid,$categories=array(),$trees=null) {
    static $func = null;
    $hl = sprintf('<ul %s>',is_null($func) ? 'id="sortid" class="categories unstyled"' : 'class="children  unstyled"');
    if (!$func) $func = __FUNCTION__;
    if ($trees === null) $trees = taxonomy_get_trees();
    foreach ($trees as $i=>$tree) {
        $checked = instr($tree['taxonomyid'],$categories) && $sortid!=$tree['taxonomyid'] ? ' checked="checked"' : '';
        $main_checked = $tree['taxonomyid']==$sortid?' checked="checked"':'';
        $hl.= sprintf('<li><input type="radio" name="sortid" value="%d"%s />',$tree['taxonomyid'],$main_checked);
        $hl.= sprintf('<label class="checkbox inline" for="category-%d">',$tree['taxonomyid']);
        $hl.= sprintf('<input type="checkbox" id="category-%1$d" name="category[]" value="%1$d"%3$s />%2$s</label>',$tree['taxonomyid'],$tree['name'],$checked);
    	if (isset($tree['subs'])) {
    		$hl.= $func($sortid,$categories,$tree['subs']);
    	}
        $hl.= '</li>';
    }
    $hl.= '</ul>';
    return $hl;
}
function display_ul_users($userid,$users=array(),$trees=null) {
    static $func = null;
	$hl = ' ';
    //$hl = sprintf('<ul %s>',is_null($func) ? 'id="sortid" class="categories"' : 'class="children"');
    if ($trees === null) $trees = user_get_trees();
    foreach ($trees as $i=>$tree) {
        $checked = instr($tree['userid'],$users) && $userid!=$tree['userid'] ? ' checked="checked"' : '';
        $main_checked = $tree['userid']==$userid?' checked="checked"':'';
        //$hl.= sprintf('<input type="radio" name="sortid" value="%d"%s />',$tree['taxonomyid'],$main_checked);
        $hl.= sprintf('<label class="checkbox  inline" for="otheruser-%d">',$tree['userid']);
        $hl.= sprintf('<input type="checkbox" id="otheruser-%1$d" name="otherusers[]" value="%1$d"%3$s />%2$s</label>',$tree['userid'],isset($tree['nickname'])?$tree['nickname']:$tree['name'],$checked);
    }
    $hl.= '';
    return $hl;
}
function dropdown_users($selected){
	$hl = ''; $username ='';
	$trees	=	user_get_trees();
	foreach ($trees as $i=>$tree) {
		$sel  = $selected==$tree['userid']?' selected="selected"':null;
		if (isset($tree['nickname'])&&$tree['nickname']!='') {
			$username = $tree['nickname'];
		}else{
			$username = $tree['name'];
		}
        $hl.= '<option value="'.$tree['userid'].'"'.$sel.'>'.$username.'</option>';
		
	}
	return $hl;
}
function dropdown_info_status($info_status='new'){
	$hl = '';
	$status = array('new'=>'未联系','contacted'=>'已联系','intention'=>'有意向','accidental'=>'无意向','useless'=>'无用信息','irrelevant'=>'不相关','missedcalls'=>'未接电话','emptynumber'=>'空号');
	foreach ($status as $key=>$val) {
		$sel  = trim($info_status)===$key?' selected="selected"':null;
		$hl.= '<option value="'.$key.'"'.$sel.'>'.$val.'</option>';
	}
	
	return $hl;
}
function translate_status($string) {
	$status = array('new'=>'未联系','contacted'=>'已联系','intention'=>'有意向','accidental'=>'无意向','useless'=>'无用信息','irrelevant'=>'不相关','missedcalls'=>'未接电话','emptynumber'=>'空号');
	$string = trim($string);
	if(isset($status[$string])) return $status[$string];
	return $string;
}
function dropdown_rates($selected) {
	$hl = '';
	for($i =0; $i<=5 ; $i++){
		$sel  = $selected==$i?' selected="selected"':null;
		$hl .= '<option value="'.$i.'"'.$sel.'>'.$i.'</option>';
	}
	return $hl;
}
function display_select($selected , $num){
	$hl = $h2 = '';
	$text = array(array('世邦电商一部','电商二部','西芝电商一部','世邦国内电商','电商三部'),
				array('商务通','B2B','办事处','报纸杂志','电话','技术员','网站留言','展会','直接来信','SNS','其它'),
				array('英语','中文','俄语','西语','阿语','法语','葡语','印尼语','泰语','波斯语','越南语','其它'),
				array('Google','Yahoo','Bing','Yandex','百度','360','搜狗','搜搜','其它'),
				array('竞价','优化','EDM'),
				//array('磨机','矿山','代理','配件','砂石','制砂机','其它'),
				array('建筑破碎','磨机','矿山用破碎','矿山用磨机','制砂机','球磨','代理'),		
				array('A','B','C','D','E'),
			);
	foreach ($text[$num] as $val) {
		foreach(explode(",", $selected) as $select){
			$sel  = trim($select)===$val?' selected="selected"':null;
			$h2	  = '<option value="'.$val.'"'.$sel.'>'.$val.'</option>';
			if($sel) break;
		}
		$hl.=$h2;
	}
	return $hl;
}
//
function show_select($selected , $array){
	$hl = '';
	foreach ($array as $key=>$val) {
		$sel  = trim($selected)===$val?' selected="selected"':null;
		$hl.= '<option value="'.$key.'"'.$sel.'>'.$val.'</option>';
	}
	foreach ($array as $val) {
		foreach(explode(",", $selected) as $select){
			$sel  = trim($select)===$val?' selected="selected"':null;
			$h2	  = '<option value="'.$val.'"'.$sel.'>'.$val.'</option>';
			if($sel) break;
		}
		$hl.=$h2;
	}
	return $hl;
}

/**
 * 显示物料的下拉选项列表
 * @param   type    $varname    description
 * @return  type    description
 * @access  public or private
 * @static  makes the class property accessible without needing an instantiation of the class
 */
function dropdown_materials($selected)
{
	$hl = '';
    $list = array('其它'=>'其它物料', '适合的'=>'适合的物料', '不适合'=>'不适合的物料', '矽藻土'=>'矽藻土( Diatomaceous Earth )', '白垩岩'=>'白垩岩( Chalk )', '砾岩'=>'砾岩( Conglomerate )', '页岩'=>'页岩( Shale )', '辉长岩'=>'辉长岩( Gabbro )', '石灰岩'=>'石灰岩( Limestone )', '卵石'=>'卵石( Pebble )', '河卵石'=>'河卵石( River pebbles )', '砂岩'=>'砂岩( Sandstone )', '花岗岩'=>'花岗岩( Granite )', '石膏'=>'石膏( Plaster )', '片麻岩'=>'片麻岩( Gneiss )', '滑石'=>'滑石( Talc )', '大理石'=>'大理石( Marble )', '白云岩'=>'白云岩( Dolomite )', '玄武岩'=>'玄武岩( Basalt )', '安山岩'=>'安山岩( Andesite )', '玢岩'=>'玢岩( Porphyrite )', '闪长岩'=>'闪长岩( Diorite )', '闪长石'=>'闪长石( Diorite )', '辉绿岩'=>'辉绿岩( Diabase )', '石英'=>'石英( Quartz )', '流纹岩'=>'流纹岩( Rhyolite )', '黑曜岩'=>'黑曜岩( Obsidian )', '浮石'=>'浮石( Pumice )', '凝灰岩'=>'凝灰岩( Tuff )', '火山岩'=>'火山岩( Volcanic )', '火山灰'=>'火山灰( Volcanic ash )', '方解石'=>'方解石( Calcite )', '白云石'=>'白云石( Dolomite )', '盐晶'=>'盐晶( Salt crystals )', '岩盐'=>'岩盐( Rock salt )', '橄辉岩'=>'橄辉岩( Olivine pyroxenite )', '泥岩'=>'泥岩( Mudstone )', '凝灰岩 '=>'凝灰岩 ( Tuff )', '松砂岩'=>'松砂岩( Loose sandstone )', '粘板岩'=>'粘板岩( Slate )', '粉砂岩'=>'粉砂岩( Siltstone )', '灰岩'=>'灰岩( Limestone )', '硬页岩'=>'硬页岩( Argillite )', '蛇纹岩'=>'蛇纹岩( Serpentinite )', '片岩'=>'片岩( Schist )', '白云灰岩'=>'白云灰岩( Dolostone )', '硬质片岩'=>'硬质片岩( Hard schist )', '云母片岩'=>'云母片岩( Mica schist )', '变质片岩'=>'变质片岩( Metamorphic schist )', '长英麻粒岩'=>'长英麻粒岩( Long leptite )', '斑岩'=>'斑岩( Porphyry )', '粗面岩'=>'粗面岩( Trachyte )', '铁遂岩'=>'铁遂岩( Iron then rock )', '硬质砂岩'=>'硬质砂岩( Hard sandstone )', '石英岩'=>'石英岩( Quartzite )', '硅质岩'=>'硅质岩( Cherts )', '炉渣'=>'炉渣( Slag )', '钢渣'=>'钢渣( Slag )', '石灰石'=>'石灰石( Limestone )', '橄榄石'=>'橄榄石( Olivine )', '玛瑙石'=>'玛瑙石( Agate )', '长石'=>'长石( Feldspar )', '重晶石'=>'重晶石( Barite )', '萤石'=>'萤石( Fluorite )', '陶瓷'=>'陶瓷( Ceramics )', '高岭土'=>'高岭土( Kaolin )', '石墨'=>'石墨( Graphite )', '珍珠岩'=>'珍珠岩( Perlite )', '煤灰'=>'煤灰( Coal ash )', '焦炭'=>'焦炭( Coke )', '蓝晶石'=>'蓝晶石( Cyanite )', '碳化硅'=>'碳化硅( Silicon carbide )', '刚玉'=>'刚玉( Corundum )', '红矾土'=>'红矾土( Dichromate soil )', '正长石'=>'正长石( Orthoclase )', '钾长石'=>'钾长石( K-feldspar )', '钠长石'=>'钠长石( Albite )', '钙长石'=>'钙长石( Anorthite )', '斜长石'=>'斜长石( Plagioclase )', '板石'=>'板石( Slab stone )', '磷矿石'=>'磷矿石( Phosphate rock )', '叶蜡石'=>'叶蜡石( Pyrophyllite )', '膨润土'=>'膨润土( Bentonite )', '水渣'=>'水渣( Water residue )', '混凝土'=>'混凝土( Concrete )', '沥青'=>'沥青( Asphalt )', '建筑垃圾'=>'建筑垃圾( Construction waste )', '云母'=>'云母( Mica )', '蛭石'=>'蛭石( Vermiculite )', '霞石'=>'霞石( Nepheline )', '骨料/集料/集合料'=>'骨料/集料/集合料( Aggregate )', '建筑用石'=>'建筑用石( Building stone )', '天然砂'=>'天然砂( Natural sand )', '机制砂'=>'机制砂( Mechanisms sand )', '路基石'=>'路基石( Cornerstone of the Road )', '道砟'=>'道砟( Ballast )', '矿渣'=>'矿渣( Slag )', '石子'=>'石子( Pebble )', '石粉'=>'石粉( Powder )', '砖'=>'砖( Brick )', '砂石'=>'砂石( Sand and gravel )', '砾石'=>'砾石( Gravel )', '黄铁矿'=>'黄铁矿( Pyrite )', '针铁矿'=>'针铁矿( Goethite )', '菱铁矿'=>'菱铁矿( Siderite )', '赤铁矿'=>'赤铁矿( Hematite )', '褐铁矿'=>'褐铁矿( Limonite )', '钛铁矿'=>'钛铁矿( Ilmenite )', '磁铁矿'=>'磁铁矿( Magnetite )', '方铅矿'=>'方铅矿( Galena )', '铅锌矿'=>'铅锌矿( Lead-zinc )', '闪锌矿'=>'闪锌矿( Sphalerite )', '菱镁矿'=>'菱镁矿( Magnesite )', '锰矿'=>'锰矿( Manganese )', '钨矿'=>'钨矿( Tungsten ore )', '铅矿'=>'铅矿( Galena )', '锌矿'=>'锌矿( Zinc mine )', '铬矿'=>'铬矿( Chrome ore )', '金矿'=>'金矿( Goldmine )', '银矿'=>'银矿( Silvermine )', '钛矿'=>'钛矿( Perovskite )', '钽矿'=>'钽矿( Tantalum ore )', '钼矿'=>'钼矿( Molybdenum Mine )', '锡矿'=>'锡矿( Tin mine )', '钇矿'=>'钇矿( Xenotime )', '铌矿'=>'铌矿( Niobium mine )', '镍矿'=>'镍矿( Nickel ore )', '铜矿'=>'铜矿( Copper )', '黄铜矿'=>'黄铜矿( Chalcopyrite )', '锆石'=>'锆石( Zircon )', '硫铁矿'=>'硫铁矿( Pyrite )');

	foreach($list as $key=>$val){
		$sel  = trim($selected)===$key?' selected="selected"':null;
		$hl.= '<option value="'.$key.'"'.$sel.'>'.$val.'</option>';
	}
	return $hl;
} // end func
function dropdown_country($selected) {
	$hl = '';
	$list = array('亚洲'=>array('中国','香港','台湾','澳门',"阿富汗","阿拉伯联合酋长国","阿曼","阿塞拜疆","巴基斯坦","巴勒斯坦","巴林","不丹","朝鲜","东帝汶","菲律宾","格鲁吉亚","哈萨克斯坦","韩国","吉尔吉斯斯坦","柬埔寨","卡塔尔","科威特","老挝","黎巴嫩","马尔代夫","马来西亚","蒙古国","孟加拉国","缅甸","尼泊尔","日本","塞普勒斯","沙特阿拉伯","斯里兰卡","塔吉克斯坦","泰国","土耳其","土库曼斯坦","文莱","乌兹别克斯坦","新加坡","叙利亚","亚美尼亚","也门","伊拉克","伊朗","以色列","印度","印度尼西亚","约旦","越南"),
		'非洲'=>array('阿尔及利亚','埃及','埃塞俄比亚','安哥拉','贝宁共和国','波札那','博茨瓦纳','布吉纳法索','蒲隆地','赤道几内亚','多哥','厄立特里亚','佛得角','冈比亚','刚果','刚果共和国','刚果民主共和国','吉布提','几内亚','几内亚比绍','加那利群岛','加纳','加蓬','辛巴威','喀麦隆','科摩罗','科特迪瓦','肯尼亚','莱索托','利比里亚','利比亚','留尼旺（法）','卢旺达','马达加斯加','马德拉群岛（葡 ）','马拉维','马里共和国','毛里求斯','毛里塔尼亚','摩洛哥','莫桑比克','纳米比亚','南非','尼日尔','尼日利亚','塞拉利昂','塞内加尔','塞舌尔','圣多美及普林西比','圣赫勒拿（英）','斯威士兰','苏丹','索马里','坦桑尼亚','突尼斯','乌干达','西撒哈拉','亚速尔群岛（葡）','赞比亚','乍得','中非'),
		'南美洲'=>array('阿根廷','巴拉圭','巴西','玻利维亚','厄瓜多尔','法属圭亚那','哥伦比亚','圭亚那','秘鲁','苏里南','委内瑞拉','乌拉圭','智利'),
		'北美洲'=>array('阿鲁巴','安圭拉','安提瓜和巴布达','巴巴多斯','巴哈马','巴拿马','百慕大','波多黎各','伯利兹','多米尼加','多米尼克','哥斯达黎加','格林纳达','格陵兰','古巴','瓜德罗普岛','海地','荷属安的列斯','洪都拉斯','加拿大','开曼群岛','马提尼克','美国','美属维尔京群岛','蒙特塞拉特','墨西哥','尼加拉瓜','萨尔瓦多','圣基茨和尼维斯','圣卢西亚','圣文森特和格林纳丁斯','特克斯和凯科斯群岛','特立尼达和多巴哥','危地马拉','牙买加','英属维尔京群岛'),
		'大洋洲'=>array('澳大利亚','巴布亚新几内亚','北马里亚纳','玻利尼西亚','法属波利尼西亚','斐济','关岛','基里巴斯','库克群岛','马绍尔群岛','美属萨摩亚','密克罗尼西亚','瑙鲁','纽埃','帕劳','皮特凯恩岛','萨摩亚','所罗门群岛','汤加','图瓦卢','托克劳','瓦利斯与富图纳','瓦努阿图','新喀里多尼亚','新西兰'),
		'欧洲'=>array('阿尔巴尼亚','爱尔兰共和国','爱沙尼亚','安道尔','奥地利','白俄罗斯共和国','保加利亚','比利时','冰岛','波斯尼亚和黑塞哥维那','波兰','丹麦','德国','俄罗斯','法国','法罗群岛','梵蒂冈','芬兰','荷兰','黑山','捷克','克罗地亚','拉脱维亚','立陶宛','列支敦士登','卢森堡','罗马尼亚','马耳他','马其顿共和国','摩尔多瓦','摩纳哥','挪威','南斯拉夫','葡萄牙','瑞典','瑞士','塞尔维亚','圣马力诺','斯洛伐克','斯洛文尼亚','乌克兰','西班牙','希腊','匈牙利','意大利','英国','科索沃')
		
	);
	foreach ($list as $continent=>$country) {
		$hl.= '<optgroup label="'.$continent.'">';
		foreach($country as $val){
			$sel  = trim($selected)===$val?' selected="selected"':null;
			$hl.= '<option value="'.$val.'"'.$sel.'>'.$val.'</option>';
		}
		$hl.= '</optgroup>';
	}
	return $hl;
}


?>