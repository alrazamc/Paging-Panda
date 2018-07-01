 <!-- Header -->
<header id="home" class="header" style="background-image: linear-gradient(150deg, #fdfbfb 0%, #eee 100%);">
   <div class="container">
      <div class="row align-items-center h-100">
         <div class="col-lg-5">
            <h1 class="">Manage your Facebook pages with <?php echo getenv('SITE_NAME') ?></h1>
            <p class="lead mt-5">Posting to multiple pages made easy. Add content to your library, create weekly schedule and <?php echo getenv("SITE_NAME") ?> will do the rest. Deep insights to better understand your audience</p>
            <hr class="w-10 ml-0 my-5">
            <div class="row">
               <div class="col-6">
                  <a class="btn btn-lg btn-round btn-success btn-block" href="#pricing">Get Started</a>
               </div>
               <div class="col-6">
                  <a class="btn btn-lg btn-round btn-outline-success btn-block" href="#features">Features</a>
               </div>
            </div>
         </div>
         <div class="col-lg-6 ml-auto mt-6">
            <div class="p-5 text-center border border-success rounded">
               <h4>Save time with content re-sharing feature</h4>
               <form id="subscribe-form">
                  <div class="form-group">
                     <input type="email" class="form-control form-control-lg" name="" placeholder="Enter your email address..." required>
                  </div>
                  <div class="form-group">
                     <button type="submit" class="btn btn-success btn-block btn-xl">Watch Demo Video</button>
                  </div>
               </form>
            </div>
            <div class="video-wrapper ratio-16x9 rounded shadow-6 mt-8 mt-lg-0 d-none">
               <iframe width="560" height="315" src="https://www.youtube.com/embed/M5S_JBRjd1s?rel=0&amp;showinfo=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen=""></iframe>
            </div>
         </div>
      </div>
   </div>
</header>
<!-- /.header -->
<!-- Main Content -->
<main class="main-content">
   <!--
      |‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒
      | Features
      |‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒
      !-->
   <section id="features" class="section">
      <div class="container">
         <header class="section-header">
            <small>Features</small>
            <h2>A smart way to manage your pages</h2>
            <hr>
         </header>
         <div class="row gap-y align-items-center">
            <div class="col-md-6 ml-auto">
               <h4>Content Library</h4>
               <p>Organize your content (text, links, photos, videos) in categories. Reuse the content or select "publish once" category to publish only once. Post the same content to multiple pages. Set an expiry date for seasonal content. </p>
               <p>Add content in bulk from CSV file or auto-import content from the RSS feed of your website/blog. Advanced filters and many more features...</p>
            </div>
            <div class="col-md-5 order-md-first">
               <div class="row">
                  <div class="col-6 text-right mb-3"><i class="icon-pictures lead-8 text-success"></i></div>
                  <div class="col-6 mb-3"><i class="icon-video lead-8 text-success"></i></div>
                  <div class="col-6 text-right"><i class="icon-attachment  lead-8 text-success"></i></div>
                  <div class="col-6"><i class="icon-rss lead-8 text-success"></i></div>
               </div>
            </div>
         </div>
         <hr class="my-8">
         <div class="row gap-y align-items-center">
            <div class="col-md-6 mr-auto">
               <h4>Weekly Schedule</h4>
               <p>Create, 24/7 posting schedule based on your categories. Using your content and schedule, <?php echo getenv('SITE_NAME') ?> will auto-fill posts queue for next two weeks. You can also manually schedule certain content for specific date/time. Review the post queue to check what's going to be published on which page and when.</p>
               <p><?php echo getenv('SITE_NAME') ?> will cycle through your content in an orderly fashion and will re-publish the content when all items in a category are published so you will never run out of content for your pages</p>
            </div>
            <div class="col-md-5">
               <div class="row">
                  <div class="col-6 text-right mb-3 pr-5"><i class="icon-clock lead-8 text-success"></i></div>
                  <div class="col-6 mb-3"><i class="icon-calendar lead-8 text-success"></i></div>
                  <div class="col-6 text-right"><i class="icon-browser lead-8 text-success"></i></div>
                  <div class="col-6"><i class="icon-refresh lead-8 text-success"></i></div>
               </div>
               
            </div>
         </div>
         <hr class="my-8">
         <div class="row gap-y align-items-center">
            <div class="col-md-6 ml-auto">
               <h4>Page Insights</h4>
               <p>Analyze 130+ insights metrics in graphical charts to know your audience. Metrics include, but not limited to, impressions, engagements, consumptions, check-ins, positive/negative feedback, fans, post reactions, video views and many more.</p>
               <p>Export charts to images, PDF, CSV or XLSX files </p>
            </div>
            <div class="col-md-5 order-md-first">
               <div class="row">
                  <div class="col-6 text-right mb-3"><i class="icon-linegraph lead-8 text-success"></i></div>
                  <div class="col-6 mb-3"><i class="icon-piechart lead-8 text-success"></i></div>
                  <div class="col-6 text-right"><i class="icon-bargraph  lead-8 text-success"></i></div>
                  <div class="col-6"><i class="icon-download lead-8 text-success"></i></div>
               </div>
            </div>
         </div>
      </div>
   </section>
   <!--
      |‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒
      | Features
      |‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒
      !-->
   <section class="section bg-gray">
      <div class="container">
         <div class="row gap-y">
            <div class="col-lg-4">
               <div class="card card-body border text-center">
                  <p class="my-5"><i class="icon-presentation lead-8 text-lighter"></i></p>
                  <h5>Content Insights</h5>
                  <p>Get reactions, comments, shares, reach, engaged users, link clicks, video views, negative feedback for each content item</p>
               </div>
            </div>
            <div class="col-lg-4">
               <div class="card card-body border text-center">
                  <p class="my-5"><i class="icon-rss lead-8 text-lighter"></i></p>
                  <h5>Content Moderation</h5>
                  <p>Approve or decline content from RSS feeds before publishing. Only approved content is published</p>
               </div>
            </div>
            <div class="col-lg-4">
               <div class="card card-body border text-center">
                  <p class="my-5"><i class="icon-calendar lead-8 text-lighter"></i></p>
                  <h5>Random Slot</h5>
                  <p>Add a random time slot to your schedule to publish any random content item from your library</p>
               </div>
            </div>
         </div>
      </div>
   </section>
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
            <p class="lead">Choose the right plan, register your account and start using <?php echo getenv('SITE_NAME') ?></p>
         </header>
         <div class="row gap-y text-center">
            <div class="col-md-4">
               <div class="pricing-1">
                  <p class="plan-name"><?php echo $plans[0]->name ?></p>
                  <br>
                  <h2 class="price">
                     <span class="price-unit">$</span>
                     <span data-bind-radio="pricing"><?php echo round($plans[0]->price) ?></span>
                  </h2>
                  <p class="small text-muted">Per Month</p>
                  <div class="text-muted">
                     <p class="mb-0">Manage up to <span class="text-success display-4"><?php echo $plans[0]->page_limit ?></span> Facebook pages</p>
                     <p class="mb-0">All features included</p>
                     <p class="mb-0"><?php echo $plans[0]->trial_period ?> days free trial - No credit card required</p>
                  </div>
                  <p class="text-center py-3"><a class="btn btn-outline-success" href="<?php echo site_url("users/signup/".$plans[0]->plan_id) ?>">Get started</a></p>
               </div>
            </div>
            <div class="col-md-4">
               <div class="pricing-1 popular">
                  <p class="plan-name"><?php echo $plans[1]->name ?></p>
                  <br>
                  <h2 class="price text-success">
                     <span class="price-unit">$</span>
                     <span data-bind-radio="pricing"><?php echo round($plans[1]->price) ?></span>
                  </h2>
                  <p class="small text-muted">Per Month</p>
                  <div class="text-muted">
                     <p class="mb-0">Manage up to <span class="text-success display-4"><?php echo $plans[1]->page_limit ?></span> Facebook pages</p>
                     <p class="mb-0">All features included</p>
                     <p class="mb-0"><?php echo $plans[1]->trial_period ?> days free trial - No credit card required</p>
                  </div>
                  <p class="text-center py-3"><a class="btn btn-success" href="<?php echo site_url("users/signup/".$plans[1]->plan_id) ?>">Get started</a></p>
               </div>
            </div>
            <div class="col-md-4">
               <div class="pricing-1">
                  <p class="plan-name"><?php echo $plans[2]->name ?></p>
                  <br>
                  <h2 class="price">
                     <span class="price-unit">$</span>
                     <span data-bind-radio="pricing"><?php echo round($plans[2]->price) ?></span>
                  </h2>
                  <p class="small text-muted">Per Month</p>
                  <div class="text-muted">
                     <p class="mb-0">Manage up to <span class="text-success display-4"><?php echo $plans[2]->page_limit ?></span> Facebook pages</p>
                     <p class="mb-0">All features included</p>
                     <p class="mb-0"><?php echo $plans[2]->trial_period ?> days free trial - No credit card required</p>
                  </div>
                  <p class="text-center py-3"><a class="btn btn-outline-success" href="<?php echo site_url("users/signup/".$plans[2]->plan_id) ?>">Get started</a></p>
               </div>
            </div>
         </div>
      </div>
   </section>
   <!--
      |‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒
      | FAQ
      |‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒
      !-->
   <section id="faq" class="section bg-gray">
      <div class="container">
         <header class="section-header">
            <small>FAQ</small>
            <h2>Frequently Asked Questions</h2>
            <hr>
            <p>Got a question? We've got answers. If you have some other questions, Click the messenger button at bottom right or mail us at <a target="_blank" class="text-success" href="mailto:support@pagingpanda.com">support@pagingpanda.com</a></p>
         </header>
         <div class="row gap-y">
            <div class="col-md-6 col-xl-4">
               <h5>Can it post to other social networks?</h5>
               <p>Sorry, it can't. This tool is built only for Facebook pages</p>
            </div>
            <div class="col-md-6 col-xl-4">
               <h5>Can I post to pages I don't own?</h5>
               <p>In order to post on a page, You must be admin or editor of that page</p>
            </div>
            <div class="col-md-6 col-xl-4">
               <h5>is it easy to use?</h5>
               <p><?php echo getenv('SITE_NAME') ?> is super easy to use. It has mobile friendly interface. You can setup your new account in minutes</p>
            </div>
            <div class="col-md-6 col-xl-4">
               <h5>How much content I can add?</h5>
               <p>There is no limit, you can add as much as you wish</p>
            </div>
            <div class="col-md-6 col-xl-4">
               <h5>Can I change my subscription plan?</h5>
               <p>Yes. You can change your plan at any time with no further obligation</p>
            </div>
            <div class="col-md-6 col-xl-4">
               <h5>Can I try your service for free?</h5>
               <p>Of course! We’re happy to offer 7 days free trial for each subscription plan with no credit card required</p>
            </div>
         </div>
      </div>
   </section>
</main>