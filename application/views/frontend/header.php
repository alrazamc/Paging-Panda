<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0, shrink-to-fit=no">
      <meta name="description" content="Scheduling and Analytics Tool for Facebook Pages">
      <meta name="keywords" content="Facebook,pages,manage,posts,schedule,insights,posts,analytics">
      <title><?php echo getenv('SITE_NAME') ?> | Scheduling and Analytics Tool for Facebook Pages</title>
      <!-- Styles -->
      <link href="<?php echo getenv('ASSET_BASE_URL') ?>assets/frontend/css/page.min.css" rel="stylesheet">
      <link href="<?php echo getenv('ASSET_BASE_URL') ?>assets/frontend/css/style.min.css" rel="stylesheet">
      <!-- Favicons -->
      <link rel="apple-touch-icon" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/favicon.png">
      <link rel="icon" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/favicon.png">
   </head>
   <body>
      <!-- Navbar -->
      <nav class="navbar navbar-expand-lg navbar-dark" data-navbar="fixed">
         <div class="container">
            <div class="navbar-left">
               <button class="navbar-toggler" type="button">&#9776;</button>
               <a class="navbar-brand" href="<?php echo site_url() ?>">
               <img class="logo-dark" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/logo.png" alt="logo" width="160">
               <img class="logo-light" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/logo.png" alt="logo" width="160">
               </a>
            </div>
            <section class="navbar-mobile">
               <nav class="nav nav-navbar ml-auto">
                  <a class="nav-link" href="#home">Home</a>
                  <a class="nav-link" href="#features">Features</a>
                  <a class="nav-link" href="#pricing">Pricing</a>
                  <a class="nav-link" href="#faq">FAQ</a>
                  <a class="nav-link" href="#contact">Contact</a>
                  <a class="nav-link" href="<?php echo site_url('users/login') ?>">Login</a>
               </nav>
            </section>
         </div>
      </nav>
      <!-- /.navbar -->
      <!-- Header -->
      <header id="home" class="header" style="background-image: linear-gradient(150deg, #fdfbfb 0%, #eee 100%);">
         <div class="container">
            <div class="row align-items-center h-100">
               <div class="col-lg-5">
                  <h1 class="">Manage Your Facebook pages with <?php echo getenv('SITE_NAME') ?></h1>
                  <p class="lead mt-5">Posting to multiple pages made easy. Add content to your library, create weekly schedule and <?php echo getenv("SITE_NAME") ?> will do the rest. Deep insights to better understand your audience</p>
                  <hr class="w-10 ml-0 my-5">
                  <p class="gap-xy">
                     <a class="btn btn-lg btn-round btn-success mw-200" href="#pricing">Get Started</a>
                     <a class="btn btn-lg btn-round btn-outline-success mw-200" href="#features">Features</a>
                  </p>
               </div>
               <div class="col-lg-6 ml-auto">
                  <div class="">
                     <div class="p-5 text-center">
                        <h3>Save time with content re-sharing feature</h3>
                     </div>
                  </div>
                  <div class="video-wrapper ratio-16x9 rounded shadow-6 mt-8 mt-lg-0 d-none">
                     <div class="poster" style="background-image: url(<?php echo getenv('ASSET_BASE_URL') ?>assets/frontend/img/preview/shot-1.png)"></div>
                     <button class="btn btn-circle btn-lg btn-info"><i class="fa fa-play"></i></button>
                     <iframe width="560" height="315" src="https://www.youtube.com/embed/M5S_JBRjd1s?rel=0&amp;showinfo=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen=""></iframe>
                  </div>
               </div>
            </div>
         </div>
      </header>
      <!-- /.header -->