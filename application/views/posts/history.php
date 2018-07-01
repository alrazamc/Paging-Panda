<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/moment.min.js"></script>

<link rel="stylesheet" href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/lightbox.min.css" />
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/lightbox.min.js"></script>
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
  var angapp = angular.module('app', []);
  angapp.filter('shorten', function() {
    return function(input) {
      if(typeof input === 'undefined') return '';
      input = parseInt(input);
      return nFormatter(input, 0);
    };
  });
  angapp.filter('tohour', function() {
    return function(input) {
      input = parseInt(input);
      input = input/(1000*60*60);
      return nFormatter(input, 1);
    };
  });
  angapp.controller('pageController', function($scope, $http, $sce, $filter, $timeout){
  	$scope.graph_url = '<?php echo GRAPH_API_URL ?>';
    $scope.alert = '';
    $scope.page_preloader = true;
    $scope.pages = <?php echo json_encode($pages) ?>;
    $scope.content = <?php echo json_encode($content) ?>;
    $scope.posts = <?php echo json_encode($posts) ?>;

    $scope.post_text = <?php echo POST_TYPE_TEXT ?>;
    $scope.post_link = <?php echo POST_TYPE_LINK ?>;
    $scope.post_photo = <?php echo POST_TYPE_PHOTO ?>;
    $scope.post_video = <?php echo POST_TYPE_VIDEO ?>;

    if($scope.content.category_id == -1)
        $scope.content.category = {
        	category_id : -1,
        	category_name : 'Publish Once',
        	category_color : '#28a745'
        };
    else if($scope.content.category_id == 0)
        $scope.content.category = {
        	category_id : 0,
        	category_name : 'General',
        	category_color : '#007bff'
        };
    $scope.posts_by_ids = {};
    for(var i=0; i<$scope.posts.length; i++)
    {
        var ptime = moment($scope.posts[i].scheduled_time);
        if(zone_op == 'plus')
            ptime.add(interval_seconds, 's');
        else if(zone_op == 'minus')
            ptime.subtract(interval_seconds, 's');
        $scope.posts[i].date = ptime.format('D MMM');
        $scope.posts[i].time = ptime.format('h:mm A');
        $scope.posts[i].page = false;
        $scope.posts[i].preloader = true;
        for(var j=0; j<$scope.pages.length; j++)
        {
            if($scope.pages[j].account_id == $scope.posts[i].account_id)
            {
                $scope.posts[i].page = $scope.pages[j];
                break;
            }
        }
        $scope.posts_by_ids[ $scope.posts[i].post_fb_id ] = $scope.posts[i];
    }    
    
    if($scope.content.post_type == $scope.post_video)
    {
        $scope.content.attachments[0] = $sce.trustAsResourceUrl($scope.content.attachments[0]);
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
    	return $sce.trustAsHtml(text);
    }

    $scope.stats = {
        reactions: 0,
        comments: 0,
        shares: 0,
        impressions: 0,
        clicks: 0,
        views: 0,
        view_time: 0,
        engaged: 0,
        nfeedback: 0,
        nfeedback_type: {
            hide_clicks: 0,
            hide_all_clicks: 0,
            report_spam_clicks: 0,
            unlike_page_clicks : 0
        },
        reactions_types:{
            like: 0,
            love: 0,
            wow: 0,
            haha: 0,
            sorry: 0,
            anger: 0
        }
    }

    $scope.batch = [];
    $scope.current_index = 0;
    $scope.gapi_fields = "id,reactions.limit(1).summary(true),comments.limit(1).summary(true),shares,insights.metric(post_impressions_unique,post_engaged_users,post_clicks_by_type,post_video_views,post_video_view_time,post_negative_feedback,post_negative_feedback_by_type,post_reactions_by_type_total).period(lifetime){values}";
    $scope.process_batch = function()
    {
        $scope.batch = [];
        while($scope.current_index < $scope.posts.length && $scope.batch.length < 50)
        {
            $scope.batch.push({
                method: "GET",
                relative_url: $scope.posts[$scope.current_index].post_fb_id + '/?access_token='+ $scope.posts[$scope.current_index].page.access_token +'&fields=' + $scope.gapi_fields
            });
            $scope.current_index++;
        }
        if($scope.batch.length == 0){
            $scope.batch_preloader = false;
            return;
        }
        $scope.batch_preloader = true;
        $http.post($scope.graph_url, {batch: $scope.batch, access_token: $scope.pages[0].access_token}).then(function(response){
            var batch = response.data;
            for(var i=0; i<batch.length; i++)
            {
                if(batch[i].code != 200) continue;
                var body = JSON.parse(batch[i].body);
                if(body.error) continue;
                var post = $scope.posts_by_ids[ body.id ];
                post.reactions = body.reactions.summary.total_count;
                post.comments = body.comments.summary.total_count;
                post.shares = typeof body.shares !== 'undefined' ? body.shares.count : 0;
                post.impressions =   body.insights.data[0].values[0].value;
                post.engaged = body.insights.data[1].values[0].value;
                if( body.insights.data[2].values[0].value['link clicks'] )
                    post.clicks = body.insights.data[2].values[0].value['link clicks'];
                else
                    post.clicks = 0;
                post.views = body.insights.data[3].values[0].value;
                post.view_time = body.insights.data[4].values[0].value;
                post.nfeedback = body.insights.data[5].values[0].value;
                post.nfeedback_type = body.insights.data[6].values[0].value;
                post.reactions_types = body.insights.data[7].values[0].value;

                $scope.stats.reactions += post.reactions;
                $scope.stats.comments += post.comments;
                $scope.stats.shares += post.shares;
                $scope.stats.impressions += post.impressions;
                $scope.stats.engaged += post.engaged;
                $scope.stats.clicks += post.clicks;
                $scope.stats.views += post.views;
                $scope.stats.view_time += post.view_time;
                $scope.stats.nfeedback += post.nfeedback;

                for(var key in $scope.stats.nfeedback_type)
                    if(post.nfeedback_type[key])
                        $scope.stats.nfeedback_type[key] += post.nfeedback_type[key];
                for(var key in $scope.stats.reactions_types)
                    if(post.reactions_types[key])
                        $scope.stats.reactions_types[key] += post.reactions_types[key];
                post.preloader = false;
            }

            $scope.process_batch();
        }, function(){
            $scope.process_batch();
        });
    }
    $scope.process_batch();

  });
</script>
<div class="container my-5" ng-app="app" ng-controller="pageController">
	<div class="row">
		<div class="col-md-12">
			<?php echo $this->session->flashdata('alert') ?>
		</div>
	</div>
	<div class="row mb-md-3 mb-0">
		<div class="col-12 mb-2">
			<h4 class="page-title">Content History</h4>
		</div>
	</div>
	<div class="row" ng-cloak>
		<div class="col-md-6">
            <div class="card border mb-3">
                <div class="card-header pl-0 pb-0">
                    <div class="row">
                        <div class="col-sm-6 col-md-12 col-lg-5 mb-2">
                            <div class="font-weight-500 text-white text-center px-2 category-color-label mr-1" style="background-color: {{ content.category.category_color  }}">{{ content.category.category_name }}</div>
                        </div>
                        <div class="col-sm-6 col-md-12 col-lg-7 mb-2 text-center text-sm-right text-md-center text-lg-right">
                            <a href="<?php echo site_url("content/delete/$content->content_id") ?>" class="btn px-2 btn-flat btn-outline-danger delete-record">Delete</a>
                            <a href="<?php echo site_url("content/edit/$content->content_id") ?>" class="btn px-2 btn-flat btn-outline-success">Edit</a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3 fb-preview">
                    <div class="mb-2">
                        <img ng-src="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/page-avatar.png" width="40" height="40" class="rounded-circle float-left border mr-2">
                        <a href="" class="page-name" target="_blank">Facebook Page Name</a>
                        <div class="post-time"><span class="text-muted">Just now</span></div>
                    </div>

                    <p class="post-message mb-1" ng-bind-html="parse_message(content)">
                    </p>

                    <div class="border air-card link-preview" ng-if="content.post_type == post_link && content.link_title != '' && content.link_description != ''" >
                        <a href="{{ content.link_url }}" target="_blank" ng-if="content.link_image != ''" >
                            <img ng-src="{{content.link_image}}" class="img-fluid w-100">
                        </a>
                        <a href="{{ content.link_url }}" class="p-2 d-block" target="_blank" >
                            <div class="link-title">{{ content.link_title }}</div>
                            <div class="link-description">{{ content.link_description }}</div>
                            <div class="link-caption text-muted text-uppercase">{{ content.link_url }}</div>
                        </a>
                    </div>
                    <div class="link-preview p-2 bg-grey" ng-if="content.post_type == post_link && content.link_title == '' && content.link_description == ''">
                        <div class="link-caption fz-14 text-muted" >
                            <div class="fz-18"><i class="far fa-image"></i> Facebook Link Preview</div>
                            Facebook will display a link preview for <a href="{{ content.link_url}}" target="_blank" class="">{{ content.link_url }}</a> when this post is published. You can see the preview by clicking the <a href="<?php echo site_url('content/edit') ?>/{{ content.content_id }}"><b>Edit</b></a> button
                        </div>
                    </div>

                    <a href="{{ content.attachments[0] }}" ng-if="content.attachments.length && content.post_type == post_photo" data-lightbox="content-{{content.content_id }}">
                        <div class="position-relative">
                            <img ng-src="{{content.attachments[0]}}" class="img-fluid w-100">
                            <span ng-if="content.attachments.length - 1 > 0" class="plus-more">+{{ content.attachments.length - 1 }}</span>
                        </div>
                    </a>

                    <div class="d-none">
                        <a href="{{ attachment }}" ng-if="!$first" ng-repeat="attachment in content.attachments"  data-lightbox="content-{{content.content_id }}"></a>
                    </div>


                    <div ng-if="content.post_type == post_video">
                        <video id="video-player" controls="" class="w-100" ng-if="content.video_mime == 'video/mp4'" onclick="this.paused ? this.play() : this.pause();">
                            <source ng-src="{{ content.attachments[0] }}" type="video/mp4">
                        </video>
                        <div class="p-5 border" ng-if="content.video_mime == 'video/quicktime'">
                            <h4 class="text-muted text-center">Video preview not available</h4>
                            <p class="text-muted text-center">.mov videos are not supported by browser. Once your video is uploaded to Facebook, it will be converted to mp4 video</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer" ng-if="posts.length">
                    <div class="row text-center">
                        <div class="col-4">
                            <i class="far fa-thumbs-up fa-lg"></i>
                            {{ stats.reactions | shorten }}
                        </div>
                        <div class="col-4">
                            <i class="far fa-comment fa-lg"></i>
                            {{ stats.comments | shorten }}
                        </div>
                        <div class="col-4">
                            <i class="fas fa-share fa-lg"></i>
                            {{ stats.shares | shorten }}
                            <i class="fas fa-spin fa-circle-notch float-right text-success" ng-if="batch_preloader"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6" ng-if="posts.length">
            <div class="row mb-4">
                <div class="col-12 mb-3 text-muted" ng-if="posts.length > 1">
                    <i class="fas fa-info-circle"></i> Showing data from {{ posts.length }} posts published in last 30 days
                    <i class="fas fa-spin fa-circle-notch fa-lg float-right text-success" ng-if="batch_preloader"></i>
                </div>
                <div class="col-2">
                    <div class="reaction text-center">
                        <img src="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/like.png">
                        <div class="count">{{ stats.reactions_types.like | shorten }}</div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="reaction text-center">
                        <img src="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/love.png">
                        <div class="count">{{ stats.reactions_types.love | shorten }}</div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="reaction text-center">
                        <img src="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/haha.png">
                        <div class="count">{{ stats.reactions_types.haha | shorten }}</div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="reaction text-center">
                        <img src="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/wow.png">
                        <div class="count">{{ stats.reactions_types.wow | shorten }}</div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="reaction text-center">
                        <img src="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/sorry.png">
                        <div class="count">{{ stats.reactions_types.sorry | shorten }}</div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="reaction text-center">
                        <img src="<?php echo getenv('ASSET_BASE_URL') ?>assets/images/anger.png">
                        <div class="count">{{ stats.reactions_types.anger | shorten }}</div>
                    </div>
                </div>
            </div>
            <div class="row">

                <div class="col-12 mb-2">
                   <span class="fz-24">{{ stats.impressions | shorten }}</span> <span class="ml-1 text-muted">people reached </span>
                   <a href="" title="The number of people who had your post enter their screen"  data-toggle="tooltip" class="text-muted"><i class="fas fa-info-circle fa-sm"></i> </a>
                </div>

                <div class="col-12 mb-2">
                   <span class="fz-24">{{ stats.engaged | shorten }}</span> <span class="ml-1 text-muted">people engaged </span>
                   <a href="" title="The number of people who clicked anywhere in your post"  data-toggle="tooltip" class="text-muted"><i class="fas fa-info-circle fa-sm"></i> </a>
                </div>

                <div class="col-12 mb-2" ng-if="content.post_type == post_link">
                   <span class="fz-24">{{ stats.clicks | shorten }}</span> <span class="ml-1 text-muted">link clicks </span>
                   <a href="" title="The number of clicks on links in post"  data-toggle="tooltip" class="text-muted"><i class="fas fa-info-circle fa-sm"></i> </a>
                </div>

                <div class="col-12 mb-2" ng-if="content.post_type == post_video">
                   <span class="fz-24">{{ stats.views | shorten }}</span> <span class="ml-1 text-muted">video views </span>
                   <a href="" title="The number of times your video was watched"  data-toggle="tooltip" class="text-muted"><i class="fas fa-info-circle fa-sm"></i> </a>
                </div>

                <div class="col-12 mb-2" ng-if="content.post_type == post_video">
                   <span class="fz-24">{{ stats.view_time | tohour }}</span> <span class="ml-1 text-muted"> hour(s) video watch time </span>
                   <a href="" title="The total number of hours your video was watched"  data-toggle="tooltip" class="text-muted"><i class="fas fa-info-circle fa-sm"></i> </a>
                </div>

                <div class="col-12 mb-2">
                   <span class="fz-24">{{ stats.nfeedback | shorten }}</span> <span class="ml-1 text-muted"> negative feeback </span>
                   <a href="" title="The number of times people took a negative action in your post"  data-toggle="tooltip" class="text-muted"><i class="fas fa-info-circle fa-sm"></i> </a>
                </div>

            </div>
            <div class="row">
                <div class="col-12 mb-3 text-muted">
                    Negative feedback clicks by type
                </div>
                <div class="col-3 text-center">
                    <div class="fz-24">{{ stats.nfeedback_type.hide_clicks | shorten  }}</div>
                    <span class="text-muted">Hide This</span>
                </div>
                <div class="col-3 text-center">
                    <div class="fz-24">{{ stats.nfeedback_type.hide_all_clicks | shorten  }}</div>
                    <span class="text-muted">Hide All</span>
                </div>
                <div class="col-3 text-center">
                    <div class="fz-24">{{ stats.nfeedback_type.report_spam_clicks | shorten  }}</div>
                    <span class="text-muted">Report Spam</span>
                </div>
                <div class="col-3 text-center">
                    <div class="fz-24">{{ stats.nfeedback_type.unlike_page_clicks | shorten  }}</div>
                    <span class="text-muted">Unlike Page</span>
                </div>
            </div>
        </div>
        <div class="col-md-12" ng-if="posts.length == 0">
            <div class="py-5 text-center text-muted" >
                <h5>No posts published using this content in last 30 days</h5>
            </div>
        </div>
        <?php if($this->uri->segment(4)){ ?>
        <div class="col-md-12 text-center text-md-right">
            <a href="<?php echo site_url("posts/history/$content->content_id") ?>"  class="btn btn-outline-success">See All Posts</a>
        </div>
        <?php } ?>
        <div class="col-12 mt-2" ng-if="posts.length">
            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Published On</th>
                            <th>Insights</th>
                            <th>Reach</th>
                            <th>Engagment</th>
                            <th ng-if="content.post_type == post_link">Clicks</th>
                            <th ng-if="content.post_type == post_video">Video</th>
                            <th>-ve Feedback</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="post in posts">
                            <td>
                                <div>{{ post.date }}</div>
                                <div>{{ post.time }}</div>
                            </td>
                            <td>
                                <a href="https://facebook.com/{{ post.post_fb_id }}" target="_blank" class="text-success">{{ post.page.account_name }}</a>
                            </td>
                            <td>
                                <div ng-show="!post.preloader">
                                    <span><i class="far fa-thumbs-up"></i> {{ post.reactions | shorten }}</span>
                                    <span><i class="far fa-comment"></i> {{ post.comments | shorten }}</span><br>
                                    <span><i class="fas fa-share"></i> {{ post.shares | shorten }}</span>
                                </div>
                            </td>
                            <td>
                               <span ng-show="!post.preloader"> {{ post.impressions | shorten }} </span>
                            </td>
                            <td>
                                <span ng-show="!post.preloader"> {{ post.engaged | shorten }} </span>
                            </td>
                            <td ng-if="content.post_type == post_link">
                                <span ng-show="!post.preloader"> {{ post.clicks | shorten }} </span>
                            </td>
                            <td ng-if="content.post_type == post_video">
                                <div ng-show="!post.preloader">
                                    <div>{{ post.views | shorten }} <span class="text-muted">views</span></div>
                                    {{ post.view_time | tohour }} <span class="text-muted">hours</span> <a href="" title="The total number of hours your video was watched"  data-toggle="tooltip" class="text-muted"><i class="fas fa-info-circle fa-sm"></i> </a>
                                </div>
                            </td>
                            <td>
                               <span ng-show="!post.preloader">  {{ post.nfeedback | shorten }} </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
	</div>

</div>

<div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Delete Content</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Attachments and history of this content will be deleted. Are you sure you want to delete this content?</p>
      </div>
      <div class="modal-footer">
        <a href="" class="text-success font-weight-500" data-dismiss="modal">Cancel</a>
        <a href="#" class="btn btn-danger btn-shadow">Delete</a>
      </div>
    </div>
  </div>
</div>