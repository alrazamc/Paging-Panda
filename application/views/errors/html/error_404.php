<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <link rel="shortcut icon" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/favicon.png" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Not Found | <?php echo getenv('SITE_NAME') ?></title>
  <link rel="stylesheet" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700">
  <link rel="stylesheet" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/style.css?v=<?php echo getenv('STYLE_VERSION') ?>" />
</head>

<body>
  <div class="container-scroller">
    <div class="container-fluid">
      <div class="row">
        <div class="content-wrapper full-page-wrapper d-flex align-items-center text-center error-page">
          <div class="col-lg-6 mx-auto">
            <img src="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/logo.png" >
            <h1 class="display-1 mb-0 text-success">404</h1>
            <h2 class="mb-4 text-success">Page Not Found!</h2>
            <a  class="btn btn-outline-success mt-5" href="<?php echo getenv('BASE_URL') ?>">Back to Application</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>