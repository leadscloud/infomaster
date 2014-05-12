<?php
// 声明  include 目录常量
define( 'DIR_WEBSOCKET', dirname(__FILE__) );

require( DIR_WEBSOCKET. '/config.php' );
require( DIR_WEBSOCKET. '/function.php' );
require( DIR_WEBSOCKET. '/class/class_websocket.php' );

if ( !add_lock( 'lock' ) ) {
	die('Running');
}


// 屏蔽错误代码
//error_reporting(0); 

// 设置超时时间
ignore_user_abort( true );
set_time_limit( 0 );

// 修改内存
ini_set( 'memory_limit', WEBSOCKET_MEMORY );


$class_websocket = new class_websocket( WEBSOCKET_HOST, WEBSOCKET_PORT );
$class_websocket->key = WEBSOCKET_KEY;
$class_websocket->domain = WEBSOCKET_DOMAIN;

$class_websocket->function['add'] = 'add_socket_call';
$class_websocket->function['get'] = 'get_socket_call';
$class_websocket->function['close'] = 'close_socket_call';

$class_websocket->run();
echo socket_strerror( $class_websocket->error() );













/**
*	添加的时候
*
*	回调函数请勿直接使用
**/
function add_socket_call( $accept, $index, $class ) {
		
	// 自动关闭 90 秒没有动作的
	$class->time[$index] = time();
	$class->bind[$index]['ip'] = $class->ip( $accept );
	
	// 关闭过久没响应的
	if ( rand( 0,1000 ) ) {
		return false;
	}
	foreach ( $class->accept as $k => $v ) {
		if ( $class->type[$k] !=  WEBSOCKET_TYPE_API ) {
			if ( empty( $class->time[$k] ) || ( time() - $class->time[$k] ) > 100 ) {
				$class->close( $v );
			}
		}
	}
}









/**
*	读取数据的时候
*
*	回调函数请勿直接使用
**/
function get_socket_call( $data, $accept, $index, $class ) {
	// 超过 1024 字节就结束
	if ( strlen( $data ) > 1024 ) {
		return false;
	}

	$data = string_turn_array( $data );

	// time 包
	if ( !empty( $data['time'] ) ) {
		$time = time();
		$class->time[$index] = $time;
		return $class->send( array( 'time' => $time ), $accept );
	}
	
	// 添加名称
	if ( !empty( $data['name'] ) ) {
		$name = htmlspecialchars( (string) $data['name'], ENT_QUOTES );
		$admin = explode( ',', $name , 2 );
		
		// 管理员的
		if ( !empty( $admin[1] ) && $admin[1] === (string) ADMIN_PASS ) {
			$name = '<strong class="admin_name">管理员:'. $admin[0] . '</strong>';
			$class->bind[$index]['admin'] = true;
		}
		
		// 你已经有名称了
		if ( !empty( $class->bind[$index]['name'] ) ) {
			return  $class->send( array( 'msg' => '<div class="msg error">你已经有名称了</div>' ), $accept );
		}
		
		// 名称已存在
		foreach ( $class->bind as $k => $v ) {
			if ( !empty( $v['name'] ) && $v['name'] == $name ) {
				return  $class->send( array( 'msg' => '<div class="msg error">名称已存在</div>' ), $accept );
			}
		}
		
		ws_send_all( array( 'list' => array( array( $name, true ) ) ), $class );
		ws_send_all( array( 'msg' => '<div class="msg login"><strong class="name">'. $name .'</strong>登录聊天室</div>' ), $class );
		
		$class->bind[$index]['name'] = $name;
		$list = array();
		foreach( $class->bind as $v ) {
			if ( !empty( $v['name'] ) ) {
				$list[] = array( $v['name'], true );
			}
		}
		$class->send( array( 'list' => $list ), $accept );
		return $class->send( array( 'name' => true, 'msg' => '<div class="msg yes">你已经成功登录上聊天室</div>' ), $accept );
	}
	
	
	// 聊天
	if ( !empty( $data['chat'] ) ) {
		$name  = empty( $class->bind[$index]['name'] ) ? '' : $class->bind[$index]['name'];
		$admin  = !empty( $class->bind[$index]['admin'] );
		$chat = $admin ? (string) $data['chat'] : nl2br( htmlspecialchars( (string) $data['chat'], ENT_QUOTES ) );
		
		if ( $admin && $chat == 'die' ) {
			die;
		}

		if ( !$name ) {
			return $class->send( array( 'msg' => '<div class="msg error">你还没有输入你的名称</div>' ), $accept );
		}
		
		return ws_send_all( array( 'chat' => '<div class="chat ' . ( $admin ? 'admin_chat' : '' ) .'"><div class="name">'. $name .' '.date('Y-m-d H:i:s').'</div><p>'. $chat .'</p></div>' ), $class );
	}
}

/**
*	读取数据的时候
*
*	回调函数请勿直接使用
**/
function close_socket_call( $bind, $class ) {
	if ( empty( $bind['name'] ) ) {
		return false;
	}
	ws_send_all( array( 'list' => array( array( $bind['name'], false ) ) ), $class );
	ws_send_all( array(  'msg' => '<div class="msg logout"><strong class="name">'. $bind['name'] .'</strong>离开聊天室</div>' ), $class );
}





function ws_send_all( $data, $class ) {
	foreach ( $class->bind as $k => $v ) {
		if ( empty( $v['name'] ) || $class->type[$k] == WEBSOCKET_TYPE_API ) {
			continue;
		}
		$class->send( $data, $class->accept[$k] );
	}
}



