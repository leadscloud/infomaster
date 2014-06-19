<?php
/**
* For the brave souls who get this far: You are the chosen ones,
* the valiant knights of programming who toil away, without rest,
* fixing our most awful code. To you, true saviors, kings of men,
* I say this: never gonna give you up, never gonna let you down,
* never gonna run around and desert you. Never gonna make you cry,
* never gonna say goodbye. Never gonna tell a lie and hurt you.
*/

// 文件名
$php_file = isset($php_file) ? $php_file : 'domain.php';
// 加载公共文件
include dirname(__FILE__).'/admin.php';
// 查询管理员信息
$_USER = user_current();
// 方法
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;

switch ($method) {
	// 强力插入
    case 'new':
    	$action = isset($_GET['action'])?$_GET['action']:null;
    	current_user_can('domain-new');
    	system_head('styles', array('css/chosen'));
		system_head('scripts',array('js/jquery.chosen'));
		system_head('scripts',array('js/domain'));
	    system_head('loadevents','domain_manage_init');
	    system_head('title', '添加域名');
	    include ADMIN_PATH.'/admin-header.php';
    	switch ($action) {
    		case 'bulk':
    			system_head('title', '批量添加域名');
    			domain_manage_page('bulk-add');
    			break;
    		
    		default:
    			domain_manage_page('add');
    			break;
    	}    
	    include ADMIN_PATH.'/admin-footer.php';
	    break;
	// 编辑
	case 'edit':
		system_head('title', '编辑域名信息');
        current_user_can('domain-edit');
        system_head('styles', array('css/chosen'));
		system_head('scripts',array('js/jquery.chosen'));
        system_head('scripts',array('js/domain'));
	    system_head('loadevents','domain_manage_init');
	    include ADMIN_PATH.'/admin-header.php';
	    domain_manage_page('edit');	    
	    include ADMIN_PATH.'/admin-footer.php';
	    break;
	case 'unapprove':
		$listids = isset($_POST['listids'])?$_POST['listids']:null;
	    if (empty($listids)) {
	    	ajax_error('你没有选择任何项目.');
	    }
		current_user_can('domain-state');
		foreach ($listids as $dmid) {
			domain_edit($dmid,array('status'=>'pending'));
		}
		redirect(referer());
		break;
	case 'approve':
		$listids = isset($_POST['listids'])?$_POST['listids']:null;
	    if (empty($listids)) {
	    	ajax_error('你没有选择任何项目.');
	    }
		current_user_can('domain-state');
		$approved = count($listids);
		foreach ($listids as $dmid) {
			domain_edit($dmid,array('status'=>'approved'));
		}
		ajax_success(sprintf('%s 个域名审核通过.', $approved),"InfoSYS.redirect('".referer()."');");
		break;
	case 'delete':
		$action  = isset($_POST['action'])?$_POST['action']:null;
	    $listids = isset($_POST['listids'])?$_POST['listids']:null;
	    if (empty($listids)) {
	    	ajax_error('你没有选择任何项目。');
	    }
		current_user_can('domain-delete');
		
		foreach ($listids as $id) {
			if('domainmeta'==$action){
				domain_meta_delete($id);
				$result ='域名访问信息已删除.';
			}else {
				domain_delete($id);
				$result ='域名信息已删除.';
			}
		}
		ajax_success($result,"InfoSYS.redirect('".referer()."');");
	    break;
	case 'export':
		set_time_limit(0);
		ini_set("memory_limit","-1");
		$db = get_conn();
		$sql = "SELECT * FROM `#@_domain` WHERE `domain`<>'' ORDER BY `author` DESC;";
		$result = $db->query($sql);
		include_file(COM_PATH.'/system/Excel/PHPExcel.php');
		$objPHPExcel = new PHPExcel();
		$objSheet = $objPHPExcel->getActiveSheet();
		$rowCount = 2;
		if ($result) {
			//表头
			$objSheet->SetCellValue('A1', '域名');
			$objSheet->SetCellValue('B1', '所属人');
			$objSheet->SetCellValue('C1', '注册日期');
			$objSheet->SetCellValue('D1', '过期日期');
			$objSheet->SetCellValue('E1', '注册商');
			$objSheet->SetCellValue('F1', '状态');
			$objSheet->SetCellValue('G1', '语种');
			$objSheet->SetCellValue('H1', '类型');
			$objSheet->SetCellValue('I1', '标记');
			//表内容
			while ($row = $db->fetch($result)) {
				$objSheet->SetCellValue('A'.$rowCount, $row['domain']);
				$objSheet->SetCellValue('B'.$rowCount, $row['author']);
				$objSheet->SetCellValue('C'.$rowCount, $row['registrationdate']);
				$objSheet->SetCellValue('D'.$rowCount, $row['expirationdate']);
				$objSheet->SetCellValue('E'.$rowCount, $row['registrar']);
				$objSheet->SetCellValue('F'.$rowCount, $row['status']);
				$objSheet->SetCellValue('G'.$rowCount, $row['language']);
				$objSheet->SetCellValue('H'.$rowCount, $row['domaintype']);
				$objSheet->SetCellValue('I'.$rowCount, $row['marker']);

				$rowCount++; 
			}
			//列宽
			$objSheet->getColumnDimension('A')->setWidth(50);
			$objSheet->getColumnDimension('B')->setWidth(10);
			$objSheet->getColumnDimension('C')->setWidth(20);
			$objSheet->getColumnDimension('D')->setWidth(20);
		}
		$objSheet->setTitle('域名数据表');
		$excel_name = '域名数据'.date('Ymd',time());
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$excel_name.'.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		break;
	case 'getwhois' :
		break;
	// 保存
	case 'save':
		$domainid = isset($_POST['domainid'])?$_POST['domainid']:0;
	    current_user_can($domainid?'domain-edit':'domain-new');
        if (validate_is_post()) {
            $referer  = referer(PHP_FILE,false);
			
			$author				= isset($_POST['author'])?$_POST['author']:null;
    		$domain  			= isset($_POST['domain'])?$_POST['domain']:null;
    		$registrationdate   = isset($_POST['registrationdate'])?$_POST['registrationdate']:null;
    		$renewaldate   		= isset($_POST['renewaldate'])?$_POST['renewaldate']:null;
    		$expirationdate  	= isset($_POST['expirationdate'])?$_POST['expirationdate']:null;
    		$registrar    		= isset($_POST['registrar'])?$_POST['registrar']:null;
			$renewalurl  		= isset($_POST['renewalurl'])?$_POST['renewalurl']:null;
			$description		= isset($_POST['description'])?$_POST['description']:null;
			$status				= isset($_POST['status'])?$_POST['status']:null;
			$whoisdata			= isset($_POST['whois'])?$_POST['whois']:null;

			//2013-10-25增加的字段
			$domaintype			= isset($_POST['domain_type'])?$_POST['domain_type']:null;
			$loginurl 			= isset($_POST['loginurl'])?$_POST['loginurl']:null;
			$loginname 			= isset($_POST['loginname'])?$_POST['loginname']:null;
			$loginpass 			= isset($_POST['loginpass'])?$_POST['loginpass']:null;
			$language 			= isset($_POST['language'])?$_POST['language']:null;
			$marker 			= isset($_POST['marker'])?$_POST['marker']:null;
			$groupid 			= isset($_POST['groupid'])?$_POST['groupid']:null;
			
			//访问信息
			$type		= isset($_POST['info_type'])?$_POST['info_type']:null;
			$username	= isset($_POST['username2'])?$_POST['username2']:null;
			$password	= isset($_POST['password2'])?$_POST['password2']:null;
			$host		= isset($_POST['host2'])?$_POST['host2']:null;
			$dbname		= isset($_POST['database2'])?$_POST['database2']:null;
			$notes		= isset($_POST['notes2'])?$_POST['notes2']:null;
			$editaccess = isset($_POST['editAccess'])?$_POST['editAccess']:null;
			
			$user = user_get_byname($author);
			$userid = isset($user['userid'])?$user['userid']:null;
			
			
			if($domainid && $editaccess == 'yes') {
				validate_check('username2',VALIDATE_EMPTY,'用户名不能为空');
				validate_check('password2',VALIDATE_EMPTY,'密码不能为空.');
			} else {
				validate_check('domain',VALIDATE_EMPTY,'你必须输入一个域名');
			}

			$url 	= new parseURL($domain); 
			$domain = $url->getRegisterableDomain();
			
			//validate_check('domain',VALIDATE_EMPTY,'你必须输入一个域名');
			
			if (validate_is_ok()) {
				//if not supply whois
				if($expirationdate==null || $expirationdate=='0000-00-00 00:00:00'){
					include COM_PATH.'/classes/whois.php';
			    	$whois = new whois(COM_PATH.'/whois/');
			    	try{

			    		$whois->set_domain($domain);
				        if ($whois->is_registered()){
				            $expirationdate = $whois->get_expiry();
				            $registrationdate = $whois->get_creation();
				            $whoisdata = implode("|",$whois->get_raw_date());
				        }
				        else{
				            $expirationdate = 'NULL';
				            $registrationdate = 'NULL';
				        }

				    }
				    catch (Exception $e){
				        $expirationdate = null;
				        $registrationdate = null;
				        //ajax_error($e->getMessage());
				    }

				    if($expirationdate) $expirationdate = W3cDate(strtotime($expirationdate)); //UNIXTIME
				    if($registrationdate)  $registrationdate = W3cDate(strtotime($registrationdate));
				}

			    //
				$data = array(
					'author'   			=> $author,
					'userid'   			=> $userid,
					'domain'   			=> $domain,
					'description'   	=> $description,
					'registrationdate'  => $registrationdate,
					'renewaldate'   	=> $renewaldate,
					'expirationdate'   	=> $expirationdate,
					'registrar'   		=> $registrar,
					'renewalurl'   		=> $renewalurl,
					//'status'			=> $status,
					'whois'				=> $whoisdata,
					'domaintype'		=> $domaintype,
					'extloginurl'		=> $loginurl,
					'extloginname'		=> $loginname,
					'extloginpass'		=> $loginpass,
					'language'			=> $language,
					'marker'			=> $marker,
					'groupid'			=> $groupid,

				);
				
				//访问信息
				if($domainid && $editaccess == 'yes') {
					
					//添加域名访问信息
					$meta	= array(
						'type'		=> $type,
						'username'	=> $username,
						'password'	=> $password,
						'host'		=> $host,
						'database'	=> $dbname,
						'notes'		=> $notes,
					);
					domain_add_meta($domainid,$meta);
					$result = '域名访问信息添加成功.';
					ajax_success($result, "InfoSYS.redirect('".referer()."');");
				}else {
					if($status) $data['status'] = $status;
					// 更新
					if ($domainid) {
						domain_edit($domainid,$data);
						$result = '域名信息更新完成.';
					}
					// 强力插入
					else {
						
						if ($domain = domain_add($domain,$data)) {
							$domainid = $domain['id'];
							$result = '域名添加成功.';
						} else {
							$result = '域名添加失败，可能域名已经存在。';
						}
						
					}
				}
				
				
				
				ajax_success($result, "InfoSYS.redirect('".$referer."');");
			}

		}
		break;
	case 'import':
		/*
		error_reporting(E_ALL | E_STRICT);
		include COM_PATH.'/system/UploadHandler.php';
		$upload_handler = new UploadHandler(array(
			'upload_dir'=>COM_PATH.'/upload/',
			'upload_url' => guess_url(). '/common/upload/',
		));

		$files = $upload_handler->get(false);
		$filename = $files['files'][0]->name;
		*/
		//$type = $_POST['mimetype']; 
    	$xhr = $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'; 

    	if ( !isset($_FILES['importFile']) ) {
			$file['error'] = '文件是空的。';
		}else if($_FILES["importFile"]["error"] > 0){
			$status['status'] = false;
			$status['message'] = "文件上传发生错误：".$_FILES["importFile"]["error"];
			echo json_encode($status);
			exit();
		}
		
		$tmp_file = $_FILES["importFile"]["tmp_name"];
		$file_types = explode ( ".", $_FILES ['importFile'] ['name'] );
		$file_type = $file_types [count ( $file_types ) - 1];

		/*判别是不是.xls文件，判别是不是excel文件*/
		if (strtolower ( $file_type ) != "xls")              
		{
			$status['status'] = false;
			$status['message'] = "文件格式不对，请上传.xls的Excel文件。";
			echo json_encode($status);
			exit();
		
		}

		include_file(COM_PATH.'/system/Excel/PHPExcel.php');
		$objReader = PHPExcel_IOFactory::createReader('Excel5'); 
		$objReader->setReadDataOnly(true); 
		$objPHPExcel = $objReader->load($tmp_file); 
		$objWorksheet = $objPHPExcel->getActiveSheet(); 
		
		$highestRow = $objWorksheet->getHighestRow(); 
		$highestColumn = $objWorksheet->getHighestColumn(); 
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); 
		$excelData = array(); 

		for ($row = 2; $row <= $highestRow; $row++) { 
			for ($col = 0; $col < $highestColumnIndex; $col++) {
				$field = (string)$objWorksheet->getCellByColumnAndRow($col, 1)->getValue();
				if($field!=null)
					$excelData[$row][$field] =(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
			}
			$domain	= isset($excelData[$row]['域名'])?$excelData[$row]['域名']:null;
			$name 	= isset($excelData[$row]['所属人'])?$excelData[$row]['所属人']:null;

			//濡加域名
			$user = user_get_byname($name);
			$author = isset($user['name'])?$user['name']:$_USER['name'];

			domain_add($domain,array('author'=>$author,'userid'=>$user['userid']));
		}

		ajax_success('域名导入成功。');

    	//if ($type == 'json') { 
        	//header('Content-type: text/xml');
        //var_dump($_POST); 
        foreach($_FILES as $file) { 
            $n = $file['name']; 
            $s = $file['size']; 
            if (!$n) continue; 
            echo "File: $n ($s bytes)"; 
        } 

		break;
	// 默认
	default:
		current_user_can('domain-list');
		system_head('title',  '域名管理');
		system_head('scripts',array('js/jquery.form')); // 
		system_head('scripts',array('js/domain'));
	    system_head('loadevents','domain_list_init');
		$domain_count = domain_count();
		$search   = isset($_REQUEST['query'])?$_REQUEST['query']:'';
		$query    = array('page' => '$');
		$type 	  = isset($_REQUEST['type'])?$_REQUEST['type']:'';
		// 排序方式
		$order =  'ASC';
		
		$conditions = array();
		$where = "WHERE 1";
		$is_domain_admin = current_user_can('domain-admin',false);
		if(!current_user_can('ALL',false) && !$is_domain_admin)
			$where.= ' AND `userid`='.$_USER['userid'];

		if($type){
		    $query['type'] = $type;
		    if($type=="pending")
		    	$where.= ' AND `status`= "pending" ';
		    else if($type=="approved")
		    	$where.= ' AND `status`= "approved" ';
		    else if($type == "expired")
		    	$where.= ' AND DATE(`expirationdate`) <= DATE_ADD(CURDATE(), INTERVAL + 90 DAY) ';
		}

		$db = get_conn();
		// 根据分类筛选
		if ($search) {
			$query['query'] = $search;
			$fields = array('`d`.`domain`','`d`.`author`','`d`.`registrar`','`d`.`renewalurl`','`d`.`description`','`dm`.`username`','`dm`.`password`','`dm`.`host`','`dm`.`database`','`dm`.`notes`');
			foreach($fields as $field) {
            	$conditions[] = sprintf("BINARY UCASE(%s) LIKE UCASE('%%%%%s%%%%')",$field,esc_sql($search));
            }
            $where.= ' AND ('.implode(' OR ', $conditions).')';
			$sql = "SELECT DISTINCT(`d`.`id`) FROM `#@_domain` AS `d` LEFT JOIN `#@_domain_meta` AS `dm` ON `d`.`id`=`dm`.`domainid` {$where} ORDER BY `d`.`id` ASC";

			$domain_count = $db->result("SELECT COUNT(DISTINCT(`d`.`id`)) FROM `#@_domain` AS `d` LEFT JOIN `#@_domain_meta` AS `dm` ON `d`.`id`=`dm`.`domainid` {$where} ORDER BY `d`.`id` ASC");
		}  else {
			// 没有任何筛选条件
		    $sql = "SELECT `id` FROM `#@_domain` {$where} ORDER BY `id` {$order}";
		    $domain_count = $db->result("SELECT count(`id`) FROM `#@_domain` {$where} ORDER BY `id` {$order}");
		}
		$result = pages_query($sql);

		

		$count_expired_result = $db->query("SELECT count(`id`) AS count FROM `#@_domain` WHERE DATE(`expirationdate`) <= DATE_ADD(CURDATE(), INTERVAL + 90 DAY)");
		$count_expired = $db->fetch($count_expired_result);
		

		// 分页地址
		$page_url   = PHP_FILE.'?'.http_build_query($query);
		
		// 加载头部
		include ADMIN_PATH.'/admin-header.php';
		
		echo '<div class="module-header">';
		echo	'<h3><i class="icon-tags"></i> 域名信息</h3>';
		echo '</div>';

		if($count_expired['count']){
			echo '<div class="alert alert-error fade in">';
			echo   "有 <b>".$count_expired['count']."</b> 个域名三个月后即将过期，请通知域名管理人员续费！";
			echo "</div>";
		}
		
		
		echo '<div class="tabbable">';
		echo	'<ul class="nav nav-tabs">';
		echo		'<li class="active"><a href="#domains" data-toggle="tab">所有域名 ('.$domain_count.')</a></li>';
		echo	'</ul>';
		echo	'<div class="tab-content">';
		echo		'<div class="tab-pane fade active in" id="domains">';
		
		table_nav('top',$page_url);
		
		echo		  '<div class="widget widget-table">';
		echo		    '<div class="widget-header">';
		echo			  '<h3><i class="icon-list icon-border"></i> 域名列表</h3>';
		echo			  '<div class="pull-right" style="margin-right:10px">';
		echo			    '<a class="btn btn-small" href="'.PHP_FILE.'?method=new"><i class="icon-plus"></i> 添加域名</a>';
		echo              '</div>';
		echo            '</div>';
		echo			'<div class="widget-content">';
		echo			  '<table class="table-report table table-striped table-hover table-bordered">';
		echo			    '<thead>';
		table_thead();
		echo			    '</thead>';
		echo           		'<tfoot>';
		table_thead();
		echo           		'</tfoot>';
		echo			    '<tbody>';
		if ($result) {

			while ($data = pages_fetch($result)) {
				$domain    = domain_get($data['id']);
				$group 	   = domain_group_get($domain['groupid'],0);
				$groupname = isset($group['name'])?$group['name']:null;
				$edit_url = PHP_FILE.'?method=edit&id='.$domain['id'];
				$label_status = $domain['status']=='pending'?'label-warning':'label-success';
				echo '<tr class="domain-'.$domain['id'].'">';
                echo    '<td class="check-column"><input type="checkbox" name="listids[]" value="'.$domain['id'].'" /></td>';
				echo    '<td><a href="'.$edit_url.'" title="'.$domain['domain'].'">'.$domain['domain'].'</a></td>';
				echo    '<td><span class="label '.$label_status.'">'.translate_status($domain['status']).'</span></td>';
				echo    '<td>'.$domain['author'].'</td>';
				echo    '<td>'.$groupname.'</td>';
				echo    '<td>'.$domain['registrationdate'].'</td>';
				echo    '<td>'.$domain['expirationdate'].'</td>';
				echo    '<td>'.$domain['registrar'].'</td>';
				echo    '<td>'.$domain['addtime'].'</td>';
				echo '</tr>';
			}
		} else {
            echo              '<tr><td colspan="9" class="tc">没有任何记录!</td></tr>';
        }
		echo			    '</tbody>';
		echo              '</table>';
		echo            '</div><!--/.widget-content-->';
		echo          '</div>';
		
		table_nav('bottom',$page_url);
		
		echo        '</div>';
		echo    '</div>';
		echo '</div><!--/.tabbable-->';
		
		// 加载尾部
		include ADMIN_PATH.'/admin-footer.php';
		break;
}
function translate_status($string) {
	$status = array('pending'=>'待审核','approved'=>'已审核');
	$string = trim($string);
	if(isset($status[$string])) return $status[$string];
	return $string;
}
/**
 * 批量操作
 *
 * @param  $side    top|bottom
 * @param  $url
 * @return void
 */
function table_nav($side,$url) {
    global $php_file, $category, $search, $type;
	$referer = referer(PHP_FILE);
	$is_domain_admin = current_user_can('domain-admin',false);
    echo '<div class="table-nav clearfix">';
	echo   '<div class="btn-group pull-left">';
	echo     '<button class="btn" data-toggle="tooltip" data-original-title="返回上级URL" onclick="javascript:;InfoSYS.redirect(\''.$referer.'\')"><i class="icon-arrow-up"></i> 返回</button>';
	echo	 '<button class="btn" name="delete" data-toggle="tooltip" data-original-title="请选择后再删除"><i class="icon-remove"></i> 删除</button>';
	echo   '</div>';
	if($is_domain_admin || current_user_can('ALL',false)){
	echo   '<div class="btn-group pull-left">';
	echo	 '<button class="btn" name="approve"><i class="icon-ok"></i> 审核通过</button>';
	echo	 '<button class="btn" name="unapprove"><i class="icon-minus"></i> 取消审核</button>';
	echo   '</div>';
	}
	echo    '<div class="btn-group">';
	echo	  '<button data-toggle="tooltip" data-original-title="导出为Excel (xls)格式" class="btn" name="export"><i class="icon-download"></i> 导出所有域名</button>';
	echo	  '<a href="#collapseImport" data-toggle="collapse" class="btn"><i class="icon-upload"></i> 批量导入域名</a>';
	echo    '</div>';
	if ($side == 'top') {
		
		echo '<div class="pull-right btn-group">';
		

		echo 	'<form action="" method="get" class="form-inline" id="formSearch">';

		
		echo '<select name="type" class="span2">';
        echo     '<option value="">查看所有域名</option> ';
        echo     '<option value="pending"'.($type=="pending"?' selected="selected"':"").'>待审核域名</option> ';
        echo     '<option value="approved"'.($type=="approved"?' selected="selected"':"").'>已审核域名</option> ';
        echo     '<option value="expired"'.($type=="expired"?' selected="selected"':"").'>即将过期域名(3个月)</option> ';
        echo '</select> ';

		echo ' <div class="input-append"> <input class="span2 search-query" name="query" type="text" value="'.esc_html($search).'"> <button class="btn" type="submit" onclick="javascript:;">搜索</button></div> </form> ';
	
	echo 	'</div>';

	echo '<div id="collapseImport" class="accordion-body collapse">';
	echo   '<div class="">';
	echo     '<form action="'.PHP_FILE.'?method=import" method="post" enctype="multipart/form-data" style="margin: 10px 0;padding:20px;border: 5px solid #C5DBEC;background: #DFEFFC;">';
	echo       '<input type="file" name="importFile" style="line-height:0;">';
	echo       '<button type="submit" class="btn btn-primary start"><i class="glyphicon glyphicon-upload"></i><span>开始导入</span></button>';
	echo     '</form>';
	echo     '<div class="progress"><div class="bar"></div><div class="percent">0%</div></div>';
	echo     '<div id="status"></div>';
	echo   '</div>';
    echo '</div>'; // #collapseImport

	} else {
        echo pages_list($url);
    }
	echo '</div>';
}
/**
 * 表头
 *
 */
function table_thead() {
    global $php_file;
    echo '<tr>';
    echo     '<th class="check-column" id="cb"><input type="checkbox" name="select" value="all" /></th>';
    echo     '<th class="span3">域名</th>';
    echo     '<th>状态</th>';
    echo     '<th class="">现所属人</th>';
    echo     '<th class="">分组</th>';
    echo     '<th class="wp15">注册日期</th>';
    echo     '<th class="w100">过期日期</th>';
	echo     '<th class="w100">注册商</th>';
	echo     '<th>添加日期</th>';
    echo '</tr>';
}

function domain_manage_page($action) {
	global $php_file,$_USER;
    $referer = referer(PHP_FILE);
    $domainid  = isset($_GET['id'])?$_GET['id']:0;
    if ($action!='add') {
    	$_DATA  = domain_get($domainid);
    }
	$userid				= isset($_DATA['userid'])?$_DATA['userid']:null;
    $author				= isset($_DATA['author'])?$_DATA['author']:null;
    $domain  			= isset($_DATA['domain'])?$_DATA['domain']:null;
    $registrationdate   = isset($_DATA['registrationdate'])?$_DATA['registrationdate']:null;
    $renewaldate   		= isset($_DATA['renewaldate'])?$_DATA['renewaldate']:null;
    $expirationdate  	= isset($_DATA['expirationdate'])?$_DATA['expirationdate']:null;
    $registrar    		= isset($_DATA['registrar'])?$_DATA['registrar']:null;
	$renewalurl  		= isset($_DATA['renewalurl'])?$_DATA['renewalurl']:null;
	$description		= isset($_DATA['description'])?$_DATA['description']:null;
	$status				= isset($_DATA['status'])?$_DATA['status']:null;
	$whoisdata			= isset($_DATA['whois'])?$_DATA['whois']:null;

	$domaintype			= isset($_DATA['domaintype'])?$_DATA['domaintype']:null;
	$loginurl			= isset($_DATA['extloginurl'])?$_DATA['extloginurl']:null;
	$loginname			= isset($_DATA['extloginname'])?$_DATA['extloginname']:null;
	$loginpass			= isset($_DATA['extloginpass'])?$_DATA['extloginpass']:null;
	$language			= isset($_DATA['language'])?$_DATA['language']:null;
	$marker				= isset($_DATA['marker'])?$_DATA['marker']:null;
	$groupid			= isset($_DATA['groupid'])?$_DATA['groupid']:null;

	$domain_group_list = domain_group_get_list();
	
	$is_domain_admin = current_user_can('domain-admin',false);
	//是否可以管理当前域名
	if($action!='add'){
		if(!current_user_can('ALL',false) && !$is_domain_admin && $author!=$_USER['name']){
			return false;
		}
	}
	
	echo	'<div class="module-header">';
	echo		'<h3>';
	if ($action=='add') {
		echo		'<i class="icon-plus-sign-alt"></i> 添加域名';
	}else{
		echo		'<i class="icon-edit icon-large"></i> 编辑域名';
	}
	echo		'</h3>';
	echo	'</div>';
	
	echo '<div class="row-fluid">';
	
	
	echo     '<div class="widget">';
	echo	   '<div class="widget-header">';
	echo		  '<i class="icon-cog"></i><h3>域名信息</h3>';
	echo	   '</div>';
	echo	   '<div class="widget-content">';
	
	echo         '<form action="'.PHP_FILE.'?method=save" method="post" name="domainmanage" class="form-horizontal form-horizontal-small" id="domainmanage">';
	
	echo         '<div class="row-fluid">';
	
	echo         '<div class="span6">';
	
	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">所属人</label>';
	echo		   '<div class="controls">';
	
	if(current_user_can('ALL',false) || $is_domain_admin) {
		echo             '<select name="author" class="chosen">';
		echo               '<option value="">选择所属人</option>';
		echo               dropdown_users($userid);
		echo             '</select>';
	}elseif($action=='add') {
		echo		     '<input type="text" name="author" value="'.$_USER['name'].'" readonly="readonly">';
	}else{
		echo		     '<input type="text" name="author" value="'.$author.'" readonly="readonly">';
	}
	//echo		     '<input type="text" name="author" placeholder="" value="'.$author.'">';
	echo		   '</div>';
	echo         '</div>';
	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">域名</label>';
	echo		   '<div class="controls">';
	echo		     '<input type="text" name="domain" placeholder="" value="'.$domain.'">';
	echo		   '</div>';
	echo         '</div>';

	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">网站类型</label>';
	echo		   '<div class="controls">';
	echo             '<select name="domain_type" class="chosen">';
	echo                '<option value="">-- 必须选择一个类型 --</option>';
	echo                '<option value="优化站"'.($domaintype=='优化站'?' selected="selected"':'').'>优化站（只做排名）</option>';
	echo                '<option value="询盘站"'.($domaintype=='询盘站'?' selected="selected"':'').'>询盘站（只做询盘，正规站）</option>';
	echo                '<option value="站群"'.($domaintype=='站群'?' selected="selected"':'').'>站群</option>';
	echo                '<option value="采集站"'.($domaintype=='采集站'?' selected="selected"':'').'>采集站</option>';
	echo                '<option value="垃圾站"'.($domaintype=='垃圾站'?' selected="selected"':'').'>垃圾站</option>';
	echo                '<option value="私人站"'.($domaintype=='私人站'?' selected="selected"':'').'>私人站</option>';
	echo                '<option value="特殊"'.($domaintype=='特殊'?' selected="selected"':'').'>特殊</option>';
	echo                '<option value="其它"'.($domaintype=='其它'?' selected="selected"':'').'>其它</option>';
	echo             '</select>';
	echo		   '</div>';
	echo         '</div>';
	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">网站标记</label>';
	echo		   '<div class="controls">';
	echo             '<select name="marker" class="chosen">';
	echo                '<option value="">-- 选择你网站状态 --</option>';
	echo                '<option value="被Google惩罚"'.($marker=='被Google惩罚'?' selected="selected"':'').'>被Google惩罚</option>';
	echo                '<option value="被Baidu惩罚"'.($marker=='被Baidu惩罚'?' selected="selected"':'').'>被Baidu惩罚</option>';
	echo                '<option value="到期不续费"'.($marker=='请求到期不续费'?' selected="selected"':'').'>请求到期不续费</option>';
	echo             '</select>';
	echo		   '</div>';
	echo         '</div>';
	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">网站语种</label>';
	echo		   '<div class="controls">';
	echo             '<select name="language" class="chosen">';
	echo                '<option value="">-- 选择你网站语言 --</option>';
	echo                '<option value="英语"'.($language=='英语'?' selected="selected"':'').'>英语</option>';
	echo                '<option value="中文"'.($language=='中文'?' selected="selected"':'').'>中文</option>';
	echo                '<option value="俄语"'.($language=='俄语'?' selected="selected"':'').'>俄语</option>';
	echo                '<option value="法语"'.($language=='法语'?' selected="selected"':'').'>法语</option>';
	echo                '<option value="印尼语"'.($language=='印尼语'?' selected="selected"':'').'>印尼语</option>';
	echo                '<option value="西班牙语"'.($language=='西班牙语'?' selected="selected"':'').'>西班牙语</option>';
	echo                '<option value="葡萄牙语"'.($language=='葡萄牙语'?' selected="selected"':'').'>葡萄牙语</option>';
	echo                '<option value="阿拉伯语"'.($language=='阿拉伯语'?' selected="selected"':'').'>阿拉伯语</option>';
	echo                '<option value="泰语"'.($language=='泰语'?' selected="selected"':'').'>泰语</option>';
	echo                '<option value="波斯语"'.($language=='波斯语'?' selected="selected"':'').'>波斯语</option>';
	echo                '<option value="越南语"'.($language=='越南语'?' selected="selected"':'').'>越南语</option>';
	echo                '<option value="其它"'.($language=='其它'?' selected="selected"':'').'>其它语种</option>';
	echo             '</select>';
	echo		   '</div>';
	echo         '</div>';
	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">所在组</label>';
	echo		   '<div class="controls">';
	echo             '<select name="groupid" class="chosen">';
	
	echo                '<option value="">-- 域名所在组--</option>';
	echo 				dropdown_groups($groupid);
	echo             '</select>';
	echo		   '</div>';
	echo         '</div>';

	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">域名管理地址</label>';
	echo		   '<div class="controls">';
	echo		     '<input type="text" name="loginurl" placeholder="www.uvip.cn" value="'.$loginurl.'">';
	echo		   '</div>';
	echo         '</div>';
	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">域名用户名</label>';
	echo		   '<div class="controls">';
	echo		     '<input type="text" name="loginname" placeholder="域名管理用户名" value="'.$loginname.'">';
	echo		   '</div>';
	echo         '</div>';
	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">域名密码</label>';
	echo		   '<div class="controls">';
	echo		     '<input type="text" name="loginpass" placeholder="域名管理密码" value="'.$loginpass.'">';
	echo		   '</div>';
	echo         '</div>';

	if(current_user_can('ALL',false) || $is_domain_admin) {
		echo		 '<div class="control-group"><label class="control-label" for="status">审核状态</label>';
		echo		   '<div class="controls">';
		echo             '<select name="status" class="chosen">';
		echo               '<option value="approved"'.($status=='approved'?' selected="selected"':'').'>通过</option>';
		echo               '<option value="pending"'.($status=='pending'?' selected="selected"':'').'>待审</option>';
		echo               '<option value="closed"'.($status=='closed'?' selected="selected"':'').'>过期域名</option>';
		echo             '</select>';
		echo		   '</div>';
		echo		 '</div>';
	}else{
		//echo		 '<input type="hidden" name="status" value="'.$status.'">';
	}
	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">注册商</label>';
	echo		   '<div class="controls">';
	echo		     '<input type="text" name="registrar" placeholder="" value="'.$registrar.'">';
	echo		   '</div>';
	echo         '</div>';
	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">注册日期</label>';
	echo		   '<div class="controls">';
	echo		     '<input type="text" name="registrationdate" placeholder="格式：1970-07-01 00:00:00" value="'.$registrationdate.'">';
	echo 			 '<span class="help-block">注册日期及过期日期系统可以自动获取，获取不到再自己填写。</span>';
	echo		   '</div>';
	echo         '</div>';
	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">续订日期</label>';
	echo		   '<div class="controls">';
	echo		     '<input type="text" name="renewaldate" placeholder="格式：1970-07-01 00:00:00" value="'.$renewaldate.'">';
	echo		   '</div>';
	echo         '</div>';
	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">过期日期</label>';
	echo		   '<div class="controls">';
	echo		     '<input type="text" name="expirationdate" placeholder="格式：1970-07-01 00:00:00" value="'.$expirationdate.'">';
	echo		   '</div>';
	echo         '</div>';
	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">续费URL</label>';
	echo		   '<div class="controls">';
	echo		     '<input type="text" name="renewalurl" placeholder="" value="'.$renewalurl.'">';
	echo		   '</div>';
	echo         '</div>';

	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">备注</label>';
	echo		   '<div class="controls">';
	echo		     '<textarea class="autosize-transition" name="description">'.$description.'</textarea>';
	echo		   '</div>';
	echo         '</div>';
	
	
	
	echo	     '</div><!---/span-->';
	echo         '<div class="span6">';
	echo           '<label>WHOIS信息（请自动获取，获取不到再手工填写）</label>';
	echo		   '<textarea class="autosize-transition span12" rows="20" name="whois" >'.$whoisdata.'</textarea>';
	echo         '</div>';
	echo	   '</div>';
	
	echo	   '<div class="control-group">';
	echo		   '<div class="controls">';

	if ($action=='add') {
        echo   '<button type="submit"  class="btn btn-primary"><i class="icon-plus-sign"></i> 添加域名信息</button>';
    } else {
        $hidden = '<input type="hidden" name="domainid" value="'.$domainid.'" />';
        $hidden.= '<input type="hidden" name="referer" value="'.referer().'" />';
        echo   '<button type="submit" class="btn btn-primary"><i class="icon-ok-sign"></i> 更新域名信息</button>'.$hidden;
    }
	echo       ' <button type="button" class="btn" onclick="InfoSYS.redirect(\''.$referer.'\')"><i class="icon-backward"></i> 返回 </button>';

	echo	      '</div>';
	echo	     '</div>';
	echo       '</form>';
	
	echo	   '</div>';//widet content
	echo	 '</div>'; //widget\
	
	echo     '<div class="widget">';
	echo	   '<div class="widget-header">';
	echo		  '<i class="icon-cog"></i><h3>访问信息</h3>';
	echo	   '</div>';
	echo	   '<div class="widget-content">';
	echo       '<form action="'.PHP_FILE.'?method=delete" method="post" class="form-horizontal">';
	$db = get_conn();
	$sql = "SELECT * FROM `#@_domain_meta` WHERE `domainid`={$domainid} ORDER BY `metaid` DESC";
	$result = $db->query($sql);
	echo		  '<table class="table-report table table-striped table-hover table-bordered">';
	echo		    '<thead>';
	echo              '<tr>';
	echo                '<th class="check-column"><input type="checkbox" name="select" value="all" /></th>';
	echo                '<th>类型</th>';
	echo                '<th>用户名</th>';
	echo                '<th>密码</th>';
	echo                '<th>主机</th>';
	echo                '<th>数据库名</th>';
	echo                '<th>备注</th>';
	echo              '</tr>';
	echo			'</thead>';
	echo		    '<tbody>';
	if ($result) {
		while ($data = $db->fetch($result)) {
			echo '<tr>';
			echo    '<td class="check-column"><input type="checkbox" name="listids[]" value="'.$data['metaid'].'" /></td>';
			echo   '<td>'.$data['type'].'</td>';
			echo   '<td>'.$data['username'].'</td>';
			echo   '<td>'.$data['password'].'</td>';
			echo   '<td>'.$data['host'].'</td>';
			echo   '<td>'.$data['database'].'</td>';
			echo   '<td>'.$data['notes'].'</td>';
			echo '</tr>';
			//print_r($data);
		}
	}
	echo		    '</tbody>';
	echo          '</table>';
	
	echo   '<div class="btn-group">';
	echo     '<button class="btn"><i class="icon-trash"></i> 删除</button>';
	echo     '<input type="hidden" name="action" value="domainmeta" />';
	echo   '</div>';
	echo   '</form>';
	
	//
	echo       '<form action="'.PHP_FILE.'?method=save" method="post" name="domainmanage" class="form-horizontal">';
	echo         '<fieldset>';
	echo           '<legend>添加账号信息</legend>';
	echo		 '<div class="control-group"><label class="control-label" for="status">类型</label>';
    echo		   '<div class="controls">';
	echo             '<select name="info_type">';
	echo               '<option value="域名管理"'.'>域名管理</option>';
    echo               '<option value="FTP账号"'.'>FTP账号</option>';
	echo               '<option value="数据库信息"'.'>数据库信息</option>';
	echo               '<option value="企业邮箱"'.'>企业邮箱</option>';
	echo               '<option value="控制面板"'.'>控制面板</option>';
	echo               '<option value="其它"'.'>其它</option>';
    echo             '</select>';
	echo		   '</div>';
  	echo		 '</div>';
	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">用户名</label>';
	echo		   '<div class="controls">';
	echo		     '<input type="text" name="username2" placeholder="">';
	echo		   '</div>';
	echo         '</div>';
	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">密码</label>';
	echo		   '<div class="controls">';
	echo		     '<input type="text" name="password2" placeholder="">';
	echo		   '</div>';
	echo         '</div>';
	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">主机名</label>';
	echo		   '<div class="controls">';
	echo		     '<input type="text" name="host2" placeholder="">';
	echo             '<span class="help-inline">如果不是mysql请留空.</span>';
	echo		   '</div>';
	echo         '</div>';
	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">数据库名</label>';
	echo		   '<div class="controls">';
	echo		     '<input type="text" name="database2" placeholder="">';
	echo             '<span class="help-inline">如果不是mysql请留空.</span>';
	echo		   '</div>';
	echo         '</div>';
	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">备注</label>';
	echo		   '<div class="controls">';
	echo		     '<textarea class="autosize-transition" name="notes2"></textarea>';
	echo		   '</div>';
	echo         '</div>';
	echo         '<div class="control-group control-group-mini">';
	echo		   '<div class="controls">';
	echo		     '<button class="btn" name="create" id="create"><i class="icon-plus"></i> 添加账号信息</button>';
	echo             '<input type="hidden" name="editAccess" value="yes" />';
	echo             '<input type="hidden" name="domainid" value="'.$domainid.'" />';
	echo             '<input type="hidden" name="domainname" value="'.$domain.'" />';
	echo		   '</div>';
	echo         '</div>';
	echo         '</fieldset>';
	echo       '</form>';
	
	echo       '</div>';
	echo	 '</div>';
	

	echo '</div>';

}
/**
 * [批量添加域名]
 * @param  [type] $action [description]
 * @return [type]         [description]
 */
function domain_bulk_page($action) {
	echo '<div class="module-header">';
	echo 	'<h3>';
	if ($action=='add') {
		echo		'<i class="icon-plus-sign-alt"></i> 批量添加域名';
	}else{
		echo		'<i class="icon-edit icon-large"></i> 批量编辑域名';
	}
	echo	'</h3>';
	echo '</div>';

	echo '<div class="row-fluid">';	
	
	echo   '<div class="widget">';
	echo     '<div class="widget-header">';
	echo       '<i class="icon-cog"></i><h3>域名信息</h3>';
	echo     '</div>';
	echo     '<div class="widget-content">';
	echo     '<form action="'.PHP_FILE.'?method=save" method="post" name="domainmanage" class="form-horizontal form-horizontal-small" id="domainmanage">';
	
	echo       '<div class="row-fluid">';
	echo         '<div class="control-group control-group-mini">';
	echo           '<label class="control-label">备注</label>';
	echo		   	  '<div class="controls">';
	echo		        '<textarea class="autosize-transition" name="description">'.$description.'</textarea>';
	echo		   	  '</div>';
	echo         '</div>';
	echo 	   '</div>';
	echo     '</form>';
	echo 	'</div>';
	echo   '</div>';

	echo '</div>';
}

/**
 * 显示用户树
 *
 * @param int $selected 被选择的用户ID
 * @param array $trees
 * @return string
 */
function dropdown_users($selected=0, $trees=null) {
    static $func = null,$n = 0; if (!$func) $func = __FUNCTION__;
    if ($trees===null) $trees = user_get_trees();
    $hl = ''; $space = str_repeat('&nbsp; &nbsp; ',$n); $n++;
    foreach ($trees as $tree) {
        $sel  = $selected==$tree['userid']?' selected="selected"':null;
        $hl.= '<option value="'.$tree['name'].'"'.$sel.'>'.$space.'├ '.$tree['name'].'</option>';
    	if (isset($tree['subs'])) {
    		$hl.= $func($selected,$tree['subs']);
    	}
    }
    return $hl;
}

function dropdown_groups($selected=null){
	$hl = ''; $groupname ='';
	$trees	=	domain_group_get_trees();
	//print_r($trees);
	foreach ($trees as $i=>$tree) {
		if($selected)
			$sel  = $selected==$tree['id']?' selected="selected"':null;
		else
			$sel  = null;
		$groupname = $tree['name'];
        $hl.= '<option value="'.$tree['id'].'"'.$sel.'>'.$groupname.'</option>';
		
	}
	return $hl;
}