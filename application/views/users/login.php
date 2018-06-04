<div class="row">
  <div class="col-xs-12 col-sm-10 col-md-8 col-lg-4 col-xl-4 mx-auto">
    <div class="card">
      <div class="card-body">
        <h5>Welcome Back</h5>
        <hr>
        <?php echo $this->session->flashdata('alert'); ?>
        <?php  if(validation_errors())  echo get_alert_html(validation_errors(), ALERT_TYPE_ERROR); ?>
        <form method="post" class="my-4" id="form" action="<?php echo current_url() ?>">
          <div class="form-group">
            <label>Email</label>
            <input type="email" class="form-control" name="email" required autofocus>
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" class="form-control" name="password" required>
          </div>
          <div class="form-group">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
              <label class="form-check-label" for="remember">Remember me</label>
            </div>
          </div>
          <div class="text-center">
            <button type="submit" class="btn btn-green btn-block enter-btn">Login</button>
          </div>
        </form>
        <p class="text-center">
          <a href="<?php echo site_url('users/forgot_password') ?>">Forgot Password</a>
          <br>
          New user ? <a href="<?php echo site_url('users/signup') ?>">Register</a>
        </p>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
$(function(){
  //validate signup form
  $('#form').validate({
      rules:{
        email : {
          required : true,
          email : true
        },
        password:{
          required: true
        }
      },
      messages:{
        email : {
          required : 'Email is required',
          email : 'Invalid email address'
        },
        password:{
          required: 'Password is required'
        }
      }
    });
});
</script>
