<div class="row">
  <div class="col-xs-12 col-sm-10 col-md-8 col-lg-4 col-xl-4 mx-auto">
    <div class="card air-card">
      <div class="card-body">
        <h5>Reset Password</h5>
        <hr class="mt-0">
        <?php echo $this->session->flashdata('alert'); ?>
        <?php  if(validation_errors())  echo get_alert_html(validation_errors(), ALERT_TYPE_ERROR); ?>
        <form method="post" class="my-4" id="form" action="<?php echo current_url() ?>">
          <div class="form-group">
            <label>New Password</label>
            <input type="password" class="form-control" name="password" required autofocus>
          </div>
          <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" class="form-control" name="confirm_password" required>
          </div>
          <div class="text-center">
            <button type="submit" class="btn btn-green btn-block enter-btn">Reset Password</button>
          </div>
        </form>
        
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
$(function(){
  //validate signup form
  $('#form').validate({
      rules:{
        password:{
          required: true,
          minlength: 6
  
        },
        confirm_password: {
          required : true,
          minlength: 6,
          password_match : true
        }
      },
      messages:{
        password:{
          required: 'Password is required',
          minlength : 'Password should be 6 characters minimum'
        },
        confirm_password: {
          required : 'Password is required',
          minlength : 'Password should be 6 characters minimum',
          password_match : 'Passwords are not same'
        }
      }
    });
    jQuery.validator.addMethod("password_match", function(value, element) {
          return this.optional(element) || $('input[name="password"]').val() == $('input[name="confirm_password"]').val();
    });
});
</script>
