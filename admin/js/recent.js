$(document).ready(function(){

	// === user recent inquiry status === //
	$.ajax({
		url: 'ajax_calls.php?call=getrecentinqiuryinfo',
		method: 'POST',
		dataType: 'json',
		success: onRecentInquiryDataReceived
	});
	var preXDays = function (days) {
		var today = (new Date()).getTime();
		var day_length = 1000 * 60 * 60 * 24; //the length of a day in milliseconds
		return today - day_length*days;
	}
	function onRecentInquiryDataReceived(data) {
		 //console.log(data);
		 $.plot($(".recent-chart"), data, {
			 series: {
				 lines: { show: true },
				 points: { show: true }
			 },
			 grid: { hoverable: true, clickable: true },
			 xaxis: {
				mode: "time",
				timezone: "browser",
				timeformat: "%Y/%m/%d",
				minTickSize: [1, "day"],
				min: preXDays(90),
				//max: (new Date()).getTime()
			}
			 //yaxis: { min: -100, max: 100 }
		 });
		 
	 }
	// === Point hover in chart === //
    var previousPoint = null;
    $(".recent-chart").bind("plothover", function (event, pos, item) {
		//console.log(item);
        if (item) {
            if (previousPoint != item.dataIndex) {
                previousPoint = item.dataIndex;
                
                $('#tooltip').fadeOut(200,function(){
					$(this).remove();
				});
                var x = item.datapoint[0],
					y = item.datapoint[1];
                x = new Date(x);
				var date = x.getFullYear()+"-"+(x.getMonth()+1)+"-"+x.getDate();
                unicorn.flot_tooltip(item.pageX, item.pageY, item.series.type+"类, 日期: " + date + " 数量: " + y);
            }
            
        } else {
			$('#tooltip').remove();
            previousPoint = null;           
        }   
    });	
	
	//柱状图
	$.ajax({
		url: 'ajax_calls.php?call=getbelonginfo',
		method: 'POST',
		dataType: 'json',
		success: onOperatorDataReceived
	});
	function onOperatorDataReceived(data){
		//console.log(data);
		$.plot(".bars", data  , {
			series: {
				bars: {
					show: true,
					barWidth: 0.4,
					align: "center",
					order: 1,
					fillColor:  "#4fabd2", //#4572A7
				},
				color: "#005580"
			},
			grid: { hoverable: true, clickable: true },
			xaxis: {
				mode: "categories",
				tickLength: 0
			}
		});
	}
	//bin hover to char
	var previousPoint = null;
	$(".bars").bind("plothover", function (event, pos, item) {
		//console.log(item);
		if (item) {
			if (previousPoint != item.datapoint) {
				previousPoint = item.datapoint;

				$("#tooltip").remove();
				var x = item.datapoint[0],
					y = item.datapoint[1] - item.datapoint[2];

				unicorn.flot_tooltip(item.pageX, item.pageY, y  + " - "+ item.series.data[item.dataIndex][0]);
			}
		}
		else {
			$("#tooltip").remove();
			previousPoint = null;            
		}
	});

	// === 询盘在各大洲分布情况 === //
	$.ajax({
		url: 'ajax_calls.php?call=getcontinentsinfo',
		method: 'POST',
		dataType: 'json',
		success: onContinentsDataReceived
	});
	function onContinentsDataReceived(data){
		//console.log(data);
		$.plot($(".pie"), data, {
			series: {
				pie: {
					show: true,
					radius: 1,
					label: {
						show: true,
						radius: 1,
						formatter: function(label, series) {
							return '<div style="font-size:11px; text-align:center; padding:2px; color:white;">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
						},
						background: {
							opacity: 0.8
						}
					}
				}
			},
			grid: {
				hoverable: true,
				clickable: true
			},
			legend: {
				show: true,
				labelBoxBorderColor: "none"
			}
		});

	}
	$(".pie").bind("plothover", pieHover);
	function pieHover(event, pos, obj)
	{
		
		if (!obj)
				return;
		percent = parseFloat(obj.series.percent).toFixed(2);
		$("#hoverdata").html('<span style="font-weight: bold; color: '+obj.series.color+'">'+obj.series.label+' ('+percent+'%)</span>');
	}
	
});
unicorn = {

		// === Tooltip for flot charts === //
		flot_tooltip: function(x, y, contents) {
			
			$('<div id="tooltip">' + contents + '</div>').css( {
				top: y + 5,
				left: x + 5
			}).appendTo("body").show();
		}
}