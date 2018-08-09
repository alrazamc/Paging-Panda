<!-- Main Content -->
<main class="main-content">
   <section class="section py-10 bg-gray">
     <div class="container">
        <div class="row">
             <div class="col-md-9 align-self-center py-8">
               <h4 class="text-center">Send us a message</h4>
               <br>

               <div class="row">
                 <div class="col-11 col-md-10 mx-auto">
                   <form class="bg-gray p-6" action="<?php echo site_url('frontend/inquiry') ?>" method="POST" >
                     <?php if($this->session->flashdata('error')){ ?>
                        <div class="alert alert-danger">
                           <?php echo $this->session->flashdata('error') ?>
                        </div>
                     <?php } ?>
                     <?php if($this->session->flashdata('success')){ ?>
                        <div class="alert alert-success">
                           <?php echo $this->session->flashdata('success') ?>
                        </div>
                     <?php } ?>
                     <div class="form-group">
                       <input class="form-control form-control-lg" type="text" name="name" placeholder="Your Name" required autofocus>
                     </div>

                     <div class="form-group">
                       <input class="form-control form-control-lg" type="email" name="email" placeholder="Your Email Address" required>
                     </div>

                     <div class="form-group">
                       <textarea class="form-control form-control-lg" rows="4" placeholder="Your Message" name="message" required></textarea>
                     </div>

                     <button class="btn btn-lg btn-block btn-success" type="submit">Submit Inquiry</button>
                   </form>

                 </div>
               </div>
             </div>


            <div class="col-md-3 text-center text-lg-left py-8 pt-11">
              <hr class="d-lg-none">
              <p>Pakki Shah Mardan<br>Mianwali, Punjab, Pakistan</p>
              <p><a href="mailto:support@pagingpanda.com" target="_blank" class="text-success">support@pagingpanda.com</a></p>
              <div class="fw-400">Follow Us</div>
              <div class="social social-sm social-inline">
                  <a class="text-success" href="<?php echo getenv('FB_PAGE') ?>" target="_blank"><i class="icon-facebook lead-5"></i></a>
                  <a class="text-success" href="<?php echo getenv('TWITTER_HANDLE') ?>" target="_blank"><i class="icon-twitter lead-5"></i></a>
              </div>
            </div>
           </div>
     </div>
     
   </section>
</main>