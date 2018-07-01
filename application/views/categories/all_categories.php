<script type="text/javascript">
  var api_base_url = '<?php echo site_url('api') ?>/';
  var angapp = angular.module('app', []);
  angapp.controller('pageController', function($scope, $http, $sce, $filter, $timeout){
  	$scope.categories = <?php echo json_encode($categories) ?>;
    $scope.categories.unshift({
    	category_id : -1,
    	category_name : 'Publish Once',
    	category_color : '#28a745',
    	include_in_random: 0,
    	content_count : <?php echo $use_once_count ?>
    });
    $scope.categories.unshift({
    	category_id : 0,
    	category_name : 'General',
    	category_color : '#007bff',
    	include_in_random: 1,
    	content_count : <?php echo $general_count ?>
    });
    
  });
</script>

<div class="container my-5" ng-app="app" ng-controller="pageController">
	<div class="row">
		<div class="col-md-12">
			<?php echo $this->session->flashdata('alert') ?>
		</div>
		<div class="col-12">
			<h4 class="page-title">Categories</h4>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 mb-3">
			<div class="input-group">
		        <div class="input-group-prepend">
		        	<div class="input-group-text text-success"><i class="fas fa-search"></i></div>
		        </div>
		        <input type="text" class="form-control" placeholder="Search category..." ng-model="query" >
		    </div>
		</div>
		<div class="col-sm-6 mb-3 text-center text-sm-right">
			<a href="<?php echo site_url('categories/add') ?>" class="btn btn-success btn-shadow" ><i class="fas fa-plus"></i> Add Category</a>
		</div>
		<div class="col-xl-4 col-lg-4 col-sm-6 mb-3" ng-repeat="record in categories | filter:query" ng-cloak>
			<div class="card air-card position-relative">
				<div class="category-color" style="background-color: {{ record.category_color }}"></div>
				<div class="card-body px-3 py-3">
					<h6>
						{{ record.category_name }}
						<span class="float-right" >
							<a href="<?php echo site_url("categories/reset/") ?>{{ record.category_id }}" class="text-dark mx-1" data-toggle="tooltip" data-placement="bottom"  title="Reset content with recently added first"><i class="fas fa-redo"></i></a>
							<a href="<?php echo site_url("categories/shuffle/") ?>{{ record.category_id }}" class="text-dark mx-1" data-toggle="tooltip" data-placement="bottom"  title="Shuffle Content"><i class="fas fa-random"></i></a>
							<a ng-if="record.category_id > 0" href="<?php echo site_url("categories/edit/") ?>{{ record.category_id }}" class="text-dark mx-1" data-toggle="tooltip" data-placement="bottom"  title="Edit"><i class="fas fa-edit"></i></a>
							<a ng-if="record.category_id > 0" href="<?php echo site_url("categories/delete/") ?>{{ record.category_id }}" class="text-dark delete-record mx-1" data-toggle="tooltip" data-placement="bottom" title="Delete"><i class="fas fa-times"></i></a>
						</span>
					</h6>
					<p class="text-muted category-include mb-0">
						<span ng-if="record.include_in_random == 1">Included</span>
						<span ng-if="record.include_in_random == 0"><span class="font-weight-500">Not</span> included</span>
						 in "Random" scheduled timeslots
					</p>
					<p class="text-muted mb-0">
						<span class="badge badge-secondary font-weight-500">{{ record.content_count | number }}</span> items in content library
					</p>
				</div>
			</div>
		</div>
	</div>
</div>


<div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Delete Category</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      	<div class="mb-2 text-center text-danger fz-20 ">
      		<i class="fas fa-exclamation-triangle"></i> Warning
      	</div>
        <p>Content, RSS feeds, posts and scheduled timeslots associated with this category will also be deleted. Are you sure, you want to delete this category?</p>
      </div>
      <div class="modal-footer">
        <a href="" class="text-success font-weight-500 mr-3" data-dismiss="modal">Cancel</a>
        <a href="#" class="btn btn-danger btn-shadow">Remove</a>
      </div>
    </div>
  </div>
</div>