<div class="backgr_color" ng-controller="QualifierController">
<?php @include('heroSection.blade.php') ?>
<div class="row">

   <div class="col-md-12">

      <div class="row filter_search margin_bottm all_selected_values" ng-if="d=='dyanamic' || vin_selected=='vin'">
        <div class="row">
          <a href="/" class="removeBorder"><span class="selected_product1">
           <
          </span></a>
          <span class="close_icon1">
            Back
          </span>
        </div>


        <div>
          <span class="selected_product selected_filters" ng-bind="selected_year"></span>
        </div>
        <div>
          <span class="selected_product selected_filters" ng-bind="selected_make"></span>
        </div>
        <div>
          <span class="selected_product selected_filters" ng-bind="selected_model"></span>
        </div>
        <div ng-if="selected_engine!='noengine'">
            <span class="selected_product selected_filters" ng-bind="selected_engine"></span>
        </div>
        
        <div ng-if="selected_q!=null" ng-repeat="q in selected_q">
            <span class="selected_product selected_filters" ng-bind="q"></span>
            <span class="close_icon closeBtnPadd"  ng-click="removeSelection(q)">X</span> 
        </div>
      </div>

      <div class="row filter_search margin_bottm all_selected_values" ng-if="d!='dyanamic' && vin_selected!='vin'">

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
          <span class="close_icon" ng-click="removeSelection('model')">X</span>
        </div>
        <div ng-if="selected_engine!='noengine'">
          <span class="selected_product selected_filters" ng-bind="selected_engine"></span>
          <span class="close_icon closeBtnPadd" ng-click="removeSelection('engine')">X</span>
        </div>
        <div ng-if="selected_q!=null" ng-repeat="q in selected_q">
          <span class="selected_product selected_filters" ng-bind="q"></span>
            <span class="close_icon closeBtnPadd"  ng-click="removeSelection(q)">X</span> 
        </div>
          
      </div>

    <div class="searchPaddingYear">
    <!-- <div class="row filter_font" style="text-align: center">Qualifier</div> -->
    <div class="row filter_font" style="text-align: center">Choose a {{name}}</div>

    <!-- <div class="row year_search" id="qualifier_name" ng-repeat="item in name">
        <div classs="col-md-12" style="text-align: left;">
            <div class="col-md-6 year_arrow">
                <span class="subcontent_font" ng-bind="item"></span>
            </div>
        </div>
    </div> -->
    <div class="row year_search"  ng-repeat="item in qualifier" id="qualifierId{{$index}}" ng-click="displayProductList(item.id,item.values,$index)">
       <div classs="col-md-12" style="text-align: left;">
            <div class="col-md-6 year_arrow">
                <span class="subcontent_font" ng-bind="item.values"></span>
            </div>
            <div class="col-md-4 vehicle_arrow">
                <img class="RightYear_arrow" src="{{publicUrl}}image/right_arrow.png" alt="Right Arrow Icon">
            </div>
       </div>
    </div>
  </div>
</div>
</div>
</div>
<?php @include('footer.blade.php') ?> 
</div>
 

