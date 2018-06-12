<link rel="stylesheet" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/daterangepicker.css" />
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/moment.min.js"></script>
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/daterangepicker.js"></script>

<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/amcharts/amcharts.js"></script>
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/amcharts/serial.js"></script>
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/amcharts/pie.js"></script>
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/amcharts/themes/light.js"></script>
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/amcharts/plugins/export/export.min.js"></script>
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/amcharts/plugins/responsive/responsive.min.js"></script>
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/country_codes.js"></script>

<link rel="stylesheet" type="text/css" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/amcharts/plugins/export/export.css">

<script type="text/javascript">
  var api_base_url = '<?php echo site_url('api') ?>/';
  var angapp = angular.module('app', []);
  angapp.filter('underscore', function() {
    return function(input) {
    	if(typeof input === 'undefined')
    		return '';
      input = input.replace(/_/g , " ");
      return input.charAt(0).toUpperCase() + input.slice(1);
    };
  });

  angapp.filter('search', function($filter) {
    return function(data, input) {
    	if(typeof input === 'undefined' || input == '')
    		return data;
      input = input.replace(/ /g , "_");
      input = input.charAt(0).toLowerCase() + input.slice(1);
      return $filter('filter')(data, input);
    };
  });
  angapp.controller('pageController', function($scope, $http, $sce, $filter, $timeout){
  	$scope.graph_url = '<?php echo GRAPH_API_URL ?>';
    $scope.page_preloader = true;
    $scope.pages = <?php echo json_encode($accounts) ?>;
    $scope.metrics = <?php echo json_encode($favorites) ?>;
    $scope.favorites = [];
    $scope.date_ranges = [];
    $scope.selected_page = '';
    if($scope.pages.length)
    	$scope.selected_page = $scope.pages[0];
    $scope.current_favorite_index = 0;
    $scope.favorites_loading = false;

    $scope.single_date = '<?php echo date('Y-m-d', strtotime('-2 day')) ?>';
    $scope.date_range = '<?php echo date('Y-m-d', strtotime('-30 day')) ?> - <?php echo date('Y-m-d') ?>';

    $scope.graph_type_line = <?php echo GRAPH_TYPE_LINE ?>;
    $scope.graph_type_multi_line = <?php echo GRAPH_TYPE_MULTI_LINE ?>;
    $scope.graph_type_bar = <?php echo GRAPH_TYPE_BAR ?>;
    $scope.graph_type_pie = <?php echo GRAPH_TYPE_PIE ?>;


   
	$http.get('<?php echo getenv('ASSET_BASE_URL') ?>assets/js/metric_date_ranges.json').then(function(response){
		$scope.date_ranges = response.data;
		$scope.selected_date_range = $scope.date_ranges[6];
		$scope.page_preloader = false;
		$scope.start_loading_metrics();
	});

	$scope.start_loading_metrics = function()
	{
		if($scope.pages.length == 0 || $scope.metrics.length == 0)
			return;
		if($scope.favorites.length == 0)
		{
			for(var i = 0; i < $scope.metrics.length; i++)
				$scope.favorites.push({
					metric: $scope.metrics[i],
					date_ranges: $scope.date_ranges.slice(0),
					selected_date_range: this.date_ranges[6],
					date_range: $scope.date_range,
					single_date: $scope.single_date,
					graph_data: [],
					graph_preloader: false,
					chart: null,
					alert: '',
					show_me: false
				});
		}else
		{
			for(var i = 0; i < $scope.favorites.length; i++){
				$scope.favorites[i].graph_data = [];
				$scope.favorites[i].graph_preloader = false;
				$scope.favorites[i].alert = '';
				$scope.favorites[i].show_me = false;
				if($scope.favorites[i].chart){
					$scope.favorites[i].chart.clear();
					$scope.favorites[i].chart = null;
				}

			}
		}

		$scope.current_favorite_index = 0;
		$scope.favorites_loading = true;
		$scope.load_favorite();
	}

	$scope.change_page = function(record){
		$scope.favorites_loading = false;
		$scope.selected_page = record;
		$scope.start_loading_metrics();
	}

	$scope.change_date_range = function(favorite, record){
		if(favorite.graph_preloader)
			return;
		favorite.selected_date_range = record;
	}

	$scope.load_favorite = function(favorite)
	{
		if($scope.favorites_loading)
		{
			if($scope.current_favorite_index >= $scope.favorites.length)
			{
				$scope.favorites_loading = false;
				return;
			}
			favorite = $scope.favorites[$scope.current_favorite_index];
			favorite.show_me = true;
		}
		favorite.graph_preloader = true;
		var request_url = $scope.graph_url+ $scope.selected_page.account_fb_id + '/insights/' + favorite.metric.metric_name + '/' + favorite.metric.period;
		var params = {
			access_token : $scope.selected_page.access_token
		}
		if(favorite.metric.graph_type == $scope.graph_type_pie || favorite.metric.graph_type == $scope.graph_type_bar)
		{
			params.since = favorite.single_date;
		}else if(favorite.selected_date_range.period != 'custom'){
			params.date_preset = favorite.selected_date_range.period;
		}else{
			var range = favorite.date_range.split(' - ');
			params.since = range[0];
			params.until = range[1];
		}
		request_url += '?' + jQuery.param(params);		
		$http.get(request_url).then(function(response){
			favorite.alert = $sce.trustAsHtml('');
			$scope.prepare_data(favorite, response.data.data);
			$timeout(register_date_pickers, 200);
			if($scope.favorites_loading)
			{
				$scope.current_favorite_index++
				$scope.load_favorite();
			}
		},function(response){
			var error = response.data.error.type + ": " +response.data.error.message;
			favorite.alert = $sce.trustAsHtml(error);
			if(favorite.chart){
				favorite.chart.clear();
				favorite.chart = null;
			}
			favorite.graph_data = [];
			favorite.graph_preloader = false;
			$timeout(register_date_pickers, 200);
			if($scope.favorites_loading)
			{
				$scope.current_favorite_index++
				$scope.load_favorite();
			}
		});
	}

	$scope.prepare_data = function(favorite, data)
	{
		if(typeof data === 'undefined' || data.length == 0 || data[0]['values'].length == 0)
		{
			favorite.graph_data = [];
			$scope.remove_chart(favorite);
			favorite.graph_preloader = false;
			return;
		}
		favorite.graph_data = data[0]['values'];
		$scope.remove_chart(favorite);
		if(favorite.metric.graph_type == $scope.graph_type_line)
			$scope.render_line_graph(favorite);
		else if(favorite.metric.graph_type == $scope.graph_type_multi_line)
			$scope.render_multi_line_graph(favorite);
		else if(favorite.metric.graph_type == $scope.graph_type_pie)
			$scope.render_pie_chart(favorite);
		else if(favorite.metric.graph_type == $scope.graph_type_bar)
			$scope.render_bar_chart(favorite);
		favorite.graph_preloader = false;
	}

	$scope.get_graph_config = function(favorite)
	{
		return  {
	      "type": "serial",
	      "theme": "light",
	      "addClassNames": true,
	      "marginRight": 40,
	      "marginLeft": 60,
	      "autoMarginOffset": 20,
	      "mouseWheelZoomEnabled":true,
	      "usePrefixes" : true,
	      "dataDateFormat": "YYYY-MM-DD",
	       "responsive": { "enabled": true },
	      "valueAxes": [{
	          "id": "g1",
	          "axisAlpha": 0,
	          "position": "left",
	          "ignoreAxisWidth":true
	      }],  
	      "chartScrollbar": {
	          "graph": "g1",
	          "oppositeAxis":false,
	          "offset":30,
	          "scrollbarHeight": 30,
	          "backgroundAlpha": 0,
	          "selectedBackgroundAlpha": 0.1,
	          "selectedBackgroundColor": "#888888",
	          "graphFillAlpha": 0,
	          "graphLineAlpha": 0.5,
	          "selectedGraphFillAlpha": 0,
	          "selectedGraphLineAlpha": 1,
	          "autoGridCount":true,
	          "color":"#AAAAAA"
	      },
	      "chartCursor": {
	        "bulletsEnabled" : true,
	        "enabled" : true,
	        "animationDuration" : 0.1
	      },
	      "categoryField": "end_time",
	      "categoryAxis": {
	          "parseDates": true,
	          "dashLength": 1,
	          "minorGridEnabled": true
	      },
	      "export": {
	          "enabled": true,
	          "fileName" : $filter('underscore')(favorite.metric.metric_name)
	      },
	      "legend": {
	            "useGraphSettings": true,
	            "align" : 'center',
	            "periodValueText" : "[[value.sum]]"
	      }
	    };
	}

	$scope.get_am_graphs = function(keys)
	{
		var colors = ['#28a745', '#007bff', '#dc3545', '#6610f2', '#fd7e14', '#6c757d', '#343a40', '#17a2b8'];
		if(keys.length == 0) return;
		var graphs = [];
		for(var i = 0; i < keys.length; i ++)
		{
		  graphs.push({
		      "id": "g" + i + 1,
		      "bullet": "round",
		      "bulletBorderAlpha": 1,
		      "bulletColor": "#FFFFFF",
		      "bulletSize": 5,
		      "hideBulletsCount": 50,
		      "lineThickness": 2,
		      "type": "smoothedLine",
		      "lineColor" : typeof colors[i] !== 'undefined' ? colors[i] : '',
		      "title": keys[i],
		      "useLineColorForBulletBorder": true,
		      "valueField": keys[i],
		      "balloonFunction": function(info) {
			      if (info.values.value)
			        return "<span style='font-size:14px; color:#000000;'><b>" + info.graph.title + " : " + nFormatter(info.values.value) + "</b></span>";
			      else
			        return '';
			    }
		  });
		}
		return graphs;
	}

	$scope.get_graph_title =  function (favorite)
	{
		return  [
		    {
		      "text": $filter('underscore')(favorite.metric.metric_name),
		      "size": 15
		    }
		  ];
	}

	$scope.remove_chart = function(favorite)
	{
		if(typeof favorite.chart !== 'undefined' &&  favorite.chart != null)
	    {
	      favorite.chart.clear();
	      favorite.chart = null;
	    }
	}

	$scope.render_line_graph = function(favorite)
	{
		if(favorite.metric.metric_name == 'page_video_view_time')
			for(var i = 0; i< favorite.graph_data.length; i++)
				favorite.graph_data[i].value = toHours(favorite.graph_data[i].value);
		var chart_config = $scope.get_graph_config(favorite);
	    chart_config.graphs = [{
	          "id": "g1",
	          "bullet": "round",
	          "bulletBorderAlpha": 1,
	          "bulletColor": "#FFFFFF",
	          "bulletSize": 5,
	          "hideBulletsCount": 50,
	          "lineThickness": 2,
	          "type": "smoothedLine",
	          "lineColor" : "#28a745",
	          "negativeLineColor": "#637bb6",
	          "title": $filter('underscore')(favorite.metric.metric_name),
	          "useLineColorForBulletBorder": true,
	          "valueField": 'value',
	          "balloonFunction": function(info) {
			        return "<span style='font-size:14px; color:#000000;'><b>" + nFormatter(info.values.value) + "</b></span>";
			    }
	      }];
	    chart_config.titles = $scope.get_graph_title(favorite);
	    chart_config.dataProvider = favorite.graph_data;
	    favorite.chart = AmCharts.makeChart("chart-"+favorite.metric.metric_id, chart_config);
    	return favorite.chart;
	}

	$scope.render_multi_line_graph = function(favorite)
	{
		var keys = [];
	    var key_counts = [];
	    for(var i = 0; i< favorite.graph_data.length; i++)
		{
			for (var key in favorite.graph_data[i].value) {
			  if (favorite.graph_data[i].value.hasOwnProperty(key)) {
			    favorite.graph_data[i][key] = favorite.graph_data[i].value[key];
			    if(keys.indexOf(key) === -1)
			      keys.push(key);
			  }
			}
			delete favorite.graph_data[i].value;
		}

		if(keys.length == 0)
		{
			favorite.graph_data = [];
			$scope.remove_chart(favorite);
			return;
		}

	    var chart_config = $scope.get_graph_config(favorite);
	    chart_config.listeners = [{
		    "event": "init",
		    "method": function(event) {
		      var chart = event.chart;
		      for(var x = 0; x < chart.graphs.length; x++) {
		        var graph = chart.graphs[x];
		        var graphSum = 0;
		        for(var i = 0; i < chart.dataProvider.length; i++) {
		          graphSum += chart.dataProvider[i][graph.valueField];
		        }
		        if (graphSum == 0){
		          graph.visibleInLegend = false;
		        }
		      }
		    }
		  }];
	    chart_config.graphs = $scope.get_am_graphs(keys);
	    chart_config.titles = $scope.get_graph_title(favorite);
	    chart_config.dataProvider = favorite.graph_data;
	    favorite.chart = AmCharts.makeChart("chart-"+favorite.metric.metric_id, chart_config);
	    return favorite.chart;
	}

	$scope.render_bar_chart  = function(favorite)
	{
		var data = [];
	    var data_set = favorite.graph_data[0].value;
	    var age_chart = true;
	    if(favorite.metric.metric_name.indexOf('gender') === -1)
	    	age_chart = false;
	    if(age_chart)
	    {
		    for(key in data_set)
		    {
		    	if(typeof data_set[key] === 'object')
		    	{
		    		data_set['M.'+key] = data_set[key]['M'];
		    		data_set['F.'+key] = data_set[key]['F'];
		    	}
		    }
		    data.push({
		      'range' : '13-17',
		      'male'  : data_set.hasOwnProperty('M.13-17') ? -data_set['M.13-17'] : 0 , 
		      'female'  : data_set.hasOwnProperty('F.13-17') ? data_set['F.13-17'] : 0 
		    });
		    data.push({
		      'range' : '18-24',
		      'male'  : data_set.hasOwnProperty('M.18-24') ? -data_set['M.18-24'] : 0 , 
		      'female'  : data_set.hasOwnProperty('F.18-24') ? data_set['F.18-24'] : 0 
		    });
		    data.push({
		      'range' : '25-34',
		      'male'  : data_set.hasOwnProperty('M.25-34') ? -data_set['M.25-34'] : 0 , 
		      'female'  : data_set.hasOwnProperty('F.25-34') ? data_set['F.25-34'] : 0 
		    });
		    data.push({
		      'range' : '35-44',
		      'male'  : data_set.hasOwnProperty('M.35-44') ? -data_set['M.35-44'] : 0 , 
		      'female'  : data_set.hasOwnProperty('F.35-44') ? data_set['F.35-44'] : 0 
		    });
		    data.push({
		      'range' : '45-54',
		      'male'  : data_set.hasOwnProperty('M.45-54') ? -data_set['M.45-54'] : 0 , 
		      'female'  : data_set.hasOwnProperty('F.45-54') ? data_set['F.45-54'] : 0 
		    });
		    data.push({
		      'range' : '55-64',
		      'male'  : data_set.hasOwnProperty('M.55-64') ? -data_set['M.55-64'] : 0 , 
		      'female'  : data_set.hasOwnProperty('F.55-64') ? data_set['F.55-64'] : 0 
		    });
		    data.push({
		      'range' : '65+',
		      'male'  : data_set.hasOwnProperty('M.65+') ? -data_set['M.65+'] : 0 , 
		      'female'  : data_set.hasOwnProperty('F.65+') ? data_set['F.65+'] : 0 
		    });
		}else{
			for(key in data_set)
			 data.push({
			 	"range" : key,
			 	"value" : data_set[key]
			 });
		}
	    var chart_config = {
		  "type": "serial",
		  "theme": "light",
		  "rotate": true,
		  "usePrefixes" : true,
		  "marginRight": 40,
	      "marginLeft": 40,
	      "marginBottom" : 40,
	      "responsive": { "enabled": true },
	      "valueAxes": [{
		    "gridAlpha": 0,
		    "ignoreAxisWidth": true,
		    "labelFunction": function(value) {
		      return nFormatter(Math.abs(value));
		    },
		    "guides": [{
		      "value": 0,
		      "lineAlpha": 0.2
		    }]
		  }],
		  "categoryField": "range",
		  "categoryAxis": {
		    "gridPosition": "start",
		    "gridAlpha": 0.2,
		    "axisAlpha": 0,
		    "title" : 'Age'
		  },
		  "export": {
	          "enabled": true,
	          "fileName" : $filter('underscore')(favorite.metric.metric_name)
	      },
		  "graphs": [{
		    "fillAlphas": 0.8,
		    "lineAlpha": 0.2,
		    "type": "column",
		    "lineColor" : "#28a745",
		    "valueField": "male",
		    "title": "Male",
		    "labelText": "[[value]]",
		    "clustered": false,
		    "labelFunction": function(item) {
		      return nFormatter(Math.abs(item.values.value));
		    },
		    "balloonFunction": function(item) {
		      return "Male <br>" + item.category + ": " + nFormatter(Math.abs(item.values.value));
		    }
		  }, {
		    "fillAlphas": 0.8,
		    "lineAlpha": 0.2,
		    "type": "column",
		    "lineColor" : "#63b676",
		    "valueField": "female",
		    "title": "Female",
		    "labelText": "[[value]]",
		    "clustered": false,
		    "labelFunction": function(item) {
		      return nFormatter(Math.abs(item.values.value));
		    },
		    "balloonFunction": function(item) {
		      return "Female <br>" + item.category + ": " + nFormatter(Math.abs(item.values.value));
		    }
		  }],
		  "allLabels": [{
		    "text": "Male",
		    "x": "10%",
		    "y": "97%",
		    "bold": true,
		    "align": "middle"
		  }, {
		    "text": "Female",
		    "x": "90%",
		    "y": "97%",
		    "bold": true,
		    "align": "middle"
		  }],
		};

		if(!age_chart)
		{
			chart_config.rotate  = false;
			chart_config.allLabels = [];
			chart_config.categoryAxis.title = '';
			chart_config.graphs = [{
		    "fillAlphas": 0.8,
		    "lineAlpha": 0.2,
		    "type": "column",
		    "lineColor" : "#28a745",
		    "valueField": "value",
		    "title": "Male",
		    "labelText": "[[value]]",
		    "clustered": false,
		    "labelFunction": function(item) {
		      return nFormatter(Math.abs(item.values.value));
		    },
		    "balloonFunction": function(item) {
		      return  nFormatter(Math.abs(item.values.value));
		    }
		  }];
		}
	    chart_config.titles = $scope.get_graph_title(favorite);
	    chart_config.dataProvider = data;	    
	    favorite.chart = AmCharts.makeChart('chart-'+favorite.metric.metric_id, chart_config);
	    return favorite.chart;
	}

	$scope.render_pie_chart = function(favorite)
	{
		var data = [];
		if(typeof favorite.graph_data[0].value === 'undefined')
		{
			favorite.graph_data = [];
			$scope.remove_chart(favorite);
			return;
		}
	    var data_set = $scope.get_top_x_objs(favorite.graph_data[0].value, 10);
	    for(var key in data_set){
	      if(data_set.hasOwnProperty(key))
	        data.push({
	          'location' : parse_code(key),
	          'value' : data_set[key]
	        });
	    }
	    var chart_config = {
	      "type": "pie",
	      "theme": "light",
	      "labelsEnabled": false,
	      "usePrefixes" : true,
	      "addClassNames": true,
	      "marginRight": 40,
	      "marginLeft": 60,
	      "autoMarginOffset": 20,
	      "titleField" : 'location',
	      "valueField" : 'value',
	      "innerRadius" : "20%",
	      "sequencedAnimation" : false,
	      "startDuration":0,
	      "labelRadius":15,
	      "balloonText" : "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
	      "depth3D" : 0,
	      "angle" : 0,
	      "responsive": { "enabled": true },
	      "export": {
	          "enabled": true,
	          "fileName" : $filter('underscore')(favorite.metric.metric_name)
	      },
	      "legend": {
	            "markerType": "circle",
			    "position": "right",
			    "marginRight": 80,
			    "autoMargins": false,
			    "truncateLabels": 25 // custom parameter
	      }
	    };
	    chart_config.titles = $scope.get_graph_title(favorite);
	    chart_config.dataProvider = data;
	    favorite.chart = AmCharts.makeChart("chart-"+favorite.metric.metric_id, chart_config);
    	return favorite.chart;
	}

	$scope.get_top_x_objs = function(object, len)
	{
		var tuples = [];
		for (var key in object) tuples.push([key, object[key]]);
		tuples.sort(function(a, b) {
		    a = a[1];
		    b = b[1];
		    return a > b ? -1 : (a < b ? 1 : 0);
		});
		var length = tuples.length > len ? len : tuples.length;
		sorted_set = {};
		for (var i = 0; i < length; i++)
		  sorted_set[tuples[i][0]] = tuples[i][1];
		return sorted_set;
	}

  });

  	function register_date_pickers()
  	{
	  	jQuery('.single-date').each(function(){
	  		jQuery(this).daterangepicker({
				locale: {
			      format: 'YYYY-MM-DD'
			    },
			     singleDatePicker: true,
			     showDropdowns: true
			});
	  	});

		jQuery('.date-range').each(function(){
	  		jQuery(this).daterangepicker({
				locale: {
			      format: 'YYYY-MM-DD'
			    },
			    "maxSpan": {
			        "days": 90
			    },
			    "applyButtonClasses": "btn-success"
			});
	  	});
  	}
</script>
<div class="container my-5" ng-app="app" ng-controller="pageController">
	<div class="card air-card">
		<div class="card-body">
			<div class="row">
				<div class="col-sm-12">
					<h4 class="page-title">Insights</h4>
				</div>
			</div>
			<hr class="mt-0">
			<div class="row">
				<div class="col-12 mb-3">
					<ul class="nav nav-tabs">
					  <li class="nav-item">
					    <a class="nav-link active" href="<?php echo site_url('insights') ?>">Favorite Metrics</a>
					  </li>
					  <li class="nav-item">
					    <a class="nav-link text-success" href="<?php echo site_url('insights/all') ?>">All Metrics</a>
					  </li>
					  <li class="nav-item">
					    <a class="nav-link text-success" href="<?php echo site_url('insights/update_favorites') ?>">Update Favorites</a>
					  </li>
					</ul>
				</div>

		        <div class="col-12 text-center" ng-show="page_preloader">
		          <h3 class="text-success"><i class="fas fa-spin fa-circle-notch"></i></h3>
		        </div>

		        <div class="col-12 p-5" ng-show="!page_preloader && pages.length == 0"  ng-cloak>
		        	<p class="text-muted text-center font-italic">No Pages, Please add your Facebook pages</p>
		        </div>

		        <div class="col-12 p-5" ng-show="!page_preloader && pages.length &&  metrics.length == 0"  ng-cloak>
		        	<p class="text-muted text-center font-italic">No metrics, Please update your favorite metrics to see some graphs</p>
		        </div>

		        <div class="col-12" ng-show="!page_preloader && pages.length && metrics.length" ng-cloak>
			        <div class="row">
				        <div class="col-md-4 offset-md-4 mb-3">
				        	<div class="dropdown border dropdown-green">
							  <a class="btn text-dark  text-truncate btn-shadow btn-block dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							    <img ng-src="{{ graph_url+selected_page.account_fb_id }}/picture?width=30&height=30&access_token={{ selected_page.access_token }}" width="30" height="30" class="rounded-circle border mr-2">
							    {{ selected_page.account_name }}
							    <i class="fas fa-caret-down dropdown-caret"></i>
							  </a>

							  <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
							  	<div class="dropdown-item">
							  		<input type="text" class="form-control" name="" placeholder="search pages..." ng-model="search_pages">
							  	</div>
							    <a class="dropdown-item {{ record.account_id == selected_page.account_id ? 'bg-success text-white' : '' }}" href="" ng-click="change_page(record)"  ng-repeat="record in pages | filter:search_pages">
							    	<img ng-src="{{ graph_url+record.account_fb_id }}/picture?width=30&height=30&access_token={{ record.access_token }}" width="30" height="30" class="rounded-circle border mr-2">
							    	{{ record.account_name }}
								</a>
							  </div>
							</div>
				        </div>
			        </div>
		        </div>
		        <div class="col-12">
		        	<div class="row" ng-repeat="favorite in favorites" ng-if="favorite.show_me">
		        		<div class="col-12"  ng-show="favorite.alert != '' " ng-cloak>
							<div class="alert alert-danger">
								<i class="fas fa-exclamation-triangle"></i> 
								<span ng-bind-html="favorite.alert"></span>
							</div>
				        </div>
			        	<div class="col-12" ng-show="!page_preloader && pages.length && metrics.length" ng-cloak>
					        <div class="row">
						        <div class="col-md-2 offset-md-8 mb-3" ng-show="favorite.metric.graph_type == graph_type_line || favorite.metric.graph_type == graph_type_multi_line">
						        	<div class="dropdown border dropdown-green">
									  <a class="btn text-dark  text-truncate btn-shadow btn-block dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									    <img ng-src="{{ graph_url+selected_page.account_fb_id }}/picture?width=30&height=30&access_token={{ selected_page.access_token }}" width="1" height="30" class="invisible rounded-circle border">
									    {{ favorite.selected_date_range.title }}
									    <i class="fas fa-caret-down dropdown-caret"></i>
									  </a>

									  <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
									  	<div class="scrollable-dropdown">
										    <a class="dropdown-item {{ record.period == favorite.selected_date_range.period ? 'bg-success text-white' : '' }}" href="" ng-click="change_date_range(favorite, record)"  ng-repeat="record in favorite.date_ranges">
										    	{{ record.title }}
											</a>
										</div>
									  </div>
									</div>
						        </div>
						        <div class="col-md-2 offset-md-8 mb-3" ng-show="favorite.metric.graph_type == graph_type_bar || favorite.metric.graph_type == graph_type_pie">
						        	<input type="text" class="form-control h-100 single-date"  ng-model="favorite.single_date" ng-disabled="favorite.graph_preloader"  placeholder="pick a date">
						        </div>
						        <div class="col-md-2 mb-3">
						        	<button class="btn btn-block btn-success btn-shadow" ng-disabled="favorite.graph_preloader || favorites_loading" ng-click="load_favorite(favorite)" >
										<img ng-src="{{ graph_url+selected_page.account_fb_id }}/picture?width=30&height=30&access_token={{ selected_page.access_token }}" width="1" height="30" class="invisible rounded-circle border">
						        		ReLoad <i ng-show="favorite.graph_preloader" class="fas fa-spin fa-circle-notch"></i>
						        	</button>
						        </div>
					        </div>
					        <div class="row" ng-show="(favorite.metric.graph_type == graph_type_line || favorite.metric.graph_type == graph_type_multi_line) && favorite.selected_date_range.period == 'custom'">
					        	<div class="col-md-4 text-center text-muted text-md-right">
					        		<p class="mb-0 mt-2">Max 90 days at a time</p>
					        	</div>
					        	<div class="col-md-8">
						        	<input type="text" class="form-control date-range" ng-model="favorite.date_range" ng-disabled="favorite.graph_preloader"  placeholder="Select date range" value="<?php echo date('Y-m-d', strtotime('-30 day')) ?> - <?php echo date('Y-m-d') ?>">
					        	</div>
					        </div>
				        </div>
				        <div class="col-12 text-center" ng-show="favorite.graph_preloader" ng-cloak>
				          <h3 class="text-success"><i class="fas fa-spin fa-circle-notch"></i></h3>
				        </div>

				        <div class="col-12 p-5" ng-show="!page_preloader && !favorite.graph_preloader && pages.length && metrics.length && favorite.graph_data.length == 0"  ng-cloak>
				        	<p class="text-muted text-center font-italic">No data found</p>
				        </div>

				        <div class="col-12 pt-2" ng-show="!graph_preloader && favorite.chart"  ng-cloak>
				        	<div id="chart-{{ favorite.metric.metric_id }}" class="am-chart"></div>
				        	<p class="text-center mt-3 mt-md-1" >
				        		{{ favorite.metric.metric_description }}
				        	</p>
				        </div>
				        <div class="col-12" ng-show="!$last">
				        	<hr class="my-2 mb-3">
				        </div>
			        </div>
		        </div>
		        



			</div>
		</div>
	</div>
</div>
