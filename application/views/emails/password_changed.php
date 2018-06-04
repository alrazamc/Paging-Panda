Hi <?php echo $name ?>,
<br>
<br>
Your account password is changed successfully. Please <a href="<?php echo site_url('users/login') ?>">Click here</a> to login with new password
<br>
If you didn't asked this request. Please contact our support team immediatly 
<br>
<br>
Regards <br>
Team <?php echo $this->config->item('site_name') ?>