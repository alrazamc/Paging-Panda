Hi <?php echo $name ?>,
<br>
<br>
we received a request to reset your password
<br>
Please click the linke below to reset your password
<br>
<a href="<?php echo site_url("users/reset_password/$hash") ?>">Reset Password</a>
<br>
<br>
Regards <br>
Team <?php echo $this->config->item('site_name') ?>