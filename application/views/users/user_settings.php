<div class="content-wrapper">
  <div class="card-deck">
    <div class="card col-lg-12 px-0 mb-4">
      <div class="card-body">
        <h5 class="card-title">Profile Settings</h5>
        <?php echo $this->session->flashdata('alert') ?>
        <?php if(validation_errors()) echo get_alert_html(validation_errors(), ALERT_TYPE_ERROR); ?>
        <div class="row">
          <div class="col-md-4">
            <form id="settings_form" action="<?php echo current_url() ?>" method="post" >
              <div class="form-group">
                  <input type="text" name="username" class="form-control" id="username"  value="<?php echo $user->username ?>" placeholder="username..." >
              </div>
              <div class="form-group">
                  <input type="email" name="email" class="form-control" id="email" value="<?php echo $user->email ?>" placeholder="Email address..." >
              </div>
              <div class="form-group">
                <input type="password" name="current_password" class="form-control" id="current_password" placeholder="Current password" >
              </div>
              <div class="form-group">
                <input type="password" name="password" class="form-control" id="password" placeholder="New password" >
              </div>
              <div class="form-group">
                <input type="password" name="confirm_password" class="form-control" id="confirm_password" placeholder="Confirm new password" >
              </div>
              <button type="submit" class="btn btn-primary">Submit</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jquery-validation@1.17.0/dist/jquery.validate.js"></script>
<script type="text/javascript">
$(function(){
  //validate signup form
  $('#settings_form').validate({
      rules:{
        username: {
          required : true,
          minlength : 4,
          maxlength : 50, 
          no_space  : true,
          remote : '<?php echo site_url('admin/users/isusernameexist/'.$user->admin_id) ?>'
        },
        email : {
          required : true,
          email : true,
          remote : '<?php echo site_url('admin/users/isemailexist/'.$user->admin_id) ?>'
        },
        password:{
          minlength: 6
  
        },
        confirm_password: {
          minlength: 6,
          password_match : true
        }
      },
      messages:{
        username: {
          required : 'Username is required',
          minlength : 'Username should be 4 characters minimum',
          maxlength : 'Username should be 50 characters maximum',
          no_space :  'Username should not contain space',
          remote: 'This username already exists'
        },
        email : {
          required : 'Email address is required',
          email : 'Invalid email address',
          remote : 'This email address already exists'
        },
        password:{
          minlength : 'Password should be 6 characters minimum'
        },
        confirm_password: {
          minlength : 'Password should be 6 characters minimum',
          password_match : 'Passwords are not same'
        }
      }
    });
    jQuery.validator.addMethod("password_match", function(value, element) {
        return this.optional(element) || $('#password').val() == $('#confirm_password').val();
    });
    jQuery.validator.addMethod("no_space", function(value, element) { 
      return value.indexOf(" ") < 0 && value != ""; 
    });
});
</script>