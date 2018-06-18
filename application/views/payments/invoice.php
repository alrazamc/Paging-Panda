<div class="container my-5">
    <div class="row">
        <div class="col-12 mb-3 text-right">
            <button class="btn btn-outline-secondary btn-flat" id="print"><i class="fas fa-print"></i> Print</button>
        </div>
    </div>
    <script type="text/javascript">
        $(function(){
            $('#print').on('click', function(){
                var restorepage = $('body').html();
                var printcontent = $('#invoice' ).clone();
                printcontent.addClass('mt-5');
                $('body').empty().html(printcontent);
                window.print();
                $('body').html(restorepage);
            });
        });
    </script>
    <div class="card" id="invoice">
        <div class="card-header">
            Invoice
            <strong><?php echo date('d M, Y', strtotime($invoice->transaction_time)) ?></strong> 
            <span class="float-right"> <strong>Status:</strong> 
                <?php if($invoice->invoice_status == 'approved' || $invoice->invoice_status == 'pending'){ ?>
                    Pending
                <?php }else if($invoice->invoice_status == 'deposited'){ ?>
                    Paid
                <?php }else if($invoice->invoice_status == 'declined'){ ?>
                    Declined
                <?php } ?>
            </span>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-sm-6">
                    <h6 class="mb-3">From:</h6>
                    <div>
                        <strong><?php echo getenv("SITE_NAME") ?></strong>
                    </div>
                    <div>Pakki Shah Mardan</div>
                    <div>Mianwali, Punjab</div>
                    <div>Pakistan</div>
                </div>
                <div class="col-sm-6">
                    <h6 class="mb-3">To:</h6>
                    <div>
                        <strong><?php echo $user->first_name.' '.$user->last_name ?></strong>
                    </div>
                    <div><?php echo $user->address ?></div>
                    <div><?php echo "$user->city, $user->state, $invoice->country" ?></div>
                    <div>Email: <?php echo $user->email ?></div>
                    <div>Phone: <?php echo $user->phone ?></div>
                </div>
            </div>
            <div class="table-responsive-sm">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="w-75">Description</th>
                            <th class="w-25 text-center pl-5">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <?php if($invoice->transaction_type == TRANS_TYPE_CREDIT){ ?>
                                    <?php if($invoice->type == PAYMENT_TYPE_INSTALLMENT){ ?>
                                        Monthly installment for <?php echo strtolower($invoice->plan_name) ?> plan 
                                    <?php }else{ ?>
                                        New subscription for <?php echo strtolower($invoice->plan_name) ?> plan 
                                    <?php } ?>
                                <?php }else{ ?>
                                    Refund from <?php echo strtolower($invoice->plan_name) ?> plan
                                <?php } ?>
                            </td>
                            <td class="text-center pl-5">$<?php echo abs($invoice->total) + abs($invoice->discount) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="col-lg-4 col-sm-5">
                </div>
                <div class="col-lg-4 col-sm-5 ml-auto">
                    <table class="table table-clear">
                        <tbody>
                            <tr>
                                <td class="left">
                                    <strong>Subtotal</strong>
                                </td>
                                <td class="right">$<?php echo abs($invoice->total) + abs($invoice->discount) ?></td>
                            </tr>
                            <?php if($invoice->discount != 0){ ?>
                            <tr>
                                <td class="left">
                                    <strong>Discount</strong>
                                </td>
                                <td class="right">$<?php echo abs($invoice->discount) ?></td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <td class="left">
                                    <strong>Total</strong>
                                </td>
                                <td class="right">
                                    <strong>$<?php echo abs($invoice->total) ?></strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>