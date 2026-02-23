<div class="backgr_color" ng-controller="MakeController">
<?php @include('heroSection.blade.php') ?>
<div class="row">
<div class="col-md-12">
    <div class="row filter_search margin_bottm all_selected_values">
      <div>
        <span class="selected_product"  ng-bind="vehicle_type"></span>
        <span class="close_icon closeBtnPadd" ng-click="removeSelection('vehicletype')">X</span>
      </div>
      <div>
        <span class="selected_product selected_filters" ng-bind="selected_year"></span>
        <span class="close_icon closeBtnPadd" ng-click="removeSelection('year')">X</span>
      </div> 
    </div>
    <div class="searchPaddingYear">
    <div class="row filter_font" style="text-align: center">Vehicle Make</div>
    <div class="row year_search" id="makeId{{$index}}" ng-repeat="item in makes" ng-click="displayModel(item.make,item.MakeID,$index)">
       <div classs="col-md-12" style="text-align: left;">
            <div class="col-md-6 year_arrow">
                <span class="subcontent_font" ng-bind="item.make"></span>
            </div>
            <div class="col-md-4 vehicle_arrow">
                <img class="RightYear_arrow" src="{{publicUrl}}image/right_arrow.png" alt="Right Arrow Icon">
            </div>
       </div>
    </div>
    </div>
</div>
<?php @include('footer.blade.php') ?> 
</div>
</div>

 

