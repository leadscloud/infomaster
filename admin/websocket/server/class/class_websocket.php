<?php
defined('DIR_WEBSOCKET') || die( 'Error' );

// 一次性最大处理数据
if ( !defined( 'WEBSOCKET_MAX' ) ) {
	define( 'WEBSOCKET_MAX', 1024 * 64 );
}

// 最大接收缓冲区
if ( !defined( 'WEBSOCKET_RCVBUF' ) ) {
	define( 'WEBSOCKET_RCVBUF', WEBSOCKET_MAX );
}

// 发送最大字节数
if ( !defined( 'WEBSOCKET_SNDBUF' ) ) {
	define( 'WEBSOCKET_SNDBUF', 1024 * 64 );
}


// 最大在线数量
if ( !defined( 'WEBSOCKET_ONLINE' ) ) {
	define( 'WEBSOCKET_ONLINE', 2048 );
}



// api 版本请求类型
define( 'WEBSOCKET_TYPE_API', 0 );

// 10- 版本请求类型
define( 'WEBSOCKET_TYPE_1', 1 );

// 10+ 版本请求类型
define( 'WEBSOCKET_TYPE_2', 2 );





class class_websocket{
	
	// 监听服务器
	var $host = '127.0.0.1';
	
	// 监听端口
	var $port = 843;
	
	// 监听途径
	var $path = '/';
	
	// 允许的域名
	var $domain = '';
	
	// 监听的资源
	var $socket = null;
	
	// 全部用户
	var $accept = array();
		
	// 全部类型
	var $type = array();
	
	// 绑定储存数据
	var $bind = array();
		
	// time 时间
	var $time = array();
	
	// 阻塞请求
	var $cycle = array();
	
	// 储存类
	var $class = array();
	
	// 回调函数
	var $function = array();
	
	// api 回调附加字符串
	var $key = '';
	
	
	/**
	*	默认执行
	*
	*	1 参数 连接 host
	*	2 参数 连接 port
	*
	*	无返回值
	**/
	function __construct( $host = '', $port = '', $path = '/' ) {
		if ( $host ) {
			$this->host = $host;
		}
		if ( $port ) {
			$this->port = $port;
		}
		$this->class[WEBSOCKET_TYPE_1] = new class_websocket_1;
		$this->class[WEBSOCKET_TYPE_2] = new class_websocket_2;
		$this->class[WEBSOCKET_TYPE_API] = new class_websocket_api;
	}

	
	/**
	*	运行 run
	*
	*	1 参数 接受回调 函数 
	*
	*	返回值 false or true
	**/
	function run() {
		if ( !$this->socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP ) ) {
			return false;
		}

		// 允许使用本地 地址
		socket_set_option( $this->socket, SOL_SOCKET, SO_REUSEADDR, true );
		
		// 接收缓冲区 最大字节
		socket_set_option( $this->socket, SOL_SOCKET, SO_RCVBUF, WEBSOCKET_RCVBUF );
		
		// 发送缓冲区 最大字节
		socket_set_option( $this->socket, SOL_SOCKET, SO_SNDBUF, WEBSOCKET_SNDBUF );

		// 绑定端口
		if ( !socket_bind( $this->socket, $this->host, $this->port ) ) {
			return false;
		}
	
		if ( !socket_listen( $this->socket, WEBSOCKET_MAX ) ) {
			return false;
		}
		
		
		while ( true ) {
			// 设置阻塞请求
			$this->cycle = $this->accept;
			$this->cycle[] = $this->socket;
			socket_select( $this->cycle, $write, $except, null );
			

			// 回调 全部
			empty( $this->function['all'] ) || call_user_func_array( $this->function['all'], array( $this->cycle, $this ) );
	
			
			
			foreach ( $this->cycle as $v ) {
				// 回调 for
				empty( $this->function['for'] ) || call_user_func_array( $this->function['for'], array( $v, $this ) );
				
				if ( $v === $this->socket ) {
					
					// 建立连接
					if ( !$accept = socket_accept( $v ) ) {
						continue;
					}
					$this->accept[] = $accept;
					$index = array_keys( $this->accept );
					$index = end( $index );
					$this->type[$index] = false; 
					$this->bind[$index] = array();
					$this->time[$index] = time();
					continue;		
				}
				
				// 在循环中被删除了
				if ( ( $index = $this->search( $v ) ) === false ) {
					continue;;
				}
				
				// 接收数据
				if ( !socket_recv( $v, $data, WEBSOCKET_MAX, 0 ) || !$data ) {
					$this->close( $v );
					continue;
				}
				
				$type = $this->type[$index];
				
				// 没接收到 header  的
				if ( $type === false ) {
					$type = $this->header( $data, $v );
					if ( $type === false ) {
						$this->close( $v );
						continue;
					}
					$this->type[$index] = $type;
					
					// 回调 add
					WEBSOCKET_TYPE_API == $type || empty( $this->function['add'] ) || call_user_func_array( $this->function['add'], array( $v, $index, $this ) );
					continue;
				}
				
				
				if ( !$data = $this->get( $v, $data ) ) {
					$this->close( $v );
					continue;
				}
				// 回调 get
				if ( WEBSOCKET_TYPE_API == $type ) {
					foreach ( $data as $vv ) {
						if ( empty( $this->function['api'] ) ) {
							$send = false;
						} else {
							$send = call_user_func_array( $this->function['api'], array( $vv, $v, $index, $this ) );;
						}
						$this->send( $send, $v );
					}
				} else {
					foreach ( $data as $vv ) {
						empty( $this->function['get'] ) || call_user_func_array( $this->function['get'], array( $vv, $v, $index, $this ) );
					}
				}
			}
		}
	
		return true;
	}
	
	/**
	*	搜索用户
	*
	*	1 参数 用户 accept
	*
	*	返回值 key
	**/
	function search( $accept ) {
		$search = array_search( $accept, $this->accept, true );
		if ( $search === null ) {
			$search = false;
		}
		return $search;
	}
	
	
	/**
	*	关闭连接
	*	
	*	1 参数 连接资源
	*	
	*	返回值 true false
	**/
	function close( $accept ) {
		if ( ( $index = $this->search( $accept ) ) === false ) {
			return false;
		}
		socket_close( $accept );
		$bind = array();
		if ( isset( $this->accept[$index] ) ) {
			unset( $this->accept[$index] );
		}
		
		if ( isset( $this->type[$index] ) ) {
			unset( $this->type[$index] );
		}

		if ( isset( $this->bind[$index] ) ) {
			$bind = $this->bind[$index];
			unset( $this->bind[$index] );
		}
		
		if ( isset( $this->cycle[$index] ) ) {
			unset( $this->cycle[$index] );
		}
		if ( isset( $this->time[$index] ) ) {
			unset( $this->time[$index] );
		}
		
		empty( $this->function['close'] ) || call_user_func_array( $this->function['close'], array( $bind, $this ) );
		return true;
	}
	
	/**
	*	解析获得到的函数
	*
	*	1 参数 回调 函数 
	*
	*	返回值 false or true
	**/
	function get( $accept, $data ) {
		if ( !$accept || !$data ) {
			return false;
		}		
		$index = $this->search( $accept );
		$type = $this->type[$index];
		if ( empty( $this->class[$type] ) ) {
			return false;
		}
		return $this->class[$type]->decode( $data );
	}
	
	
	/**
	*	发送
	*
	*	1 参数 发送的数据
	*	2 参数 $accept 用户标识符
	*
	*	返回值 true false;	
	**/
	function send( $data, $accept ) {
		if ( !$accept ) {
			return false;
		}
		$index = $this->search( $accept );
		$type = $this->type[$index];
		if ( empty( $this->class[$type] ) ) {
			return false;
		}
		if ( !$data = $this->class[$type]->encode( $data ) ) { 
			return false;;
		}
		if ( !$write = socket_write( $accept, $data, strlen( $data ) ) ) {
			$this->close( $accept );
		}
		return true;
	}
	
	/**
	*	发送全部用户
	*
	*	1 参数 发送的数据
	*
	*	返回值 true false;	
	**/
	function send_all( $data ) {
		if ( !$data || !$this->accept ) {
			return false;
		}
		$count = 0;
		foreach( $this->accept as $k =>	$v ) {
			$index = $this->search( $v );
			if ( $this->type[$k] !== false && $this->type[$k] != WEBSOCKET_TYPE_API && $this->send( $data, $v ) ) {
				$count++;
			}
		}
		return $count;
	}
	/**
	*	获得 连接ip
	*
	*	1 参数 accept
	*	
	*	返回值 连接的ip
	**/
	function ip( $accept ) {
		socket_getpeername( $accept, $ip );
		return $ip;
	}
	
	/**
	*	用户 header
	*
	*	1 参数 用户标识符
	*	
	*	返回值 false 或 连接类型
	**/
	function header( $data, $accept ) {
		$header = parse_header( $data, true );
		$msg = '';
		
		// 最多 4096 信息
		if ( strlen( $data ) >= 4096 ) {
			return false;
		}
		
		// 系统本身的 api 条用
		if ( !empty( $header['api'] ) ) {
			// key = 验证的 time = 验证的
			$arr = explode( '|' , trim( $header['api'] ), 2 );
			if ( count( $arr ) != 2 ) {
				return false;
			}
			
			list( $time, $key ) = $arr;
			if ( $time > time() || $time < ( time() - 10 ) ) {
				return false;
			}
			
			if ( empty( $this->key ) || strlen( $key ) != 64 ) {
				return false;
			}
			
			if ( ( md5( $this->key . $time ) . md5( $time . $this->key ) ) !== $key ) {
				return false;
			}
			
			$msg .= '200';
			if ( !socket_write( $accept, $msg, strlen( $msg ) ) ) {
				return false;
			}
			
			return WEBSOCKET_TYPE_API;
		}
		
		
	
		// flash 验证信息
		if ( trim( implode( '', $header ) ) == '<policy-file-request/>' ) {
			$msg .= '<?xml version="1.0"?>';
			$msg .= '<cross-domain-policy>';
			$msg .= '<allow-access-from domain="'. ( $this->domain ? '*.' . $this->domain : '*' ) .'" to-ports="*"/>';
			$msg .= '</cross-domain-policy>';
			$msg .= "\0";
			socket_write( $accept, $msg, strlen( $msg ) );
			return false;
		}
		
				
		// 超过最大在线
		if ( WEBSOCKET_ONLINE <= count( $this->accept ) ) {
			return false;
		}
		
		// 来路
		$origin = empty( $header['origin'] ) ? empty( $header['websocket-origin'] ) ? '' : $header['websocket-origin'] : $header['origin'];
		$parse = parse_url( $origin );
		$scheme = empty( $parse['scheme'] ) || $parse['scheme'] != 'https' ? '' : 's';
		$origin = $origin && !empty( $parse['host'] ) ? 'http' . $scheme . '://' . $parse['host'] : '';
		
		// 无效来路
		if ( $this->domain && !empty( $parse['host'] ) && !preg_match( '/(^|\.)'. preg_quote( $this->domain, '/' ) .'$/i', $parse['host'] ) ) {
			return false;
		}
		

		
		//  10+ 版本的
		if ( !empty( $header['sec-websocket-key'] ) ) {
			$type = WEBSOCKET_TYPE_2;
			$a = base64_encode( sha1( trim( $header['sec-websocket-key'] ) . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true ) );
			
			
			$msg .= "HTTP/1.1 101 Switching Protocols\r\n";
			$msg .= "Upgrade: websocket\r\n";
			$msg .= "Connection: Upgrade\r\n";
			if ( $origin ) {
				$msg .= "Sec-WebSocket-Origin: {$origin}\r\n";
			}
			$msg .= "Sec-WebSocket-Accept: $a\r\n";
			$msg .= "\r\n";
			
			if ( !socket_write( $accept, $msg, strlen( $msg ) ) ) {
				return false;
			}
			
			return WEBSOCKET_TYPE_2;
		}
		
		
		// 10- 版本的
		if ( !empty( $header['sec-websocket-key1'] ) && !empty( $header['sec-websocket-key2'] ) && !empty( $header['html'] ) ) {			

			
			$key1 = $header['sec-websocket-key1'];
			$key2 = $header['sec-websocket-key2'];
			$key3 = $header['html'];
			if ( !preg_match_all('/([\d]+)/', $key1, $key1_num ) || !preg_match_all('/([\d]+)/', $key2, $key2_num ) ) {
				return false;
			}
			$key1_num = implode( $key1_num[0] );
			$key2_num = implode( $key2_num[0] );
			
			if ( !preg_match_all('/([ ]+)/', $key1, $key1_spc ) || !preg_match_all('/([ ]+)/', $key2, $key2_spc ) ) {
				return false;
			}
			
			$key1_spc = strlen( implode( $key1_spc[0] ) );
			$key2_spc = strlen( implode( $key2_spc[0] ) );
			
			$key1_sec = pack("N", $key1_num / $key1_spc );
			$key2_sec = pack("N", $key2_num / $key2_spc );
			
			
	
			$msg .= "HTTP/1.1 101 Web Socket Protocol Handshake\r\n";
			$msg .= "Upgrade: WebSocket\r\n";
			$msg .= "Connection: Upgrade\r\n";
			if ( $origin ) {
				$msg .= "Sec-WebSocket-Origin: {$origin}\r\n";
			}
			$msg .= "Sec-WebSocket-Location: ws{$scheme}://{$this->host}:{$this->port}{$this->path}\r\n";
			$msg .= "\r\n";
			$msg .= md5( $key1_sec.$key2_sec . $key3, true );
			if ( !socket_write( $accept, $msg, strlen( $msg ) ) ) {
				return false;
			}
			return WEBSOCKET_TYPE_1;
		}
		
		return false;
	}
	
	/**
	*	错误代码
	*
	*	无参数
	*
	*	返回值 true false
	**/
	function error() {
		if ( !$this->socket ) {
			return -1;
		}
		return socket_last_error( $this->socket );
	}
}



/**
*	websocket  第一个版本的
***/
class class_websocket_1{
	function decode( $data ) {
		$len = strlen( $data );
		if ( $len < 3 ) {
			return false;
		}
		$r = array();
		$k = -1;
		$str = '';
		
		for( $i = 0; $i < $len; $i++ ) {
			$ord = ord( $data[$i] );
			if ( $ord == 0 ) {
				$k++;
				$str = '';
				continue;
			}
			if ( $ord == 255 ) {
				$r[$k] = $str;
				continue;
			}
			
			$str .= $data[$i];
		}
		return $r;
	}
	
	function encode( $data ) {
		$data = is_array( $data ) || is_object( $data ) ? json_encode( $data ) : (string) $data;
		return chr(0) . $data . chr(255);
	}
}







/**
*	websocket  第二个版本的
**/
class class_websocket_2{
	/**
	*	解码
	**/
	function decode( $data ) {
		if ( strlen( $data ) < 6 ) {
			return array();
		}
		
		$r = array();
		$back = $data;
		while( $back ) {
			$type = bindec( substr( sprintf( '%08b', ord( $back[0] ) ) , 4, 4 ) );
			$encrypt = (bool) substr( sprintf( '%08b', ord( $back[1] ) ), 0, 1 );
			$payload = ord( $back[1] ) & 127;
			$datalen = strlen( $back );
			if( $payload == 126 ) {
				if ( $datalen <= 8 ) {
					break;
				}
				$len = substr( $back, 2, 2 );
				$len = unpack('n*', $len );
				$len = end( $len );
				
				if ( $datalen < 8 + $len ) {
					break;
				}
				$mask = substr( $back, 4, 4 );
				$data = substr( $back, 8, $len );
				$back = substr( $back, 8 + $len );
			} elseif( $payload == 127 ) {
				if ( $datalen <= 14 ) {
					break;
				}
				$len = substr( $back, 2, 8 );
				$len = unpack('N*', $len );
				$len = end( $len );
				if ( $datalen < 14 + $len ) {
					break;
				}
				$mask = substr( $back, 10, 4 );
				$data = substr( $back, 14, $len );
				$back = substr( $back, 14 + $len );
			} else {
				$len = $payload;
				if ( $datalen < 6 + $len ) {
					break;
				}
				$mask = substr( $back, 2, 4 );
				$data = substr( $back, 6, $len );
				$back = substr( $back, 6 + $len );
			}
			
			if ( $type != 1 ) {
				continue;
			}
			$str = '';
			if ( $encrypt ) {
				$len = strlen( $data );
				for ( $i = 0; $i < $len; $i++ ) {
					$str .= $data[$i] ^ $mask[$i % 4];
				}
			} else {
				$str = $data;
			}
			$r[] = $str;
		}
		return $r;
	}
	
	/**
	*	编码
	**/
	function encode( $data ) {
		$data = is_array( $data ) || is_object( $data ) ? json_encode( $data ) : (string) $data;
		$len = strlen( $data );
		$head[0] = 129;
		if ( $len <= 125 ) {
			$head[1] = $len;
		} elseif ( $len <= 65535 ) {
			$split = str_split( sprintf('%016b', $len ), 8 );
			$head[1] = 126;
			$head[2] = bindec( $split[0] );
			$head[3] = bindec( $split[1] );
		} else {
			$split = str_split( sprintf('%064b', $len ), 8 );
			$head[1] = 127;
			for ( $i = 0; $i < 8; $i++ ) {
				$head[$i+2] = bindec( $split[$i] );
			}
			if ( $head[2] > 127 ) {
				return false;
			}
		}
		foreach( $head as $k => $v ) {
			$head[$k] = chr( $v );
		}

		return implode('', $head ) . $data;
	}
}



/**
*	API 应用
**/
class class_websocket_api{

	function decode( $data ) {
		if ( !$data = trim( $data ) ) {
			return false;
		}
		$data = explode( "\n", $data );
		$r = array();
		foreach( $data as $v ) {
			if ( $v = @unserialize( trim( $v ) ) ) {
				$r[] = $v;
			}
		}
		return $r;
	}


	function encode( $data ) {
		return serialize( $data );
	}
}
