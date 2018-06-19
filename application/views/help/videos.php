<div class="container my-5">
	<div class="row">
		<div class="col-md-12">
			<?php echo $this->session->flashdata('alert') ?>
		</div>
		<div class="col-12">
			<h4 class="page-title">Help</h4>
		</div>
	</div>
	<?php foreach($videos as $video){ ?>
	<div class="row">
		<div class="col-12 text-center text-muted">
			<h5><?php echo $video->title ?></h5>
		</div>
		<div class="col-12 mt-2 mb-4">
			<div class="embed-responsive embed-responsive-16by9 border">
              <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/<?php echo $video->youtube_id ?>?showinfo=0" frameborder="0"  allowfullscreen></iframe>
            </div>
		</div>	
	</div>
	<?php } ?>
	<div class="row">
		<div class="col-12 mb-5 text-muted text-center fz-20">
			<div class="p-5">
				Couldn't find what you are looking for? Contact our support team	
			</div>
		</div>
	</div>
</div>
