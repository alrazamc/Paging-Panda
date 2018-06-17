<script src="https://www.2checkout.com/static/checkout/javascript/direct.min.js"></script>
<script type="text/javascript">
  var api_base_url = '<?php echo site_url('api') ?>/';
  var angapp = angular.module('app', []);
  angapp.controller('pageController', function($scope, $http, $sce, $filter, $timeout, $rootScope){
  	$scope.plans = <?php echo json_encode($plans) ?>;
  	$scope.selected_plan = {};
  	$scope.user_plan_id = <?php echo $user->plan_id ?>;
  	
  	$scope.on_trial = <?php echo $user->on_trial ?>;
  	$scope.user_status = <?php echo $user->status ?>;
  	$scope.active_status = <?php echo USER_STATUS_ACTIVE ?>;

  	$scope.trial_days_remaining = <?php echo $trial_days_remaining ?>;
  	$scope.total_pages = <?php echo $total_pages ?>;
  	$scope.due_date_valid = <?php echo $due_date_valid ? 'true' : 'false' ?>;
  	$scope.discount = 0.00;
  	$scope.total = 0.00;
  	$scope.inline_form = false;
  	for(var i=0; i<$scope.plans.length; i++)
  	{
  		if($scope.user_plan_id == $scope.plans[i].plan_id)
  			$scope.selected_plan = $scope.plans[i];
  		$scope.plans[i].price = parseInt($scope.plans[i].price);
  	}
  	
  	$scope.select_plan = function(plan){
  		if($scope.inline_form) return;
  		$scope.selected_plan = plan;
  		$scope.cal_discount();
  	}

  	$scope.cal_discount = function()
  	{
  		if(!$scope.selected_plan.price) return;
  		var dp = ($scope.selected_plan.price/30)*$scope.trial_days_remaining;
  		$scope.discount = Math.ceil(dp);
  		$scope.total = $scope.selected_plan.price - $scope.discount;
  	}
  	$scope.cal_discount();
  	$scope.submit_form = function()
  	{
  		$scope.inline_form = true;
  		jQuery('#pageController form').submit();
  	}
  });
  var myCallback = function(data) {
  		if(data.event_type == 'checkout_closed')
  		{
  			angular.element(document.getElementById('pageController')).scope().inline_form = false;
  			angular.element(document.getElementById('pageController')).scope().$apply();
  		}
    };
     (function() {
         inline_2Checkout.subscribe('checkout_loaded', myCallback);
         inline_2Checkout.subscribe('checkout_closed', myCallback);
     }());
</script>

<div class="container my-5"  ng-app="app" ng-controller="pageController" id="pageController" ng-cloak>
	<div class="row pay">
		<div class="col-12 mb-3">
			<h5>Choose Plan</h5>
		</div>
		
		<div class="col-sm-4 mb-3" ng-repeat="plan in plans">
			<div class="card btn-shadow plan {{ plan.plan_id == selected_plan.plan_id ? 'selected' : '' }}" ng-click="select_plan(plan)">
				<div class="card-body p-3 p-sm-1 p-lg-3 text-center">
					<h4>{{ plan.name }}</h4>
					<h2 class="text-success">${{ plan.price }}<small class="text-muted fz-16">/month</small></h2>

					<span class="text-muted">Manage up to {{ plan.page_limit }} pages</span>
				</div>
			</div>
		</div>

		<div class="col-12" ng-if="selected_plan.page_limit - total_pages < 0">
			<div class="alert alert-danger">
				<i class="fas fa-exclamation-triangle"></i> Please remove {{ total_pages - selected_plan.page_limit }} <a href="<?php echo site_url('accounts') ?>" class="text-success">pages</a> from your account to buy this plan
			</div>
		</div>

		<div class="col-12" ng-if="on_trial == 0 && user_status == active_status && user_plan_id == selected_plan.plan_id && due_date_valid">
			<div class="alert alert-info">
				<i class="fas fa-info-circle"></i> You are already subscribed to this plan
			</div>
		</div>

	</div>

	<div class="row">
		
	</div>

	<div class="card air-card" ng-show="selected_plan.page_limit - total_pages >= 0 && (on_trial == 1 || user_status != active_status || user_plan_id != selected_plan.plan_id || !due_date_valid )">
		<div class="card-body">
			<div class="row mb-2">
				<div class="col-8 col-sm-9">
					<div class="fz-18 pt-2 pt-sm-0">
						<?php echo getenv('SITE_NAME') ?> <span class="text-success">{{ selected_plan.name }}</span> Plan 
						<span class="fz-16 text-muted">({{ selected_plan.page_limit }} pages)</span>
					</div>
				</div>
				<div class="col-4 col-sm-3">
					<h3 class="text-success">${{ selected_plan.price }}</h3>
				</div>

				<div class="col-8 col-sm-9" ng-show="trial_days_remaining > 0">
					<div class="fz-18 pt-2 pt-sm-0">
						Discount for {{ trial_days_remaining }} remaining trial days <span class="fz-14 text-muted">(for first payment only)</span>
					</div>
				</div>
				<div class="col-4 col-sm-3" ng-show="trial_days_remaining > 0">
					<h3 class="text-success">-${{ discount }}</h3>
				</div>

				<div class="col-12">
					<div class="border-bottom"></div>
				</div>

				<div class="col-8 col-sm-9 text-right">
					<div class="fz-18 pt-2">
						Current Total (USD):
					</div>
				</div>
				<div class="col-4 col-sm-3">
					<h3 class="text-success">${{ total }}</h3>
				</div>

			</div>

			<form action='https://<?php echo getenv('2CO_MODE') ?>.2checkout.com/checkout/purchase' method='post' ng-show="selected_plan.plan_id">
				<input type='hidden' name='sid' value="<?php echo getenv('2CO_SELLER_ID') ?>" />
				<input type='hidden' name='mode' value='2CO' />
				<input type='hidden' name='li_0_type' value='product' />
				<input type='hidden' name='li_0_name' value="<?php echo getenv('SITE_NAME') ?> {{ selected_plan.name }} Plan" />
				<input type='hidden' name='li_0_price' value="{{ selected_plan.price }}" />
				<input type='hidden' name='li_0_tangible' value="N" />
				<input type='hidden' name='li_0_recurrence' value="1 Month" />
				<input type='hidden' name='li_0_duration' value="Forever" />
				<input type='hidden' name='li_0_startup_fee' value="-{{ discount }}" ng-if="discount > 0"/>
				<input type='hidden' name='li_0_product_id' value="{{ selected_plan.plan_id }}" />

				<input type='hidden' name='email' value="<?php echo $user->email ?>" />
				<input type="hidden" name="plan_id" value="{{ selected_plan.plan_id }}" />
				<div class="row">
					<div class="col-lg-3">
						<span class="fz-18 text-muted">Billing Info</span>
					</div>
					<div class="col-12 col-lg-6">
						<div class="form-group">
							<input type="text" class="form-control" name="card_holder_name" value="<?php echo $user->first_name.' '.$user->last_name ?>" placeholder="Card holder name..." required>
						</div>
						<div class="form-group">
							<input type="text" class="form-control" name="street_address" value="<?php echo $user->address ?>" placeholder="Street address..." required>
						</div>
						<div class="form-group">
							<input type="text" class="form-control" name="street_address2" placeholder="Street address 2...">
						</div>
						<div class="row">
							<div class="col-4">
								<div class="form-group">
									<input type="text" class="form-control" name="city" value="<?php echo $user->city ?>" placeholder="City..." required>
								</div>
							</div>
							<div class="col-4">
								<div class="form-group">
									<input type="text" class="form-control" name="state" value="<?php echo $user->state ?>" placeholder="State..." required>
								</div>
							</div>
							<div class="col-4">
								<div class="form-group">
									<input type="text" class="form-control" name="zip" value="<?php echo $user->zip_code ?>" placeholder="Zip" required>
								</div>
							</div>
						</div>
						<div class="form-group">
				            <select class="form-control" name="country" required>
				            	<optgroup>
				            		<option value="">Select Country</option>
				            	</optgroup>
				            	<optgroup>
					            	<option value="USA" <?php echo $user->country == 226 ? 'selected' : '' ?>>United States</option>
					            	<option value="GBR" <?php echo $user->country == 225 ? 'selected' : '' ?>>United Kingdom</option>
					            	<option value="CAN" <?php echo $user->country == 38 ? 'selected' : '' ?>>Canada</option>
					            	<option value="AUS" <?php echo $user->country == 13 ? 'selected' : '' ?>>Australia</option>
				            	</optgroup>
				            	<optgroup>
					            	<?php foreach($countries as $country){ ?>
					            	<option value="<?php echo $country->iso3 ?>" <?php echo $country->country_id == $user->country ? 'selected' : '' ?>><?php echo $country->nicename ?></option>
					            	<?php } ?>
				            	</optgroup>
				            </select>
				        </div>
				        <div class="form-group">
							<input type="text" class="form-control" name="phone" value="<?php echo $user->phone ?>" placeholder="Phone number..." required>
						</div>
						<div class="form-group text-center">
							<button type="button" class="btn btn-success btn-shadow" ng-click="submit_form()" ng-disabled="inline_form">Checkout <i class="fas fa-spin fa-circle-notch" ng-show="inline_form"></i></button>
						</div>
						<div class="text-center text-muted" ng-if="on_trial == 0 && user_status == active_status">
							<i class="fas fa-info-circle"></i> Remaining amount(if any) from previous plan will be refunded upon checkout if you change plan
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
