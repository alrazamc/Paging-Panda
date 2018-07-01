<div class="container my-5">
	<div class="row">
		<div class="col-md-12">
			<?php echo $this->session->flashdata('alert') ?>
		</div>
	</div>
	<div class="row mb-md-3 mb-0">
		<div class="col-md-6 col-6 mb-2">
			<h4 class="page-title">Import Bulk Content</h4>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 mb-3">
			<div class="border-bottom library-tabs fz-20 mb-4">
				<a href="" class="text-success border-active mr-4 text-decoration-none">RSS Feeds</a>
				<a href="<?php echo site_url('import/csv') ?>" class="text-muted mr-4 text-decoration-none">Upload CSV</a>
			</div>
			<div class="row">
				<div class="col-sm-12 mb-3 text-sm-right text-center">
                    <a href="<?php echo site_url('import/add') ?>" class="btn btn-success btn-shadow"><i class="fas fa-plus"></i> Add RSS Feed</a>
				</div>
			</div>
			<?php if(empty($feeds)){ ?>
			<div class="p-5 text-center text-muted" >
				<h3>No RSS feeds found</h3>
        <p>Add RSS feeds of your websites/blogs and every 24 hours, <?php echo getenv('SITE_NAME') ?> will add new content from your feeds to your library </p>
			</div>
            <?php }else{ ?>
                <div class="table-responsive-lg bg-white" >
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th class="font-weight-500">Feed</th>
                      <th class="font-weight-500">Pages</th>
                      <th class="font-weight-500">Category</th>
                      <th class="font-weight-500">Import To</th>
                      <th class="font-weight-500">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach($feeds as $feed){ ?>
                    <tr>
                      <td>
                        <div><?php echo $feed->rss_feed_name ?></div>
                        <div><?php echo $feed->rss_feed_url ?></div>
                      </td>
                      <td>
                        <?php echo count(json_decode($feed->accounts)) ?> page(s)
                      </td>
                      <td>
                            <?php if($feed->use_once){ ?>
                                <div class="category-color-round mr-1" style="background-color: #28a745">&nbsp;</div>
                                Publish Once
                            <?php }else if($feed->category_id == 0){ ?>
                                <div class="category-color-round mr-1" style="background-color: #007bff">&nbsp;</div>
                                General
                            <?php }else{ ?>
                                <div class="category-color-round mr-1" style="background-color: <?php echo $categories[$feed->category_id]->category_color ?>">&nbsp;</div>
                                <?php echo $categories[$feed->category_id]->category_name ?>
                            <?php } ?>
                      </td>
                      <td>
                          <?php if($feed->to_pending){ ?>
                            Pending Content
                          <?php }else{ ?>
                            Approved Content
                          <?php } ?>
                      </td>
                      <td>
                            <a href="<?php echo site_url("import/edit/$feed->rss_feed_id") ?>" class="btn btn-success btn-shadow" data-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="<?php echo site_url("import/refresh/$feed->rss_feed_id") ?>" class="btn btn-success btn-shadow" data-toggle="tooltip" title="Import Now"><i class="fas fa-sync"></i></a>
                            <a href="<?php echo site_url("import/delete/$feed->rss_feed_id") ?>" class="btn btn-danger btn-shadow delete-record" data-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></a>
                      </td>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
            </div>
            <?php } ?>
			
		</div>
	</div>

</div>


<div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Delete RSS Feed</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
        <form method="post" action="<?php echo current_url() ?>">
          <div class="modal-body">
            <p>This will not <span class="font-weight-500">NOT</span> delete the content imported through this feed. If you also want to delete the content, Please use <a href="<?php echo site_url('content/bulk_edit') ?>" class="text-success font-weight-500">Bulk Edit</a> option first
            <br>
            Are you sure you want to delete this RSS Feed?</p>
          </div>
          <div class="modal-footer">
            <a href="" class="text-success font-weight-500" data-dismiss="modal">Cancel</a>
            <a href="" class="btn btn-danger btn-shadow">Delete</a>
          </div>
        </form>
    </div>
  </div>
</div>