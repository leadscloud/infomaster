<?php
defined('DIR_WEBSOCKET') || die( 'Error' );


/**
*	解析 header
*
*	1 参数 header
*	2 参数 key是否转换成小写
*
*	返回值 数组
**/
function parse_header( $html = '', $strtolower = false ) {
	if ( !$html ) {
		return array();
	}
	$html = str_replace( "\r\n", "\n", $html );
	$html = explode( "\n\n", $html, 2 );
	$header = explode( "\n", $html[0] );
	$r = array();
	foreach ( $header as $k => $v ) {
		if ( $v ) {
			$v = explode( ':', $v, 2 );
			if( isset( $v[1] ) ) {
				if ( $strtolower ) {
					$v[0] = strtolower( $v[0] );
				}
				
				if ( substr( $v[1],0 , 1 ) == ' ' ) {
					$v[1] = substr( $v[1], 1 );
				}
				$r[trim($v[0])] = $v[1];
			} elseif ( empty( $r['status'] ) && preg_match( '/^(HTTP|GET|POST)/', $v[0] ) ) {
				$r['status'] = $v[0];
			} else {
				$r[] = $v[0];
			}
		}
	}
	if ( !empty( $html[1] ) ) {
		$r['html'] = $html[1] ;
	}
	return $r;
}



/**
*	字符串转换成数组
* 
*	1 参数 输入GET类型字符串
*
*	返回值 GET数组
**/
function string_turn_array( $s ) {
	if( is_array( $s ) ) {
		return $s;
	}
	parse_str( $s, $r );
	if( get_magic_quotes_gpc() ) {
		$r = stripslashes_array( $r );
	}
	return $r;
}


/**
*	stripslashes 取消转义 数组
*
*	1 参数 输入数组
*
*	返回值 处理后的数组
**/
function stripslashes_array( $value ) {
	if ( is_array( $value ) ) {
		$value = array_map( __FUNCTION__, $value );
	} elseif ( is_object( $value ) ) {
		$vars = get_object_vars( $value );
		foreach ( $vars as $key => $data ) {
			$value->{$key} = stripslashes_array( $data );
		}
	} else {
		$value = stripslashes( $value );
	}
	return $value;
}


/**
*	数组转换成字符串
*
*	1 参数 数组
*
*	返回值 GET字符串
**/
function array_turn_string( $array = '' ) {
	if( !is_array( $array ) ) {
		return $array;
	}
	return http_build_query( $array );
}



/**
*	添加 锁定
*
*	1 参数 锁定 keys
**/
function add_lock( $keys, $wait = false ) {
	global $_lock;
	
	// 如果 $_lock 没变量 就 创建 
	if ( !isset( $_lock ) ) {
		$_lock = array();
	}
	
	// 如果有 keys 就返回false
	if ( isset( $_lock[$keys] ) ) {
		return false;
	}
		
	
	// 打开文件
	$_lock[$keys]['file'] = DIR_WEBSOCKET. '/lock/'. md5( $keys ) . '.txt';
	$_lock[$keys]['data'] = fopen( $_lock[$keys]['file'], 'w+' );
	
	// 锁定文件
	if ( $wait ) {
		$is = flock( $_lock[$keys]['data'], LOCK_EX );
	} else {
		$is = flock( $_lock[$keys]['data'], LOCK_EX|LOCK_NB );
	}
	
	if( !$is ) {
		fclose( $_lock[$keys]['data'] );
		unset( $_lock[$keys] );
		return false;
	}
	
	return true;
}