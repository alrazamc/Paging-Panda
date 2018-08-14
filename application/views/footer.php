	<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/popper.min.js"></script>
	<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/jquery.validate.min.js"></script>
	<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/custom.js?v=<?php echo getenv('JS_VERSION') ?>"></script>
	<?php if($this->uri->segment(1) != 'schedule'){ ?>
	<a href="<?php echo site_url('content/add') ?>" class="add-content-float d-none d-sm-inline btn-shadow"><i class="fas fa-plus"></i></a>
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js#xfbml=1&version=v2.12&autoLogAppEvents=1';
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>
	<div class="fb-customerchat"
	  attribution=setup_tool
	  page_id="393892214457345"
	  theme_color="#28a745"
	  logged_in_greeting="Hi <?php echo $this->session->userdata('first_name') ?>! How can we help you?"
	  logged_out_greeting="Hi <?php echo $this->session->userdata('first_name') ?>! How can we help you?"
	  greeting_dialog_display="hide"
	  ref="uid-<?php echo $this->session->userdata('user_id') ?>">
	</div>
	<?php } ?>
	<?php if(getenv('CI_ENV') != 'development'){ ?>
		<script async src="https://www.googletagmanager.com/gtag/js?id=UA-55986435-3"></script>
		<script>
		  window.dataLayer = window.dataLayer || [];
		  function gtag(){dataLayer.push(arguments);}
		  gtag('js', new Date());
		  gtag('config', 'UA-55986435-3');
		</script>
		<?php if($this->session->flashdata('signup')){ ?>
		<!-- Facebook Pixel Code -->
		<script>
		  !function(f,b,e,v,n,t,s)
		  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
		  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
		  n.queue=[];t=b.createElement(e);t.async=!0;
		  t.src=v;s=b.getElementsByTagName(e)[0];
		  s.parentNode.insertBefore(t,s)}(window, document,'script',
		  'https://connect.facebook.net/en_US/fbevents.js');
		  fbq('init', '237126063803110');
		  fbq('track', 'PageView');
		</script>
		<noscript><img height="1" width="1" style="display:none"
		  src="https://www.facebook.com/tr?id=237126063803110&ev=PageView&noscript=1"
		/></noscript>
		<!-- End Facebook Pixel Code -->
		<?php } ?>

	<?php } ?>
</body>
</html>
