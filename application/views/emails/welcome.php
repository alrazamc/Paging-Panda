<?php $this->load->view('emails/header-min') ?>
<tr>
  <td align="center" valign="top" id="templateBody">
    <!--[if (gte mso 9)|(IE)]>
    <table align="center" border="0" cellspacing="0" cellpadding="0" width="600" style="width:600px;">
      <tr>
        <td align="center" valign="top" width="600" style="width:600px;">
          <![endif]-->
          <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" class="templateContainer">
            <tr>
              <td valign="top" class="bodyContainer">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="min-width:100%;">
                  <tbody class="mcnTextBlockOuter">
                    <tr>
                      <td valign="top" class="mcnTextBlockInner" style="padding-top:9px;">
                        <!--[if mso]>
                        <table align="left" border="0" cellspacing="0" cellpadding="0" width="100%" style="width:100%;">
                          <tr>
                            <![endif]-->
                            <!--[if mso]>
                            <td valign="top" width="600" style="width:600px;">
                              <![endif]-->
                              <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width:100%; min-width:100%;" width="100%" class="mcnTextContentContainer">
                                <tbody>
                                  <tr>
                                    <td valign="top" class="mcnTextContent" style="padding-top:0; padding-right:18px; padding-bottom:9px; padding-left:18px;">
                                      <h3><span style="font-size:14px">Hi <?php echo $name ?>,</span></h3>
                                      &nbsp;
                                      <p>We are excited to welcome you to the <?php echo getenv('SITE_NAME') ?>. Let's get it started.</p>
                                      <ol>
                                        <li>Import your <a href="<?php echo site_url('accounts') ?>">pages</a></li>
                                        <li><a href="<?php echo site_url('content/add') ?>">Add</a> some content to library</li>
                                        <li>Add your desired time slots to weekly <a href="<?php echo site_url('schedule') ?>">schedule</a></li>
                                        <li><a href="<?php echo site_url('posts') ?>">Unpause</a> the queue</li>
                                      </ol>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                              <!--[if mso]>
                            </td>
                            <![endif]-->
                            <!--[if mso]>
                          </tr>
                        </table>
                        <![endif]-->
                      </td>
                    </tr>
                  </tbody>
                </table>
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="min-width:100%;">
                  <tbody class="mcnTextBlockOuter">
                    <tr>
                      <td valign="top" class="mcnTextBlockInner" style="padding-top:9px;">
                        <!--[if mso]>
                        <table align="left" border="0" cellspacing="0" cellpadding="0" width="100%" style="width:100%;">
                          <tr>
                            <![endif]-->
                            <!--[if mso]>
                            <td valign="top" width="600" style="width:600px;">
                              <![endif]-->
                              <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width:100%; min-width:100%;" width="100%" class="mcnTextContentContainer">
                                <tbody>
                                  <tr>
                                    <td valign="top" class="mcnTextContent" style="padding-top:0; padding-right:18px; padding-bottom:9px; padding-left:18px;">
                                      <p>Its great to have you onboard. Feel free to <a href="<?php echo site_url('contact') ?>">contact us</a>  anytime</p>
                                      <p>Regards,
                                        <br>
                                        Team <?php echo getenv('SITE_NAME') ?>
                                      </p>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                              <!--[if mso]>
                            </td>
                            <![endif]-->
                            <!--[if mso]>
                          </tr>
                        </table>
                        <![endif]-->
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
            </tr>
          </table>
          <!--[if (gte mso 9)|(IE)]>
        </td>
      </tr>
    </table>
    <![endif]-->
  </td>
</tr>
<?php $this->load->view('emails/footer-min') ?>
