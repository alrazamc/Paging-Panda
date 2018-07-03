<link href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/angular-icheck.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/angular-icheck.js"></script>

<script type="text/javascript">
  var api_base_url = '<?php echo site_url('api') ?>/';
  var angapp = angular.module('app', ['angular-icheck']);
  angapp.controller('pageController', function($scope, $http, $sce, $filter, $timeout){
  	$scope.graph_url = '<?php echo GRAPH_API_URL ?>';
    $scope.page_preloader = true;
    $scope.pages = <?php echo json_encode($pages) ?>;
    $scope.categories = <?php echo json_encode($categories) ?>;
    $scope.alert = '';
    $scope.selected_page_count = 0;
    $scope.select_all = true;
    $scope.new_category_name = '';
    $scope.new_category_include = '0';
    $scope.new_category_preloader = false;
    $scope.feed_url = '';
    $scope.feed_title = '';
    $scope.to_pending = '0';

    for(var i=0; i < $scope.pages.length; i++)
    	$scope.pages[i].selected = false;
    $scope.append_categories = function()
    {
    	$scope.categories.unshift({
	    	category_id : -1,
	    	category_name : 'Publish Once',
	    	category_color : '#28a745'
	    });
	    $scope.categories.unshift({
	    	category_id : 0,
	    	category_name : 'General',
	    	category_color : '#007bff'
	    });
    }

    $scope.append_categories();

    $scope.selected_category = $scope.categories[0];
    	
    $scope.change_category = function(record){
    	$scope.selected_category = record;
    }

    $scope.add_new_category = function()
    {
    	if($scope.new_category_name == "")
    		return;
    	$scope.new_category_preloader = true;
    	var data = {
    		category : $scope.new_category_name,
    		include : $scope.new_category_include
    	};
    	$http.post(api_base_url + "categories/add", data).success(function(data){
    		$scope.categories = data;
            $scope.append_categories();
            for(var i=0; i<$scope.categories.length; i++)
                if($scope.categories[i].category_name == $scope.new_category_name)
                  $scope.selected_category = $scope.categories[i];
            $scope.new_category_preloader = false;
            $scope.new_category_name = '';
            $scope.new_category_include = '0';
    		jQuery('#add-category').modal('hide');
    		jQuery('#new-category-msg').removeClass('d-none').fadeIn().delay(3000).fadeOut();
    	});	
    }

    $scope.$watch('[pages]', function(){
    	var selected_page_count = 0;
    	for(var i=0; i < $scope.pages.length; i++)
    		if($scope.pages[i].selected)
    			selected_page_count++;
    	$scope.selected_page_count = selected_page_count;
    }, true);

    $scope.$watch('[select_all]', function(){
    	for(var i=0; i < $scope.pages.length; i++)
    		$scope.pages[i].selected = $scope.select_all;
    }, true);
    


    $scope.is_valid = function()
    {
    	if(typeof $scope.selected_category === 'undefined')
    		return false;
    	return true;
    }

    $scope.find_feed = function()
    {
        if($scope.feed_url == '') return;
        $scope.alert = ''
        $scope.find_preloader = true;
        $http.post(api_base_url + 'import/find', {url: $scope.feed_url}).then(function(response){
            if(response.data.error)
                $scope.alert = $sce.trustAsHtml(response.data.message);
            else if(response.data.success)
                $scope.feed_title = response.data.feed_title;
            $scope.find_preloader = false;
        }, function(){
            $scope.find_preloader = false;
        });
    }
    

    $scope.send_feed = function()
    {
    	if(!$scope.is_valid()) return
    	$scope.send_preloader = true;
        $scope.alert = '';
    	var pages = [];
    	
    	for(var i=0; i<$scope.pages.length; i++)
    		if($scope.pages[i].selected)
    			pages.push($scope.pages[i].account_id);

    	var feed = {
    		pages : pages,
    		category_id : $scope.selected_category.category_id,
            feed_url: $scope.feed_url,
    		feed_title: $scope.feed_title,
    		to_pending: $scope.to_pending
    	};

    	$http.post(api_base_url + 'import/add', {feed:feed}).then(function(response){
            if(response.data.error)
                $scope.alert = $sce.trustAsHtml(response.data.message);
            else if(response.data.success)
            {
                window.location.href = response.data.message;
            }
            $scope.send_preloader = false;
        }, function(){ 
            $scope.send_preloader = false;
        });
    }
  });
</script>
<div class="container my-5" ng-app="app" ng-controller="pageController">
	<div class="row">
		<div class="col-sm-8 mb-3">
			<h4 class="page-title">Add RSS Feed</h4>
		</div>
		<div class="col-sm-4 mb-3 text-center text-sm-right">
			<a href="<?php echo site_url('import/feeds') ?>" class="btn btn-green btn-shadow" ><i class="fas fa-arrow-left"></i> RSS Feeds</a>
		</div>
		<div class="col-md-12">
			<?php echo $this->session->flashdata('alert') ?>
		</div>
		<div class="col-md-12" ng-bind-html="alert">
		</div>
	</div>
    <div class="row" ng-show="feed_title == ''"  ng-cloak>
        <div class="col-12">
            <div class="card air-card">
                <div class="card-body">
                    <p class="text-muted mb-1">Add an RSS feed of your website/blog and <?php echo getenv('SITE_NAME') ?> will automatically add new content published on that site to your library here.</p>
                    <p class="text-muted"><i class="fas fa-info-circle"></i> <?php echo getenv('SITE_NAME') ?> will check every 24 hours for new content</p>
                    <div class="row">
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="" ng-model="feed_url" placeholder="Put RSS feed url here...">
                        </div>
                        <div class="col-sm-6">
                            <button class="btn btn-success btn-shadow" ng-disabled="find_preloader || feed_url == ''" ng-click="find_feed()">Find Feed <i class="fas fa-spin fa-circle-notch" ng-show="find_preloader"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<div class="row" ng-show="feed_title != '' && feed_url != ''" ng-cloak>
		<div class="col-xl-4 col-lg-4 col-md-4 mb-3">
			<div class="card air-card">
				<div class="card-body {{ pages.length == 0 ? 'p-5' : '' }}">
					<div class="row" ng-show="pages.length">
						<div class="col-12">
							<i-check ng-model="select_all">Pages ({{ selected_page_count }}/{{ pages.length }})</i-check>
						</div>
						<div class="col-12"><hr class="mt-2"></div>
					</div>

					<div class="row mb-2" ng-repeat="page in pages">
						<div class="col-10 text-truncate">
							<i-check ng-model="page.selected">
								{{ page.account_name }}
							</i-check>
						</div>
						<div class="col-2">
							<img ng-src="{{ graph_url+page.account_fb_id }}/picture?width=30&height=30&access_token={{ page.access_token }}" width="30" height="30" class="rounded-circle border float-right">
						</div>
					</div>
					<p class="text-muted text-center" ng-if="pages.length == 0">
						Import your Facebook pages to start posting<br>
						Let's go to <a href="<?php echo site_url('accounts') ?>">Pages Menu</a>
					</p>

				</div>
			</div>
		</div>

		<div class="col-xl-8 col-lg-8 col-md-8 mb-5">
			<div class="card air-card">
				<div class="card-body">
					<div class="row">
						<div class="col-lg-6 col-md-12 mb-3">
							<label class="font-weight-500 text-muted">Category</label>
							<a href="" class="float-right text-muted" id="btn-add-category"><i class="fas fa-plus-circle"></i> New Category</a>
							<div class="dropdown dropdown-green">
							  <a class="btn btn-default btn-block border text-left text-dark dropdown-green text-truncate  dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							    <div class="category-color-round mr-1" style="background-color: {{ selected_category.category_color  }}">&nbsp;</div>
							    {{ selected_category.category_name }}
							    <i class="fas fa-caret-down dropdown-caret"></i>
							  </a>

							  <div class="dropdown-menu w-100" aria-labelledby="dropdownMenuLink">
							  	<div class="scrollable-dropdown">
							  		<a class="dropdown-item {{ record.category_id == selected_category.category_id ? 'bg-success text-white' : '' }}" href="" ng-click="change_category(record)"  ng-repeat="record in categories">
								    	<div class="category-color-round mr-1" style="background-color: {{ record.category_color  }}">&nbsp;</div>
								    	{{ record.category_name }}
									</a>
							  	</div>
							  </div>
							</div>
							<div class="text-success mt-2 text-center d-none" id="new-category-msg">New category added in list</div>
						</div>
						<div class="col-12">
                            <h5>Website URL</h5>
							<div class="p-3 mb-3 bg-grey">
                                <div>{{ feed_title }}</div>
                                <div>{{ feed_url }}</div>
                            </div>
                            <div class="">
                                Import new items to
                                <div class="form-check">
                                  <input class="form-check-input" type="radio" id="to_pending_1" name="to_pending" ng-model="to_pending"  ng-value="1">
                                  <label class="form-check-label" for="to_pending_1" >
                                    Pending Content
                                  </label>
                                </div>
                                <div class="form-check">
                                  <input class="form-check-input" type="radio" id="to_pending_0" name="to_pending" ng-model="to_pending"  ng-value="0" checked>
                                  <label class="form-check-label" for="to_pending_0">
                                    Approved Content
                                  </label>
                                </div>
                            </div>
						</div>
					</div>

					<div class="row">
						<div class="col-12 text-center mt-3">
							<button class="btn btn-success btn-shadow" ng-disabled="send_preloader" ng-click="send_feed()">Add Feed <i class="fas fa-spin fa-circle-notch" ng-show="send_preloader"></i></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="add-category" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h5 class="modal-title" id="exampleModalLongTitle">Add New Category</h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
	      <div class="modal-body">
	        <div class="row">
	        	<div class="col-12">
					<div class="form-group">
						<label>Category Name</label>
						<input type="text" class="form-control" name="category" ng-model="new_category_name" autofocus="" required="">
					</div>
					<div class="form-group">
						<div class="form-check">
							<input type="checkbox" class="form-check-input" id="include" name="include" ng-model="new_category_include" value="1" ng-true-value="1" ng-false-value="0">
							<label class="form-check-label" for="include">Include the content of this category in the random post selection</label>
						</div>
					</div>
					<button type="button" class="btn btn-success btn-shadow" ng-click="add_new_category()" ng-disabled="!new_category_name || new_category_preloader">Add Category <i class="fas fa-spin fa-circle-notch" ng-show="new_category_preloader"></i></button>
        			<a href="" class="text-success font-weight-500"  data-dismiss="modal">Cancel</a>
	        	</div>
	        </div>
	      </div>
	      
	    </div>
	  </div>
	</div>

</div>


<script type="text/javascript">
	$(function(){
		$('#btn-add-category').click(function(){
			$('#add-category').modal('show');
		});
		$('#add-category').on('shown.bs.modal', function(){
			$('#add-category input[type="text"]').focus();
		});
	});
</script>