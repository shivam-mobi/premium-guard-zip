<div class="static_pages_main" ng-controller="staticPagesController">
<?php @include('heroSection.blade.php') ?>


    <div class="row bulletins_main_row">
        <div class="container-fluid">
            <div class="col-md-10  col-sm-10 col-md-offset-1 col-sm-offset-1">
                <div class="col-md-12">
                    <h1 class="cabin-heading">Videos</h1>
                </div>
                <div class="col-xs-12 col-sm-11 col-md-11 col-lg-11 resourcesNoteDiv" align="justify">
                    Premium Guard is committed to helping our customers and automotive technicians stay informed on new technologies, New Part Numbers, New makes, and New models.  From Technical Service Bulletins, Installation Instructions, and Videos, Premium Guard is here to help you do the job right the first time and avoid comebacks.
                </div>
                <div class="col-xs-12 col-sm-10 col-md-10 col-lg-11 resourcesNoteDiv">
                    <div class="col-xs-12 col-sm-10 col-md-5 col-lg-5 resourcesSearchBox removePaddings">
                        <input id="videoSearch" name="videoSearch" ng-model='videoSearch' type="text" value="" class="filterLooupInputs" placeholder="Enter search term here">
                        <div class="loader" id="loader">Please wait</div>
                        <button type="button" class="clearButton" id="clearBtn" ng-click="removevideoSearch()"><i class="fa fa-times" aria-hidden="true"></i></button>
                        <button type="button" class="searchButton" id="searchVideos" ng-click="searchVideos()">Search</button>
                    </div>
                </div>
                <br/>

                <div class="col-xs-12 col-sm-10 col-md-10 col-lg-11 blocksizeformobile" id="mainContentDiv">
                    <div ng-if="get_videossearchterm==null">
                    <div class="col-md-12" style="text-align:center">
                          <iframe width="100%" height="320" ng-src="{{ videos }}" frameborder="0" allowfullscreen class="video-size"></iframe>
                          <div><h3 class="video_name_style"><b>{{video_title}}</b></h3></div>
                          <div class="video-description"><p dd-text-collapse dd-text-collapse-max-length="300" dd-text-collapse-text="{{video_desc}}"></p></div>
                    </div>
                    <div ng-if="othervideos!=null">
                    <div class="col-xs-12 col-sm-11 col-md-11 col-lg-12 blocksizeformobile"><h3 id="default_heading" class="relvideossection">Related Videos</h3><h5 id="search_heading" style="display: none">Search result</h5>
                        <div class="col-xs-12 col-sm-4 col-md-3 col-lg-4 videoDivs" ng-repeat="othervideos1 in othervideos">
                        <a href="" ng-click="displaySelectedVideo(othervideos1.video_id)"><img ng-src="https://img.youtube.com/vi/{{othervideos1.video_link}}/mqdefault.jpg" alt="Main Video" width="100%"></a>
                         <div><h5 class="rel_video_name_style"><b>{{othervideos1.video_name}}</b></h5></div>
                        </div>
                        </div>
                    </div>
                    </div>
                    <div ng-if="get_videossearchterm!=null">
                        <div class="col-xs-12 col-sm-4 col-md-3 col-lg-4 videoDivs" ng-repeat="othervideos1 in othervideos">
                            <a href="" ng-click="displaySelectedVideo(othervideos1.video_id)"><img ng-src="https://img.youtube.com/vi/{{othervideos1.video_link}}/mqdefault.jpg" alt="All Videos" width="100%"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php @include('footer.blade.php') ?>

