<link rel="stylesheet" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/daterangepicker.css" />
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/moment.min.js"></script>
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/daterangepicker.js"></script>

<link rel="stylesheet" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/perfect-scrollbar.css" />
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/perfect-scrollbar.min.js"></script>

<link rel="stylesheet" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/lightbox.min.css" />
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/lightbox.min.js"></script>

<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/ui-bootstrap-custom-2.5.0.min.js"></script>
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/ui-bootstrap-custom-tpls-2.5.0.min.js"></script>

<script type="text/javascript">
    <?php date_default_timezone_set($this->session->userdata('time_zone')); ?>
    var interval_seconds = <?php echo abs(date('Z')) ?>;
    <?php if(date('Z') < 0){ ?>
    var zone_op = 'minus';
    <?php }else{ ?>
    var zone_op = 'plus';
    <?php } ?>  
  var api_base_url = '<?php echo site_url('api') ?>/';
  var poster_url = '<?php echo getenv('ASSET_BASE_URL') ?>assets/images/video-preloader.gif';
  var angapp = angular.module('app', ['ui.bootstrap']);
  angapp.controller('pageController', function($scope, $http, $sce, $filter, $timeout){
  	$scope.graph_url = '<?php echo GRAPH_API_URL ?>';
    $scope.alert = '';
    $scope.page_preloader = true;
    $scope.pages = <?php echo json_encode($pages) ?>;
    $scope.categories = <?php echo json_encode($categories) ?>;
    $scope.rss_feeds = <?php echo json_encode($rss_feeds) ?>;
    $scope.post_type = 'Choose Type';
    $scope.start_date = '';
    $scope.end_date = '';
    $scope.query = '';
    $scope.posts_preloader = true;
    $scope.skip_preloader  = false;
    $scope.posts = [];
    $scope.current_page = 1;
    $scope.total_items = 0;
    $scope.posts_limit = <?php echo getenv('POSTS_PER_PAGE') ?>;

    $scope.status_pending = <?php echo POST_STATUS_PENDING ?>;
    $scope.status_published = <?php echo POST_STATUS_PUBLISHED ?>;
    $scope.status_error = <?php echo POST_STATUS_ERROR ?>;

    $scope.post_text = <?php echo POST_TYPE_TEXT ?>;
    $scope.post_link = <?php echo POST_TYPE_LINK ?>;
    $scope.post_photo = <?php echo POST_TYPE_PHOTO ?>;
    $scope.post_video = <?php echo POST_TYPE_VIDEO ?>;

    $scope.tab = $scope.status_pending;

    $scope.categories.unshift({
    	category_id : -1,
    	category_name : 'Publish Once',
    	category_color : '#28a745'
    });
    $scope.categories.unshift({
    	category_id : 0,
    	category_name : 'General',
    	category_color : '#007bff'
    });
    $scope.categories.unshift({
    	category_id : false,
    	category_name : 'Choose Category',
    	category_color : '#ffffff'
    });

    $scope.pages.unshift({
    	account_id : false,
    	account_name : 'Choose Page'
    });
    $scope.rss_feeds.unshift({
    	rss_feed_id : false,
    	rss_feed_name : 'Choose RSS Feed'
    });
    $scope.selected_category = $scope.categories[0];
    $scope.selected_page = $scope.pages[0];
    $scope.selected_rss_feed = $scope.rss_feeds[0];

    $scope.change_page = function(record){
		$scope.selected_page = record;
	}	
    $scope.change_category = function(record){
    	$scope.selected_category = record;
    }
    $scope.change_rss_feed = function(record){
    	$scope.selected_rss_feed = record;
    }

    $scope.change_post_type = function(record){
    	$scope.post_type = record;
    }

    $scope.$watch('[selected_category, selected_page, selected_rss_feed, post_type, start_date, end_date, query, tab]', function(){  
    	$scope.current_page = 1;
    	$scope.posts = [];
    	$scope.load_posts();
    }, true);
    $scope.change_pagination = function()
    {
    	$scope.posts = [];
    	$scope.load_posts();
    }

    $scope.reset_filters = function()
    {
    	$scope.selected_category = $scope.categories[0];
    	$scope.selected_page = $scope.pages[0];
    	$scope.selected_rss_feed = $scope.rss_feeds[0];
    	$scope.post_type = 'Choose Type';
	    $scope.start_date = '';
	    $scope.end_date = '';
	    $scope.query = '';
    }

    $scope.parse_message = function(record)
    {
    	text = record.message;
    	if(record.post_type == $scope.post_link)
    	{
    		var matches = text.match(/\bhttps?:\/\/\S+\s*(\r\n|\r|\n)?/gi);
    		if(matches)
    			text = text.replace(matches[0], '');
    	}
    	text = text.replace(/(https?:\/\/[^\s]+)/g, "<a href='$1' target='_blank'>$1</a>");
    	text = text.replace(/^\s+|\s+$/g, '');
    	text = text.replace(/#(\S*)/g,'<span class="hashtag">#<a target="_blank" href="https://facebook.com/hashtag/$1">$1</a></span>');
    	text = text.replace(/(?:\r\n|\r|\n)/g, '<br>');
    	return text;
    }

    $scope.get_filters = function()
    {
    	var filters = {
    		query: $scope.query == "" ? false : $scope.query,
    		category_id: $scope.selected_category.category_id,
    		use_once: false,
    		account_id: $scope.selected_page.account_id,
    		rss_feed_id: $scope.selected_rss_feed.rss_feed_id,
    		post_type: false,
    		start_date: $scope.start_date == "" ? false : $scope.start_date,
    		end_date: $scope.end_date == "" ? false : $scope.end_date,
    		status: $scope.tab,
    		page: $scope.current_page
    	};
    	
    	if($scope.selected_category.category_id === -1){
    		filters.use_once = true;
    		filters.category_id = false;
    	}

    	if($scope.post_type == 'Text')
    		filters.post_type = $scope.post_text;
    	else if($scope.post_type == 'Link')
    		filters.post_type = $scope.post_link;
    	else if($scope.post_type == 'Photo')
    		filters.post_type = $scope.post_photo;
    	else if($scope.post_type == 'Video')
    		filters.post_type = $scope.post_video;
    	return filters;
    }

    $scope.is_filtered_view = function()
    {
        if($scope.selected_category.category_id !== false || $scope.selected_page.account_id !== false || $scope.selected_rss_feed.rss_feed_id !== false)
            return true;
        if($scope.post_type != 'Choose Type')
            return true;
        if($scope.start_date != '' || $scope.end_date != '' || $scope.query != '')
            return true;
        return false;
    }

    $scope.load_posts = function()
    {
    	$scope.posts_preloader = true;
    	$http.post(api_base_url + 'posts/list', {filters:$scope.get_filters()}).then(function(response){
    		$scope.posts = response.data.posts;
    		$scope.total_items = response.data.total;
    		angular.forEach($scope.posts, function(record, index){

    			record.message = $sce.trustAsHtml($scope.parse_message(record));
    			if(record.use_once == '1'){
    				record.category_name = $scope.categories[2].category_name;
    				record.category_color = $scope.categories[2].category_color;
    			}else if(record.category_id == '0'){
    				record.category_name = $scope.categories[1].category_name;
    				record.category_color = $scope.categories[1].category_color;
    			}else{
                    for(var i=0; i<$scope.categories.length; i++)
                        if($scope.categories[i].category_id == record.category_id)
                        {
                            record.category_name = $scope.categories[i].category_name;
                            record.category_color = $scope.categories[i].category_color;
                        }
                }
                var time = moment(record.scheduled_time);
                if(zone_op == 'plus')
                    time.add(interval_seconds, 's');
                else if(zone_op == 'minus')
                    time.subtract(interval_seconds, 's');
                record.scheduled_time_text = time.format('D MMM, h:mm A');
    			record.page = {};
    			for(var i=0; i<$scope.pages.length; i++)
    				if($scope.pages[i].account_id == record.account_id)
    					record.page = $scope.pages[i];

                if( index == 0 )
                {
                    record.date_divider = time.format('dddd, MMMM D'); 
                }else
                {
                    prev_record = $scope.posts[index - 1];
                    var time2 = moment(prev_record.scheduled_time);
                    if(zone_op == 'plus')
                        time2.add(interval_seconds, 's');
                    else if(zone_op == 'minus')
                        time2.subtract(interval_seconds, 's');
                    if(time.format('D') != time2.format('D'))
                        record.date_divider = time.format('dddd, MMMM D');
                }

    		});
            $scope.posts_preloader = false;
            $scope.skip_preloader  = false;
            $timeout(register_scrollbar, 400);
        }, function(){ 
            $scope.posts_preloader = false;
            $scope.skip_preloader  = false;
        });
    }


    $scope.show_video = function(record)
    {
    	var mp4_extensions = ['mp4', 'm4a', 'm4p', 'm4b', 'm4r', 'm4v'];
        var ext = record.attachments[0].split('?')[0].split('.').pop();
        ext = ext.toLowerCase();
        if(mp4_extensions.indexOf(ext) !== -1)
    	{
    		$scope.show_video_player = true;
    		$('#video-modal video').attr('poster', poster_url);
	    	$('#video-modal video').attr('src', record.attachments[0]);
	    	$('#video-modal video').on('canplay', function (event) {
			    $(this).attr('poster', '');
			});
	    	$('#video-modal').modal('show');
	    	$('#video-modal').on("hidden.bs.modal", function(){
	    		if(!($('#video-modal video').get(0).paused))
	    			$('#video-modal video').get(0).pause();
	    	});
    	}else
    	{
    		$scope.show_video_player = false;
    		$('#video-modal').modal('show');
    	}	
    }

    $scope.skip_post = function(record)
    {
        $scope.skip_preloader = true;
        record.skip_preloader = true;
        record.skipping = true;
        $http.get(api_base_url + 'posts/skip/' + record.content_id + '/' + record.is_random).then(function(response){
            record.skipping = false;
            record.updating_queue = true;
            $timeout(function(){
                $scope.load_posts();
            }, 2000);
        }, function(){
            window.location.reload();
        });
    }
  });
  function register_scrollbar()
  {
  	
  	$('.content-card').each(function(){
  		if($(this).children('.row').height() <= 175)
  			return;
  		var ps = new PerfectScrollbar(this,{
  			wheelPropagation: true,
  			swipeEasing : true,
            suppressScrollX: true
  		});
  	});
  }
</script>
<div class="container my-5" ng-app="app" ng-controller="pageController">
	<div class="row">
		<div class="col-md-12">
			<?php echo $this->session->flashdata('alert') ?>
		</div>
	</div>
	<div class="row mb-md-3 mb-0">
		<div class="col-md-3 col-6 mb-2">
			<h4 class="page-title">Posts</h4>
		</div>
		<div class="col-md-9 mb-2">
			<div class="input-group">
		        <div class="input-group-prepend">
		        	<div class="input-group-text text-success"><i class="fas fa-search"></i></div>
		        </div>
		        <input type="text" class="form-control" placeholder="Search..." ng-model="query" ng-model-options="{debounce:400}">
		    </div>
		</div>
	</div>
	<div class="row" ng-cloak>
		<div class="col-md-3 mb-3">
			<div class="border-bottom fz-20 mb-4">
				Filters <a href="" class="text-success float-right fz-18" ng-click="reset_filters()">Reset</a>
			</div>
			<div class="form-group">
				<label class="text-muted">Category</label>
				<div class="dropdown border dropdown-green">
				  <a class="btn btn-default btn-block bg-white text-left text-dark dropdown-green text-truncate  dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				    <div ng-if="selected_category.category_id !== false" class="category-color-round mr-1" style="background-color: {{ selected_category.category_color  }}">&nbsp;</div>
				    {{ selected_category.category_name }}
				    <i class="fas fa-caret-down dropdown-caret"></i>
				  </a>

				  <div class="dropdown-menu w-100" aria-labelledby="dropdownMenuLink">
				  	<div class="scrollable-dropdown">
				  		<a class="dropdown-item {{ record.category_id === selected_category.category_id ? 'bg-success text-white' : '' }}" href="" ng-click="change_category(record)"  ng-repeat="record in categories">
					    	<div ng-if="record.category_id !== false" class="category-color-round mr-1" style="background-color: {{ record.category_color  }}">&nbsp;</div>
					    	{{ record.category_name }}
						</a>
				  	</div>
				  </div>
				</div>
			</div>
			<div class="form-group">
				<label class="text-muted">Page</label>
				<div class="dropdown border dropdown-green">
				  <a class="btn btn-default btn-block bg-white text-left text-dark dropdown-green text-truncate dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				    <img ng-if="selected_page.account_id" ng-src="{{ graph_url+selected_page.account_fb_id }}/picture?width=30&height=30&access_token={{ selected_page.access_token }}" width="20" height="20" class="rounded-circle border mr-2">
				    {{ selected_page.account_name }}
				    <i class="fas fa-caret-down dropdown-caret"></i>
				  </a>

				  <div class="dropdown-menu w-100" aria-labelledby="dropdownMenuLink">
				  	<div class="scrollable-dropdown">
					    <a class="dropdown-item {{ record.account_id == selected_page.account_id ? 'bg-success text-white' : '' }}" href="" ng-click="change_page(record)"  ng-repeat="record in pages">
					    	<img ng-if="record.account_id" ng-src="{{ graph_url+record.account_fb_id }}/picture?width=30&height=30&access_token={{ record.access_token }}" width="30" height="30" class="rounded-circle border mr-2">
					    	{{ record.account_name }}
						</a>
					</div>
				  </div>
				</div>
			</div>
			<div class="form-group">
				<label class="text-muted">RSS Feed</label>
				<div class="dropdown border dropdown-green">
				  <a class="btn btn-default btn-block bg-white text-left text-dark dropdown-green text-truncate dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				    {{ selected_rss_feed.rss_feed_name }}
				    <i class="fas fa-caret-down dropdown-caret"></i>
				  </a>

				  <div class="dropdown-menu w-100" aria-labelledby="dropdownMenuLink">
				  	<div class="scrollable-dropdown">
					    <a class="dropdown-item {{ record.rss_feed_id == selected_rss_feed.rss_feed_id ? 'bg-success text-white' : '' }}" href="" ng-click="change_rss_feed(record)"  ng-repeat="record in rss_feeds">
					    	{{ record.rss_feed_name }}
						</a>
					</div>
				  </div>
				</div>
			</div>
			<div class="form-group">
				<label class="text-muted">Post Type</label>
				<div class="dropdown border dropdown-green">
				  <a class="btn btn-default btn-block bg-white text-left text-dark dropdown-green text-truncate dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				    {{ post_type }}
				    <i class="fas fa-caret-down dropdown-caret"></i>
				  </a>

				  <div class="dropdown-menu w-100" aria-labelledby="dropdownMenuLink">
				  	<div class="scrollable-dropdown">
					    <a class="dropdown-item {{ post_type == 'Choose Type' ? 'bg-success text-white' : '' }}" href="" ng-click="change_post_type('Choose Type')" >
					    	Choose Status
						</a>
						<a class="dropdown-item {{ post_type == 'Text' ? 'bg-success text-white' : '' }}" href="" ng-click="change_post_type('Text')" >
					    	Text
						</a>
						<a class="dropdown-item {{ post_type == 'Link' ? 'bg-success text-white' : '' }}" href="" ng-click="change_post_type('Link')" >
					    	Link
						</a>
						<a class="dropdown-item {{ post_type == 'Photo' ? 'bg-success text-white' : '' }}" href="" ng-click="change_post_type('Photo')" >
					    	Photo
						</a>
						<a class="dropdown-item {{ post_type == 'Video' ? 'bg-success text-white' : '' }}" href="" ng-click="change_post_type('Video')" >
					    	Video
						</a>
					</div>
				  </div>
				</div>
			</div>
			<div class="form-group">
				<label class="text-muted">Date Created</label>
				<div class="mb-2">
					<input type="text" class="form-control date-time-picker" placeholder="Start Date..." ng-model="start_date">
				</div>
				<div class="mb-2">
					<input type="text" class="form-control date-time-picker" placeholder="End Date..." ng-model="end_date">
				</div>
			</div>
		</div>
		<div class="col-md-9 mb-3">
			<div class="border-bottom library-tabs fz-20 mb-4">
				<a href="" ng-click="tab=status_pending" class="{{ tab == status_pending ? 'text-success border-active' : 'text-muted' }} mr-4 text-decoration-none">In Queue</a>
                <a href="" ng-click="tab=status_published" class="{{ tab == status_published ? 'text-success border-active' : 'text-muted' }} mr-4 text-decoration-none">Published</a>
			</div>
			<div class="row">
				<div class="col-sm-8 mb-3 text-sm-left text-center" >
                    <span class="text-muted" ng-show="(!posts_preloader || skip_preloader) && tab==status_pending"><span class="fz-20">{{ total_items | number  }}</span> posts are scheduled for next two weeks</span>
					<span class="text-muted" ng-show="(!posts_preloader || skip_preloader) && tab==status_published"><span class="fz-20">{{ total_items | number }}</span> posts are published in last 30 days</span>
				</div>
				<div class="col-sm-4 mb-3 text-sm-right text-center">
                    <?php if($this->session->userdata('queue_paused')){ ?>
                    <a href="<?php echo site_url('posts/unpause') ?>" ng-show="tab==status_pending" class="btn btn-success btn-shadow">UnPause Queue</a>
                    <?php }else{ ?>
                    <a href="<?php echo site_url('posts/pause') ?>" ng-show="tab==status_pending" class="btn btn-success btn-shadow">Pause Queue</a>
                    <?php } ?>
				</div>
			</div>
			<div class="p-5 text-center text-success" ng-if="posts_preloader && !skip_preloader">
				<h1><i class="fas fa-spin fa-circle-notch"></i></h1>
			</div>
			<div class="p-5 text-center text-muted" ng-if="!posts_preloader && posts.length == 0">
				<h3>No posts found</h3>
                <div ng-if="!is_filtered_view() && tab == status_pending">
                    <p class="mb-1">Using your content in the library and time slots in the schedule, <?php echo getenv('SITE_NAME') ?> will fill the post queue</p>
                    <p class="mb-1">You can also schedule posts for specific date/time while adding/editing content</p>
                </div>
                <div ng-if="!is_filtered_view() && tab == status_published">
                    <p>Posts published successfully on your pages, will be displayed here</p>
                </div>
                <a href="" class="text-success fz-18" ng-click="reset_filters()" ng-if="is_filtered_view()">Reset Filters</a>
			</div>

            <div ng-repeat="record in posts">
                <div class="text-md-left text-center my-2" ng-if="record.date_divider">
                   <div class="d-inline-block fz-18 date-divider text-success font-weight-500 py-1 px-3 rounded bg-white"> {{ record.date_divider }} </div>
                </div>
    			<div class="card air-card mb-3">
    				<div class="card-body p-2 content-card post-card position-relative" id="post-{{ record.post_id }}">
    					<div class="row">
                            <div class="col-sm-12 mb-2">
                                <div class="">
                                    <a href="https://facebook.com/{{ record.post_fb_id }}"  ng-if="record.post_fb_id" target="_blank"  class="float-right text-dark"><i class="fab fa-facebook-square fa-2x"></i></a>
                                    <img ng-src="{{ graph_url+record.page.account_fb_id }}/picture?width=40&height=40&access_token={{ record.page.access_token }}" width="40" height="40" class="rounded-circle float-left border mr-2">
                                    <a href="https://facebook.com/{{ record.page.account_fb_id }}" class="page-name text-dark fz-18" target="_blank">{{ record.page.account_name }}</a>
                                    <div class="post-time fz-14"><span class="text-muted">{{ record.scheduled_time_text }}</span></div>
                                </div>
                            </div>
    						<div class="col-sm-12 {{ record.post_type == post_link ? '' : 'd-flex' }}">
    							
    							<a href="" ng-click="show_video(record)"  ng-if="record.attachments.length && record.post_type == post_video">
    								<div class="post-attachment mr-3" >
    									<span class="video-icon text-dark"><i class="fas fa-video fa-2x"></i></span>
    								</div>
    							</a>

    							<a href="{{ record.attachments[0] }}" ng-if="record.attachments.length && record.post_type == post_photo" data-lightbox="post-{{record.post_id }}">
    								<div class="post-attachment mr-3" style="background: url({{ record.attachments[0] }}); background-size: cover;" >
    								<span ng-if="record.attachments.length - 1 > 0" class="plus-more">+{{ record.attachments.length - 1 }}</span>
    								</div>
    							</a>

    							<div class="d-none">
    								<a href="{{ attachment }}" ng-if="!$first" ng-repeat="attachment in record.attachments"  data-lightbox="post-{{record.post_id }}"></a>
    							</div>

    							<p class="text-muted mb-0 post-message" ng-bind-html="record.message"></p>

    							<div class="link-preview p-2 bg-grey d-flex" ng-if="record.post_type == post_link">
    								<div ng-if="record.link_image != ''" class="post-attachment link-image mr-3" style="background: url({{ record.link_image }}); background-size: cover;">
    								</div>
    								<div>
                                        <div class="link-title" ng-if="record.link_title != '' && record.link_description != ''"><a href="{{ record.link_url}}" target="_blank">{{ record.link_title }}</a></div>
    									<div class="link-description" ng-if="record.link_title != '' && record.link_description != ''">{{ record.link_description }}</div>
    									<div class="link-caption fz-14" ng-if="record.link_title != '' && record.link_description != ''"><a href="{{ record.link_url}}" target="_blank" class="text-muted">{{ record.link_url }}</a></div>
    									
    									<div class="link-caption fz-14 text-muted" ng-if="record.link_title == '' && record.link_description == ''">
    										<div class="fz-18"><i class="far fa-image"></i> Facebook Link Preview</div>
    										Facebook will display a link preview for <a href="{{ record.link_url}}" target="_blank" class="">{{ record.link_url }}</a> when this post is published. You can see the preview by clicking the <b>Edit</b> button
    									</div>
    								</div>
    							</div>

    						</div>
    					</div>
    				</div>
    				<div class="card-footer bg-white pl-0 py-2">
    					<div class="row">
    						<div class="col-md-7 mb-sm-0 mb-2 text-left d-flex">
    							<div class="font-weight-500 text-white text-center px-2 category-color-label mr-1" style="background-color: {{ record.category_color  }}">{{ record.category_name }}</div>
                                <i class="ml-2 text-muted" ng-if="record.is_random == 1">Selected via Random</i>
    							<i class="ml-2 text-muted" ng-if="record.schedule_id == 0">Manually  Scheduled</i>
    						</div>
    						<div class="col-md-5 mb-sm-0 mb-2 text-md-right text-center">
    							<a ng-if="tab==status_published" href="<?php echo site_url("posts/history") ?>/{{ record.content_id }}/{{ record.post_fb_id }}" class="text-success font-weight-500 mr-4">Insights</a>
                                <a ng-if="tab==status_pending && record.schedule_id == 0" href="<?php echo site_url("posts/delete") ?>/{{ record.post_id }}"  class="text-success font-weight-500 mr-4 delete-record"><i class="fas fa-times"></i> Remove</a>
    							<a ng-if="tab==status_pending && record.schedule_id != 0" href="" ng-click="skip_post(record)"  class="text-success font-weight-500 mr-4"> 
                                    <span ng-show="!record.skip_preloader">Skip <i class="fas fa-fast-forward"></i> </span>
                                    <span ng-show="record.skip_preloader && record.skipping">Skipping...</span>
                                    <span ng-show="record.skip_preloader && record.updating_queue">Updating Queue...</span>
                                     <i ng-show="record.skip_preloader" class="fas fa-spin fa-circle-notch"></i> 
                                 </a>
    							
                                <a href="<?php echo site_url("content/edit") ?>/{{ record.content_id }}"    class="btn btn-success btn-flat btn-shadow">Edit</a>
                            </div>
    					</div>
    				</div>
    			</div>
            </div>

			<div class="mt-4" ng-show="!posts_preloader && posts.length > 0 && total_items > posts_limit">
				<ul class="justify-content-center"  uib-pagination total-items="total_items" items-per-page="posts_limit" ng-model="current_page" ng-change="change_pagination()"></ul>
			</div>
		</div>
	</div>


	<div class="modal fade" id="video-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-body">
	      	<div ng-show="show_video_player">
	      		<video class="w-100" controls="" controlsList="nodownload" onclick="this.paused ? this.play() : this.pause();">
	        	</video>
	      	</div>
	      	<div class="p-5 border" ng-show="!show_video_player">
				<h4 class="text-muted text-center">Video preview not available</h4>
				<p class="text-muted text-center">.mov videos are not supported by browser. Once your video is uploaded to Facebook, it will be converted to mp4 video</p>
			</div>
	        <div class="text-center mt-3">
	      		<button type="button" class="btn btn-green" data-dismiss="modal">Close</button>
	        </div>
	      </div>
	    </div>
	  </div>
	</div>


</div>


<div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Remove Post</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>This post will be removed from publishing queue. Are you sure you want to remove this post?</p>
      </div>
      <div class="modal-footer">
        <a href="" class="text-success font-weight-500" data-dismiss="modal">Cancel</a>
        <a href="#" class="btn btn-danger btn-shadow">Remove</a>
      </div>
    </div>
  </div>
</div>




<script type="text/javascript">
	$(function(){
		$('.date-time-picker').each(function(){
			$(this).daterangepicker({
				autoUpdateInput: false,
			    singleDatePicker: true,
			    showDropdowns: true,
                drops: 'up'
			});
			$(this).on('apply.daterangepicker', function(ev, picker) {
			    $(this).val(picker.startDate.format('DD MMM, GGGG'));
			    angular.element(this).triggerHandler('input');
			});
		})	
	});
</script>
