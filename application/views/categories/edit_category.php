<div class="container my-5">
	<div class="card air-card">
		<div class="card-body">
			<div class="row">
				<div class="col-sm-8">
					<h4 class="page-title">Edit Category</h4>
				</div>
				<div class="col-sm-4 text-center text-sm-right">
					<a href="<?php echo site_url('categories') ?>" class="btn btn-green btn-shadow" ><i class="fas fa-arrow-left"></i> Categories</a>
				</div>
			</div>
			<hr class="mt-1">
			<div class="row">
				<div class="col-md-12">
					<?php echo $this->session->flashdata('alert') ?>
					<?php if(validation_errors()) echo get_alert_html(validation_errors(), ALERT_TYPE_ERROR); ?>
				</div>
				<div class="col-xl-6 col-lg-6 col-sm-6 offset-sm-3 mb-3">
					<form action="<?php echo current_url() ?>" method="post">
						<div class="form-group">
							<label>Category Name</label>
							<input type="text" class="form-control" name="category" value="<?php echo $category->category_name ?>" autofocus="" required="">
						</div>
						<div class="form-group">
							<div class="form-check">
								<input type="checkbox" class="form-check-input" id="include" name="include" value="1" <?php if($category->include_in_random) echo 'checked' ?>>
								<label class="form-check-label" for="include">Include content of this category in random post selection </label>
							</div>
						</div>
						<button type="submit" class="btn btn-success btn-shadow">Update Category</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
