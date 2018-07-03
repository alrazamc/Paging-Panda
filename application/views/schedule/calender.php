<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/moment.min.js"></script>

<link href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/angular-icheck.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/angular-icheck.js"></script>

<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/bootstrap-timepicker.js"></script>


<script type="text/javascript">
  var api_base_url = '<?php echo site_url('api') ?>/';
  var poster_url = '<?php echo getenv('ASSET_BASE_URL') ?>assets/images/video-preloader.gif';
  var angapp = angular.module('app', ['angular-icheck']);

  <?php date_default_timezone_set($this->session->userdata('time_zone')); ?>
    var interval_seconds = <?php echo abs(date('Z')) ?>;
    <?php if(date('Z') < 0){ ?>
    var zone_op = 'minus';
    <?php }else{ ?>
    var zone_op = 'plus';
    <?php } ?>          
  angapp.controller('pageController', function($scope, $http, $sce, $filter, $timeout){
  	$scope.graph_url = '<?php echo GRAPH_API_URL ?>';
    $scope.alert = '';
    $scope.page_preloader = true;
    $scope.pages = <?php echo json_encode($pages) ?>;
    $scope.categories = <?php echo json_encode($categories) ?>;
    $scope.schedules = <?php echo json_encode($schedules) ?>;
    $scope.slots = [];
    $scope.page_by_id = {};
    for(var i=0; i < $scope.pages.length; i++)
    {
        $scope.page_by_id[ $scope.pages[i].account_id ] =  $scope.pages[i];
        $scope.pages[i].selected = false;
    }

    $scope.select_all = true;

    $scope.categories.unshift({
    	category_id : '-1',
    	category_name : 'Publish Once',
    	category_color : '#28a745'
    });
    $scope.categories.unshift({
    	category_id : '0',
    	category_name : 'General',
    	category_color : '#007bff'
    });
    $scope.categories.unshift({
    	category_id : false,
    	category_name : 'Random',
    	category_color : '#6c757d'
    });
    $scope.selected_category = $scope.categories[0];

    $scope.change_category = function(record){
        $scope.selected_category = record;
    }

    $scope.filter_pages = angular.copy($scope.pages);
    $scope.filter_categories = angular.copy($scope.categories);

    $scope.filter_categories.unshift({
        category_id : '',
        category_name : 'All Categories',
        category_color : '#ffffff'
    });

    $scope.filter_pages.unshift({
        account_id : '',
        account_name : 'All Pages'
    });

    $scope.selected_filter_category = $scope.filter_categories[0];
    $scope.selected_filter_page = $scope.filter_pages[0];
    
    $scope.change_filter_category = function(record){
        $scope.selected_filter_category = record;
    }

    $scope.change_filter_page = function(record){
        $scope.selected_filter_page = record;
    }
    $scope.clear_filters = function(){
        $scope.selected_filter_category = $scope.filter_categories[0];
        $scope.selected_filter_page = $scope.filter_pages[0];
    }

    $scope.$watch('[selected_filter_category, selected_filter_page, slots]', function(){
        for(var i=0; i < $scope.pages.length; i++)
        {
            if($scope.selected_filter_page.account_id === '' || $scope.selected_filter_page.account_id == $scope.pages[i].account_id)
                $scope.pages[i].show = true;
            else
                $scope.pages[i].show = false;
        }
        for(var i=0; i<$scope.slots.length; i++)
        {
            for(var j=0; j<$scope.slots[i].days.length; j++)
            {
                for(var k=0; k<$scope.slots[i].days[j].schedules.length; k++)
                {
                    var slot = $scope.slots[i].days[j].schedules[k];
                    if($scope.selected_filter_category.category_id === '' || $scope.selected_filter_category.category_id === slot.category.category_id)
                        slot.show = true;
                    else
                        slot.show = false;
                    if(slot.show)
                    {
                        var showing_pages = false;
                        for(var m=0; m<slot.pages.length; m++)
                        {
                            if(slot.pages[m].show){
                                showing_pages = true;
                                break;
                            }
                        }
                        if(!showing_pages)
                            slot.show = false;
                    }

                }    
            }

        }
    }, true);

    $scope.$watch('[select_all]', function(){
        for(var i=0; i < $scope.pages.length; i++)
            $scope.pages[i].selected = $scope.select_all;
    }, true);

    $scope.$watch('[pages]', function(){
        var selected_page_count = 0;
        for(var i=0; i < $scope.pages.length; i++)
            if($scope.pages[i].selected)
                selected_page_count++;
        $scope.selected_page_count = selected_page_count;
    }, true);

    for(var i=0; i<=23; i++)
    {
        $scope.slots[i] = {
            hour: i,
            moment: moment().hours(i),
            text: moment().hours(i).format('h A'),
            days: []
        };
        for(var j=0; j<7; j++)
            $scope.slots[i].days.push({
                day:j,
                moment: moment().day(j),
                text: moment().day(j).format('dddd'),
                schedules: []
            });
    }

    $scope.new_slot = function(hour, day)
    {
        $scope.modal_preloader = false;
        $scope.modal_title = "Add New Slot";
        $scope.modal_schedule_id = false;
        $scope.modal_day = day ? day.day.toString() : '0';
        $scope.modal_time = hour ? hour.moment.format('h:00 A') : '8:00 PM';
        $scope.selected_category = $scope.categories[0];
        $scope.modal_slot = {};
        $scope.select_all = false;
        for(var i=0; i<$scope.pages.length; i++)
            $scope.pages[i].selected = false;

        $('.timepick').timepicker({
            minuteStep : 1,
            defaultTime : $scope.modal_time
          });
        $('#slot-modal').modal('show');
    }

    $scope.update_slot = function($event, hour, day, slot)
    {
        $event.stopPropagation();
        $scope.modal_preloader = false;
        $scope.modal_title = "Update Slot";
        $scope.modal_day = slot.day;
        $scope.modal_time = slot.moment.format('h:mm A');
        $scope.selected_category = slot.category;
        $scope.modal_slot = slot;
        $scope.select_all = false;
        for(var i=0; i<$scope.pages.length; i++)
            $scope.pages[i].selected = false;
        for(var i=0; i<$scope.modal_slot.pages.length; i++)
            $scope.modal_slot.pages[i].selected = true;

        $('.timepick').timepicker({
            minuteStep : 1,
            defaultTime : $scope.modal_time
          });
        $('#slot-modal').modal('show');
    }

    $scope.save_slot = function()
    {
        if($scope.selected_page_count == 0) return;
        $scope.modal_preloader = true;
        $scope.modal_slot.day = $scope.modal_day;
        var time = moment( $scope.modal_day+' '+$scope.modal_time, 'e h:mm A');
        $scope.modal_slot.time = time.format('HH:mm:ss');
        $scope.modal_slot.moment = time;
        if($scope.selected_category.category_id === false)
        {
            $scope.modal_slot.category_id = 0;
            $scope.modal_slot.use_once = 0;
            $scope.modal_slot.use_random = 1;
        }else if($scope.selected_category.category_id == '-1')
        {
            $scope.modal_slot.category_id = 0;
            $scope.modal_slot.use_once = 1;
            $scope.modal_slot.use_random = 0;
        }else
        {
            $scope.modal_slot.category_id = $scope.selected_category.category_id;
            $scope.modal_slot.use_once = 0;
            $scope.modal_slot.use_random = 0;
        }
        var pages = [];
        for(var i=0; i<$scope.pages.length; i++)
            if($scope.pages[i].selected)
                pages.push($scope.pages[i].account_id);
        $scope.modal_slot.accounts = pages;
        var modal_slot = angular.copy($scope.modal_slot);
        var slot = angular.copy($scope.modal_slot);

        var tz_moment = moment.unix( slot.moment.unix() );
         if(zone_op == 'minus')
            tz_moment.add(interval_seconds, 's');
        else if(zone_op == 'plus')
             tz_moment.subtract(interval_seconds, 's');
        slot.day = tz_moment.format('d');
        slot.time = tz_moment.format('HH:mm:ss');

        delete slot.moment;
        delete slot.pages;
        delete slot.category;
        delete slot.text;
        $http.post(api_base_url + 'schedule/save', {slot: slot}).then(function(response){
            if(response.data != 'true')
            {
                modal_slot.schedule_id = response.data;
            }
            $scope.remove_slot(modal_slot);
            $scope.place_slot(modal_slot);            
            $scope.modal_preloader = false;
            $('#slot-modal').modal('hide');
        }, function(){

        });
    }

    $scope.delete_slot = function()
    {
        if(typeof $scope.modal_slot.schedule_id === 'undefined') return;
        $scope.delete_preloader = true;
        var modal_slot = angular.copy($scope.modal_slot);
        $http.post(api_base_url + 'schedule/remove', {schedule_id: $scope.modal_slot.schedule_id}).then(function(response){
            if(response.data == 'true')
            {
                $scope.remove_slot(modal_slot);
                $scope.delete_preloader = false;
                $('#slot-modal').modal('hide');
            }
        }, function(){

        });
    }



    $scope.remove_slot = function(slot)
    {
        for(var i=0; i<$scope.slots.length; i++)
        {
            for(var j=0; j<$scope.slots[i].days.length; j++)
            {
                for(var k=0; k<$scope.slots[i].days[j].schedules.length; k++)
                {
                    if(slot.schedule_id == $scope.slots[i].days[j].schedules[k].schedule_id)
                        $scope.slots[i].days[j].schedules.splice(k, 1);
                }    
            }
        }
    }

    $scope.place_slot = function(slot)
    {
        if(typeof slot.moment === 'undefined')
        {
            slot.moment = moment(slot.time, 'HH:mm:ss');
        }
        slot.text = slot.moment.format('h:mm A');
        if(slot.use_random == 1)
            slot.category = $scope.categories[0];
        else if(slot.use_once == 1)
            slot.category = $scope.categories[2];
        else if(slot.category_id == 0)
            slot.category = $scope.categories[1];
        else
        {
            for(var i=0; i<$scope.categories.length; i++)
                if(parseInt(slot.category_id) === parseInt($scope.categories[i].category_id))
                    slot.category = $scope.categories[i];
        }
        slot.pages = [];
        for(var i=0; i<slot.accounts.length; i++)
            slot.pages.push(  $scope.page_by_id[ slot.accounts[i] ]);
        $scope.slots[ slot.moment.hour() ].days[ slot.day ].schedules.push(slot);
    }

    for(var i=0; i < $scope.schedules.length; i++)
    {
        $scope.schedules[i].moment = moment($scope.schedules[i].day + ' '+ $scope.schedules[i].time, 'e HH:mm:ss');
        if(zone_op == 'minus')
            $scope.schedules[i].moment.subtract(interval_seconds, 's');
        else if(zone_op == 'plus')
             $scope.schedules[i].moment.add(interval_seconds, 's');
        $scope.schedules[i].day = $scope.schedules[i].moment.format('d');
        $scope.place_slot($scope.schedules[i]);
    }
  });
</script>
<div class="container-fluid" ng-app="app" ng-controller="pageController">
	<div class="row mb-0 mt-4" id="calender-nav">
		<div class="col-lg-5 text-center text-lg-left col-12 mb-2">
			<h4 class="page-title d-sm-inline-block d-block">
                Weekly Schedule
            </h4>
            <span class="text-muted"><?php echo $this->config->item($this->session->userdata('time_zone'), 'tzones') ?></span>
		</div>
        <div class="col-lg-7 col-12 text-center text-lg-right mb-2" ng-cloak>
            <div class="row">
                <div class="col-md-3 col-sm-6 pt-2">
                    <a href="" ng-show="selected_filter_category.category_id !== '' || selected_filter_page.account_id !== ''" class="text-success font-weight-500 mr-3" ng-click="clear_filters()">Clear Filters</a>
                    <span ng-show="selected_filter_category.category_id === '' && selected_filter_page.account_id === ''" class="text-success font-weight-500 mr-3" >Filters</a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="dropdown border dropdown-green mb-2">
                      <a class="btn btn-default bg-white w-100 text-left text-dark dropdown-green text-truncate  dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div ng-if="selected_filter_category.category_id !== ''" class="category-color-round mr-1" style="background-color: {{ selected_filter_category.category_color  }}">&nbsp;</div>
                        {{ selected_filter_category.category_name }}
                        <i class="fas fa-caret-down dropdown-caret"></i>
                      </a>

                      <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
                        <div class="scrollable-dropdown">
                            <a class="dropdown-item {{ record.category_id === selected_filter_category.category_id ? 'bg-success text-white' : '' }}" href="" ng-click="change_filter_category(record)"  ng-repeat="record in filter_categories">
                                <div ng-if="record.category_id !== ''" class="category-color-round mr-1" style="background-color: {{ record.category_color  }}">&nbsp;</div>
                                {{ record.category_name }}
                            </a>
                        </div>
                      </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="dropdown border dropdown-green mb-2">
                      <a class="btn btn-default w-100 bg-white text-left text-dark dropdown-green text-truncate dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img ng-if="selected_filter_page.account_id" ng-src="{{ graph_url+selected_filter_page.account_fb_id }}/picture?width=30&height=30&access_token={{ selected_filter_page.access_token }}" width="20" height="20" class="rounded-circle border mr-2">
                        {{ selected_filter_page.account_name }}
                        <i class="fas fa-caret-down dropdown-caret"></i>
                      </a>

                      <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
                        <div class="scrollable-dropdown">
                            <a class="dropdown-item {{ record.account_id == selected_filter_page.account_id ? 'bg-success text-white' : '' }}" href="" ng-click="change_filter_page(record)"  ng-repeat="record in filter_pages">
                                <img ng-if="record.account_id" ng-src="{{ graph_url+record.account_fb_id }}/picture?width=30&height=30&access_token={{ record.access_token }}" width="30" height="30" class="rounded-circle border mr-2">
                                {{ record.account_name }}
                            </a>
                        </div>
                      </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <button class="btn btn-success btn-block btn-shadow" ng-click="new_slot(false, false)"><i class="fas fa-plus"></i> Add Time Slot</button>
                </div>
            </div>
            
            

            

        </div>
	</div>
	<div class="w-100 schedule-container" ng-cloak>
        <table>
          <thead class="text-center">
            <tr>
              <th>&nbsp;</th>
              <th>SUN</th>
              <th>MON</th>
              <th>TUE</th>
              <th>WED</th>
              <th>THU</th>
              <th>FRI</th>
              <th>SAT</th>
            </tr>
          </thead>
          <tbody>
            <tr ng-repeat="hour in slots ">
              <td class="hour" >{{ hour.text }}</td>
              <td class="{{ day.schedules.length > 0 ? 'p-2' : '' }}" ng-repeat="day in hour.days" ng-click="new_slot(hour, day)">
                  <div class="time-slot text-left" ng-show="slot.show" ng-repeat="slot in day.schedules">
                      <div class="mb-0">{{ slot.text }}</div>
                      <div class="slot-card air-card pt-2 px-2 pb-0" style="border-color: {{ slot.category.category_color  }}" ng-click="update_slot($event, hour,day,slot)">
                            <div>{{ slot.category.category_name }}</div>
                            <a href="" ng-show="page.show" data-toggle="tooltip" title="{{ page.account_name }}"  ng-repeat="page in slot.pages"><img ng-src="{{ graph_url+page.account_fb_id }}/picture?width=30&height=30&access_token={{ page.access_token }}" width="30" height="30" class="rounded-circle border mr-2 mb-2"></a>
                      </div>
                  </div>
                  <span class="intro">{{ day.text }} - {{ hour.text }} </span>&nbsp;
              </td>
            </tr>
          </tbody>
        </table>
    </div>

    <div class="row mt-3" ng-if="pages.length && schedules.length == 0">
        <div class="col-12 text-muted text-center">
           <h4>Add some time slots to fill the post queue </h4>
        </div>
    </div>

    <div class="row mt-3" ng-if="pages.length == 0">
        <div class="col-12 text-muted text-center">
           <h4>No pages, please add your <a href="<?php echo site_url('accounts') ?>" class="text-success">Facebook pages</a> first</h4>
        </div>
    </div>

	<div class="modal fade" id="slot-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle">{{ modal_title }}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="row">
                <div class="col-md-6 mb-3">
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
                    <p class="text-muted text-center p-5" ng-if="pages.length == 0">
                        No pages added<br>
                        Let's go to <a href="<?php echo site_url('accounts') ?>" class="text-success">Pages menu</a> to import
                    </p>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-6">
                            <label class="font-weight-500">Publish at</label>
                            <select class="form-control" ng-model="modal_day">
                                <option value="0">Sunday</option>
                                <option value="1">Monday</option>
                                <option value="2">Tuesday</option>
                                <option value="3">Wednesday</option>
                                <option value="4">Thursday</option>
                                <option value="5">Friday</option>
                                <option value="6">Saturday</option>
                            </select>
                        </div>
                        <div class="col-6 bootstrap-timepicker timepicker">
                            <label class="font-weight-500 invisible">Publish at</label>
                            <input type="text" class="form-control timepick" ng-model="modal_time" name="">
                        </div>
                        <div class="col-12 mt-3">
                            <label class="font-weight-500">Category</label>
                            <div class="dropdown dropdown-green">
                              <a class="btn btn-default btn-block border text-left text-dark dropdown-green text-truncate  dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <div class="category-color-round mr-1" style="background-color: {{ selected_category.category_color  }}">&nbsp;</div>
                                {{ selected_category.category_name }}
                                <i class="fas fa-caret-down dropdown-caret"></i>
                              </a>

                              <div class="dropdown-menu w-100" aria-labelledby="dropdownMenuLink">
                                <div class="scrollable-dropdown">
                                    <a class="dropdown-item {{ record.category_id === selected_category.category_id ? 'bg-success text-white' : '' }}" href="" ng-click="change_category(record)"  ng-repeat="record in categories">
                                        <div class="category-color-round mr-1" style="background-color: {{ record.category_color  }}">&nbsp;</div>
                                        {{ record.category_name }}
                                    </a>
                                </div>
                              </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <a href="" class="text-success font-weight-500 mr-3" data-dismiss="modal">Cancel</a>
            <button class="btn btn-danger btn-shadow" ng-click="delete_slot()" ng-show="modal_slot.schedule_id" ng-disabled="modal_preloader || delete_preloader">Delete <i class="fas fa-spin fa-circle-notch" ng-show="delete_preloader"></i></button>
            <button class="btn btn-success btn-shadow" ng-click="save_slot()" ng-disabled="modal_preloader || delete_preloader || selected_page_count == 0">Save <i class="fas fa-spin fa-circle-notch" ng-show="modal_preloader"></i></button>
          </div>
        </div>
      </div>
    </div>


</div>
<script type="text/javascript">
    $(function(){
        function set_height()
        {
            var calender_height = $(window).height() - $('nav').outerHeight(true) - $('#calender-nav').outerHeight(true);
            $('.schedule-container').css('max-height', calender_height);
        }
        set_height();
        $(window).on('resize', set_height);
    });
</script>



