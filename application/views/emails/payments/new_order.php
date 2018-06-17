Hi <?php echo $name ?>,
<br>
<br>
You are successfully subscribed to <?php echo strtolower($plan->name) ?> plan for $<?php echo round($plan->price) ?>/month.
Thank you for choosing <?php echo getenv('SITE_NAME') ?>
<br>
<br>

Regards <br>
Team <?php echo getenv('SITE_NAME') ?>