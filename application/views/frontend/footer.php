<!-- Footer -->
<footer class="footer">
  <div class="container">
    <div class="row gap-y align-items-center">

      <div class="col-md-3 text-center text-md-left">
        <a href="<?php echo site_url() ?>"><img src="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/logo.png" alt="logo" width="160"></a>
      </div>

      <div class="col-md-6">
        <div class="nav nav-bold nav-uppercase nav-center">
          <a class="nav-link" href="<?php echo site_url("privacy") ?>">Policy</a>
          <a class="nav-link" href="<?php echo site_url("terms") ?>">Terms</a>
          <a class="nav-link" href="<?php echo site_url("contact") ?>">Contact</a>
        </div>
      </div>

      <div class="col-md-3 text-center text-md-right">
        <div class="social social-sm social-hover-bg-brand">
          <a class="social-facebook" href="<?php echo getenv('FB_PAGE') ?>" target="_blank"><i class="fa fa-facebook"></i></a>
          <a class="social-twitter" href="<?php echo getenv('TWITTER_HANDLE') ?>" target="_blank"><i class="fa fa-twitter"></i></a>
        </div>
      </div>

    </div>
  </div>
</footer>
<!-- /.footer -->
<!-- Scroll top -->
<button class="btn btn-circle btn-success scroll-top"><i class="fa fa-angle-up"></i></button>
<!-- Scripts -->
<script src="<?php echo getenv('ASSET_BASE_URL') ?>assets/frontend/js/page.min.js"></script>
<script src="<?php echo getenv('ASSET_BASE_URL') ?>assets/frontend/js/script.js"></script>
<script type="text/javascript">
  jQuery(function($){
    $('#subscribe-form').on('submit', function(){
      var email = $('#subscribe-form input').val();
      if(email == '') return;
      $(this).parent('div').addClass('d-none');
      $(this).parent('div').siblings('.video-wrapper').removeClass('d-none');
      $.ajax({
        url: '<?php echo site_url('api/users/subscribe') ?>',
        method: 'post',
        data : { email: email },
        success: function(){}
      });
      return false;
    });
  })
</script>
<?php if(getenv('CI_ENV') != 'development'){ ?>
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