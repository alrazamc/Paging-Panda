Hi <?php echo $name ?>,
<br>
<br>
We were unable to charge your account for monthly payment of <?php echo $invoice->plan_name ?> plan($<?php echo $invoice->total ?>)
<br>
Please update your details in <a href="<?php echo site_url('users/settings') ?>">Settings</a>
<br>
<br>

Regards <br>
Team <?php echo $this->config->item('site_name') ?>