<div class="row">
  <div class="col-xs-12 col-sm-10 col-md-8 col-lg-4 col-xl-4 mx-auto">
    <div class="card air-card">
      <div class="card-body">
        <h5>Register Account</h5>
        <hr class="mt-0">
        <?php echo $this->session->flashdata('alert'); ?>
        <?php  if(validation_errors())  echo get_alert_html(validation_errors(), ALERT_TYPE_ERROR); ?>
        <form method="post" class="my-4" id="form" action="<?php echo current_url() ?>">
          <input type="hidden" name="tzone_offset" value="0">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>First Name</label>
                <input type="text" class="form-control"  name="first_name"  required autofocus>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Last Name</label>
                <input type="text" class="form-control"  name="last_name" required>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" class="form-control" name="password" required>
          </div>
          
          <div class="text-center">
            <button type="submit" class="btn btn-green btn-block enter-btn">REGISTER</button>
          </div>
        </form>
        <p>
          By clicking this button, you agree to our <a target="_blank" href="<?php echo site_url('privacy') ?>" class="text-success">Privacy Policy</a> and <a target="_blank" href="<?php echo site_url('terms') ?>" class="text-success">Terms &amp; Conditions</a>
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
        first_name: {
          required : true
        },
        last_name: {
          required : true
        },
        email : {
          required : true,
          email : true,
          remote : '<?php echo site_url('users/isemailexist') ?>'
        },
        password:{
          required: true,
          minlength: 6
        }
      },
      messages:{
        first_name: {
          required : 'required'
        },
        last_name: {
          required : 'required'
        },
        email : {
          required : 'Email is required',
          email : 'Invalid email address',
          remote : 'This email is already registered'
        },
        password:{
          required: 'Password is required',
          minlength : 'Password should be 6 characters minimum'
        }
      }
    });

  var mins = new Date().getTimezoneOffset();
  mins = mins == 0 ? 0 : -mins;
  $('input[name="tzone_offset"]').val(mins);
});
</script>
