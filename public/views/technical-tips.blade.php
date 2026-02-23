<div class="static_pages_main" ng-controller="staticPagesController">
<?php @include('heroSection.blade.php') ?>


    <div class="row bulletins_main_row">
        <div class="container-fluid">
            <div class="col-md-10  col-sm-10 col-md-offset-1 col-sm-offset-1">
            <div class="col-md-12">
                <h1 class="cabin-heading">Tech Tips</h1>
            </div>
                    <div class="col-xs-12 col-sm-11 col-md-11 col-lg-11 resourcesNoteDiv" align="justify">
            Premium Guard is committed to helping our customers and automotive technicians stay informed on new technologies, New Part Numbers, New makes, and New models.  From Technical Service Bulletins, Installation Instructions, and Videos, Premium Guard is here to help you do the job right the first time and avoid comebacks.
        </div>
        <div class="col-xs-12 col-sm-10 col-md-10 col-lg-11 resourcesNoteDiv">
            <div class="col-xs-12 col-sm-10 col-md-5 col-lg-5 resourcesSearchBox removePaddings">
                <input id="techtipsSearch" name="techtipsSearch" ng-model='techtipsSearch' type="text" value="" class="filterLooupInputs" placeholder="Enter search term here">
                <div class="loader" id="loader"></div>
                <button type="button" class="clearButton" id="clearBtn" ng-click="removetechtipSearch()"><i class="fa fa-times" aria-hidden="true"></i></button>
                <button type="button" class="searchButton" id="searchTechTipsclick" ng-click="searchTechTips()">Search</button>
            </div>
        </div>
        <br/>

        <div class="col-xs-12 col-sm-10 col-md-10 col-lg-11" id="mainContentDiv">
            <ul class="bullets-padding">
                <li class="send_data" ng-if="get_searchtechtipsterm==null" ng-repeat="userData1 in userData">
                    <a ng-init="" href="tech-tips/{{userData1.id}}"  class="techBulletins">{{userData1.title.rendered}}</a>
                </li>
                <li class="send_data" ng-if="get_searchtechtipsterm!=null" ng-repeat="newstore1 in newstore">
                    <a ng-init="" href="tech-tips/{{newstore1.id}}"  class="techBulletins">{{newstore1.title.rendered}}</a>
                </li>
            </ul>
        </div>
        </div>
        </div>
    </div>
</div>
<?php @include('footer.blade.php') ?>

