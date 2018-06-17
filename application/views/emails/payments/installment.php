Hi <?php echo $name ?>,
<br>
<br>
Your monthly payment for <?php echo $invoice->plan_name ?> plan($<?php echo $invoice->total ?>) received successfully.
Thank you for choosing <?php echo $this->config->item('site_name') ?>
<br>
<br>

Regards <br>
Team <?php echo $this->config->item('site_name') ?>