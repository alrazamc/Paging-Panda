<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?php echo $page_title .' | '.$this->config->item('site_name') ?></title>
  <link rel="shortcut icon" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/favicon.png" />
  <link rel="stylesheet" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/fontawesome-all.min.css" />
  <link rel="stylesheet" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500">
  <link rel="stylesheet" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/style.css?v=<?php echo getenv('STYLE_VERSION') ?>" />
  <script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/jquery.min.js"></script>
  <script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/angular.min.js"></script>
</head>
<body>
  <nav class="navbar navbar-expand-sm navbar-light bg-white">
    <div class="container flex-sm-column flex-lg-row">
      <a class="navbar-brand" href="<?php echo getenv('BASE_URL') ?>">
        <img src="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/logo.png" >
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto flex-md-row">
          <li class="nav-item">
            <a class="nav-link <?php if($this->uri->segment(1) == 'content') echo 'active' ?>" href="<?php echo site_url('content') ?>">Library</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php if($this->uri->segment(1) == 'posts') echo 'active' ?>" href="<?php echo site_url('posts') ?>">Posts</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php if($this->uri->segment(1) == 'schedule') echo 'active' ?>" href="<?php echo site_url('schedule') ?>">Schedule</a>
          </li>
          
          <li class="nav-item">
            <a class="nav-link <?php if($this->uri->segment(1) == 'categories') echo 'active' ?>" href="<?php echo site_url('categories') ?>">Categories</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php if($this->uri->segment(1) == 'insights') echo 'active' ?>" href="<?php echo site_url('insights') ?>">Insights</a>
          </li>
          
          <li class="nav-item d-block d-sm-none d-md-inline-block ">
            <a class="nav-link <?php if($this->uri->segment(1) == 'accounts') echo 'active' ?>" href="<?php echo site_url('accounts') ?>">Pages</a>
          </li>
          
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" >
              <?php echo $this->session->userdata('first_name') ?>
              <i class="fas fa-caret-down"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right air-card">
              <a class="dropdown-item d-none d-sm-block d-md-none " href="<?php echo site_url('accounts') ?>">Pages</a>
              <a class="dropdown-item" href="<?php echo site_url('users/settings') ?>">Settings</a>
              <a class="dropdown-item" href="<?php echo site_url('users/logout') ?>">Logout</a>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <?php if(isset($global_alerts)){ ?>
    <div class="container mt-3">
      <?php echo $global_alerts ?>
    </div>
  <?php } ?>