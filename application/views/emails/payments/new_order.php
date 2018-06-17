Hi <?php echo $name ?>,
<br>
<br>
You are successfully subscribed to <?php echo $invoice->plan_name ?> plan for $<?php echo ($invoice->total + abs($invoice->discount)) ?>/month.
Thank you for choosing <?php echo $this->config->item('site_name') ?>
<br>
<br>

Regards <br>
Team <?php echo $this->config->item('site_name') ?>