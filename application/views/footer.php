	<?php if($this->uri->segment(1) != 'schedule'){ ?>
	<a href="<?php echo site_url('content/add') ?>" class="add-content-float d-none d-sm-inline btn-shadow"><i class="fas fa-plus"></i></a>
	<?php } ?>
	<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/popper.min.js"></script>
	<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/jquery.validate.min.js"></script>
	<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/custom.js?v=<?php echo getenv('JS_VERSION') ?>"></script>

</body>
</html>
