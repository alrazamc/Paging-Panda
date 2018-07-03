<link href="<?php echo getenv('ASSET_BASE_URL') ?>assets/css/angular-icheck.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo getenv('ASSET_BASE_URL') ?>assets/js/angular-icheck.js"></script>

<script type="text/javascript">
  var api_base_url = '<?php echo site_url('api') ?>/';
  var angapp = angular.module('app', ['angular-icheck']);
  angapp.directive('fileReader', function() {
      return {
        scope: {
          fileReader:"=",
          filePreloader: "="
        },
        link: function(scope, element) {
          $(element).on('change', function(changeEvent) {
            var files = changeEvent.target.files;
            if (files.length) {
              var r = new FileReader();
              r.onload = function(e) {
                  var contents = e.target.result;
                  scope.$apply(function () {
                    scope.fileReader = CSVToArray(contents);
                    scope.filePreloader = false;
                  });
              };
              scope.filePreloader = true;
              r.readAsText(files[0]);
            }
          });
        }
      };
    });
    angapp.controller('pageController', function($scope, $http, $sce, $filter, $timeout){
      	$scope.graph_url = '<?php echo GRAPH_API_URL ?>';
        $scope.alert = '';
        $scope.page_preloader = true;
        
        $scope.pages = <?php echo json_encode($pages) ?>;
        $scope.categories = <?php echo json_encode($categories) ?>;
        $scope.content_preloader = true;
        $scope.content = [];
        $scope.posts = false;
        $scope.file_preloader = false;
        $scope.bulk_action_preloader = false;
        $scope.master_category = '';

        $scope.csv_loaded = false;

        for(var i=0; i<$scope.pages.length; i++)
            $scope.pages[i].apply_all = false;

        $scope.categories.unshift({
        	category_id : "-1",
        	category_name : 'Publish Once',
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
    
        $scope.$watch('[select_all]', function(){
            for(var i=0; i < $scope.content.length; i++)
                $scope.content[i].update = $scope.select_all;
        }, true);

        $scope.$watch('[posts]', function(){
           if($scope.posts)
           {
             for(var i=0; i<$scope.posts.length; i++)
             {
                if(typeof $scope.posts[i][0] === 'undefined' || $scope.posts[i][0] == "" )
                    continue;
                var item = {
                    update: false,
                    text: $scope.posts[i][0],
                    message: $scope.posts[i][0].length > 40 ? $scope.posts[i][0].substring(0,40) + "..." : $scope.posts[i][0],
                    category_id : $scope.get_category_id($scope.posts[i]),
                    selected_pages: []
                };
                for(var j=0; j<$scope.pages.length; j++)
                    item.selected_pages[j] = false;
                $scope.content.push(item);
             }
           }
        }, true);

        $scope.get_category_id = function(post)
        {
            if(typeof post[1] === 'undefined' || post[1] == '')
                return "0";
            if(post[1].toLowerCase() == 'publish once')
                return "-1";
            for(var i=0; i<$scope.categories.length; i++)
                if($scope.categories[i].category_name.toLowerCase() == post[1].toLowerCase() )
                    return $scope.categories[i].category_id;
            return "0";
        }
    
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

        $scope.save_bulk = function()
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
                        category_id: $scope.content[i].category_id == '-1' ? 0 : $scope.content[i].category_id,
                        use_once: $scope.content[i].category_id == '-1' ? 1 : 0,
                        accounts: accounts,
                        message: $scope.content[i].text
                    });
                }
            $http.post(api_base_url + 'import/csv_bulk', {items:items}).then(function(response){
                $scope.bulk_action_preloader = false;
                window.location.href=response.data.url;
            }, function(){
                $scope.bulk_action_preloader = false;
            })
        }

    });

    function CSVToArray( strData, strDelimiter ){
        strDelimiter = (strDelimiter || ",");
        var objPattern = new RegExp(
            (
                "(\\" + strDelimiter + " *|\\r?\\n|\\r|^)" +
                "(?:\"([^\"]*(?:\"\"[^\"]*)*)\"|" +
                "([^\"\\" + strDelimiter + " *\\r\\n]*))"
            ),
            "gi"
            );
        var arrData = [[]];
        var arrMatches = null;
        while (arrMatches = objPattern.exec( strData )){
            var strMatchedDelimiter = arrMatches[ 1 ];
            if (
                strMatchedDelimiter.length &&
                strMatchedDelimiter.trim() !== strDelimiter
                ){
                arrData.push( [] );
            }
            var strMatchedValue;
            if (arrMatches[ 2 ]){
                strMatchedValue = arrMatches[ 2 ].replace(
                    new RegExp( "\"\"", "g" ),
                    "\""
                    );
            } else {
                strMatchedValue = arrMatches[ 3 ];
            }
            arrData[ arrData.length - 1 ].push( strMatchedValue );
        }
        return( arrData );
    }
</script>
<div class="container my-5" ng-app="app" ng-controller="pageController">
	<div class="row">
		<div class="col-md-12">
			<?php echo $this->session->flashdata('alert') ?>
		</div>
	</div>
	<div class="row mb-md-3 mb-0">
		<div class="col-sm-8 mb-3">
            <h4 class="page-title">Import content from CSV file</h4>
        </div>
        <div class="col-sm-4 mb-3 text-center text-sm-right">
            <a href="<?php echo site_url('import/feeds') ?>" class="btn btn-green btn-shadow" ><i class="fas fa-arrow-left"></i> RSS Feeds</a>
        </div>
        <div class="col-md-12">
            <?php echo $this->session->flashdata('alert') ?>
        </div>
        <div class="col-md-12" ng-bind-html="alert">
        </div>
	</div>
    <div class="row" ng-show="content.length == 0"  ng-cloak>
        <div class="col-12">
            <div class="card air-card">
                <div class="card-body">
                    <p class="text-muted mb-1">Import the text content in bulk using CSV file</p>
                    <ul class="text-muted">
                        <li>Do not include a header row</li>
                        <li>First column should contain content text</li>
                        <li>Second column may have a category name(optional)</li>
                        <li>File must be in UTF-8 format</li> 
                    </ul>
                    <div class="row">
                        <div class="col-sm-6">
                            <input type="file" class="form-control" file-reader="posts" file-preloader="file_preloader" accept=".csv" >
                        </div>
                        <div class="col-sm-6">
                            <i class="fas fa-spin fa-circle-notch" ng-show="file_preloader"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<div class="row" ng-show="content.length" ng-cloak>
		<div class="col-md-12 mb-3">
			<div class="row">
				<div class="col-sm-6 mb-3 text-sm-left text-center" >
					<span class="text-muted" ng-show="content.length"><span class="fz-20">{{ content.length }}</span> items</span>
				</div>
                <div class="col-sm-6 mb-3 text-sm-right text-center">
                    <button class="btn btn-success btn-shadow" onclick="window.location.reload()">Cancel</button>
                </div>
			</div>

			<div class="p-5 text-center text-muted" ng-if="content.length == 0">
				<h3>No content found in csv file</h3>
			</div>

            <div class="table-responsive-md bg-white" ng-show="content.length">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th class="font-weight-500">Save?</th>
                      <th class="font-weight-500">Category</th>
                      <th class="font-weight-500">Content</th>
                      <th class="font-weight-500">Pages</th>
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
                          {{ record.message }}
                      </td>
                      <td>
                         <a href="" ng-click="record.selected_pages[$index] = !record.selected_pages[$index]" ng-if="page.account_id" target="_blank" data-toggle="tooltip" title="{{ page.account_name }}"  ng-repeat="page in pages"><img ng-src="{{ graph_url+page.account_fb_id }}/picture?width=30&height=30&access_token={{ page.access_token }}" width="30" height="30" class="rounded-circle border mr-1 mb-1 {{ record.selected_pages[$index] === false ? 'disabled-img' : '' }}"></a>
                      </td>
                    </tr>
                  </tbody>
                </table>
            </div>

			<div class="text-center" ng-show="content.length">
                <button class="btn btn-success btn-shadow" ng-click="save_bulk()"  ng-disabled="!any_record_selected() || bulk_action_preloader">Save Selected</button>
                <i class="fas fa-spin fa-circle-notch text-success" ng-show="bulk_action_preloader"></i>
            </div>

		</div>
	</div>

</div>
