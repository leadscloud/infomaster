WebSocket = window.WebSocket || window.MozWebSocket;
if( !WebSocket ) {
	var wsobg = {};
	var ws_ready = false;
	$(window).load(function(){
		ws_ready = true;
	});
	$(function(){		
		$.ajax({
			url: WS_STATIC_URL + '/jquery.swfobject.js',
			dataType: 'script',
			async: false,
			cache: true
		});
		
		window.WebSocket = function( a ) {
			a = a.match( /wss{0,1}\:\/\/([0-9a-z_.-]+)(?:\:(\d+)){0,1}/i );
			this.host = a[1];
			this.port = a[2] || 843;
			this.onopen = function(){}
			this.onclose = function(){}
			this.onmessage = function(){}
			this.onerror = function(){}
			this.ready = function(b){
				return true;
			}
			this.send = function(b){
				return wsobg.call.Send(b);
			}
			this.close = function(){
				return wsobg.call.Close();
			}
			this.ping = function(){
				return wsobg.call.Ping();
			}
			this.connect = function(){
				wsobg.call = $('#flash_websocket')[0];
				wsobg.call.Connect( this.host,this.port );
			};
			if ( $('#websocket').size() ) {
				this.connect();
			} else {
				
				var div = $('<div></div>').attr({id:'websocket'}).css({position:'absolute', top:-999, left:-999});
				div.flash({
					swf: WS_STATIC_URL +'/websocket/websocket.swf',
					wmode: "window",
					scale: "showall",
					allowFullscreen : true,
					allowScriptAccess : 'always',
					id: 'flash_websocket',
					width : 1,
					height : 1,
					flashvars : { call: 'wsobg._this' }
				});
				$('body').append(div);
			}
			wsobg._this = this;
		}
	});
}

$(function($) {
	$.ws = { obg: {}, message: {}, open:[], close:[], status: false, link:function(){} };
	
	// 添加消息回调函数 1 参数 消息 keys 2参数 回调函数
	$.wsmessage = function( k, f ) {
		if ( !k || !$.isFunction( f ) ) {
			return false;
		}
		$.ws.message[k] = $.ws.message[k] || [];
		$.ws.message[k].push(f);
	};
	
	// 注册 打开回调函数
	$.wsopen = function( f ) {
		if ( !$.isFunction( f ) ) {
			return false;
		}
		$.ws.open.push(f);
	};
	
	// 注册 关闭回调函数
	$.wsclose = function( f ) {
		if ( !$.isFunction( f ) ) {
			return false;
		}
		$.ws.close.push(f);
	};
	
	// 注册 发送信息
	$.wssend = function( d ) {
		return $.ws.status && $.ws.obg.send(d);
	};
	
	$.ws.link = function () {
		$.ws.obg = new WebSocket('ws://' + WS_HOST + ':'+ WS_PORT +'/');
		
		// 打开
		$.ws.obg.onopen = function(){
			$.ws.status = true;
			$.each( $.ws.open,function( k, v ) {
				v.call(this);
			})
		};
		
		// 关闭
		$.ws.obg.onclose = function() {
			$.ws.status = false;
			$.each( $.ws.close,function( k, v ) {
				v.call(this);
			})
		};
		
		// 接收消息
		$.ws.obg.onmessage = function( msg ) {
			var d = $.parseJSON( msg.data );
			d = d || [];
			$.each( d,function( k, v ) {
				$.ws.message[k] = $.ws.message[k] || [];
				$.each( $.ws.message[k],function( kk, vv ) {
					vv.call( this, v );
				})
			})
		};
	};
	
	// 连接
	$.ws.link();
	
	
	// 关闭自动重新连接
	$.wsclose(function(){
		setTimeout( $.ws.link, 6000 );
	});
	
	
	// 定时 time
	$.wsopen(function(){
		$.ws.time = setInterval( function(){ $.wssend( "time=ture" ); }, 30000 );
	});
	$.wsclose(function(){
		$.ws.time && clearInterval( $.ws.time );
	});

});
