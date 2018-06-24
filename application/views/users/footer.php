  </div>
  <script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/popper.min.js"></script>
  <script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/bootstrap.min.js"></script>
  <script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/jquery.validate.min.js"></script>
  	<?php if(getenv('CI_ENV') != 'development'){ ?>
		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=UA-55986435-3"></script>
		<script>
		  window.dataLayer = window.dataLayer || [];
		  function gtag(){dataLayer.push(arguments);}
		  gtag('js', new Date());

		  gtag('config', 'UA-55986435-3');
		</script>
	<?php } ?>
</body>
</html>
