<div class="static_pages_main" ng-controller="staticPagesController">
<?php @include('heroSection.blade.php') ?>
<div class="row">

	<div class="container-fluid">
        <div class="row bulletins_main_row">
            <div class="col-md-10  col-sm-10 col-md-offset-1 col-sm-offset-1">
                <div class="col-md-12">
                    <h1 class="cabin-heading">Technical Bulletins</h1>
                </div>
                
            	<div class="col-md-12 resourcesNoteDiv">
                    <p class="bulletins-text">Premium Guard is committed to helping our customers and automotive technicians stay informed on new technologies, New Part Numbers, New makes, and New models.  From Technical Service Bulletins, Installation Instructions, and Videos, Premium Guard is here to help you do the job right the first time and avoid comebacks.</p>
                </div>
                <!-- <div class="col-md-12">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Enter search term here" aria-label="Enter search term here" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button">Search</button>
                        </div>
                    </div>
                </div> -->
                <div class="col-md-12">
                <div class="col-xs-12 col-sm-10 col-md-5 col-lg-5 resourcesSearchBox removePaddings">
                       <input id="resourcesSearch" name="resourcesSearch" ng-model='resourcesSearch' type="text" value="" class="filterLooupInputs" placeholder="Enter search term here">
                       <div class="loader" id="loader"></div>
                       <button type="button" class="clearButton" id="clearBtn" ng-click="removeSearch()"><i class="fa fa-times" aria-hidden="true"></i></button>
                      <button type="button" class="searchButton" id="searchresourcesclick" ng-click="searchResources()">Search</button>
                  </div>
                </div>
                  

                    <div class="col-xs-12 col-sm-10 col-md-10 col-lg-11" id="installationInstructionsDivMain">

                        <div id="tech-bulletins-div" ng-if="get_searchterm"  ng-repeat="bu in bulletins">
                            <h2 class="bulletins-category-title">{{bu.cat_title}}</h2>
                            <ul class="bullets-padding">
                                <li class="bulletins-li" ng-repeat="tp in bu.cat_pdf_title">
                                    <a ng-init="myFileName = 'https://www.pgfilters.com/'" href="{{myFileName}}{{tp.pdf_path}}"  class="techBulletins">{{tp.pdf_title}}</a>
                                </li>
                            </ul>
                        </div>

                        <div id="tech-bulletins-div" ng-if="!get_searchterm"  ng-repeat="bu in bulletins">
                            <h2 class="bulletins-category-title">{{bu.cat_title}}</h2>
                            <ul class="bullets-padding">
                                <li class="bulletins-li" ng-repeat="tp in bu.cat_pdf_title">
                                    <a ng-init="myFileName = 'https://www.pgfilters.com/'" href="{{myFileName}}{{tp.pdf_path}}"  class="techBulletins">{{tp.pdf_title}}</a>
                                </li>
                            </ul>
                        </div>

                        <div ng-if="bulletins.length==0" class="no-result-found-div">
                            <h4>No Result Found</h4>
                        </div>

                        <!-- <ul class="bullets-padding">
                            <li class="bulletins" ng-if="get_searchterm==null" ng-repeat="bu in bulletins">
                                <a ng-init="myFileName = 'https://www.pgfilters.com'" href="{{myFileName}}{{bu.resources_path}}"  class="techBulletins">{{bu.resources_path}}</a>
                            </li>
                            <li class="bulletins" ng-if="get_searchterm!=null" ng-repeat="bu in bulletins">
                                <a ng-init="myFileName = 'https://www.pgfilters.com'" href="{{myFileName}}{{bu.resources_path}}"  class="techBulletins">{{bu.resources_path}}</a>
                                    
                            </li>
                        </ul> -->
                    </div>
                </div>
           	    </div>
        </div>
   	</div>
   	
</div>
<?php @include('footer.blade.php') ?> 
</div>