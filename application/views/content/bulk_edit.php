<link rel="stylesheet" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/daterangepicker.css" />
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/moment.min.js"></script>
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/daterangepicker.js"></script>

<link href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/angular-icheck.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/angular-icheck.js"></script>

<script type="text/javascript">
  var api_base_url = '<?php echo site_url('api') ?>/';
  var angapp = angular.module('app', ['angular-icheck']);
  angapp.controller('pageController', function($scope, $http, $sce, $filter, $timeout){
  	$scope.graph_url = '<?php echo GRAPH_API_URL ?>';
    $scope.alert = '';
    $scope.page_preloader = true;
    $scope.pages = <?php echo json_encode($pages) ?>;
    $scope.categories = <?php echo json_encode($categories) ?>;
    $scope.rss_feeds = <?php echo json_encode($rss_feeds) ?>;
    $scope.post_type = 'Choose Type';
    $scope.is_published = 'Choose Status';
    $scope.is_expired = 'Choose Status';
    $scope.start_date = '';
    $scope.end_date = '';
    $scope.query = '';
    $scope.content_preloader = true;
    $scope.content = [];
    $scope.total_items = 0;
    $scope.bulk_action_preloader = false;
    $scope.master_category = '';
    for(var i=0; i<$scope.pages.length; i++)
        $scope.pages[i].apply_all = false;
    $scope.status_approved = <?php echo CONTENT_MODERATION_APPROVED ?>;

    $scope.post_text = <?php echo POST_TYPE_TEXT ?>;
    $scope.post_link = <?php echo POST_TYPE_LINK ?>;
    $scope.post_photo = <?php echo POST_TYPE_PHOTO ?>;
    $scope.post_video = <?php echo POST_TYPE_VIDEO ?>;


    $scope.categories.unshift({
    	category_id : "-1",
    	category_name : 'Use Once',
    	category_color : '#28a745'
    });
    $scope.categories.unshift({
    	category_id : "0",
    	category_name : 'General',
    	category_color : '#007bff'
    });
    $scope.categories.unshift({
    	category_id : "",
    	category_name : 'Choose Category',
    	category_color : '#ffffff'
    });

    $scope.pages.unshift({
    	account_id : false,
    	account_name : 'Choose Page'
    });
    $scope.rss_feeds.unshift({
    	rss_feed_id : false,
    	rss_feed_name : 'Choose RSS Source'
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

    $scope.change_published = function(record){
    	$scope.is_published = record;
    }

    $scope.change_expired = function(record){
    	$scope.is_expired = record;
    }

    $scope.$watch('[selected_category, selected_page, selected_rss_feed, post_type, is_published, is_expired, start_date, end_date, query, tab]', function(){  
    	$scope.content = [];
    	$scope.load_content();
    }, true);
    $scope.change_pagination = function()
    {
    	$scope.content = [];
    	$scope.load_content();
    }

    $scope.reset_filters = function()
    {
    	$scope.selected_category = $scope.categories[0];
    	$scope.selected_page = $scope.pages[0];
    	$scope.selected_rss_feed = $scope.rss_feeds[0];
    	$scope.post_type = 'Choose Type';
    	$scope.is_published = 'Choose Status';
	    $scope.is_expired = 'Choose Status';
	    $scope.start_date = '';
	    $scope.end_date = '';
	    $scope.query = '';
    }

    $scope.parse_message = function(record)
    {
    	text = record.message;
    	if(record.post_type == $scope.post_link)
    	{
    		var matches = text.match(/\bhttps?:\/\/\S+/gi);
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
    		is_published: false,
    		is_expired: false,
    		start_date: $scope.start_date == "" ? false : $scope.start_date,
    		end_date: $scope.end_date == "" ? false : $scope.end_date,
    		moderation: $scope.status_approved
    	};
    	
    	if($scope.selected_category.category_id == -1){
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

    	if($scope.is_published == 'Yes')
    		filters.is_published = 1;
    	else if($scope.is_published == 'No')
    		filters.is_published = 0;

    	if($scope.is_expired == 'Yes')
    		filters.is_expired = 1;
    	else if($scope.is_expired == 'No')
    		filters.is_expired = 0;
    	return filters;
    }

    $scope.is_filtered_view = function()
    {
        var filters = $scope.get_filters();
        delete filters.moderation;
        if(filters.category_id === '')
            filters.category_id = false;
        for(var key in filters)
        {
            if(filters[key] !== false){
                return true;
            }
        }
        
        return false;
    }

    $scope.load_content = function()
    {
    	$scope.content_preloader = true;
    	$http.post(api_base_url + 'content/bulk', {filters:$scope.get_filters()}).then(function(response){
    		$scope.content = response.data.content;
    		$scope.total_items = response.data.total;
    		angular.forEach($scope.content, function(record){
                record.update = false;
                if(record.message.length > 40)
                {
                    record.message = record.message.substring(0,40) + "...";
                }
                if(record.link_title.length > 40)
                {
                    record.link_title = record.link_title.substring(0,40) + "...";
                }
    			if(record.use_once == '1')
                    record.category_id = "-1";
                //record.category_id = parseInt(record.category_id);
                record.selected_pages = [];
    			for(var i=0; i<$scope.pages.length; i++)
                {
                    if(record.accounts.indexOf(parseInt($scope.pages[i].account_id)) !== -1)
                        record.selected_pages[i] = true;
                    else
                        record.selected_pages[i] = false;
                }
    					

    		});
            $scope.content_preloader = false;
        }, function(){ 
            $scope.content_preloader = false;
        });
    }

    $scope.$watch('[select_all]', function(){
        for(var i=0; i < $scope.content.length; i++)
            $scope.content[i].update = $scope.select_all;
    }, true);
    
    $scope.change_master_category = function()
    {
        if($scope.master_category == "") return;
        for(var i=0; i < $scope.content.length; i++)
            $scope.content[i].category_id = $scope.master_category;
    }

    $scope.change_master_page = function(page, index)
    {
        var apply_all = !page.apply_all;
        for(var i=0; i<$scope.content.length; i++)
            $scope.content[i].selected_pages[index] = apply_all;
        page.apply_all = apply_all;
    }

    $scope.any_record_selected = function()
    {
        for(var i=0; i<$scope.content.length; i++)
            if($scope.content[i].update === true)
                return true;
        return false;
    }

    $scope.delete_bulk = function()
    {
        if(!$scope.any_record_selected()) return;
        $("#delete-modal").modal('hide');
        $scope.bulk_action_preloader = true;
        var content_ids = [];
         for(var i=0; i<$scope.content.length; i++)
            if($scope.content[i].update === true)
                content_ids.push($scope.content[i].content_id);
        $http.post(api_base_url + 'content/delete_bulk', {ids:content_ids}).then(function(response){
            $scope.bulk_action_preloader = false;
            window.location.href=response.data.url;
        }, function(){
            $scope.bulk_action_preloader = false;
        })
    }

    $scope.update_bulk = function()
    {
        if(!$scope.any_record_selected()) return;
        $scope.bulk_action_preloader = true;
        var items = [];
         for(var i=0; i<$scope.content.length; i++)
            if($scope.content[i].update === true)
            {
                var accounts = [];
                for(var index = 0; index<$scope.pages.length; index++)
                {
                    if($scope.pages[index].account_id == false) continue;
                    if($scope.content[i].selected_pages[index])
                        accounts.push($scope.pages[index].account_id);
                }
                items.push({
                    content_id: $scope.content[i].content_id,
                    category_id: $scope.content[i].category_id == '-1' ? 0 : $scope.content[i].category_id,
                    use_once: $scope.content[i].category_id == '-1' ? 1 : 0,
                    accounts: accounts
                });
            }
        $http.post(api_base_url + 'content/update_bulk', {items:items}).then(function(response){
            $scope.bulk_action_preloader = false;
            window.location.href=response.data.url;
        }, function(){
            $scope.bulk_action_preloader = false;
        })
    }

  });
</script>
<div class="container my-5" ng-app="app" ng-controller="pageController">
	<div class="row">
		<div class="col-md-12">
			<?php echo $this->session->flashdata('alert') ?>
		</div>
	</div>
	<div class="row mb-md-3 mb-0">
		<div class="col-md-3 col-6 mb-2">
			<h4 class="page-title">Bulk Edit</h4>
		</div>
		<div class="col-md-9 order-3 order-md-2 mb-2">
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
				<label class="text-muted">RSS Source</label>
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
				<label class="text-muted">Previously Published</label>
				<div class="dropdown border dropdown-green">
				  <a class="btn btn-default btn-block bg-white text-left text-dark dropdown-green text-truncate dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				    {{ is_published }}
				    <i class="fas fa-caret-down dropdown-caret"></i>
				  </a>

				  <div class="dropdown-menu w-100" aria-labelledby="dropdownMenuLink">
				  	<div class="scrollable-dropdown">
					    <a class="dropdown-item {{ is_published == 'Choose Status' ? 'bg-success text-white' : '' }}" href="" ng-click="change_published('Choose Status')" >
					    	Choose Status
						</a>
						<a class="dropdown-item {{ is_published == 'Yes' ? 'bg-success text-white' : '' }}" href="" ng-click="change_published('Yes')" >
					    	Yes
						</a>
						<a class="dropdown-item {{ is_published == 'No' ? 'bg-success text-white' : '' }}" href="" ng-click="change_published('No')" >
					    	No
						</a>
					</div>
				  </div>
				</div>
			</div>
			<div class="form-group">
				<label class="text-muted">Expired</label>
				<div class="dropdown border dropdown-green">
				  <a class="btn btn-default btn-block bg-white text-left text-dark dropdown-green text-truncate dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				    {{ is_expired }}
				    <i class="fas fa-caret-down dropdown-caret"></i>
				  </a>

				  <div class="dropdown-menu w-100" aria-labelledby="dropdownMenuLink">
				  	<div class="scrollable-dropdown">
					    <a class="dropdown-item {{ is_expired == 'Choose Status' ? 'bg-success text-white' : '' }}" href="" ng-click="change_expired('Choose Status')" >
					    	Choose Status
						</a>
						<a class="dropdown-item {{ is_expired == 'Yes' ? 'bg-success text-white' : '' }}" href="" ng-click="change_expired('Yes')" >
					    	Yes
						</a>
						<a class="dropdown-item {{ is_expired == 'No' ? 'bg-success text-white' : '' }}" href="" ng-click="change_expired('No')" >
					    	No
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
			<div class="row">
				<div class="col-sm-6 mb-3 text-sm-left text-center" >
					<span class="text-muted" ng-show="!content_preloader"><span class="fz-20">{{ total_items }}</span> items</span>
				</div>
                <div class="col-sm-6 mb-3 text-sm-right text-center">
                    <a href="<?php echo site_url('content') ?>" class="btn btn-green btn-shadow" ><i class="fas fa-arrow-left"></i> Library</a>
                </div>
			</div>
            <div class="alert alert-warning" ng-if="!is_filtered_view() && !content_preloader && content.length">
                <i class="fas fa-exclamation-triangle"></i> Warning! This is your entire library. You can use the filters on left to limit the results 
            </div>
            <div class="alert alert-info" ng-if="is_filtered_view() && !content_preloader && content.length">
                <i class="fas fa-info-circle"></i> This is filtered view of your library. <a href="" class="text-success"  ng-click="reset_filters()">Click here to remove filter</a> 
            </div>
			<div class="p-5 text-center text-success" ng-if="content_preloader">
				<h1><i class="fas fa-spin fa-circle-notch"></i></h1>
			</div>
			<div class="p-5 text-center text-muted" ng-if="!content_preloader && content.length == 0">
				<h3>No content found</h3>
			</div>

            <div class="table-responsive-md bg-white" ng-show="content.length">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th class="font-weight-500">Update?</th>
                      <th class="font-weight-500">Category</th>
                      <th class="font-weight-500">Content</th>
                      <th class="font-weight-500">Accounts</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="bg-grey">
                      <th>
                        <i-check ng-model="select_all">
                        </i-check>
                      </th>
                      <td>
                          <select class="form-control" ng-model="master_category" ng-change="change_master_category()">
                                <option ng-if="category.category_id != ''" ng-repeat="category in categories" value="{{category.category_id}}">
                                    {{ category.category_name }}
                                </option>
                          </select>
                      </td>
                      <td></td>
                      <td>
                         <a href="" ng-click="change_master_page(page, $index)" ng-if="page.account_id" target="_blank" data-toggle="tooltip" title="{{ page.account_name }}"  ng-repeat="page in pages"><img ng-src="{{ graph_url+page.account_fb_id }}/picture?width=30&height=30&access_token={{ page.access_token }}" width="30" height="30" class="rounded-circle border mr-1 mb-1 {{ page.apply_all === false ? 'disabled-img' : '' }}"></a>
                      </td>
                    </tr>
                    <tr ng-repeat="record in content">
                      <th>
                        <i-check ng-model="record.update">
                        </i-check>
                      </th>
                      <td>
                        <select class="form-control" ng-model="record.category_id">
                              <option ng-if="category.category_id != ''" ng-repeat="category in categories" value="{{category.category_id}}" ng-selected="{{record.category_id == category.category_id}}">
                                  {{ category.category_name }}
                              </option>
                        </select>
                      </td>
                      <td>
                          <i data-toggle="tooltip" title="Text post"  class="fas fa-edit" ng-if="record.post_type == post_text"></i>
                          <i data-toggle="tooltip" title="Link post"  class="fas fa-link" ng-if="record.post_type == post_link"></i>
                          <i data-toggle="tooltip" title="Photo post"  class="fas fa-image" ng-if="record.post_type == post_photo"></i>
                          <i data-toggle="tooltip" title="Video post"  class="fas fa-video" ng-if="record.post_type == post_video"></i>
                          <span ng-if="record.message != '' && record.link_title == ''">{{ record.message }}</span>
                          <span ng-if="record.post_type == post_link && record.link_title != ''">{{ record.link_title }}</span>
                      </td>
                      <td>
                         <a href="" ng-click="record.selected_pages[$index] = !record.selected_pages[$index]" ng-if="page.account_id" target="_blank" data-toggle="tooltip" title="{{ page.account_name }}"  ng-repeat="page in pages"><img ng-src="{{ graph_url+page.account_fb_id }}/picture?width=30&height=30&access_token={{ page.access_token }}" width="30" height="30" class="rounded-circle border mr-1 mb-1 {{ record.selected_pages[$index] === false ? 'disabled-img' : '' }}"></a>
                      </td>
                    </tr>
                  </tbody>
                </table>
            </div>

			<div class="text-center" ng-show="content.length">
                <button class="btn btn-danger btn-shadow" data-toggle="modal" data-target="#delete-modal"  ng-disabled="!any_record_selected() || bulk_action_preloader">Delete Selected</button>
                <button class="btn btn-success btn-shadow" ng-click="update_bulk()"  ng-disabled="!any_record_selected() || bulk_action_preloader">Update Selected</button>
                <i class="fas fa-spin fa-circle-notch text-success" ng-show="bulk_action_preloader"></i>
            </div>

		</div>
	</div>


    <div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle">Delete Selected Content</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p>Attachments and history of selected content will be deleted. Are you sure you want to delete selected content?</p>
          </div>
          <div class="modal-footer">
            <a href="" class="text-success font-weight-500" data-dismiss="modal">Cancel</a>
            <a href="#" class="btn btn-danger btn-shadow" ng-click="delete_bulk()">Delete</a>
          </div>
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
