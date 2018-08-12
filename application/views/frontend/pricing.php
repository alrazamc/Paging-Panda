<!-- Main Content -->
<main class="main-content">
   <!--
      |‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒
      | Pricing
      |‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒
      !-->
   <section id="pricing" class="section">
      <div class="container">
         <header class="section-header">
            <h2>Flexible Pricing</h2>
            <hr>
            <p class="lead">Choose the right plan, register your account and start using <?php echo getenv('SITE_NAME') ?> right away</p>
         </header>
         <div class="row gap-y text-center">
            <div class="col-md-4">
               <div class="pricing-1">
                  <p class="plan-name lead-3"><?php echo $plans[0]->name ?></p>
                  <br>
                  <h2 class="price">
                     <span class="price-unit">$</span>
                     <span data-bind-radio="pricing"><?php echo round($plans[0]->price) ?></span>
                  </h2>
                  <p class="text-muted">Per Month</p>
                  <div class="text-muted">
                     <p class="mb-0 lead-1">Manage up to <span class="text-success display-4"><?php echo $plans[0]->page_limit ?></span> Facebook pages</p>
                     <p class="mb-0 lead-1">All features included</p>
                  </div>
                  <p class="text-center pt-3 mb-1"><a class="btn btn-success" href="<?php echo site_url("users/signup/".$plans[0]->plan_id) ?>">Start <?php echo $plans[0]->trial_period ?> days free trial </a></p>
                  <p class="text-center mb-0 text-muted">No credit card required </p>
               </div>
            </div>
            <div class="col-md-4">
               <div class="pricing-1 popular">
                  <p class="plan-name lead-3"><?php echo $plans[1]->name ?></p>
                  <br>
                  <h2 class="price text-success">
                     <span class="price-unit">$</span>
                     <span data-bind-radio="pricing"><?php echo round($plans[1]->price) ?></span>
                  </h2>
                  <p class="text-muted">Per Month</p>
                  <div class="text-muted">
                     <p class="mb-0 lead-1">Manage up to <span class="text-success display-4"><?php echo $plans[1]->page_limit ?></span> Facebook pages</p>
                     <p class="mb-0 lead-1">All features included</p>
                  </div>
                  <p class="text-center pt-3 mb-1"><a class="btn btn-success" href="<?php echo site_url("users/signup/".$plans[1]->plan_id) ?>">Start <?php echo $plans[1]->trial_period ?> days free trial </a></p>
                  <p class="text-center mb-0 text-muted">No credit card required </p>
               </div>
            </div>
            <div class="col-md-4">
               <div class="pricing-1">
                  <p class="plan-name lead-3"><?php echo $plans[2]->name ?></p>
                  <br>
                  <h2 class="price">
                     <span class="price-unit">$</span>
                     <span data-bind-radio="pricing"><?php echo round($plans[2]->price) ?></span>
                  </h2>
                  <p class="text-muted">Per Month</p>
                  <div class="text-muted">
                     <p class="mb-0 lead-1">Manage up to <span class="text-success display-4"><?php echo $plans[2]->page_limit ?></span> Facebook pages</p>
                     <p class="mb-0 lead-1">All features included</p>
                  </div>
                  <p class="text-center pt-3 mb-1"><a class="btn btn-success" href="<?php echo site_url("users/signup/".$plans[2]->plan_id) ?>">Start <?php echo $plans[2]->trial_period ?> days free trial </a></p>
                  <p class="text-center mb-0 text-muted">No credit card required </p>
               </div>
            </div>
         </div>
      </div>
   </section>
</main>