<script type="text/javascript">
  var angapp = angular.module('app', []);
  angapp.controller('pageController', function($scope, $http, $sce, $filter, $timeout, $rootScope){
  	$scope.processing = true;
  	$scope.params = <?php echo json_encode($_GET) ?>;
  	$scope.settings_url = "<?php echo site_url('users/settings') ?>";
  	$http.get('<?php echo site_url('payments/process') ?>?'+$.param($scope.params)).then(function(){
  		window.location.href = $scope.settings_url;
  	}, function(){
  		$scope.processing = false;
  	});
  });
</script>
<div class="container my-5" ng-app="app" ng-controller="pageController" id="pageController" ng-cloak>
	<?php echo $this->session->flashdata('alert') ?>
	<div class="card air-card">
		<div class="card-body p-5">
			<div class="row" ng-show="processing">
				<div class="col-12 text-center text-success">
					<i class="fas fa-spinner fa-spin fa-2x"></i>
				</div>
				<div class="col-12 fz-18 text-center text-muted">
					<p class="mb-0">Processing... Please do not close this page</p>
				</div>
			</div>
			<div class="row" ng-show="!processing">
				<div class="col-12 fz-18 text-center text-danger">
					<p class="mb-0">Something went wrong, Please contact our customer support</p>
				</div>
			</div>
		</div>
	</div>
</div>
