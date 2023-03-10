<div class="container my-5">
	<div class="row">
		<div class="col-md-12">
			<?php echo $this->session->flashdata('alert') ?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-8">
			<h4 class="page-title">Pages</h4>
		</div>
		<div class="col-sm-4">
			<p class="mb-1">Page Limit <span class="float-right"> <?php echo count($pages) ?> of <?php echo $page_limit ?></span></p>
			<div class="progress slim-progress">
			  <div class="progress-bar bg-green"  style="width: <?php echo round( (count($pages) / $page_limit)*100 ) ?>%;" ></div>
			</div>
		</div>
	</div>
	<div class="row">
		<?php if(count($pages)){ ?>
		<div class="col-sm-12 text-center text-sm-right my-3">
			<a href="<?php echo $fb_login_url ?>" class="btn btn-facebook" ><img width="30" class="mr-2" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/fblogo.png" alt="FB"> Import Facebook Pages</a>
		</div>
		<?php } ?>
		<?php if($expired_tokens){ ?>
		<div class="col-md-12 text-center mb-3 text-danger">
			some pages below needs to be reconnected, Please <a href="<?php echo site_url('accounts/auth') ?>" class="btn btn-success btn-shadow" >Reconnect Pages</a>
		</div>
		<?php } ?>
		<?php if(count($pages)){ ?>
		<?php foreach($pages as $page){ ?>
		<div class="col-xl-4 col-lg-4 col-sm-6 mb-3">
			<div class="card air-card position-relative">
				<div class="card-body">
					<img src="<?php echo GRAPH_API_URL.$page->account_fb_id.'/picture?width=50&height=50&access_token='.$page->access_token ?>" width="50" height="50" class="float-left rounded-circle border mr-2">
					<p class="pt-2 mb-0">
						<?php echo $page->account_name ?>
						<?php if($page->token_expired == YES){ ?>
						<span class="text-danger">
							<br>
							Reconnect Page
						</span>
						<?php } ?>
						<?php if($page->post_perms == NO){ ?>
						<span class="text-danger"
		                  data-toggle="tooltip" data-placement="bottom" 
		                  title="You can only view analytics for this page as you do not have permissions to publish posts for this page, Please contact page admin to get the editor role for this page then remove page and Import again">
		                  <br>
		                  No post permissions
		                </span>
		                <?php } ?>
					</p>
					<a href="<?php echo site_url("accounts/remove/$page->account_id") ?>" class="text-danger position-absolute delete-record page-delete-btn" data-toggle="tooltip" data-placement="bottom" title="Remove Page"><i class="fas fa-trash"></i></a>
				</div>
			</div>
		</div>
		<?php } ?>
		<?php }else{ ?>
		<div class="col-md-12">
			<p class="text-muted p-0 p-sm-5 mt-3 text-center">
				Manage your Facebook pages with <?php echo getenv('SITE_NAME') ?>
				<br>
				<br>
				<a href="<?php echo $fb_login_url ?>" class="btn btn-facebook" ><img width="30" class="mr-2" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/fblogo.png" alt="FB"> Import Facebook Pages</a>
			</p>
		</div>
		<?php } ?>
	</div>
</div>


<div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Remove Page</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      	<div class="mb-2 text-center text-danger fz-20 ">
      		<i class="fas fa-exclamation-triangle"></i> Warning
      	</div>
        <p>All content, Schedule and RSS feeds associations of this page will be deleted and if you are intending to use this page in future, you will need to re-link this page with content, schedule and RSS feeds. 
        <br>
        If you do not want to publish on this page, Just remove it from schedule slots
        <br>
        Are you sure you want to delete this page from the application?</p>
      </div>
      <div class="modal-footer">
        <a href=""  class="text-success font-weight-500" data-dismiss="modal">Cancel</a>
        <a href="#" class="btn btn-danger btn-shadow">Delete From Application</a>
      </div>
    </div>
  </div>
</div>

<?php if($this->session->flashdata('signup')){ ?>
<div class="modal fade" id="welcome-modal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-body">
      	<div class="row">
      		<div class="col-12">
      			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      		</div>
      		<div class="col-12 text-center">
      			<h5 class="modal-title d-block" >Welcome to <?php echo getenv('SITE_NAME') ?></h5>
      			<span class="text-muted">Let's get it started, watch a quick demo video to have an idea of features.</span>
      		</div>
      		<div class="col-12 mt-2 mt-2">
				<div class="embed-responsive embed-responsive-16by9 border">
	              <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/<?php echo $quick_setup_video ?>?start=47&showinfo=0" frameborder="0"  allowfullscreen></iframe>
	            </div>
			</div>
      		<div class="col-12 text-center font-weight-500 mt-4">
      			<a href="" class="text-success font-weight-500" data-dismiss="modal">Close</a>
      		</div>
      	</div>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
	$(function(){
		$("#welcome-modal").modal('show');
	});
</script>

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
  fbq('init', '249330235714421');
  fbq('track', 'PageView');
  fbq('track', 'CompleteRegistration');
</script>
<noscript><img height="1" width="1" style="display:none"
  src="https://www.facebook.com/tr?id=249330235714421&ev=PageView&noscript=1"
/></noscript>
<!-- End Facebook Pixel Code -->

<?php } ?>