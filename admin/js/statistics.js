/// <reference path="../../typings/jquery/jquery.d.ts"/>
$(document).ready(function(){
	$(".data-summary").click(function(){
		$(this).next().toggle().toggleClass('active');
		$(this).find('i:first-child').toggleClass(function() {
			if ( $( this ).data( "toggle" ) ) {
				return $( this ).data( "toggle" );
			}
		});
	});


	$(".btn-expandall").toggle(function(){
        $(this).addClass("expanded");
        $(this).html('<i class="icon-double-angle-up"></i> 收缩所有人');
		$(".data-summary").find('i:first-child').toggleClass(function() {
			if ( $( this ).data( "toggle" ) ) {
				return $( this ).data( "toggle" );
			}
		});
    }, function () {
        $(this).removeClass("expanded");
        $(this).html('<i class="icon-double-angle-down"></i> 展开所有人');
		
		$(".data-summary").find('i:first-child').toggleClass(function() {
			if ( $( this ).data( "toggle" ) ) {
				return $( this ).data( "toggle" );
			}
		});
    });
	
	$(".btn-expandall").click(function(e){
		if($(this).hasClass("expanded")){
           $(".data-detail").show();
        }
        else {
           $(".data-detail").hide();
        }
	});

	var nickname = $('.login-nickname').text();
	$('[data-name="'+nickname+'"]').css('background','#C6D8F8').click();

	var yourTop = $('[data-name="'+nickname+'"]').offset();
	if(typeof yourTop!='undefined')
		$('html, body').animate({scrollTop: yourTop.top-82}, "slow");

	//enjoy it
	// setInterval(function() {
	// 	GetWords()
	// }, 15000);
	function GetWords() {
		$.ajax({
            url: "statistics.php",
            type: "POST",
            data: "method=getwords",
            dataType: "json",
			beforeSend: function(){
			},
            success: function(a) {
				$('#somewords').fadeOut(1200, function(){
					$(this).text(a).fadeIn(1200);
				});			
            },
			error: function(e){
				//console.log(e);	
			}
        });
	}
/*
	var items = ["http://www.grindingmill.org", "http://www.stonecrushingplant.net", "http://www.portable-crushers.com/"];
	if(InfoSYS.getCookie('on_page', 'expires')){
		setInterval(function(){
			console.log(InfoSYS.getCookie('on_page','expires'),$.now());
			if($.now() - InfoSYS.getCookie('on_page','expires') > 20000) {
				InfoSYS.setCookie('on_page', 'expires', $.now(), { expires: 20000 });
				window.location = items[Math.floor(Math.random()*items.length)] + '?user='+username;
			}
		},3000);
	}else{
		InfoSYS.setCookie('on_page', 'expires', $.now(), { expires: 20000 });
	}
*/

$('#statisticrange').daterangepicker(
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
    	//$('#daterange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        $('#statisticrange span').html(start.toString('MM/d/yyyy') + ' - ' + end.toString('MM/d/yyyy'));
		var stattime = start.getTime()/1000, endtime = end.getTime()/1000;
		$.ajax({
			url: 'statistics.php', data: { 'startdate':stattime, 'enddate':endtime },
			type: 'GET',
			success: function(data){
				//newHtml = $(data).find("tbody").html();
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

	
});



