<!DOCTYPE html>
<html lang="en">
   <head>
      <title><?php echo $page_title  ?></title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0, shrink-to-fit=no">
      <meta name="description" content="Scheduling and Analytics Tool for Facebook Pages">
      <meta name="keywords" content="Facebook,pages,manage,posts,schedule,insights,posts,analytics">

      <meta property="og:title" content="<?php echo getenv('SITE_NAME') ?>" />
      <meta property="og:type" content="website" />
      <meta property="og:image" content="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/og.png" />
      <meta property="og:url" content="<?php echo current_url() ?>" />
      <meta property="og:description" content="Scheduling and analytics tool for Facebook pages" />

      <meta name="twitter:card" content="summary" />
      <meta name="twitter:title" content="<?php echo getenv('SITE_NAME') ?>" />
      <meta name="twitter:description" content="Scheduling and analytics tool for Facebook pages" />
      <meta name="twitter:image" content="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/og.png" />
      <!-- Styles -->
      <link href="<?php echo getenv('ASSET_BASE_URL') ?>assets/frontend/css/page.min.css" rel="stylesheet">
      <!-- Favicons -->
      <link rel="apple-touch-icon" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/favicon.png">
      <link rel="icon" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/favicon.png">
   </head>
   <body class="">
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
                  <a class="nav-link" href="<?php echo empty($this->uri->segment(1)) ? '' : site_url() ?>#home">Home</a>
                  <a class="nav-link" href="<?php echo site_url('features') ?>">Features</a>
                  <a class="nav-link" href="<?php echo site_url('pricing') ?>">Pricing</a>
                  <a class="nav-link" href="<?php echo empty($this->uri->segment(1)) ? '' : site_url() ?>#faq">FAQ</a>
                  <a class="nav-link" href="<?php echo site_url('contact') ?>">Contact</a>
                  <a class="nav-link" href="<?php echo site_url('users/login') ?>">Login</a>
               </nav>
            </section>
         </div>
      </nav>
      <!-- /.navbar -->