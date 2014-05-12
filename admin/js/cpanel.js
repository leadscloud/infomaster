$(document).ready(function(){
	unicorn.peity();
	
	// === Prepare the chart data ===/
	var sin = [], cos = [];
    for (var i = 0; i < 14; i += 0.5) {
        sin.push([i, Math.sin(i)]);
        cos.push([i, Math.cos(i)]);
    }
	
	// === Make chart === //
	$.ajax({
		url: 'ajax_calls.php?call=getinqiuryinfo',
		method: 'POST',
		dataType: 'json',
		success: onInquiryDataReceived
	});
	function onInquiryDataReceived(data) {
		 //console.log(data);
		 $.plot($(".chart"), data, {
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
				//min: (new Date(2013, 0, 1)).getTime(),
				//max: (new Date()).getTime()
			}
			 //yaxis: { min: -100, max: 100 }
		 });
		 
	 }

	
    
	// === Point hover in chart === //
    var previousPoint = null;
    $(".chart").bind("plothover", function (event, pos, item) {
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

	//每个信息员本月录入信息分布
	$.ajax({
		url: 'ajax_calls.php?call=getoperatorinfo',
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
					fillColor:  "#4572A7",
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

	//world map
    $.ajax({
		url: 'ajax_calls.php?call=getworldinfo',
		method: 'POST',
		dataType: 'json',
		success: onWorldDataReceived
	});
	function onWorldDataReceived(data){
		$('#world-map').vectorMap({
		  map: 'world_mill_cn',
		  series: {
			regions: [{
			  values: data,
			  scale: ['#efe6dc', '#109618'],
			  stroke: '#oe8716',
			  "stroke-width": 1,
			  normalizeFunction: 'polynomial'
			}]
		  },
		  onRegionLabelShow: function(e, el, code){
			el.html(el.html()+' (信息数 - '+data[code]+')');
		  },
		  regionsSelectable: true,
		  backgroundColor: '#ffffff',
		  regionStyle: {
			  initial: {
				fill: '#f5f5f5',
				"fill-opacity": 1,
				stroke: '#dddddd',
				"stroke-width": 1,
				"stroke-opacity": 1
			  },
			  hover: {
				"fill-opacity": 0.8,
				 stroke: '#bbbbbb',
				 "stroke-width": 2,
			  },
			  selected: {
				fill: 'yellow',
				stroke: '#bbbbbb',
				"stroke-width": 2,
			  },
			  selectedHover: {
			  }
		  },
		  markerStyle : {
			  initial: {
				fill: 'grey',
				stroke: '#dddddd',
				"fill-opacity": 1,
				"stroke-width": 1,
				"stroke-opacity": 1,
				r: 5
			  },
			  hover: {
				stroke: 'black',
				"stroke-width": 2
			  },
			  selected: {
				fill: 'blue'
			  },
			  selectedHover: {
			  }
		}
		});
	}
	

	
});
unicorn = {
		// === Peity charts === //
		peity: function(){		
			$.fn.peity.defaults.line = {
				strokeWidth: 1,
				delimeter: ",",
				height: 24,
				max: null,
				min: 0,
				width: 50
			};
			$.fn.peity.defaults.bar = {
				delimeter: ",",
				height: 24,
				max: null,
				min: 0,
				width: 50
			};
			$(".peity_line_good span").peity("line", {
				colour: "#B1FFA9",
				strokeColour: "#459D1C"
			});
			$(".peity_line_bad span").peity("line", {
				colour: "#FFC4C7",
				strokeColour: "#BA1E20"
			});	
			$(".peity_line_neutral span").peity("line", {
				colour: "#CCCCCC",
				strokeColour: "#757575"
			});
			$(".peity_bar_good span").peity("bar", {
				colour: "#459D1C"
			});
			$(".peity_bar_bad span").peity("bar", {
				colour: "#BA1E20"
			});	
			$(".peity_bar_neutral span").peity("bar", {
				colour: "#757575"
			});
		},

		// === Tooltip for flot charts === //
		flot_tooltip: function(x, y, contents) {
			
			$('<div id="tooltip">' + contents + '</div>').css( {
				top: y + 5,
				left: x + 5
			}).appendTo("body").show();
		}
}