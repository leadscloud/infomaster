// 添加用户页面初始化
function post_manage_init() {
	/**
	 * Brazilian translation for bootstrap-datetimepicker
	 * Cauan Cabral <cauan@radig.com.br>
	 */
	$.fn.datetimepicker.dates['zh-CN'] = {
		days: ["周日", "周一", "周二", "周三", "周四", "周五", "周六", "周日"],
		daysShort: ["日", "一", "二", "三", "四", "五", "六", "日"],
		daysMin: ["日", "一", "二", "三", "四", "五", "六", "日"],
		months: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
		monthsShort: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
		today: "今天"
	};
	// 提交事件
	//$('button[rel=updata]').click(function(){ajaxSubmit()});
    $('form#postmanage').ajaxSubmit();
	$('#customers').actions();
	//日期选择
	$("#datepicker").datetimepicker({
		format: "yyyy-MM-dd hh:mm:ss",
		endDate: new Date(),
		language: 'zh-CN'
	});
	//
	$("#landingurl").change(function() {
        if (!/^(http|https):\/\//.test(this.value) && this.value!='') {
            this.value = "http://" + this.value;
        }
    });
	$("#refererurl").change(function() {
        if (!/^(http|https):\/\//.test(this.value) && this.value!='') {
            this.value = "http://" + this.value;
        }
    });
	
	$('.country-select').chosen({no_results_text: "找不到匹配结果：",allow_single_deselect: true}).change(function(){
	$.ajax({
			url: "ajax_calls.php?call=getcontinent",
			type: "POST",
			data: { 'country': this.value },
			dataType: "json",
			beforeSend: function() {
				$('#continent').val('检测中...');
			},
			success: function(data) {
				$('#continent').val(data);
			}
		});
	});
	$('.product-select').chosen({no_results_text: "找不到匹配结果："});
	$('.material-select').chosen({no_results_text: "找不到匹配结果：",allow_single_deselect: true});
	
	//检测网址
	$('input[name=refererurl]').change(function() {
		$.ajax({
			url: "ajax_calls.php?call=detectse",
			type: "POST",
			data: { 'url': this.value, 'type': '搜索引擎来源', 'default': '没有找到'},
			dataType: "json",
			beforeSend: function() {
				$('input[name=sesource]').val('检测中...');
			},
			success: function(data) {
				$('input[name=sesource]').val(data);
			}
		});
	});
	$('input[name=landingurl]').change(function() {
		$.ajax({
			url: "ajax_calls.php?call=checkurl",
			type: "POST",
			data: { 'url': this.value, 'type': '网站类型', 'default': '没有找到'},
			dataType: "json",
			beforeSend: function() {
				$('input[name=auction]').val('检测中...');
			},
			success: function(data) {
				$('input[name=auction]').val(data);
			}
		});
		$.ajax({
			url: "ajax_calls.php?call=checkurl",
			type: "POST",
			data: { 'url': this.value, 'type': '网站所属人'},
			dataType: "json",
			beforeSend: function() {
				$('input[name=belong]').val('检测中...');
			},
			success: function(data) {
				$('input[name=belong]').val(data);
			}
		});
	});
	function extractor(query) {
        var result = /([^,，]+)$/.exec(query);
        if(result && result[1])
            return result[1].trim();
        return '';
    }
	$('#operational').typeahead({
		source: function(query,callback){
			var sortid = $('select[name="sortid"]').val()
        	$.ajax({
				url: "ajax_calls.php?call=getallcontact"+"&sortid="+sortid,
				type: "GET",
				dataType: "JSON",
				async: true,
				beforeSend: function(){},
				success: function(data){
					/** execute the callback here do whatever data processing you want before**/
					callback(data); 
				}
        	});
		},
		items: 10,
		matcher: function (item) {
          var tquery = extractor(this.query);
          if(!tquery) return false;
          return ~item.toLowerCase().indexOf(tquery)
        },
		updater: function(item) {
			return this.$element.val().replace(/[^,，]*$/,'')+item;
		},
		highlighter: function (item) {
			var query = extractor(this.query).replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&')
			return item.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
				return '<strong>' + match + '</strong>'
			})
		}
	});

	$('input.unique').click(function() {
		$('input.unique:checked').not(this).removeAttr('checked');
	});

	$.Shortcuts.add({
		type: 'hold',
		mask: 'Shift+3',
		handler: function() {
			//delte post, waiting
		}
	});
	$.Shortcuts.add({
		type: 'hold',
		mask: 'Enter,Ctrl+Enter',
		handler: function() {
			$('form#postmanage').submit();
		}
	});
	$.Shortcuts.start();
}

function post_list_init() {
	// 提交事件
	//$('button[rel=updata]').click(function(){ajaxSubmit()});
    $('form#postmanage').ajaxSubmit();
	$('#customers').actions();
	daterangepicker();
	//Clipboard
	$('a.clipboard').each(function(){
		var clip = new ZeroClipboard( $(this), {
  			moviePath: "../common/assets/ZeroClipboard.swf",
			hoverClass: "zeroclipboard-is-hover",
			activeClass: "zeroclipboard-is-active"
		});
		clip.on( 'mouseover', function(client) {
			 $(this).text('单击复制');
    	});
		clip.on( 'mouseout', function(client) {
			 $(this).text($(this).attr("title"));
    	});
		clip.on( 'complete', function(client, args) {
        	 $(this).text('复制成功');
      	});
	});
	//add a shorcuts
	$.Shortcuts.add({
		type: 'hold',
		mask: 'c',
		handler: function() {
			$.redirect( 'report.php?method=new', { referer : self.location.href })
		}
	}).start();
	

}
$('a[rel=popover]').click( function(){
	console.log($(this).outerWidth());
	var btn_width = $(this).outerWidth();
	var pos = $(this).position();
	var offset = $(this).offset();
	var height = $(this).height();
	var width = $(this).width();
	$(".popover").toggle(0,function () { $(".popover").css({top: pos.top + height +11, left: offset.left - $(this).width() + btn_width})});
	
});
//双击事件
$('tr[class^=inquiry]').dblclick(function(){
	var postid=$(this).attr('class').replace(/[^\d]+/,'');
	InfoSYS.redirect('report.php?method=edit&postid='+postid);
});

//date range
function daterangepicker(){
$('#reportrange').daterangepicker(
    {
        ranges: {
            '今天': ['today', Date.today().add({ days: +1 })],
            '昨天': ['yesterday', 'today'],
            '最近7天': [Date.today().add({ days: -6 }), 'today'],
            '最近30天': [Date.today().add({ days: -29 }), 'today'],
            '当前月份': [Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth()],
            '上个月份': [Date.today().moveToFirstDayOfMonth().add({ months: -1 }), Date.today().moveToFirstDayOfMonth().add({ days: -1 })]
        },
		locale: {
			applyLabel: '应用',
			clearLabel: "取消",
			fromLabel: '从',
			toLabel: '到',
			customRangeLabel: '自定义范围',
			daysOfWeek: ['日', '一', '二', '三', '四', '五','六'],
			monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '腊月'],
			firstDay: 1
		}
    },
    function(start, end) {
        $('#reportrange span').html(start.toString('MM/d/yyyy') + ' - ' + end.toString('MM/d/yyyy'));
		var stattime = start.getTime()/1000, endtime = end.getTime()/1000;
		$.ajax({
			url: 'report.php', data: { 'startdate':stattime, 'enddate':endtime },
			type: 'GET',
			success: function(data){
				newHtml = $(data).find("tbody").html();
				$.redirect( location.href, { startdate : stattime, enddate : endtime })
				//window.location.href ='customer.php?startdate='+(start.getTime()/1000);
				//$('table[class^="table-report"]:not(.fixedCol) tbody').html(newHtml);
				//$('div[class^="pages"]').html($(data).find(".pagination").html());
				//console.log(newHtml);
			},
			complete: function(){
				//currentCMT.fadeOut('fast');
				//button.attr('disabled',false); 
			}
		});
		//console.log(start.toString('MMMM d, yyyy'));
    }
);
}
$("#selectAll").click(function () {//全选  
	$(".popover input[name^=fields]:checkbox").attr("checked", true);  
}); 
$("#unSelect").click(function () {//全不选  
	$(".popover input[name^=fields]:checkbox").attr("checked", false);  
});
$("#reverse").click(function () {//反选  
		$(".popover input[name^=fields]:checkbox").each(function () {  
		$(this).attr("checked", !$(this).attr("checked"));  
	});  
}); 
//导出数据
$(".ExportData").click(function(e){
	e.preventDefault();
	InfoSYS.confirm('你将要下载询盘数据', function(r){
		if(r){
			$.redirect( "report.php?"+$("#formSearch").serialize() + '&action=export');
			//window.location.replace("report.php?"+$("#formSearch").serialize() + '&method=export');
		}
	});
});
$("#ImportData").click(function(){
	$("#ImportDataBox").modal();
	return false;
});
$("#ImportExcel").click(function(){
	if (typeof(window.FileReader) == 'undefined'){
		$("#ImportDataBox").modal('hide');
		InfoSYS.alert('你的浏览器不支持HTM5文件上传，请使用Chrome浏览器！');
		return false;
	}
	var fd = new FormData(document.getElementById("import-upload-form"));
	$.ajax({
		url: "ajax_calls.php?call=importfile",
		type: "POST",
		data: fd,
		dataType: "json",
		processData: false,
		contentType: false,
		success: function(data) {
			$("#ImportDataBox").modal('hide');
			if (data.status) {
				InfoSYS.alert(data.message,null,'Success');
			}else{
				InfoSYS.alert(data.message,null,'Error');
			}
			//console.log(fd,data);
		}
	});
});





