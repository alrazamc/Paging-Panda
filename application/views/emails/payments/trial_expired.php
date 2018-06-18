Hi <?php echo $name ?>,
<br>
<br>
Trial period has expired. Please buy a plan to use <?php echo getenv('SITE_NAME') ?>
<br>
<a href="<?php echo site_url('payments/pay') ?>">Buy Plan</a>
<br>
<br>

Regards <br>
Team <?php echo $this->config->item('site_name') ?>