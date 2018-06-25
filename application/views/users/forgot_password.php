<div class="row">
  <div class="col-xs-12 col-sm-10 col-md-8 col-lg-4 col-xl-4 mx-auto">
    <div class="card air-card">
      <div class="card-body">
        <h5>Forgot Password</h5>
        <hr class="mt-0">
        <?php echo $this->session->flashdata('alert'); ?>
        <?php  if(validation_errors())  echo get_alert_html(validation_errors(), ALERT_TYPE_ERROR); ?>
        <form method="post" class="my-4" id="form" action="<?php echo current_url() ?>">
          
          <div class="form-group">
            <label>Email</label>
            <input type="email" class="form-control" name="email" required autofocus>
          </div>
          
          <div class="text-center">
            <button type="submit" class="btn btn-green btn-block enter-btn">Forgot Password</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
$(function(){
  //validate signup form
  $('#form').validate({
      rules:{
        email : {
          required : true,
          email : true
        }
      },
      messages:{
        email : {
          required : 'Email is required',
          email : 'Invalid email address'
        }
      }
    });
});
</script>