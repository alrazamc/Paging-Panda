<script type="text/javascript">
  var angapp = angular.module('app', []);
  angapp.controller('pageController', function($scope, $http, $sce, $filter, $timeout, $rootScope){
  	$scope.confirm = '';
  	$scope.submit_preloader = false;
  	$scope.submit_form = function(){
  		$scope.submit_preloader = true;
  		$('#closeform').submit();
  	}
  });
</script>

<div class="container my-5" ng-app="app" ng-controller="pageController" id="pageController" ng-cloak>
	<?php echo $this->session->flashdata('alert') ?>
	<?php if(validation_errors()) echo get_alert_html(validation_errors(), ALERT_TYPE_ERROR); ?>
	<div class="card air-card">
		<div class="card-body">
			<div class="row">
				<div class="col-sm-12">
					<h4 class="page-title">Close Account</h4>
				</div>
			</div>
			<hr class="mt-0">
			<div class="row">
				<div class="col-xl-8 col-lg-8 col-sm-8 offset-sm-2 mb-3">
					<form action="<?php echo current_url() ?>" method="post" id="closeform">
						<p>Having some problem with <?php echo strtolower(getenv('SITE_NAME')) ?>? we can help. Please contact us</p>
						<div class="form-group mt-5">
							<div class="text-center mb-2 font-weight-500">
								Type 'CLOSE' to close your account
							</div>
							<div class="row">
								<div class="col-sm-6 offset-sm-3">
									<input type="text" class="form-control" name="cancel" ng-model="confirm" autofocus="" required="">
								</div>
							</div>
						</div>
						<div class="form-group text-center">
							<button type="button" ng-click="submit_form()" class="btn btn-success btn-shadow" ng-disabled="confirm.toLowerCase() != 'close' || submit_preloader ">Close Account <i class="fas fa-spin fa-circle-notch" ng-show="submit_preloader"></i></button>							
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
