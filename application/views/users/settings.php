<div class="container my-5">
	<div class="card air-card">
		<div class="card-body">
			<div class="row">
				<div class="col-6">
					<h4>Settings</h4>
				</div>
				<div class="col-6 text-right">
					<a href="<?php echo site_url('users/profile') ?>" class="btn btn-green">Edit Profile</a>
				</div>
			</div>
			<hr>
			<div class="row">
				<div class="col-lg-6 offset-lg-3">
					<?php echo $this->session->flashdata('alert'); ?>
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
				</div>
			</div>
		</div>
	</div>
</div>