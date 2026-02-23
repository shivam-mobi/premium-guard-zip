<div class="backgr_color" ng-controller="VehicleTypeController">
<?php @include('heroSection.blade.php') ?>
    <div class="row" >
        <div class="col-md-12" >
            <div class="row filter_search margin_bottm">
                <a href="/" class="removeBorder"><span class="selected_product1">
           <
          </span></a>
                <span class="close_icon1">
            Back
          </span>
            </div>

            <div class="searchPaddingYear">
                <div class="row filter_font" style="text-align: center">Vehicle Type </div>
                <div class="row year_search" id="vehicletypeGroupId{{$index}}" ng-repeat="item in items" ng-click="displayYear(item.vehicletypeID,item.vehicletype,$index)">
                    <div classs="col-md-12" style="text-align: left;" >
                        <div class="col-md-6 year_arrow">
                            <span class="subcontent_font">{{item.vehicletype}}</span>
                        </div>
                        <div class="col-md-4 vehicle_arrow" ng-cl>
                            <img class="RightYear_arrow" id="" src="{{publicUrl}}image/right_arrow.png" alt="Right Arrow Icon">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php @include('footer.blade.php') ?>
</div>


