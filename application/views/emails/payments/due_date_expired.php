Hi <?php echo $name ?>,
<br>
<br>
we were unable to charge your account on due date <?php echo date('d M, Y', strtotime($user->next_due_date)) ?>
<br>
After 3 days wait period, we are sorry to inform you that your account is suspended and you won't be billed anymore.
<br>
Please <a href="<?php echo site_url('payments/pay') ?>">Buy a Plan</a> to continue using <?php echo getenv('SITE_NAME') ?>
<br>
<br>

Regards <br>
Team <?php echo $this->config->item('site_name') ?>