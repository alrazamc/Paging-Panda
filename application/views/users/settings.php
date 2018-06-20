<div class="container my-5">
	<?php echo $this->session->flashdata('alert'); ?>
	<div class="card air-card">
		<div class="card-body">
			<div class="row">
				<div class="col-12">
					<h4>Settings</h4>
				</div>
			</div>
			<hr class="mt-0">
			<div class="row">
				<div class="col-lg-3 text-center text-lg-left">
					<h6 class="text-muted mb-0">Profile</h6>
				</div>
				<div class="col-lg-6">
					<ul class="list-group list-group-flush">
					  	<li class="list-group-item">
					  		<div class="row">
					  			<div class="col-4"><strong>Name</strong></div>
					  			<div class="col-8"><?php echo $user->first_name.' '.$user->last_name ?></div>
					  		</div>
						</li>
						<li class="list-group-item">
					  		<div class="row">
					  			<div class="col-4"><strong>Email</strong></div>
					  			<div class="col-8"><?php echo $user->email ?></div>
					  		</div>
						</li>
						<li class="list-group-item">
					  		<div class="row">
					  			<div class="col-4"><strong>Time Zone</strong></div>
					  			<div class="col-8"><?php echo $this->config->item($user->time_zone, 'tzones');  ?></div>
					  		</div>
						</li>
						<li class="list-group-item">
					  		<div class="row">
					  			<div class="col-4"><strong>Address</strong></div>
					  			<div class="col-8"><?php echo $user->address ?></div>
					  		</div>
						</li>
						<li class="list-group-item">
					  		<div class="row">
					  			<div class="col-4"><strong>Phone</strong></div>
					  			<div class="col-8"><?php echo $user->phone ?></div>
					  		</div>
						</li>
					</ul>

					<div class="text-center mt-3">
						<a href="<?php echo site_url('users/profile') ?>" class="btn btn-success btn-shadow">Edit Profile</a>
					</div>
				</div>
			</div>


			<hr>
			<div class="row">
				<div class="col-lg-3 text-center text-lg-left">
					<h6 class="text-muted mb-0">Subscription</h6>
				</div>
				<div class="col-lg-6">
					<?php if($user->status == USER_STATUS_ACTIVE){ ?>
					<ul class="list-group list-group-flush">
					  	<li class="list-group-item">
					  		<div class="row">
					  			<div class="col-4"><strong>Plan</strong></div>
					  			<div class="col-8">
					  				<?php echo $plan->name ?>
					  				<?php if($user->on_trial){ ?> 
						  				<?php if(isset($expired)){ ?>
						  				<span class="text-muted"> (Trial period expired on <?php echo $expiry_date ?> )<span>
						  				<?php }else{ ?>
					  					<span class="text-muted"> (On trial period, expiring on <?php echo $expiry_date ?>)<span>
					  					<?php } ?>
					  				<?php } ?>
					  			</div>
					  		</div>
						</li>
						<li class="list-group-item">
					  		<div class="row">
					  			<div class="col-4"><strong>Price</strong></div>
					  			<div class="col-8">$<?php echo $plan->price ?> per month</div>
					  		</div>
						</li>
					</ul>
					<?php } ?>

					<div class="text-center mt-3">
						<a href="<?php echo site_url('payments/pay') ?>" class="btn btn-success btn-shadow">
						<?php if($user->on_trial || $user->status == USER_STATUS_SUSPENDED || $user->status == USER_STATUS_CANCELLED){ ?>
							Buy a plan
						<?php }else{ ?>
							Change Plan
						<?php } ?>
						</a>
					</div>
				</div>
			</div>



			<?php if($user->on_trial == NO){ ?>
			<hr>
			<div class="row">
				<div class="col-lg-3 text-center text-lg-left">
					<h6 class="text-muted mb-0">Next Due Date</h6>
				</div>
				<div class="col-lg-6">
					<ul class="list-group list-group-flush">
						<?php if($user->status == USER_STATUS_ACTIVE && $user->next_due_date){ ?>
					  	<li class="list-group-item">
					  		<div class="row">
					  			<div class="col-12">
					  					<b>$<?php echo $plan->price ?></b> will be charged  on <b><?php echo date('d M, Y', strtotime($user->next_due_date));?></b>
					  			</div>
					  		</div>
						</li>
						<?php } ?>
						<?php if($user->on_trial == NO){ ?>
						<li class="list-group-item">
					  		<div class="row">
					  			<div class="col-6">
					  				<a href="<?php echo site_url('payments/invoices') ?>" class="text-success">View Invoices</a>
					  			</div>
					  			<?php if($user->status != USER_STATUS_CANCELLED){ ?>
					  			<div class="col-6 text-right">
					  				<a href="<?php echo site_url('payments/close') ?>" class="text-success">Close Account</a>					  				
					  			</div>
					  			<?php } ?>
					  		</div>
						</li>
						<?php } ?>
					</ul>

				</div>
			</div>
			<?php } ?>


		</div>
	</div>
</div>