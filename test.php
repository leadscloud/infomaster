<?php
// 加载公共文件
include dirname(__FILE__).'/admin/admin.php';
/**
 * 测试页面
 */
// 文件名
$php_file = isset($php_file) ? $php_file : 'domain.php';
// 加载公共文件
//include dirname(__FILE__).'/admin/admin.php';
// 查询管理员信息
//$_USER = user_current();

function get_cat_codename($taxonomyid){
	$sort = taxonomy_get($taxonomyid);
	$name = $sort['codename'];
	if($sort['codename']==null){
		$parentid = $sort['parent'];
		if($parentid){
			return get_cat_codename($parentid);
		}else{
			return $name;
		}
	}else{
		return $name;
	}
	return $name;
}
$post = post_get(2);
$taxonomyid = isset($post['category'])?$post['category'][0]:null;
$sort = get_parent_catid($taxonomyid);
print_r($sort)

//Start
//
//
/*
echo md5('admin'.'6A718A1B-7183-97C0-E3F0-83E12B0CF092');
echo '<br>';
$user =  user_login('admin','admin');
print_r($admin);

echo '<br>';
*/

//echo authcode(1);

/**
 * fb3f5ff1838e3c83f7f69653663dc27b
 * ee4ed9c2d70719ddb12442c7a91e26f5
 */
?>