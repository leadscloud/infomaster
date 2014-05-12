<?php
	
/*
PHP 写的 WebSocket 在线聊天室
使用 需要修改
./index.php
WS_STATIC_URL
WS_HOST

./server/config.php
WEBSOCKET_HOST
ADMIN_PASS  管理员登录能使用 die 结束掉 php 进程  登录 管理员 输入名称的时候 恋月,123456  123456 = 密码 然后发送die 能结束掉php 进程

Linux 需要 root 权限 运行
IIS 直接url 访问 
./server/index.php 就能开启聊天室了

*/









?>
<!DOCTYPE html>
<html dir="ltr" lang="zh" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>WebSocket 在线聊天室</title>
<link rel="stylesheet" href="./static/style.css" type="text/css" media="all" />
<script type="text/javascript" src="./static/jquery.js"  media="all"></script>
<script type="text/javascript" src="./static/jquery.websocket.js"  media="all"></script>
<script type="text/javascript">
WS_STATIC_URL = 'http://info.shibangsoft.com/admin/websocket/static';
WS_HOST = '96.126.121.54';
WS_PORT = 843;

$(function(){
	var t = $('.message');
	$.wsmessage( 'msg', function( data ){
		t.append( data );
		$('.message').animate( { scrollTop: $('.message')[0].scrollHeight } ,0 );
	});
	
	$.wsmessage( 'chat', function( data ){
		t.append( data );
		$('.message').animate( { scrollTop: $('.message')[0].scrollHeight } ,0 );
	});
	
	$.wsmessage( 'name', function( data ) {
		if ( data ) {
			$('.msg.info.name').remove();
		}
		
	});
	
	$.wsmessage( 'list', function( data ) {
		if ( !data ) {
			return false;
		}
		$.each( data, function( k, v ){
			if ( v[1] ) {
				var w = $( '<li>' + v[0] + '</li>' ).click(function(){
					$('.send .chat').val( '@' + v[0] + ' ' );
				});
				$('.list ul').append( w );
			} else {
				$(".list ul li").each(function(){
					if ( $(this).html() == v[0] ) {
						$(this).remove();
						return false;
					}
				});
			}
		});
		$('.online').html( $('.list ul li').size() );
	});
	$.wsclose(function( data ){
		$(".list ul li").html('');
		$('.online').html( 0 );
		t.append( '<div class="msg info">连接已断开, 6秒后自动重试</div>' );
	});
	
	
	$.wsopen( function( data ) {
		t.append( '<div class="msg info">连接服务器成功</div>' );
		var w = t.append( '<div class="msg info name">请输入你的名称:<input type="text" class="name" name="name"  /><input type="submit" class="submit" name="submit" value="确认" /></div>' );
		w.find('.submit').click(function(){
			$.wssend('name=' + w.find('input.name').val() );
			return false;
		});
	});
	
	
	
	
	$('.send .submit').click(function(){
		if ( $('.send .chat').val() ) {
			
			$.wssend($.param( { chat : $('.send .chat').val() } ) );
			$('.send .chat').val('');
		}
		return false;
	});
	$('.send  .chat').keydown(function (e) {
		if ( ( e.ctrlKey && e.keyCode == 13 ) || ( e.altKey && e.keyCode == 83 ) ) {
			$('.send .submit').click();
			return false;
		}
	})
	
	$('.tool .empty').click(function(){
		t.html('');
	})
});
</script>
</head>
<body>
<div class="content">
	<div class="message"></div>
	<div class="tool">
		<span class="empty">清空记录</span>
	</div>
	<div class="send">
		<textarea class="chat" name="chat"></textarea>
		<p><input type="submit" class="submit" name="submit" value="发送" /></p>
	</div>
	<div class="list">
		<h3>在线用户<strong class="online">0</strong></h3>
		<ul>
		</ul>
	</div>
</div>
</body>
</html>