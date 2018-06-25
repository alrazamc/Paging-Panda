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
                                      <p>We weren't able to charge you $<?php echo $invoice->plan_price ?> for <?php echo getenv('SITE_NAME') ?> <?php echo strtolower($invoice->plan_name) ?> plan. Please check your credit card expiration date and billing address.</p>
                                      <p>You still have until <?php echo date('d M, Y', strtotime($user->next_due_date." +3 days")) ?> to <a href="<?php echo site_url('payments/pay') ?>">update your billing information</a>. However, after this period, your account will be suspended. If this happens, your library content will not be deleted but you won't be able to access the features of <?php echo getenv('SITE_NAME') ?> until you subscribe again</p>

                                      <p>If you have any questions or feedback, just reply to this email, and we'll get right back to you</p>
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
