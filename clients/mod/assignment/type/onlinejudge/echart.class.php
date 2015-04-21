<?php
class Echarts {
	public function show($id, array $data, $title, $subtitle) {
		$xaxis = "";
		$series = "";
		$xname = "";
		
		if (empty ( $data )) {
			$data = array (
					'xaxis' => array (
							'type' => 'category',
							'boundaryGap' => 'false',
							'data' => array (
									'' 
							) 
					),
					'series' => array (
							array (
									'type' => 'bar',
									'itemStyle' => "{normal: {areaStyle: {type: 'default'}}}",
									'data' => array () 
							) 
					) 
			);
		}
		
		foreach ( $data as $key => $value ) {
			switch ($key) {
				case 'xaxis' :
					foreach ( $value as $k => $v ) {
						switch ($k) {
							case 'type' :
								$xaxis [] = $k . ":'" . $v . "'";
								break;
							case 'name' :
								$xname = $v;
								$xaxis [] = $k . ":'" . $v . "'";
								break;
							case 'axisLabel':
								$xaxis [] = $k . ":'" . $v . "'";
								break;
							case 'boundaryGap' :
								$xaxis [] = $k . ':' . $v;
								break;
							case 'data' :
								$xaxis [] = $k . ':' . json_encode ( $v );
								break;
						}
					}
					$xaxis = '{' . implode ( ', ', $xaxis ) . '}';
					break;
				
				case 'series' :
					foreach ( $value as $list ) {
						$tmp = array ();
						foreach ( $list as $k => $v ) {
							switch ($k) {
								case 'name' :
									$tmp [] = $k . ":'" . $v . "'";
									break;
								case 'type' :
									$tmp [] = $k . ":'" . $v . "'";
									break;
								case 'barMaxWidth' :
									$tmp [] = $k . ':' . $v;
									break;
								case 'max' :
									$tmp [] = $k . ':' . $v;
									break;
								case 'data' :
									$tmp [] = $k . ':' . json_encode ( $v );
							}
						}
						$series [] = '{' . implode ( ', ', $tmp ) . '}';
					}
					$series = implode ( ', ', $series );
					break;
			}
		}
		
		$script = <<<eof
		<script src="js/echarts.js"></script>
		<script type="text/javascript">
		
		require.config({
			paths:{ 
				echarts: './js'
			} 
		}); 
		// require echarts and use it in the callback.
		require(
				[ 
		  			'echarts', 
		  			'echarts/chart/bar', 
		  			'echarts/chart/line', 
		  			'echarts/chart/pie' 
				], 
		  		function(ec) {
		  			var myChart = ec.init(document.getElementById('$id')); 
		  			var option = { 
		  			    color : ['#87cefa', '#da70d6', '#32cd32', '#6495ed', 
    							'#ff69b4', '#ba55d3', '#cd5c5c', '#ffa500', '#40e0d0', 
   							 	'#1e90ff', '#ff6347', '#7b68ee', '#00fa9a', '#ffd700', 
    							'#6b8e23', '#ff00ff', '#3cb371', '#b8860b', '#30e0e0'],
		  				title : { 
		  					text: '$title',
		  					subtext: '$subtitle' 
		  				}, 
		  				tooltip : { 
		  					trigger: 'item' 
		  				},
			  			toolbox: { 
			  				show : true,
			  				orient : 'horizontal',
			  				x : 'right',
			  				y : 'top',
    						backgroundColor : 'rgba(0,0,0,0)',
    						borderWidth : 0,
    						padding : 5,
    						showTitle : true,
			  				feature : { 
			  					mark : true,
				  				
			  					dataZoom : {
					                show : true,
					                title : {
					                    dataZoom : '区域缩放',
					                    dataZoomReset : '区域缩放-后退'
					                }
					            },
			  					restore : true, 
			  					saveAsImage : {
							        show : true,
							        title : 'Save',
							        type : 'jpg',
							        lang : ['Click to Save'] 
							    },
							    dataView : {
					                show : true,
					                title : '数据视图',
					                readOnly: true,
					                lang : ['数据视图', '关闭', '刷新'],
					                optionToContent: function(opt) {
					                    var axisData = opt.xAxis[0].data;
					                    var series = opt.series;
					                    var table = '<table style="width:100%;text-align:center"><tbody><tr>';
					                    if('$xname' == 'OJ assignment') {
					                    	table += '<td>$xname</td>'
					                                 + '<td>通过人数</td>'
					                                 + '<td>提交人数</td>'
					                                 + '<td>' + series[0].name + '</td>'
					                                 + '</tr>';
						                    for (var i = 0, l = axisData.length; i < l; i++) {
						                    	var element = axisData[i].split(/[\(|\)|/]/);
						                        table += '<tr style="border-bottom:#e5e5e5 solid 1px">'
						                                 + '<td>' + element[0] + '</td>'
						                                 + '<td>' + element[1] + '</td>'
						                                 + '<td>' + element[2] + '</td>'
						                                 + '<td>' + series[0].data[i] + '</td>'
						                                 + '</tr>';
					                    	}
					                    } else {
					                    	table += '<td>$xname</td>'
					                                 + '<td>' + series[0].name + '</td>'
					                                 + '</tr>';
						                    for (var i = 0, l = axisData.length; i < l; i++) {
						                        table += '<tr style="border-bottom:#e5e5e5 solid 1px">'
						                                 + '<td>' + axisData[i] + '</td>'
						                                 + '<td>' + series[0].data[i] + '</td>'
						                                 + '</tr>';
					                    	}
					                    }
					                    table += '</tbody></table>';
					                    return table;
					                }
					            },
			  				} 
		  				},
				  		xAxis : [$xaxis], 
				  		yAxis : [{type : 'value'}], 
				  		series : [$series] 
					}; 
			  		
				  	myChart.setOption(option);
				} 
		); 
		</script>
eof;
		echo $script;
	}
}