<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1, user-scalable=0, shrink-to-fit=no">
  <title><?php echo $page_title .' | '.getenv('SITE_NAME') ?></title>
  <link rel="apple-touch-icon" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/favicon.png">
  <link rel="icon" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/favicon.png">
  
  <link rel="stylesheet" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/fontawesome-all.min.css" />
  <link rel="stylesheet" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700">
  <link rel="stylesheet" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/login.css" />
  <script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/jquery.min.js"></script>
</head>
<body>
  <nav class="navbar navbar-expand-sm navbar-light bg-white">
    <div class="container">
      <a class="navbar-brand" href="<?php echo getenv('BASE_URL') ?>">
        <img src="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/logo.png" >
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item mx-2">
            <a class="nav-link" href="<?php echo site_url() ?>">Home</a>
          </li>
          <li class="nav-item mx-2">
            <a class="nav-link" href="<?php echo site_url('users/login') ?>">Login</a>
          </li>
          <li class="nav-item mx-2">
            <a class="nav-link" href="<?php echo site_url('users/signup/3') ?>">Register</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <div class="container-fluid mt-5">
    