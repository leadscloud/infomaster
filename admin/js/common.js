var InfoSYS = window.InfoSYS = window.SYS = {
    // javascript libaray version
    version: '1.0',
    // 语言包对象
    L10n: {},
    // URI对象
    URI: {},
    // 后台根目录
    ADMIN: '/admin/',
    // 站点根目录
    ROOT: '/',
    // Loading...
    //Loading: $('<div id="loading_overlay" class="modal-backdrop in"></div><div id="loading_loader">Loading...</div>'),
	//Loading: $('<div id="loading"><ul><li class="li1"></li><li class="li2"></li><li class="li3"></li><li class="li4"></li><li class="li5"></li><li class="li6"></li></ul></div>'),
   Loading: $('<div id="loading"><div id="loading_loader">正在加载...</div></div>'), //
	confirm: function(message,callback){
        InfoSYS.dialog({
            name:'confirm', title:'确认对话框',styles:{ 'top':-100, width:'400px' },
            body:'<p>' + message + '</p>',
            buttons:[{
                focus:true,
                text:'确定',
				classes:'btn-primary',
                handler:function(){
                    InfoSYS.removeDialog('confirm');
                    return callback.call(this,true);
                }
            },{
                text:'取消',
                handler:function(){
                    InfoSYS.removeDialog('confirm');
                    return callback.call(this,false);
                }
            }]
        });
        return false;
    },
	prompt: function(message,callback){
		var str         = "",
            cb          = null,
            defaultVal  = "default values";
			
		// let's keep a reference to the form object for later
        var form = $("<form></form>");
        form.append("<input autocomplete=off type=text value='" + defaultVal + "' />");
		 
         var div = InfoSYS.dialog({
            name:'prompt', title:message,styles:{ 'top':-100, width:'400px' },
            body:'<form><input autocomplete="off" type="text" value=""></form>',
            buttons:[{
                focus:true,
                text:'确定',
				classes:'btn-primary',
                handler:function(){ //alert(arguments.length);
                    InfoSYS.removeDialog('prompt');
                    return callback.call(this,$(this).find("input[type=text]").val());
                }
            },{
                text:'取消',
                handler:function(){
                    InfoSYS.removeDialog('prompt');
                    return callback.call(this,false);
                }
            }]
        });
		//div.modal('show');
		
        return false;
    },
    /**
     * 模拟alert
     * 
     * @param message   消息内容，html格式
     * @param callback  点击确定之后的回调函数
     * @param code      警告类型：Success，Error，Other
     */
	 alert: function(message,callback,code) {
        var iconclass;
        if (callback && !$.isFunction(callback)) {
            code = callback;
        }
        if (code) {
            switch (code) {
                case 'Success':
                    iconclass = 'icon-ok-sign';
                    break;
                case 'Error':
                    iconclass = 'icon-remove-sign';
                    break;
                default:
                    iconclass = 'icon-exclamation-sign';
                    break;
            }
            message = '<i class="' + iconclass + '"></i> ' + message + '';
        }

        InfoSYS.dialog({
            name:'alert', title:'提示对话框', close:false,
            body:message,
            buttons:[{
                focus:true,
                text:'确定',
                handler:function(opts){
                    InfoSYS.removeDialog('alert');
                    if ($.isFunction(callback)) callback();
                    return false;
                }
            }]
        });
    },
	 /**
     * 模拟的弹出框
     * 
     * @param options
     * @param callback
     */
    dialog: function(options,callback) {
        return $('body').dialog(options,callback);
    },
	/**
     * 删除弹出框
     *
     * @param name  options.name
     */
    removeDialog: function(name){
        var dialog = $.data(document,name);
            dialog = dialog ? dialog : $('.crm_dialog_' + name);
            dialog.modal('hide').remove();
    },
    /**
     * ajax success
     * 
     * @param xhr
     * @param s
     */
    success: function(xhr, s, loading){
        // 接管success
        s.orisuccess = s.success;
        // 自定义success
        s.success = function(data, status, xhr) {
            if (xhr && xhr.getResponseHeader('X-Powered-By') && s.url.toLowerCase().indexOf('.php') != -1) {
                if (xhr.getResponseHeader('X-Powered-By').indexOf("\x52\x61\x79") == -1) return ;
            }
            var data = InfoSYS.ajaxSuccess.apply(this,arguments);
            if (null!==data && s.orisuccess) {
                s.orisuccess.call(this, data, status, xhr);
            }
        }
        if (typeof loading == 'undefined') {
            InfoSYS.Loading.appendTo('body');
        }
    },
	/**
     * 设置cookie
     *
     * @param name
     * @param key
     * @param val
     * @param options
     */
    setCookie: function(name,key,val,options) {
        options = options || {};
        var cookie  = $.cookie(name),
            opts    = $.extend({ expires: 365, path: InfoSYS.ROOT }, options),
            cookies = cookie===null ? {} : InfoSYS.parse_str(cookie);
        // 取值
        if (arguments.length == 2) {
            if (cookies[key]) return cookies[key];
            else return null;
        }
        // 赋值
        else {
            cookies[key] = val;
            return $.cookie(name, $.param(cookies), opts);
        }
    },
    /**
     * 取得cookie
     *
     * @param name
     * @param key
     */
    getCookie: function(name,key) {
        return InfoSYS.setCookie(name,key);
    },
	/**
     * 等同于PHP parse_str
     * 
     * @param str
     */
    parse_str: function(str) {
        var pairs = str.split('&'),params = {}, urldecode = function(s){
            return decodeURIComponent(s.replace(/\+/g, '%20'));
        };
        $.each(pairs,function(i,pair){
            if ((pair = pair.split('='))[0]) {
                var key  = urldecode(pair.shift());
                var value = pair.length > 1 ? pair.join('=') : pair[0];
                if (value != undefined) value = urldecode(value);

                if (key in params) {
                    if (!$.isArray(params[key])) {
                        params[key] = [params[key]];
                    }
                    params[key].push(value);
                } else {
                    params[key] = value;
                }
            }
        });
        return params;
    },
    /**
     * 统一处理ajax返回结果
     * 
     * @param data      ajax response
     * @param status    
     * @param xhr
     */
    ajaxSuccess: function(data, status, xhr) {
        var code = xhr.getResponseHeader('X-InfoMaster-Code');
		//alert(data);
        switch (code) {
			// 提示
            case 'Success': case 'Error': case 'Alert':
                InfoSYS.alert(data,function(){
                    // 调用脚本
                    try { eval(xhr.getResponseHeader('X-InfoMaster-Eval')) } catch (e) {}
                },code);
                break;
            // 确认
            case 'Confirm':
                InfoSYS.confirm(data, function(r){
                    // 调用脚本
                    if (r) {
                        try { eval(xhr.getResponseHeader('X-InfoMaster-Submit')) } catch (e) {}
                    } else {
                        try { eval(xhr.getResponseHeader('X-InfoMaster-Cancel')) } catch (e) {}
                    }
                });
                break;
            // 跳转
            case 'Redirect':
                InfoSYS.redirect(data.Location, data.Time, data.Message);
                break;
            // 处理验证异常
            case 'Validate':
                $(document).error(data);
                break;
            // 返回结果
            case 'Return': default:
                break;
        }
        if (code && $.inArray(code, ['Success','Return'])==-1) data = null;
        return data;
    },
    /**
     * URL跳转
     *
     * @param url
     * @param time
     * @param message
     */
    redirect: function(url,time,message) {
        if (typeof url != 'undefined' && url != '') {
            url = url.replace('&amp;', '&');
            var win = top || window;
                win.location.replace(url);
        }
    },
	refresh: function(url) {
		var ajax_load = '<i class="icon-spinner icon-spin icon-2x pull-left"></i>';

		$("#customers .widget-content").html(ajax_load).load(url + " #customers .widget-content");
		
    }
};

InfoSYS.URI.Host = (('https:' == self.location.protocol) ? 'https://'+self.location.hostname : 'http://'+self.location.hostname);
InfoSYS.URI.Path = self.location.href.replace(/\?(.*)/,'').replace(InfoSYS.URI.Host,'');
InfoSYS.URI.File = InfoSYS.URI.Path.split('/').pop();
InfoSYS.URI.Path = InfoSYS.URI.Path.substr(0,InfoSYS.URI.Path.lastIndexOf('/')+1);
InfoSYS.URI.Url  = InfoSYS.URI.Host + InfoSYS.URI.Path + InfoSYS.URI.File;

// 设置全局 AJAX 默认选项
$.ajaxSetup({
    beforeSend: InfoSYS.success,
    error:function(xhr,status,error) { //alert('error on ajax submit'+status+error);
		var title = $.parseJSON(xhr.getResponseHeader('X-Dialog-title'));
            title = title || '系统错误';
		InfoSYS.dialog({
            title:title, body: xhr.responseText
        });
        
		InfoSYS.Loading.delay(1000).fadeOut(500);
		InfoSYS.Loading.remove();
    },
    complete: function(){
		InfoSYS.Loading.delay(1000).fadeOut(500);
        InfoSYS.Loading.remove();
    }
});

//Jquery 扩展
(function($){
	// 退出登录
	$.fn.logout = function(){
		var url = this.attr('href');
		return InfoSYS.confirm(' 确定退出吗?',function(r){
			if (r) {
				InfoSYS.redirect(url);
			}
		});
	}
	
	

    /**
     * ajax 表单提交
     *
     * @param callback
     */
    $.fn.ajaxSubmit = function(callback){
        return this.each(function(){
            var _this = $(this);
                _this.submit(function(){
                    // 取消样式
                    $('.input_error,.textarea_error,.ul_error',_this).removeClass('input_error').removeClass('textarea_error').removeClass('ul_error');
                    var button = $('button[type=submit]',this).attr('disabled',true);
                    // 取得 action 地址
                    var url = _this.attr('action'); if (url==''||typeof url=='undefined') { url = self.location.href; }
                    // ajax submit
                    $.ajax({
                        cache: false, url: url, dataType:'json',
                        type: _this.attr('method') && _this.attr('method').toUpperCase() || 'POST',
                        data: _this.serializeArray(),
                        success: function(data, status, xhr){
							//console.log(callback);
                            if ($.isFunction(callback)) callback.call(_this,data, status, xhr);
                        },
                        complete: function(){
                            button.attr('disabled',false); 
							InfoSYS.Loading.remove();
							//InfoSYS.Loading.delay(1250).fadeOut(500);
                        }
                    });
                    return false;
                });
        });
    };
    /**
     * 模拟的弹出框
     *
     * @param options   参数
     *          {
     *              name:标识,
     *              title:标题,
     *              body:内容,
     *              styles:css 样式,
     *              masked:是否需要遮罩，默认true,
     *              close:是否显示关闭按钮，默认true,
     *              way:浮动位置，默认居中，参数：c,lt,rt,lb,rb,
     *              remove:点击关闭按钮触发的事件,
     *              buttons:按钮
     *          }
     * @param callback  回调函数(dialog jquery对象,传入的 options)
     */
    $.fn.dialog = function(options,callback) {
        // this
        var _this = $(this);
        // 默认设置
        var opts = $.extend({
            title:'',
            body:'',
            styles:{},
            name:null,
            masked:true,
            close:true,
			way:'c',
			className:'dialog',
            remove:function(){ InfoSYS.removeDialog(opts.name); },
            buttons:[]
        }, options||{});

        // 按钮个数
        var btnLength = opts.buttons.length;
        // 设置默认名称
        opts.name = opts.name?'crm_dialog_' + opts.name:'crm_dialog';
        // 定义弹出层对象
        var dialog = $('<div class="modal ' + opts.name + ' ' + opts.className + ' window" style="display:none;"><div class="modal-header"><a class="close" data-dismiss="modal">×</a><h3>'+opts.title+'</h3></div><div class="wrapper modal-body">Loading...</div><div class="modal-footer"></div></div>');
        var target = $('div.' + opts.name,_this);
            if (target.is('div')) {
                dialog = target;
            } else {
                _this.append(dialog);
            }
            // 添加删除事件
            dialog.removeDialog = opts.remove;
        // 添加遮罩层
        if (opts.masked) {
            //InfoSYS.masked({'z-index':$('*').maxIndex() + 1});
        }
		

        // 添加关闭按钮
        if (opts.close) {
            if ($('.close',dialog).is('a')) {
                $('.close',dialog).click(function(){
                    dialog.removeDialog();
                });
            } else {
                $('<a href="javascript:;" class="close">Close</a>').click(dialog.removeDialog).insertAfter($('h3',dialog));
            }
        } else {
            $('.close',dialog).remove();
        }

        // 重新调整CSS
        //var styles = $.extend({overflow:'','z-index':$('*').maxIndex() + 1,height:'auto'},opts.styles); dialog.css(styles);

        // 设置标题
        //$('h1',dialog).text(opts.title);

        // 设置内容
        if ($('div.wrapper','<div>' + opts.body + '</div>').is('div')) {
            $('div.wrapper',dialog).replaceWith(opts.body);
        } else {
            $('.wrapper',dialog).html(opts.body + '<div class="clear"></div>');
            // 删除原来存在的按钮
            $('.modal-footer',dialog).remove();
        }
        // 绑定关闭
        $('[rel=close]',dialog).click(function(){
            dialog.removeDialog();
        });
      
        dialog.modal('show');
		
		// well, *if* we have a primary - give the first dom element focus
        dialog.on('shown', function() {
            dialog.find("a.btn-primary:first").focus();
        });

        dialog.on('hidden', function() {
            dialog.remove();
        });
		
		//$(dialog).on('shown', function () {
			// do something…
			//alert('shown test');
		//})
		
		

        // 添加按钮
        if (btnLength > 0) {
            $('.wrapper',dialog).after('<div class="modal-footer"></div>');
            for (var i=0;i<btnLength;i++) {
                var button = $('<a class="btn ' + opts.buttons[i].classes + '">' + opts.buttons[i].text + '</a>');
                    // 绑定按钮事件
                    (function(i){
                        button.click(function(){
                            if ($.isFunction(opts.buttons[i].handler)) opts.buttons[i].handler.call(dialog,opts);
                            return false;
                        });
                    })(i);
                    $('.modal-footer',dialog).append(button);
                    // 设置按钮类型
                    //opts.buttons[i].type && button.attr('type',opts.buttons[i].type) || null;
                    // 设置鼠标焦点
                    opts.buttons[i].focus && button.focus() || null;
            }
        }
        // 保存对象
        $.data(document,opts.name,dialog);
        // 执行回调函数
        if ($.isFunction(callback)) callback.call(dialog,opts);

        return dialog;
    }
    /**
     * 错误处理
     *
     * @param data
     *          [
     *              {
     *                  id:输入框name,
     *                  text:错误信息
     *              },
     *              {
     *                  id:输入框name,
     *                  text:错误信息
     *              },
     *          ]
     */
    $.fn.error = function(data) {
        var wrap = this, s = '<ul class="unstyled">', elm, xheLayout;
        $.each(data,function(i){
            elm = $('#'+this.id, wrap);
            if (elm.length > 0) {
				elm.parent().parent().addClass('error');//add for bootstrap
                xheLayout = elm.next().next().find('.xheLayout');
                if (elm.is('textarea') && xheLayout.is('table')) {
                    xheLayout.addClass(elm.get(0).tagName.toLowerCase() + '_error');
                } else {
                    elm.addClass(elm.get(0).tagName.toLowerCase() + '_error');
                }
            }
            s+= '<li>' + this.text + '</li>';
        });
        s+= '</ul>';
		//$('#myModal').modal();
        InfoSYS.alert(s);
    };
	    /**
     * Create a cookie with the given name and value and other optional parameters.
     *
     * @example $.cookie('the_cookie', 'the_value');
     * @desc Set the value of a cookie.
     * @example $.cookie('the_cookie', 'the_value', { expires: 7, path: '/', domain: 'jquery.com', secure: true });
     * @desc Create a cookie with all available options.
     * @example $.cookie('the_cookie', 'the_value');
     * @desc Create a session cookie.
     * @example $.cookie('the_cookie', null);
     * @desc Delete a cookie by passing null as value. Keep in mind that you have to use the same path and domain
     *       used when the cookie was set.
     *
     * @param String name The name of the cookie.
     * @param String value The value of the cookie.
     * @param Object options An object literal containing key/value pairs to provide optional cookie attributes.
     * @option Number|Date expires Either an integer specifying the expiration date from now on in days or a Date object.
     *                             If a negative value is specified (e.g. a date in the past), the cookie will be deleted.
     *                             If set to null or omitted, the cookie will be a session cookie and will not be retained
     *                             when the the browser exits.
     * @option String path The value of the path atribute of the cookie (default: path of page that created the cookie).
     * @option String domain The value of the domain attribute of the cookie (default: domain of page that created the cookie).
     * @option Boolean secure If true, the secure attribute of the cookie will be set and the cookie transmission will
     *                        require a secure protocol (like HTTPS).
     * @type undefined
     *
     * @name $.cookie
     * @cat Plugins/Cookie
     * @author Klaus Hartl/klaus.hartl@stilbuero.de
     */
    /**
     * Get the value of a cookie with the given name.
     *
     * @example $.cookie('the_cookie');
     * @desc Get the value of a cookie.
     *
     * @param String name The name of the cookie.
     * @return The value of the cookie.
     * @type String
     *
     * @name $.cookie
     * @cat Plugins/Cookie
     * @author Klaus Hartl/klaus.hartl@stilbuero.de
     */
    $.cookie = function(name, value, options) {
        if (typeof value != 'undefined') { // name and value given, set cookie
            options = options || {};
            if (value === null) {
                value = '';
                options.expires = -1;
            }
            var expires = '';
            if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
                var date;
                if (typeof options.expires == 'number') {
                    date = new Date();
                    date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
                } else {
                    date = options.expires;
                }
                expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
            }
            // CAUTION: Needed to parenthesize options.path and options.domain
            // in the following expressions, otherwise they evaluate to undefined
            // in the packed version for some reason...
            var path = options.path ? '; path=' + (options.path) : '';
            var domain = options.domain ? '; domain=' + (options.domain) : '';
            var secure = options.secure ? '; secure' : '';
            document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
        } else { // only name given, get cookie
            var cookieValue = null;
            if (document.cookie && document.cookie != '') {
                var cookies = document.cookie.split(';');
                for (var i = 0; i < cookies.length; i++) {
                    var cookie = jQuery.trim(cookies[i]);
                    // Does this cookie string begin with the name we want?
                    if (cookie.substring(0, name.length + 1) == (name + '=')) {
                        cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                        break;
                    }
                }
            }
            return cookieValue;
        }
    };
    // 绑定批量操作事件
    $.fn.actions = function(callback) {
        // 取得 action 地址
        var form   = $(this);
        // 绑定全选事件
		/*
        $('input[name=select]',form).click(function(){
            $('input[name^=list]:checkbox,input[name=select]:checkbox',form).attr('checked',!$(this).hasClass('active'));
        });*/
        $('input[name=select]',form).click(function(){
            $('input[name^=list]:checkbox,input[name=select]:checkbox',form).attr('checked',this.checked);
        });
		
		//禁止提交表单
		//$('button').click(function () { return false; });

		
		
		var header = form.attr('header'), method = form.attr('method'),
            url    = header && $.trim(header.substr(header.indexOf(' '))) || form.attr('action');
            method = header && $.trim(header.substring(0,header.indexOf(' '))) || method;
			if(method==undefined) method = '';

        if (url=='' || typeof url=='undefined') url = self.location.href;
        $('.btn-group',form).each(function(i){
            var _this  = $(this);
            $('button[name]',_this).click(function(){ //button:not([onclick^="javascript"])
                var button  = $(this), listids = [] ,action = button.attr("name"),
				referer = $('input[name=referer]').val(),
                // 提交方法
                submit = function(url,data) {
					//alert(url+data['listids']);
                    button.attr('disabled',true);
                    $.ajax({
                        dataType: 'json', url: url, data: data,
                        type: method.toUpperCase() || 'POST',
                        success: function(data){
                            if ($.isFunction(callback)) callback.call(_this,data);
                        },
                        complete: function(){
                            button.attr('disabled',false); 
							InfoSYS.Loading.remove();
                        }
                    });
                }
                if (action=='') {
                    return InfoSYS.alert('Did not select any action!');
                }

                $('input:checkbox[name^=listids]:checked',form).each(function(){
                    listids.push(this.value);
                });
                switch (action) {
                    case 'delete':
					
                       InfoSYS.confirm('确定删除?',function(r){
                           if (r) {
                               submit(url,{
                                   'method':action,
                                   'listids':listids
                               });
                           }
                       });
                       break;
                    case 'export':
                        window.location.href = url+'?method=export';
                        break;
				    case 'refresh':
				   	   $.ajax({
  							url: url,
							type: 'POST',
							data: 'referer='+escape(referer),
							success: function(data) {
								InfoSYS.redirect(url);
								//$("body").html(data); 
							}
					   });
				   	   break;
                    default:
                       submit(url,{
                           'method':action,
                           'listids':listids
                       });
                       break;
                }
            });
        });
    };

})(jQuery);
    function _(str) {
        if (parent) {
            document.write(parent._(str));
        } else {
            document.write(str);
        }
    }

jQuery.redirect = function(url, params) {

    url = url || window.location.href || '';
    url =  url.match(/\?/) ? url : url + '?';

    for ( var key in params ) {
        var re = RegExp( ';?' + key + '=?[^&;]*', 'g' );
        url = url.replace( re, '');
        url += '&' + key + '=' + params[key]; 
    }  
    // cleanup url 
    url = url.replace(/[;&]$/, '');
    url = url.replace(/\?[;&]/, '?'); 
    url = url.replace(/[;&]{2}/g, '&');
    // $(location).attr('href', url);
    window.location.replace( url ); 
};
$('[data-toggle="tooltip"]').tooltip();
$.scrollUp({
        scrollName: 'scrollUp', // Element ID
        topDistance: 300, // Distance from top before showing element (px)
        topSpeed: 300, // Speed back to top (ms)
        animation: 'fade', // Fade, slide, none
        animationInSpeed: 200, // Animation in speed (ms)
        animationOutSpeed: 200, // Animation out speed (ms)
        scrollText: '返回顶部', // Text for element
		scrollHtml: '<i class="icon-double-angle-up bigger-110"></i>',
        scrollImg: false, // Set true to use image
        activeOverlay: false // Set CSS color to display scrollUp active point, e.g '#00FFFF'
    });

jQuery(function () {
    handle_side_menu();
    $(document).off("click.dropdown-menu");
	$("#settings-btn").on("click", function () {
        $(this).toggleClass("open");
        $("#settings-box").toggleClass("open")
    });
    $("#settings-header").change(function () {
        if (this.checked) {
            $(".navbar").addClass("navbar-fixed-top");
            $(document.body).addClass("navbar-fixed");

        } else {
            $(".navbar").removeClass("navbar-fixed-top");
            $(document.body).removeClass("navbar-fixed");
			
            if ($("#settings-sidebar").get(0).checked) {
                $("#settings-sidebar").click();
            }

            $("#settings-breadcrumbs").attr("checked",true);
            $("#settings-breadcrumbs").click();
        }
		InfoSYS.setCookie('prefer_setting', 'navbar-fixed', this.checked);
    });
    $("#settings-sidebar").change(function () {
        if (this.checked) {
            $("#sidebar").addClass("fixed");
            if (!$("#settings-header").get(0).checked) {
                $("#settings-header").click();
            }
        } else {
            $("#sidebar").removeClass("fixed")
        }
		InfoSYS.setCookie('prefer_setting', 'sidebar-fixed', this.checked);
    });
    $("#settings-breadcrumbs").change(function () {
        if (this.checked) {
            $("#breadcrumbs").addClass("fixed");
            $(document.body).addClass("breadcrumbs-fixed");
            $("#settings-sidebar").attr("checked",false);
            $("#settings-sidebar").click();
        } else {
            $("#breadcrumbs").removeClass("fixed");
            $(document.body).removeClass("breadcrumbs-fixed");
        }
        InfoSYS.setCookie('prefer_setting', 'breadcrumbs-fixed', this.checked);
    });
	
	//初始化偏好设置
	var perfer_navbar = InfoSYS.getCookie('prefer_setting','navbar-fixed'),
	perfer_siderbar = InfoSYS.getCookie('prefer_setting','sidebar-fixed'),
    perfer_breadcrumbs = InfoSYS.getCookie('prefer_setting','breadcrumbs-fixed');
	if(perfer_navbar=='true') {
		$('#settings-header').attr('checked',true);
		$(".navbar").addClass("navbar-fixed-top");
        $(document.body).addClass("navbar-fixed")
	}else {
		$('#settings-header').attr('checked',false);
		$(".navbar").removeClass("navbar-fixed-top");
		$(document.body).removeClass("navbar-fixed");
	}
	
	if(perfer_siderbar=='true') {
		$('#settings-sidebar').attr('checked',true);
		$("#sidebar").addClass("fixed");
	}else {
		$('#settings-sidebar').attr('checked',false);
		$("#sidebar").removeClass("fixed");
	}

    if(perfer_breadcrumbs=='true') {
        $('#settings-breadcrumbs').attr('checked',false);
        $("#settings-breadcrumbs").click();
    }else {
        $('#settings-breadcrumbs').attr('checked',true);
         $("#settings-breadcrumbs").click();
    }
	//
	$('#sidebar-shortcuts button').each(function() {
		var element = $(this);
		element.click(function(){
			document.location.href=element.data( "href" );
		});
	});
	//
	$('.dropdown-user').click(function(e){
		e.stopPropagation();
	});
	$('.nav-info [class*="icon-animated-"]').closest("a").on("click", function () {
        var b = $(this).find('[class*="icon-animated-"]').eq(0);
        var a = b.attr("class").match(/icon\-animated\-([\d\w]+)/);
        b.removeClass(a[0]);
        $(this).off("click")
    });
	//*******
	var logged = true;
	var total_messages = 0;
	RunCheck();
	setInterval(function() {
		RunCheck()
	}, 5000)
	function RunCheck() {
		$.ajax({
            url: baseurl+"ajax_calls.php",
            type: "POST",
            data: "call=checkpms",
            dataType: "json",
			beforeSend: function(){
			},
            success: function(a) {
				//console.log(a);
                if (a.status) {
                    if (a.unread_pms > 0) {
                        $(".nav.pull-right .badge-pm").html(a.unread_pms);
						$(".nav.pull-right .pm-count").html(a.unread_pms);
						$(".nav.pull-right .badge-info.pm-count").html("+"+a.unread_pms);
                        if (!$(".nav.pull-right .badge-pm").is(":visible")) {
                            $(".nav.pull-right .badge-pm").show()
                        }
                        total_messages = a.unread_pms
                    } else {
                        if ($(".nav.pull-right .badge-pm").is(":visible")) {
                            $(".nav.pull-right .badge-pm").fadeOut()
                            }
                    }
					
                } else {
                    logged = false
				}
				
            },
			error: function(){
			}
        });
	}

    $('.go-full-screen').click(function(){
        
        if($('body > .white-backdrop').length <= 0) {
            $('<div class="white-backdrop">').appendTo('body');
        }

        backdrop = $('.white-backdrop');
        wbox = $(this).parents('.widget-box');
        
        if(wbox.hasClass('widget-full-screen')) {  
            backdrop.fadeIn(200,function(){ 
                wbox.removeClass('widget-full-screen');
                backdrop.fadeOut(200);
            });
        } else { 
            backdrop.fadeIn(200,function(){ 
                wbox.addClass('widget-full-screen');
                backdrop.fadeOut(200);
            });
        }
    });
	
});

function handle_side_menu() {
	
	var mode  = InfoSYS.setCookie('menu_setting', 'mode'),
		hover = function() {
			$('.menu-min li.head').unbind().hover(function(){
				$('ul.submenu',this).addClass('open');
			},function(){ 
				$('ul.submenu',this).removeClass('open');
			});
		};
	if (mode !== null) {
		$('#sidebar').toggleClass('menu-min',mode=='true');
		if (mode=='true') hover();
	}
	
    $("#menu-toggler").on("click", function () {
        $("#sidebar").toggleClass("display");
        $(this).toggleClass("display");
        return false
    });
    var a = false;
    $("#sidebar-collapse").on("click", function () {
        $("#sidebar").toggleClass("menu-min");
        $(this.firstChild).toggleClass("icon-double-angle-right");
        a = $("#sidebar").hasClass("menu-min");
        if (a) {
            $(".open > .submenu").removeClass("open")
        }
		// 保存Cookie
        InfoSYS.setCookie('menu_setting', 'mode', $('#sidebar').hasClass('menu-min'));
    });
    $(".nav-list").on("click", function (d) { 
        if (a) {
            return
        }
        var c = $(d.target).closest(".dropdown-toggle");
        if (c && c.length > 0) {
            var b = c.next().get(0);
            if (!$(b).is(":visible")) {
                $(".open > .submenu").each(function () {
                    if (this != b && !$(this.parentNode).hasClass("active")) {
                        $(this).slideUp(200).parent().removeClass("open");
						InfoSYS.setCookie('menu_setting', 'm' + $(this).parent().attr('menu_guid'), false);
                    }
                })
            }
			var head = $(b).parent();
			
            $(b).slideToggle(200).parent().toggleClass("open");
			InfoSYS.setCookie('menu_setting', 'm' + head.attr('menu_guid'), head.hasClass('open'));
            return false
        }
    });
	// 记录COOKIE
	$('.nav-list .head').each(function(i){
		var t = $(this); t.attr('menu_guid',i);
		var c = InfoSYS.getCookie('menu_setting','m' + i);
		if (c !== null && !t.hasClass('active')) {
			t.toggleClass('open',c=='true');
		}
		if (c !== null && t.hasClass('active')) {
			if(c=='true')
				t.children(".submenu").slideDown(200).parent().toggleClass("open",c=='true');
			else
				t.children(".submenu").slideUp(200).parent().toggleClass("open",c=='true');
		}
	});
	//$(".nav-list").click();
}