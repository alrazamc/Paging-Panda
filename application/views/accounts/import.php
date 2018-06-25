<script type="text/javascript">
  var url = '<?php echo site_url('api/accounts') ?>/';
  var angapp = angular.module('app', []);
  angapp.controller('pageController', function($scope, $http, $sce){
    $scope.fb_pages = [];
    $scope.fb_pages_before = '';
    $scope.fb_pages_after = '';
    $scope.fb_pages_preloader = false;
    $scope.selected_pages  = 0;
    $scope.alert = '';
    $scope.account_limit = '<?php echo $page_limit ?>';
    $scope.pages_added = '<?php echo count($pages) ?>';
    $scope.remaining_pages = $scope.account_limit - $scope.pages_added;
    $scope.added_pages_ids = <?php echo json_encode($pages) ?>;

    $scope.get_pages = function(cursor, cursor_value){
      req_params = {};
      if(cursor)
      	req_params[cursor] = cursor_value;
      $scope.fb_pages_preloader = true;
      //$scope.fb_pages = [];
      $scope.fb_pages_before = '';
      $scope.fb_pages_after = '';
      $http.get(url+'import/', {params: req_params}).success(function(data){
        $scope.fb_pages_preloader = false;
        if(data.type == <?php echo AJAX_RESPONSE_TYPE_ERROR ?>){
          $scope.alert = $sce.trustAsHtml(data.message);
          return;
        }
        $scope.fb_pages = data.nodes;
        if(data.next)
            $scope.fb_pages_after = data.next;
        if(data.previous)
            $scope.fb_pages_before = data.previous;
      });
    }

    $scope.get_pages();
    
    $scope.savePages = function(){
      $scope.save_preloader = true;
      var selected_pages = [];
      for(var i=0; i<$scope.fb_pages.length; i++)
            if($scope.fb_pages[i].selected)
            	selected_pages.push($scope.fb_pages[i]);
      if(selected_pages.length == 0)
      	return;
      var post_data = {
        data: selected_pages, 
      };
      $http.post(url+'save/',  post_data).success(function(data, status, headers) {
        $scope.save_preloader = false;
        if(data.type == <?php echo AJAX_RESPONSE_TYPE_REDIRECT ?>){
          window.location.href = data.message;
          return true;
        }else if(data.type == <?php echo AJAX_RESPONSE_TYPE_SUCCESS ?> || data.type == <?php echo AJAX_RESPONSE_TYPE_ERROR ?>){
          $scope.alert = $sce.trustAsHtml(data.message);
        }
      });      
    };


    $scope.$watch('[fb_pages]', function(){
      $scope.selected_pages = 0;
      for(var i=0; i<$scope.fb_pages.length; i++)
            if($scope.fb_pages[i].selected && $scope.added_pages_ids.indexOf($scope.fb_pages[i].id) == -1)
            	$scope.selected_pages++;
    }, true);


    $scope.allSelected = false;
    $scope.toggleAllSelected = function(){
      if($scope.allSelected)
        $scope.allSelected = true;
      else
        $scope.allSelected = false;
      for(var i=0; i<$scope.filter_fb_pages.length; i++)
            $scope.filter_fb_pages[i].selected = $scope.allSelected;
    }
  });
</script>
<div class="container my-5" ng-app="app" ng-controller="pageController">
  <div class="row">
    <div class="col-md-12" ng-bind-html="alert" ng-show="alert"></div>
    <div class="col-md-12">
      <?php echo $this->session->flashdata('alert') ?>
    </div>
  </div>
  <div class="row">
    <div class="col-8">
      <h4 class="page-title">Import Pages</h4>
    </div>
    <div class="col-4 text-right">
      <a href="<?php echo site_url('/accounts') ?>" class="btn btn-green"><i class="fas fa-arrow-left"></i> Pages</a>
    </div>
  </div>

  <div class="row" ng-show="fb_pages.length" ng-cloak>
    <div class="col-md-12">
      <p class="text-center">
        <span ng-show="remaining_pages > 0" class="text-muted">Please select and save the pages, You can add <strong>{{ remaining_pages }}</strong> more new page(s)</span>
        <span ng-show="remaining_pages == 0" class="text-danger">You have reached page limit, Please <a class="text-success" href="<?php echo site_url('payments/pay') ?>">upgrade your subscription plan</a> or remove some pages to add more</span>
      </p>
    </div>
    <div class="col-sm-6 mb-3">
      <form>
        <div class="form-group">
          <div class="input-group">
            <div class="input-group-prepend">
              <div class="input-group-text text-success"><i class="fas fa-search"></i></div>
            </div>
            <input type="text" class="form-control" placeholder="search pages" ng-model="search_fb_pages">
          </div>      
        </div>
      </form>
    </div>
    <div class="col-sm-3 text-center text-muted pt-2 mb-3" ng-cloak>
      <strong>{{ selected_pages }}</strong> new page(s) selected
    </div>
    <div class="col-sm-3 mb-3 text-center">
      <button class="btn btn-success btn-shadow" ng-click="savePages()" ng-disabled="selected_pages == 0" ng-show="selected_pages <= remaining_pages">Save Pages <i class="fas fa-spin fa-circle-notch" ng-show="save_preloader"></i></button>
      <div class="text-danger pt-2" ng-show="selected_pages > remaining_pages">Page Limit Reached</div>
    </div>
  </div>

  <div class="row" ng-show="fb_pages_preloader">
    <div class="col-sm-12 text-center">
      <h3 class="text-success"><i class="fas fa-spin fa-circle-notch"></i></h3>
    </div>
  </div>

  <div class="row" ng-cloak ng-show="fb_pages.length && !fb_pages_preloader">
    <div class="col-xl-4 col-lg-4 col-sm-6 mb-3" ng-repeat="node in (filter_fb_pages = (fb_pages | filter: search_fb_pages))">
      <div class="card air-card page-card {{ node.selected == true ? 'selected' : '' }}" ng-click="node.selected = !node.selected">
        <div class="card-body">
          <img ng-src="{{ node.picture.url }}" width="50" height="50" class="float-left rounded-circle border">
          <p class="pt-2 mb-0">
            {{ node.name }} <br>
            <span class="text-muted">{{ node.fan_count | number }} likes</span>
            <span class="text-danger" ng-show="node.perms.indexOf('<?php echo FB_POST_PERMISSION ?>') == -1"
              data-toggle="tooltip" data-placement="bottom" 
              title="You can only view analytics for this page as you do not have permissions to publish posts for this page, Please contact page admin to get the editor role for this page">
              <br>
              No post permissions
            </span>
          </p>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12 text-center" ng-show="fb_pages_before || fb_pages_after" ng-cloak>
      <a class="btn btn-green" ng-show="fb_pages_before != '' " href="" ng-click="get_pages('before', fb_pages_before)">Previous</a>
      <a class="btn btn-green" ng-show="fb_pages_after != '' "  href="" ng-click="get_pages('after', fb_pages_after)">Next</a>
    </div>
  </div>
	
</div>