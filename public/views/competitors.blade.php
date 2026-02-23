
<!DOCTYPE html>
<html>
<head>
  <!--  @include('header')-->
    <title>Pgfilter | Competitors</title>
</head>
<body class="backgr_color">
<!--@include('header1')-->
<?php @include('heroSection.blade.php') ?>
<div class="row" ng-app="myApp" ng-controller="CompetitorsController">
   <div class="col-md-12">
     <div class="row filter_search margin_bottm">
          <a href="product-page/{{selected_product}}/{{ymm}}/{{selected_parttype_name}}" class="removeBorder"><span class="selected_product1">
           <
          </span></a>
          <span class="close_icon1">
            Back
          </span>
    </div>
     <div class="row filter_search appc_label">
        <div class="col-md-12" style="text-align: left;padding: 0;">
            <div class="col-xs-6 col-md-6">
                <div class="application_font">Competitor Name</div>
            </div>
            <div class="col-xs-6 col-md-6">
                <div class="application_font">Cross Reference</div>
            </div>
       </div>
    </div>
 <div class="row">
   <div class="col-md-12 remove_padding_Left remove_padding_right">
    <div class="searchPaddingYear">
    <div class="row filter_font" style="text-align: center">Competitor's Interchanges</div>
    <div class="row year_search_application" ng-repeat="interchanges1 in interchanges">
        <div classs="col-md-12" style="text-align: left;">
            <div class="col-xs-6 col-md-6 application remove_padding_Left remove_padding_right">
                <span class="application_font applic_padd" ng-bind="interchanges1.interchange_name"></span>
            </div>
            <div class="col-xs-6 col-md-6 application remove_padding_right">
                <span class="application_font" ng-bind="interchanges1.partIntPartNumber"></span>
            </div>
       </div>
    </div>
    

</div>
</div>
</div>

</div>
</div>
<?php @include('footer.blade.php') ?> 
</body>
</html>
 

