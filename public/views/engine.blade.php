<div class="backgr_color" ng-controller="EngineController">
<?php @include('heroSection.blade.php') ?>
<div class="row" >
   <div class="col-md-12">

    <div class="row filter_search margin_bottm all_selected_values">
      <div>
        <span class="selected_product"  ng-bind="vehicle_type"></span>
        <span class="close_icon closeBtnPadd" ng-click="removeSelection('vehicletype')">X</span>
      </div>
      <div>
        <span class="selected_product selected_filters" ng-bind="selected_year"></span>
        <span class="close_icon" ng-click="removeSelection('year')">X</span>
      </div>
      <div>
        <span class="selected_product selected_filters" ng-bind="selected_make"></span>
        <span class="close_icon" ng-click="removeSelection('make')">X</span>     
      </div>
      <div>
        <span class="selected_product selected_filters" ng-bind="selected_model"></span>
        <span class="close_icon closeBtnPadd" ng-click="removeSelection('model')">X</span>  
      </div>
    </div>
    
    <div class="searchPaddingYear">
    <div class="row filter_font" style="text-align: center">Engine</div>
    <div class="row year_search" id="engineId{{$index}}" ng-repeat="item in engine" ng-click="displayNote(item.engine,item.EngineConfigID,$index)">
       <div classs="col-md-12" style="text-align: left;">
            <div class="col-md-6 year_arrow">
                <span class="subcontent_font" ng-bind="item.engine"></span>
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
 

