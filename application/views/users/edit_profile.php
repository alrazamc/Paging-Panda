<div class="container my-5">
	<div class="card air-card">
		<div class="card-body">
			<div class="row">
				<div class="col-6">
					<h4>Edit Profile</h4>
				</div>
				<div class="col-6 text-right">
					<a href="<?php echo site_url('users/settings') ?>" class="btn btn-green"><i class="fas fa-arrow-left"></i> Settings </a>
				</div>
			</div>
			<hr>
			<div class="row">
				<div class="col-lg-6 offset-lg-3">
					<?php echo $this->session->flashdata('alert'); ?>
        			<?php  if(validation_errors())  echo get_alert_html(validation_errors(), ALERT_TYPE_ERROR); ?>
					<form action="<?php echo current_url() ?>" id="form" method="post" autocomplete="off">
						<div class="row">
			            <div class="col-md-6">
			              <div class="form-group">
			                <label class="required">First Name</label>
			                <input type="text" class="form-control"  name="first_name" value="<?php echo $user->first_name ?>" required autofocus>
			              </div>
			            </div>
			            <div class="col-md-6">
			              <div class="form-group">
			                <label class="required">Last Name</label>
			                <input type="text" class="form-control"  name="last_name" value="<?php echo $user->last_name ?>" required>
			              </div>
			            </div>
			          </div>
			          <div class="form-group">
			            <label class="required">Email</label>
			            <input type="email" class="form-control" name="email" value="<?php echo $user->email ?>" required>
			          </div>
			          <div class="form-group">
			            <label class="required">Time Zone</label>
			            <select class="form-control" name="time_zone" required>
			            	<option value="">Select Time Zone</option>
			            	<?php foreach($tzones as $key => $value){ ?>
			            	<option value="<?php echo $key ?>" <?php echo $key == $user->time_zone ? 'selected' : '' ?>><?php echo $value ?></option>
			            	<?php } ?>
			            </select>
			          </div>

			          <div class="form-group">
			            <label>Phone Number</label>
			            <input type="text" class="form-control" name="phone" value="<?php echo $user->phone ?>">
			          </div>
			          <div class="form-group">
			            <label>Address</label>
			            <input type="text" class="form-control" name="address" value="<?php echo $user->address ?>">
			          </div>
			          <div class="form-group">
			            <label>City</label>
			            <input type="text" class="form-control" name="city" value="<?php echo $user->city ?>">
			          </div>
			          <div class="form-group">
			            <label>State/Province/Region</label>
			            <input type="text" class="form-control" name="state" value="<?php echo $user->state ?>">
			          </div>
			          <div class="form-group">
			            <label>ZIP/Postal code</label>
			            <input type="text" class="form-control" name="zip_code" value="<?php echo $user->zip_code ?>">
			          </div>

			          <div class="form-group">
			            <label >Country</label>
			            <select class="form-control" name="country">
			            	<optgroup>
			            		<option value="">Select Country</option>
			            	</optgroup>
			            	<optgroup>
				            	<option value="226" <?php echo $user->country == 226 ? 'selected' : '' ?>>United States</option>
				            	<option value="225" <?php echo $user->country == 225 ? 'selected' : '' ?>>United Kingdom</option>
				            	<option value="38" <?php echo $user->country == 38 ? 'selected' : '' ?>>Canada</option>
				            	<option value="13" <?php echo $user->country == 13 ? 'selected' : '' ?>>Australia</option>
			            	</optgroup>
			            	<optgroup>
				            	<?php foreach($countries as $country){ ?>
				            	<option value="<?php echo $country->country_id ?>" <?php echo $country->country_id == $user->country ? 'selected' : '' ?>><?php echo $country->nicename ?></option>
				            	<?php } ?>
			            	</optgroup>
			            </select>
			          </div>

			          <div class="form-group">
			            <label>New Password</label>
			            <input type="password" class="form-control" name="password" autocomplete="new-password" placeholder="Leave this blank if you don't want to change your password">
			          </div>
			          <hr>
			          <div class="form-group">
			            <label class="required">Current Password</label>
			            <input type="password" class="form-control" name="current_password" autocomplete="new-password" placeholder="Enter current password to confirm changes" required>
			          </div>


			          <div class="form-group">
			          	<button class="btn btn-success btn-shadow" type="submit">Update Profile</button>
			          </div>
					</form>
				</div>
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
          remote : '<?php echo site_url('users/isemailexist/'.$this->session->userdata('user_id')) ?>'
        },
        time_zone: {
          required : true
        },
        password:{
          minlength: 6
        },
        current_password:{
          required: true
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
        time_zone: {
          required : 'Please select a time zone'
        },
        password:{
          minlength : 'Password should be 6 characters minimum'
        },
        current_password:{
          required : 'Current password is required to confirm changes'
        }
      }
    });
});
</script>