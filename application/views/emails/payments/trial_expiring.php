Hi <?php echo $name ?>,
<br>
<br>
Trial period is about to end in 24 hours. Please buy a plan to continue using <?php echo getenv('SITE_NAME') ?>
<br>
<a href="<?php echo site_url('payments/pay') ?>">Buy Plan</a>
<br>
<br>

Regards <br>
Team <?php echo $this->config->item('site_name') ?>