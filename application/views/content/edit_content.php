<link rel="stylesheet" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/daterangepicker.css" />
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/moment.min.js"></script>
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/daterangepicker.js"></script>

<link href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/angular-icheck.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/angular-icheck.js"></script>

<link href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/angular-ui-switch.min.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/angular-ui-switch.min.js"></script>

<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/angular-multiple-file-upload.js"></script>

<link href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/images-grid.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/images-grid.js"></script>

<script type="text/javascript">
  	var current_time = moment('<?php echo date('Y-m-d H:i:s') ?>');
	setInterval(function(){ 
		current_time.add(1, 's');
	 }, 1000);
  var api_base_url = '<?php echo site_url('api') ?>/';
  var poster_url = '<?php echo getenv('ASSET_BASE_URL') ?>assets/images/video-preloader.gif';
  var content_id = <?php echo $content->content_id ?>;
  var angapp = angular.module('app', ['angular-icheck', 'uiSwitch', 'fileUpload']);
  angapp.controller('pageController', function($scope, $http, $sce, $filter, $timeout){
  	$scope.graph_url = '<?php echo GRAPH_API_URL ?>';
    $scope.page_preloader = true;
    $scope.pages = <?php echo json_encode($pages) ?>;
    $scope.selected_page = $scope.pages.length ? $scope.pages[0] : false;
    $scope.categories = <?php echo json_encode($categories) ?>;
    $scope.alert = '';
    $scope.selected_page_count = 0;
    $scope.select_all = true;
    $scope.tab = 'editor';
    $scope.new_category_name = '';
    $scope.new_category_include = '0';
    $scope.new_category_preloader = false;

    $scope.preview_api = '<?php echo getenv('LINK_PREVIEW_API') ?>';
    $scope.pak = '<?php echo getenv('LINK_PREVIEW_KEY') ?>';    
    $scope.image_limit = <?php echo getenv('POST_IMG_LIMIT') ?>;
    $scope.image_size_limit = <?php echo getenv('POST_IMAGE_SIZE') ?>;
    $scope.video_limit = <?php echo getenv('POST_VIDEO_LIMIT') ?>;
    $scope.video_size_limit = <?php echo getenv('POST_VIDEO_SIZE') ?>;

    $scope.status_pending = <?php echo CONTENT_MODERATION_PENDING ?>;
    $scope.status_approved = <?php echo CONTENT_MODERATION_APPROVED ?>;
    $scope.status_declined = <?php echo CONTENT_MODERATION_DECLINED ?>;



    $scope.images_uploaded = 0;
    $scope.attachment_upload_url = api_base_url + "content/upload_attachments";

    $scope.post  = {};
    $scope.post.is_scheduled = false;
    $scope.post.should_expire = false;
    $scope.post.images = [];
    $scope.post.videos = [];

    $scope.dirty = true;

    $scope.previews = {};
    for(var i=0; i < $scope.pages.length; i++)
    	$scope.pages[i].selected = false;

    $http.get(api_base_url + 'content/details/' + content_id).then(function(response){
        $scope.post = response.data;
        if($scope.post.moderation_status != $scope.status_approved)
            $scope.post.approve = false;
        if($scope.post.rss_id != 0)
            $scope.post.suggested_text = $scope.post.preview.title + '\n' + $scope.post.link_url;
        for(var i=0; i < $scope.pages.length; i++)
            $scope.pages[i].selected = response.data.accounts.indexOf(parseInt($scope.pages[i].account_id)) === -1 ? false : true;
        for(var i=0; i < $scope.categories.length; i++)
            if($scope.categories[i].category_id == $scope.post.category_id)
                $scope.selected_category = $scope.categories[i];
        if($scope.post.link_url && $scope.post.preview.title)
            $scope.previews[$scope.post.link_url] = $scope.post.preview;
        if($scope.post.is_scheduled)
        {
            var scheduled_time = moment($scope.post.scheduled_time, 'DD MMM, GGGG hh:mm A');
            if(scheduled_time.format('X') < current_time.format("X"))
            {
                $scope.post.is_scheduled = false;
                $scope.post.scheduled_time = '<?php echo date('d M, Y h:i A', strtotime('+1 hour')) ?>';
            }
        }else
            $scope.post.scheduled_time = '<?php echo date('d M, Y h:i A', strtotime('+1 hour')) ?>';

        if(!$scope.post.should_expire)
            $scope.post.expiry_time = '<?php echo date('d M, Y h:i A', strtotime('+12 hours')) ?>';
        if($scope.post.is_scheduled || $scope.post.should_expire)
            $('#schedule-settings').collapse('show');
        $timeout(register_datetime_picker, 500);
        $timeout(function(){
            $scope.dirty = false;
            $scope.handle_unload();
        }, 500);
        $scope.page_preloader = false;
    }, function(){
        $scope.page_preloader = false;
    });

    $scope.append_categories = function()
    {
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
    }

    $scope.change_page = function(record){
		$scope.selected_page = record;
	}

    $scope.append_categories();

    $scope.selected_category = $scope.categories[0];
    	
    $scope.change_category = function(record){
    	$scope.selected_category = record;
    }

    $scope.add_new_category = function()
    {
    	if($scope.new_category_name == "")
    		return;
    	$scope.new_category_preloader = true;
    	var data = {
    		category : $scope.new_category_name,
    		include : $scope.new_category_include
    	};
    	$http.post(api_base_url + "categories/add", data).success(function(data){
    		$scope.categories = data;
            $scope.append_categories();
            for(var i=0; i<$scope.categories.length; i++)
                if($scope.categories[i].category_name == $scope.new_category_name)
                  $scope.selected_category = $scope.categories[i];
            $scope.new_category_preloader = false;
            $scope.new_category_name = '';
            $scope.new_category_include = '0';
    		jQuery('#add-category').modal('hide');
    		jQuery('#new-category-msg').removeClass('d-none').fadeIn().delay(3000).fadeOut();
    	});	
    }

    $scope.remove_attachments = function(attachments, index){
    	if(typeof attachments[index].key !== 'undefined' && typeof attachments[index].orign === 'undefined')
    	{
    		var post = {
    			key : attachments[index].key,
    			type : attachments[index].type
    		}
    		$http.post(api_base_url + 'content/delete_attachment', {files:[post]}).then(function(response){
    		});
    	}	
    	attachments.splice(index, 1);
    	jQuery('.tooltip').hide();
    }
    $scope.$watch('[pages]', function(){
    	var selected_page_count = 0;
    	for(var i=0; i < $scope.pages.length; i++)
    		if($scope.pages[i].selected)
    			selected_page_count++;
    	$scope.selected_page_count = selected_page_count;
    }, true);

    $scope.$watch('[select_all]', function(){
    	for(var i=0; i < $scope.pages.length; i++)
    		$scope.pages[i].selected = $scope.select_all;
    }, true);

    $scope.$watch('[post.message]', function(){
    	if(typeof $scope.post.message === 'undefined')
    		$scope.post.message = '';
    	var message = $scope.post.message;
    	var matches = message.match(/\bhttps?:\/\/\S+/gi);
    	if(matches)
    	{
    		$scope.post.link_available = true;
    		$scope.post.link_url = matches[0];
    		if(typeof $scope.post.show_link_preview === 'undefined')
    			$scope.post.show_link_preview = false;
    	}else
    	{
    		$scope.post.link_available = false;
    		$scope.post.link_url = '';
    	}
        $scope.dirty = true;
    	$scope.handle_unload();
    });

    $scope.$watch('[post.images, post.videos]', function(){
     	$('#attach-img-btn').tooltip('dispose');
     	$('#attach-video-btn').tooltip('dispose');
        $scope.dirty = true;
     	if($scope.post.videos.length == 0)
     	{
     		if($scope.post.images.length < $scope.image_limit)
     		{
     			$('#attach-img-btn').tooltip({ html:true,title: $('#img-tooltip').html() });
     		}else if($scope.post.images.length >= $scope.image_limit)
     		{
     			$('#attach-img-btn').tooltip({ html:true,title: $('#img-tooltip-limit').html() });
     		}
     	}else if($scope.post.videos.length >= 1)
     	{
            $timeout(function(){
                if($scope.post.videos[0].object_url && $('#video-player').attr('src') !=  $scope.post.videos[0].object_url)
                {
                    $('#video-player').attr('poster', poster_url);
                    $('#video-player').attr('src', $scope.post.videos[0].object_url);
                    $('#video-player').on('canplay', function (event) {
                        $(this).attr('poster', '');
                    });
                }
            }, 500);
     		$('#attach-img-btn').tooltip({ html:true,title: $('#img-tooltip-video').html() });
     	}

     	if($scope.post.images.length == 0)
     	{
     		if($scope.post.videos.length < $scope.video_limit)
     		{
     			$('#attach-video-btn').tooltip({ html:true,title: $('#video-tooltip').html() });
     		}else if($scope.post.videos.length >= $scope.video_limit)
     		{
     			$('#attach-video-btn').tooltip({ html:true,title: $('#video-tooltip-limit').html() });
     		}
     	}else if($scope.post.images.length >= 1)
     	{
     		$('#attach-video-btn').tooltip({ html:true,title: $('#video-tooltip-images').html() });
     	}
     	$scope.images_uploaded = 0;
     	var images = [];
     	for(var i=0; i < $scope.post.images.length; i++)
     	{
     		if($scope.post.images[i].size < $scope.image_size_limit)
     			images.push($scope.post.images[i].preview);
     		if(typeof $scope.post.images[i].uploaded !== 'undefined' && $scope.post.images[i].uploaded === true)
     			$scope.images_uploaded++;
     	}
     	$('#preview-images').imagesGrid({ images: images } );
    	$scope.handle_unload();
    }, true);

    $scope.$watch('[post.scheduled_time]', function(){
    	var scheduled_time = moment($scope.post.scheduled_time, 'DD MMM, GGGG hh:mm A');
    	$scope.post.scheduled_time_passed = scheduled_time.format('X') < current_time.format("X");
    });

    $scope.$watch('[post.expiry_time]', function(){
    	var expiry_time = moment($scope.post.expiry_time, 'DD MMM, GGGG hh:mm A');
    	$scope.post.expiry_time_passed = expiry_time.format('X') < current_time.format("X");
    });

    $scope.is_valid = function()
    {
    	if(typeof $scope.selected_category === 'undefined')
    		return false;
    	if($scope.post.is_scheduled && ($scope.post.scheduled_time == '' || $scope.post.scheduled_time_passed == true ||  $scope.selected_page_count == 0))
    		return false;
        if($scope.is_used_once())
            return false;
    	if($scope.post.should_expire && $scope.post.expiry_time == '')
    		return false;
    	return $scope.is_valid_post();
    }

    $scope.is_valid_post = function(){
    	if($scope.post.videos.length)
    	{
    		if($scope.post.videos[0].uploaded)
    			return true;
    		else
    			return false;
    	}
    	if($scope.post.images.length)
    	{
    		var all_uploaded = true;
    		var images_size_exceeding = 0;
    		var i=0; 
    		for(;i<$scope.post.images.length; i++)
    		{
    			if($scope.post.images[i].size > $scope.image_size_limit){
    				images_size_exceeding++;
    				continue;
    			}
    			if(typeof $scope.post.images[i].uploaded === 'undefined' || $scope.post.images[i].uploaded === false){
    				all_uploaded = false;
    				break;
    			}
    		}
    		if(images_size_exceeding == $scope.post.images.length)
    			return false;
    		return all_uploaded;
    	}
        if($scope.post.message !== '')
            return true;
    	return false;
    }

    $scope.handle_unload = function()
    {
    	jQuery(window).unbind('beforeunload');
    	if($scope.dirty)
    		jQuery(window).bind('beforeunload', function(){ 
    			return 'Changes you made may not be saved?'; 
    		});
    }
    $scope.preview_message = function(text)
    {
    	var matches = text.match(/\bhttps?:\/\/\S+\s*(\r\n|\r|\n)?/gi);
    	if(matches && $scope.post.images.length == 0 && $scope.post.videos.length == 0)
    	{
    		if($scope.post.show_link_preview)
    		{
    			text = text.replace(matches[0], '');
    			var link = matches[0];
                link = link.replace(/^\s+|\s+$/g, '');
                link = link.replace(/(?:\r\n|\r|\n)/g, '');
    			if(typeof $scope.previews[link] === 'undefined')
    			{
    				$scope.previews[link] = false;
    				var params = {key: $scope.pak, q: link};
                    $http.get($scope.preview_api + '?' + $.param(params)).then(function(response){
    					$scope.previews[link] = response.data;
    					var url = $scope.previews[link].url;
    					url = url.replace(/\/$/, "");
    					$scope.previews[link].url = url.replace(/https?:\/\//g, '');
    				});
    			}
    		}
    	}
    	text = text.replace(/(https?:\/\/[^\s]+)/g, "<a href='$1' target='_blank'>$1</a>");
    	text = text.replace(/^\s+|\s+$/g, '');
    	text = text.replace(/#(\S*)/g,'<span class="hashtag">#<a target="_blank" href="https://facebook.com/hashtag/$1">$1</a></span>');
    	text = text.replace(/(?:\r\n|\r|\n)/g, '<br>');
    	return $sce.trustAsHtml(text);
    }

    $scope.get_final_msg = function(text)
    {
    	var text = $scope.post.message;
    	var matches = text.match(/\bhttps?:\/\/\S+/gi);
    	if(matches && $scope.post.show_link_preview)
			text = text.replace(matches[0], '');
    	text = text.replace(/^\s+|\s+$/g, '');
    	return text;
    }

    $scope.send_post = function()
    {
    	if(!$scope.is_valid()) return
    	$scope.post.preloader = true;
    	var pages = [];
    	var attachments = [];
    	for(var i=0; i<$scope.pages.length; i++)
    		if($scope.pages[i].selected)
    			pages.push($scope.pages[i].account_id);

    	for(var i=0; i<$scope.post.images.length; i++)
    		if($scope.post.images[i].key)
    			attachments.push($scope.post.images[i].key);
    	for(var i=0; i<$scope.post.videos.length; i++)
    		if($scope.post.videos[i].key)
    			attachments.push($scope.post.videos[i].key);
    	var post = {
    		pages : pages,
    		category_id : $scope.selected_category.category_id,
    		message: $scope.post.message,
    		link_url: attachments.length == 0 && $scope.post.show_link_preview && $scope.post.link_url ? $scope.post.link_url : '',
    		link_preview: attachments.length == 0 && $scope.post.show_link_preview && $scope.post.link_url && $scope.previews[$scope.post.link_url] ? $scope.previews[$scope.post.link_url] : '',
    		attachments: attachments,
    		is_scheduled: $scope.post.is_scheduled,
    		scheduled_time: moment($scope.post.scheduled_time, 'DD MMM, GGGG hh:mm A').format('YYYY-MM-DD HH:mm:ss'),
    		should_expire: $scope.post.should_expire,
    		expiry_time: moment($scope.post.expiry_time, 'DD MMM, GGGG hh:mm A').format('YYYY-MM-DD HH:mm:ss')
    	};
        if($scope.post.moderation_status != $scope.status_approved)
            post.approve = $scope.post.approve;
    	$http.post(api_base_url + 'content/update/' + content_id, {post:post}).then(function(response){
            jQuery(window).unbind('beforeunload');
            window.location.href = response.data.message;
        }, function(){ 
            $scope.post.preloader = false;
        });
    }

    $scope.is_used_once = function()
    {
        if($scope.selected_category.category_id != '-1' || $scope.post.is_scheduled == false)
            return false;
        if($scope.selected_page_count == 0) return false;
        for(var i=0; i<$scope.pages.length; i++)
            if($scope.pages[i].selected && $scope.post.published_on.indexOf(parseInt($scope.pages[i].account_id)) === -1 )
                return false;
        return true;
    }

    $scope.is_used_once_on_some = function()
    {
        if($scope.selected_category.category_id != '-1' || $scope.post.is_scheduled == false)
            return false;
        if($scope.selected_page_count == 0 || $scope.post.published_on.length == 0) return false;
        for(var i=0; i<$scope.pages.length; i++)
            if($scope.pages[i].selected && $scope.post.published_on.indexOf(parseInt($scope.pages[i].account_id)) === -1 )
                return true;
        return false;
    }
  });
</script>
<div class="container my-5" ng-app="app" ng-controller="pageController">
	<div class="row mb-3">
		<div class="col-sm-8">
			<h4 class="page-title">Edit Content</h4>
		</div>
		<div class="col-sm-4 text-center text-sm-right">
			<a href="<?php echo site_url('content') ?>" class="btn btn-green btn-shadow" ><i class="fas fa-arrow-left"></i> Library</a>
		</div>
		<div class="col-md-12">
			<?php echo $this->session->flashdata('alert') ?>
		</div>
		<div class="col-md-12" ng-bind-html="alert">
		</div>
        <div class="col-12 mt-5 p-5 text-success" ng-show="page_preloader">
            <h1 class="text-center"> <i class="fas fa-spin fa-circle-notch"></i> </h1>
        </div>
	</div>
	<div class="row" ng-show="!page_preloader"  ng-cloak>
		<div class="col-xl-4 col-lg-4 col-md-4 mb-3">
			<div class="card air-card">
				<div class="card-body {{ pages.length == 0 ? 'p-5' : '' }}">
					<div class="row" ng-show="pages.length">
						<div class="col-12">
							<i-check ng-model="select_all">Pages ({{ selected_page_count }}/{{ pages.length }})</i-check>
						</div>
						<div class="col-12"><hr class="mt-2"></div>
					</div>

					<div class="row mb-2" ng-repeat="page in pages">
						<div class="col-10 text-truncate">
							<i-check ng-model="page.selected">
								{{ page.account_name }}
							</i-check>
						</div>
						<div class="col-2">
							<img ng-src="{{ graph_url+page.account_fb_id }}/picture?width=30&height=30&access_token={{ page.access_token }}" width="30" height="30" class="rounded-circle border float-right">
						</div>
					</div>
					<p class="text-muted text-center" ng-if="pages.length == 0">
						Import your Facebook pages to start posting<br>
						Let's go to <a href="<?php echo site_url('accounts') ?>" class="text-success">Pages</a> menu
					</p>

				</div>
			</div>
		</div>

		<div class="col-xl-8 col-lg-8 col-md-8 mb-5">
			<div class="card air-card">
				<div class="card-body">
					<div class="row">
						<div class="col-12 editor-nav">
							<a href="" class="text-muted {{ tab=='editor' ? 'active' : '' }}" ng-click="tab='editor'">Editor</a>
							<a href="" class="text-muted {{ tab=='preview' ? 'active' : '' }}" ng-click="tab='preview'">Preview</a>
						</div>
					</div>
					<hr class="mt-2">
					<div class="row" ng-show="tab=='editor'">
						<div class="col-lg-6 col-md-12 mb-3">
							<label class="font-weight-500 text-muted">Category</label>
							<a href="" class="float-right text-muted" id="btn-add-category"><i class="fas fa-plus-circle"></i> New Category</a>
							<div class="dropdown dropdown-green">
							  <a class="btn btn-default btn-block border text-left text-dark dropdown-green text-truncate  dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							    <div class="category-color-round mr-1" style="background-color: {{ selected_category.category_color  }}">&nbsp;</div>
							    {{ selected_category.category_name }}
							    <i class="fas fa-caret-down dropdown-caret"></i>
							  </a>

							  <div class="dropdown-menu w-100" aria-labelledby="dropdownMenuLink">
							  	<div class="scrollable-dropdown">
							  		<a class="dropdown-item {{ record.category_id == selected_category.category_id ? 'bg-success text-white' : '' }}" href="" ng-click="change_category(record)"  ng-repeat="record in categories">
								    	<div class="category-color-round mr-1" style="background-color: {{ record.category_color  }}">&nbsp;</div>
								    	{{ record.category_name }}
									</a>
							  	</div>
							  </div>
							</div>
							<div class="text-success mt-2 text-center d-none" id="new-category-msg">New category added in the list</div>
						</div>

                        <div class="col-12" ng-if="post.rss_id != 0 && post.link_url == post.message">
                            <div class="alert alert-info d-flex">
                                <span class="mr-2"><i class="fas fa-info-circle"></i> </span>
                                <span>Facebook requires manual input of post's text for imported content. 
                                You can copy and paste the suggested text or add your own text</span>
                            </div>
                        </div>
						<div class="col-12" ng-if="post.rss_id != 0 && post.link_url == post.message">
							<div class="form-group">
								<label>Suggested Text</label>
								<textarea class="form-control" rows="3" ng-model="post.suggested_text"></textarea>
							</div>
						</div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Post</label>
                                <textarea class="form-control" rows="8" ng-model="post.message"></textarea>
                            </div>
                        </div>
						<div class="col-12">
							<div class="row">
								<div class="col-12 col-sm-4 text-center text-sm-left mb-3">
									<file-upload class="d-inline-block" uri="{{attachment_upload_url}}" disabled="post.images.length == image_limit || post.videos.length" ng-model="post.images" multiple="true" accept="image/jpeg,image/gif,image/png" preview="true" limit="{{image_limit}}" size="{{image_size_limit}}">
									    <a id="attach-img-btn" class="mr-4 attachement-btn {{ post.images.length == image_limit || post.videos.length ? 'disabled' : 'text-success' }}" href=""><i class="fas fa-camera fa-2x"></i></a>
									</file-upload>
									<file-upload class="d-inline-block" uri="{{attachment_upload_url}}" disabled="post.videos.length == video_limit || post.images.length" ng-model="post.videos" multiple="false" accept="video/mp4,video/quicktime" limit="video_limit" size="{{video_size_limit}}">
										<a id="attach-video-btn" href="" class="attachement-btn {{ post.videos.length == video_limit || post.images.length ? 'disabled' : 'text-success' }}"><i class="fas fa-video fa-2x"></i></a>
									</file-upload>
								</div>
								<div class="col-12 col-sm-8 text-center text-sm-right mb-3" ng-if="!(post.images.length || post.videos.length) && post.link_available ">
									<div class="d-inline-block">
										<span class="d-inline-block mt-0 float-right">Enable Link Preview</span>
										<switch  ng-model="post.show_link_preview" class="green float-right mr-2"></switch>
									</div>
								</div>
							</div>
							<div class="attachment-container mb-3" ng-show="post.images.length || post.videos.length">
								<p class="text-muted mb-1" ng-if="post.images.length">{{ images_uploaded }}/{{ post.images.length }} image{{ post.images.length > 1 ? 's' : '' }} uploaded</p>
								<p class="text-muted mb-1" ng-if="post.videos.length && post.videos[0].uploaded === false">Uploading video...</p>
								<p class="text-muted mb-1" ng-if="post.videos.length && post.videos[0].uploaded === true">Video Uploaded</p>
								<div class="post-attachment" ng-repeat="img in post.images" style="background: url({{ img.preview }}); background-size: cover;" data-toggle="tooltip" data-placement="top" title="{{ img.size > image_size_limit ? 'Image will not be uploaded as size is greater than allowed size.' : '' }}">
									<a href="" class="remove-attachment text-dark" title="Remove Image" ng-click="remove_attachments(post.images, $index)"><i class="fas fa-times-circle"></i></a>
									<span ng-if="img.size > image_size_limit" class="video-icon text-danger"><i class="fas fa-exclamation-triangle fa-2x"></i></span>
									<div class="progress" ng-hide="img.percent == 0">
						                <div class="progress-bar bg-success" style="width: {{ img.percent }}%;"></div>
						            </div>
								</div>
								<div class="post-attachment" ng-repeat="video in post.videos" data-toggle="{{ video.size > video_size_limit ? 'tooltip' : '' }}" data-placement="top" title="{{ video.size > video_size_limit ? 'Video will not be uploaded as size is greater than allowed size.' : '' }}">
									<a href="" class="remove-attachment text-dark" title="Remove Video" ng-click="remove_attachments(post.videos, $index)"><i class="fas fa-times-circle"></i></a>
									<span ng-if="video.size <= video_size_limit" class="video-icon"><i class="fas fa-video fa-2x"></i></span>
									<span ng-if="video.size > video_size_limit" class="video-icon text-danger"><i class="fas fa-exclamation-triangle fa-2x"></i></span>
									<div class="progress" ng-hide="video.percent == 0">
						                <div class="progress-bar bg-success" style="width: {{ video.percent }}%;"></div>
						            </div>
								</div>
							</div>
						</div>
					</div>
					<div class="row" ng-show="tab=='preview'">
						<div class="col-12 border-bottom">
							<div class="row">
								<div class="col-md-8 offset-md-2 form-group" ng-if="pages.length">
									<label>Select page to preview</label>
						        	<div class="dropdown border dropdown-green">
									  <a class="btn text-dark  text-truncate btn-shadow btn-block dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									    <img ng-src="{{ graph_url+selected_page.account_fb_id }}/picture?width=30&height=30&access_token={{ selected_page.access_token }}" width="30" height="30" class="rounded-circle border mr-2">
									    {{ selected_page.account_name }}
									    <i class="fas fa-caret-down dropdown-caret"></i>
									  </a>

									  <div class="dropdown-menu w-100" aria-labelledby="dropdownMenuLink">
									  	<div class="scrollable-dropdown">
										    <a class="dropdown-item {{ record.account_id == selected_page.account_id ? 'bg-success text-white' : '' }}" href="" ng-click="change_page(record)"  ng-repeat="record in pages | filter:search_pages">
										    	<img ng-src="{{ graph_url+record.account_fb_id }}/picture?width=30&height=30&access_token={{ record.access_token }}" width="30" height="30" class="rounded-circle border mr-2">
										    	{{ record.account_name }}
											</a>
										</div>
									  </div>
									</div>
						        </div>
							</div>
						</div>
						<div class="col-12 bg-grey mb-3">
							<div class="py-2 px-2">
								<p class="text-muted d-flex">
									 <span><i class="fas fa-info-circle mr-1"></i> </span>
									 <span>This is an estimated preview of your post. Original may be different.</span>
								</p>
								<div class="row">
									<div class="col-12">
										<div class="px-lg-5 px-0 ">
											<div class="card border mb-3">
												<div class="card-body p-3 fb-preview">

													<div class="mb-2" ng-if="selected_page">
										    			<img ng-src="{{ graph_url+selected_page.account_fb_id }}/picture?width=40&height=40&access_token={{ selected_page.access_token }}" width="40" height="40" class="rounded-circle float-left border mr-2">
										    			<a href="https://facebook.com/{{ selected_page.account_fb_id }}" class="page-name" target="_blank">{{ selected_page.account_name }}</a>
										    			<div class="post-time"><span class="text-muted">Just now</span></div>
										    		</div>

										    		<div class="mb-2" ng-if="selected_page === false">
										    			<img ng-src="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/page-avatar.png" width="40" height="40" class="rounded-circle float-left border mr-2">
										    			<a href="https://facebook.com" class="page-name" target="_blank">Facebook Page Name</a>
										    			<div class="post-time"><span class="text-muted">Just now</span></div>
										    		</div>

										    		<p class="post-message mb-1" ng-bind-html="preview_message(post.message)">
										    		</p>

										    		<div class="mt-1 p-5 text-success" ng-if="post.show_link_preview == true && previews[post.link_url] === false">
										    			<h1 class="text-center"><i class="fas fa-spin fa-circle-notch"></i></h1>
										    		</div>
										    		<div class="border air-card link-preview" ng-if="post.show_link_preview == true && previews[post.link_url] && post.images.length == 0 && post.videos.length == 0">
										    			<a href="{{ post.link_url }}" target="_blank" >
										    				<img ng-src="{{previews[post.link_url].image}}" class="img-fluid w-100">
										    			</a>
										    			<a href="{{ post.link_url }}" class="p-2 d-block" target="_blank" >
										    				<div class="link-title">{{ previews[post.link_url].title }}</div>
										    				<div class="link-description">{{ previews[post.link_url].description }}</div>
										    				<div class="link-caption text-muted text-uppercase">{{ previews[post.link_url].url }}</div>
										    			</a>
										    		</div>

										    		<div id="preview-images">
										    			
										    		</div>


										    		<div ng-if="post.videos.length >= 1 && post.videos[0].size < video_size_limit">
										    			<video id="video-player" controlsList="nodownload" controls="" class="w-100" ng-if="post.videos[0].type == 'video/mp4'" onclick="this.paused ? this.play() : this.pause();">
										    			</video>
										    			<div class="p-5 border" ng-if="post.videos[0].type == 'video/quicktime'">
										    				<h4 class="text-muted text-center">Video preview not available</h4>
										    				<p class="text-muted text-center">.mov videos are not supported by the browser. Once your video is uploaded to Facebook, it will be converted to mp4 video</p>
										    			</div>
										    		</div>
										    		<div class="border" ng-if="post.videos[0].size > video_size_limit">
										    			<div class="p-5">
										    				<h4 class="text-muted text-center">Video size limit</h4>
										    				<p class="text-muted text-center">Please upload a video file having size less than {{ video_size_limit/(1024*1024) }}MB</p>
										    			</div>
										    		</div>

												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

					</div>
					<div class="row">
						<div class="col-12 text-center text-sm-left">
							<h5><a data-toggle="collapse" href="#schedule-settings"  class="text-success collapsed">Schedule Settings <i class="fas"></i> </a></h5>
						</div>
						<div class="col-12 collapse" id="schedule-settings">
							<div class="py-4 border-top border-bottom">
								<div class="alert alert-{{ pages.length ? 'danger' : 'info' }}" ng-if="selected_page_count == 0">
								  <i class="fas fa-info-circle"></i> 
								  <span ng-if="pages.length">Please select at least one page in order to schedule your content</span>
								  <span ng-if="pages.length == 0">Import pages to publish a post at a specific date and time </span>
								</div>
                                <div class="alert alert-danger" ng-if="is_used_once()">
                                    <i class="fas fa-exclamation-triangle"></i> Selected category is "Publish Once" and content is already published on selected page(s), Please choose different category or select more pages to schedule content for publishing
                                </div>
                                <div class="alert alert-danger" ng-if="is_used_once_on_some()">
                                    <i class="fas fa-exclamation-triangle"></i> This "Publish Once" content is already published on some selected pages and will be scheduled to only those pages where it is not published yet, Please choose a different category to schedule on all selected pages
                                </div>

								<div class="mb-2 text-info post-time-desc">
									<i class="fas fa-info-circle"></i> All times are in <?php echo $this->config->item($this->session->userdata('time_zone'), 'tzones'); ?>
								</div>
								<i-check ng-model="post.is_scheduled">Schedule</i-check>
								<p class="text-muted post-time-desc mb-1">This content will be published on selected date/time and will be re-used after that if "Publish Once" category is not selected</p>
								<div class="row">
									<div class="col-lg-6 mb-3">
										<div class="post-time-desc">
											<input type="text" class="form-control date-time-picker" name="" ng-model="post.scheduled_time" ng-disabled="!post.is_scheduled" placeholder="<?php echo date('d M, Y h:i A') ?>">
										</div>
									</div>
									<div class="col-lg-6" ng-if="post.scheduled_time_passed && post.is_scheduled">
										<p class="text-danger mb-1 post-time-desc">This time is passed. Please select a future time for scheduling</p>
									</div>
								</div>

								<i-check ng-model="post.should_expire">Set Expiry Date</i-check>
								<p class="text-muted post-time-desc mb-1">This content will not be published after selected date/time, but will not be deleted from the library and you can change this setting later</p>
								<div class="row">
									<div class="col-lg-6 mb-3">
										<div class="post-time-desc">
											<input type="text" class="form-control date-time-picker" name="" ng-model="post.expiry_time" ng-disabled="!post.should_expire" placeholder="<?php echo date('d M, Y h:i A', strtotime('+1 day')) ?>">
										</div>
									</div>
									<div class="col-lg-6" ng-if="post.expiry_time_passed && post.should_expire">
										<p class="text-danger mb-1 post-time-desc">This time is passed. Content will be not published</p>
									</div>
								</div>
							</div>
						</div>
                        <div class="col-12 mt-2">
                            <div class="alert alert-warning" ng-show="post.is_scheduled == true && post.moderation_status != status_approved && post.approve == false">
                                <i class="fas fa-exclamation-triangle"></i> Content will not be scheduled for publishing until approved
                            </div>
                        </div>
						<div class="col-12 text-center mt-3">
                            <div class="d-inline-block mr-4" ng-show="post.moderation_status != status_approved"><i-check class="d-inline-block" ng-model="post.approve">Approve</i-check></div>
							<button class="btn btn-success btn-shadow" ng-disabled="!is_valid() || post.preloader" ng-click="send_post()">Update Content <i class="fas fa-spin fa-circle-notch" ng-show="post.preloader"></i></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>


	<div class="modal fade" id="add-category" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h5 class="modal-title" id="exampleModalLongTitle">Add New Category</h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
	      <div class="modal-body">
	        <div class="row">
	        	<div class="col-12">
					<div class="form-group">
						<label>Category Name</label>
						<input type="text" class="form-control" name="category" ng-model="new_category_name" autofocus="" required="">
					</div>
					<div class="form-group">
						<div class="form-check">
							<input type="checkbox" class="form-check-input" id="include" name="include" ng-model="new_category_include" value="1" ng-true-value="1" ng-false-value="0">
							<label class="form-check-label" for="include">Include the content of this category in the random post selection</label>
						</div>
					</div>
					<button type="button" class="btn btn-success btn-shadow" ng-click="add_new_category()" ng-disabled="!new_category_name || new_category_preloader">Add Category <i class="fas fa-spin fa-circle-notch" ng-show="new_category_preloader"></i></button>
                    <a href="" class="text-success font-weight-500"  data-dismiss="modal">Cancel</a>
	        	</div>
	        </div>
	      </div>
	      
	    </div>
	  </div>
	</div>

	<div class="d-none">
		<div id="img-tooltip">
			<div class="text-center">
				<b>Upload Images</b>
			</div>	
			<ul class="list-group py-3 text-left pl-3 pr-0">
				<li>JPG, PNG, GIF images</li>
				<li>Max 3MB image size</li>
				<li>Supports up to 20 images</li>
			</ul>
		</div>
		<div id="img-tooltip-limit">
			<div class="text-center">
				<b>Limit Reached</b>
			</div>	
			<div class="py-3 text-left pl-1 pr-1">
				Facebook supports up to 20 images, Please remove some image(s) to add more
			</div>
		</div>
		<div id="img-tooltip-video">
			<div class="text-center">
				<b>Video Post</b>
			</div>	
			<div class="py-3 text-left pl-1 pr-1">
				Post can't have both video and images, Please remove the video to upload photos
			</div>
		</div>


		<div id="video-tooltip">
			<div class="text-center">
				<b>Upload Video</b>
			</div>	
			<ul class="list-group py-3 text-left pl-3 pr-0">
				<li>MP4, MOV Videos</li>
				<li>Max 512MB video size</li>
                <li>20 mins maximum duration</li>
			</ul>
		</div>
		<div id="video-tooltip-limit">
			<div class="text-center">
				<b>Limit Reached</b>
			</div>	
			<div class="py-3 text-left pl-1 pr-1">
				Post can have only one video
			</div>
		</div>
		<div id="video-tooltip-images">
			<div class="text-center">
				<b>Image Post</b>
			</div>	
			<div class="py-3 text-left pl-1 pr-1">
				Post can't have both images and video, Please remove image(s) to upload video
			</div>
		</div>
	</div>


</div>


<script type="text/javascript">
	$(function(){
		$('#btn-add-category').click(function(){
			$('#add-category').modal('show');
		});
		$('#add-category').on('shown.bs.modal', function(){
			$('#add-category input[type="text"]').focus();
		});	
	});
    function register_datetime_picker()
    {
        jQuery('.date-time-picker').each(function(){
            jQuery(this).daterangepicker({
                timePicker: true,
                applyButtonClasses : 'btn-success',
                drops: 'up',
                minDate : '<?php echo date('d/m/Y', strtotime('-1 day')) ?>',
                locale: {
                  format: 'DD MMM, GGGG hh:mm A'
                },
                 singleDatePicker: true,
                 showDropdowns: true
            });
        });
    }
</script>