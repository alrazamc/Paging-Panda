<script type="text/javascript">
  var api_base_url = '<?php echo site_url('api') ?>/';
  var angapp = angular.module('app', []);
  angapp.filter('underscore', function() {
    return function(input) {
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
  angapp.controller('pageController', function($scope, $http, $sce){
    $scope.metrics = <?php echo json_encode($metrics) ?>;
    $scope.favorites = <?php echo json_encode($favorites) ?>;
    $scope.fav_metrics = [];
    $scope.page_preloader = false;
    $scope.alert = '';
    for(var i = 0; i < $scope.favorites.length; i++)
    {
      for(var j = 0; j < $scope.metrics.length; j++)
        if($scope.metrics[j].metric_id == $scope.favorites[i].metric_id)
          $scope.fav_metrics.push( $scope.metrics[j] );
    }

    $scope.move_down = function(index){
      var temp = $scope.fav_metrics[index];
      $scope.fav_metrics[index] = $scope.fav_metrics[index+1];
      $scope.fav_metrics[index + 1] = temp;
    }

    $scope.move_up = function(index){
      var temp = $scope.fav_metrics[index];
      $scope.fav_metrics[index] = $scope.fav_metrics[index - 1];
      $scope.fav_metrics[index - 1] = temp;
    }

    $scope.add_to_fav = function(metric, $event){
      jQuery($event.currentTarget).fadeOut(200).fadeIn(200);
      for(var i = 0; i < $scope.fav_metrics.length; i++)
      {
          if($scope.fav_metrics[i].metric_id == metric.metric_id)
            return;
      }
      $scope.fav_metrics.push(metric);
      return;
    }

    $scope.remove_from_fav = function(metric, $event){
      for(var i = 0; i < $scope.fav_metrics.length; i++)
      {
          if($scope.fav_metrics[i].metric_id == metric.metric_id)
            $scope.fav_metrics.splice(i, 1);
      }
      return;
    }

    
    $scope.save_favorites = function(){
      $scope.save_preloader = true;
      var fav_metrics = [];
      for(var i=0; i<$scope.fav_metrics.length; i++)
          fav_metrics.push($scope.fav_metrics[i].metric_id);
      var post_data = {
        metrics: fav_metrics, 
      };
      $http.post(api_base_url+'insights/save',  post_data).success(function(data) {
        $scope.save_preloader = false;
        $scope.alert = $sce.trustAsHtml(data.message);
      });      
    };
    
  });
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
					    <a class="nav-link text-success" href="<?php echo site_url('insights') ?>">Favorite Metrics</a>
					  </li>
					  <li class="nav-item">
					    <a class="nav-link text-success" href="<?php echo site_url('insights/all') ?>">All Metrics</a>
					  </li>
					  <li class="nav-item">
					    <a class="nav-link active" href="<?php echo site_url('insights/update_favorites') ?>">Update Favorites</a>
					  </li>
					</ul>
				</div>

        <div class="col-12" ng-bind-html="alert">
          
        </div>

        <div class="col-12 text-center" ng-show="page_preloader">
          <h3 class="text-green"><i class="fas fa-spin fa-circle-notch"></i></h3>
        </div>

        <div class="col-sm-6 mb-3" ng-show="metrics.length"  ng-cloak>
          <h5 class="text-muted text-center">available metrics <span class="badge badge-secondary">{{ metrics.length }}</span></h5>
          <form>
            <div class="form-group">
              <input type="text" class="form-control" ng-model="search_metrics" placeholder="search available metrics" name="">
            </div>
          </form>

          <div class="list-group">
            <div class="list-group-item flex-column align-items-start" ng-repeat="metric in metrics | search:search_metrics">
              <div class="w-100 d-flex justify-content-between">
                <h6 class="mb-1">{{ metric.metric_name | underscore}}</h6>
                  <a href="" ng-click="add_to_fav(metric, $event)" class="text-success" title="Add to favorites"> 
                    <i class="fas fa-long-arrow-alt-right fa-2x"></i> 
                  </a>
              </div>
              <p class="mb-1">{{ metric.metric_description }}</p>
            </div>
          </div>
        </div>

        <div class="col-sm-6 mb-3" ng-show="metrics.length"  ng-cloak>
          <h5 class="text-muted text-center">favorites metrics <span class="badge badge-secondary">{{ fav_metrics.length }}</span></h5>
          <div class="row">
            <div class="col-lg-8">
              <form>
                <div class="form-group">
                  <input type="text" class="form-control" ng-model="search_fav_metrics" placeholder="search favorite metrics" name="">
                </div>
              </form>
            </div>
            <div class="col-lg-4 mb-3">
              <button class="btn btn-block btn-success btn-shadow" ng-click="save_favorites()" ng-disabled="save_preloader">Save Favorites <i ng-show="save_preloader" class="fas fa-spin fa-circle-notch"></i></button>
            </div>
          </div>
          

          
          <div class="list-group">
            <div class="list-group-item flex-column align-items-start" ng-repeat="metric in fav_metrics | search:search_fav_metrics">
              <div class="w-100 d-flex justify-content-between">
                <h6 class="mb-1">{{ metric.metric_name | underscore}}</h6>
                <div>
                  <a href="" class="text-success" title="Move Down" ng-show="!$last" ng-click="move_down($index)"><i class="fas fa-chevron-circle-down"></i></a>
                  <a href="" class="text-success" title="Move Up" ng-show="!$first" ng-click="move_up($index)"><i class="fas fa-chevron-circle-up"></i></a>
                  <a ng-click="remove_from_fav(metric, $event)" href="" class="text-danger "  title="Remove from favorites"> 
                    <i class="fas fa-times fa-lg"></i> 
                  </a>
                </div>
              </div>

              <p class="mb-1">{{ metric.metric_description }}</p>
            </div>
          </div>
        </div>


			</div>
		</div>
	</div>
</div>