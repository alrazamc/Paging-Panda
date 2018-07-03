<div class="container my-5">
	<?php echo $this->session->flashdata('alert') ?>
	<div class="card air-card">
		<div class="card-body">
			<div class="row">
				<div class="col-sm-12">
					<h4 class="page-title">Account Closed</h4>
				</div>
			</div>
			<hr class="mt-0">
			<div class="row">
				<div class="col-12 fz-18">
					<p class="mb-0">Account closed successfully. It will not be charged again</p>
					<p class="mb-0">If you ever want to come back, just login to your account and subscribe to a plan </p>
					<p>Thanks for using <?php echo strtolower(getenv('SITE_NAME')) ?> :)</p>
				</div>
			</div>
		</div>
	</div>
</div>
