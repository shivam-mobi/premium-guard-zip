<div class="backgr_color" ng-controller="YearController">
<?php @include('heroSection.blade.php') ?>
<div class="row" >
   <div class="col-md-12" >
      <div class="row filter_search margin_bottm">
          <span class="selected_product"  ng-bind="vehicle_type">
          </span>
           <span class="close_icon closeBtnPadd" ng-click="removeSelection('vehicletype')">
            X
          </span>
       </div>
     
<div class="searchPaddingYear">
<div class="row filter_font" style="text-align: center">Vehicle Year </div>
<div class="row year_search"  ng-repeat="item in items" id="yearId{{$index}}" ng-click="displayMake(item.year,$index)">
       <div classs="col-md-12" style="text-align: left;" >
            <div class="col-md-6 year_arrow">
                <span class="subcontent_font">{{item.year}}</span>
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
 

