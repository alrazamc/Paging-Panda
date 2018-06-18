<div class="container my-5">
	<div class="row">
		<div class="col-md-12">
			<?php echo $this->session->flashdata('alert') ?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<h4 class="page-title">Invoices</h4>
		</div>
	</div>
	<div class="card air-card">
		<div class="card-body">
			<?php if(empty($invoices)){ ?>
			<div class="py-5 text-center text-muted">
				<h5>No invoices found</h5>
			</div>
			<?php }else{ ?>
				<div class="table-responsive">
					<table class="table table-hover invoices">
						<?php foreach($invoices as $invoice){ ?>
						<tr>
							<td><?php echo date('d M, Y', strtotime($invoice->transaction_time)) ?></td>
							<td> <a class="text-success" title="Details" href="<?php echo site_url("payments/invoice/$invoice->transaction_id") ?>"><?php echo $invoice->invoice_id ?></a> </td>
							<td class="text-muted">
								<?php if($invoice->transaction_type == TRANS_TYPE_CREDIT){ ?>
									<?php if($invoice->type == PAYMENT_TYPE_INSTALLMENT){ ?>
										Monthly installment for <?php echo strtolower($invoice->plan_name) ?> plan 
									<?php }else{ ?>
										New subscription for <?php echo strtolower($invoice->plan_name) ?> plan 
									<?php } ?>
								<?php }else{ ?>
									Refund from <?php echo strtolower($invoice->plan_name) ?> plan, invoice#(<?php echo $invoice->invoice_id ?>)
								<?php } ?>
							</td>
							<td>$<?php echo abs($invoice->total) ?></td>
							
							<td><a class="text-success" target="_blank" title="Download PDF"  href="<?php echo site_url("payments/download_invoice/$invoice->transaction_id") ?>"> <i class="fas fa-download"></i> </a></td>
							
						</tr>
						<?php } ?>
					</table>
				</div>
			<?php } ?>
		</div>
	</div>
</div>
