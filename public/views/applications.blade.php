
<!DOCTYPE html>
<html>
<head>
  <!--  @include('header')-->
    <title>Pgfilter | Application</title>
</head>
<body class="backgr_color">
<!--@include('header1')-->

<?php @include('heroSection.blade.php') ?>
<div class="loader" id="loader"></div>  
<div class="row" ng-app="myApp" ng-controller="ApplicationsController">
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
            <div class="col-xs-3 col-md-3">
                <div class="application_font">Year</div>
            </div>
            <div class="col-xs-3 col-md-3">
                <div class="application_font">Make</div>
            </div>
            <div class="col-xs-3 col-md-3">
                <div class="application_font">Model</div>
            </div>
            <div class="col-xs-3 col-md-3">
                <div class="application_font">Engine</div>
            </div>
       </div>
    </div>
 <div class="row">
   <div class="col-md-12 remove_padding_Left remove_padding_right">
    <div class="searchPaddingYear">
    <div class="row filter_font" style="text-align: center">Applications</div>

  <div class="">

    
    <div class="row year_search_application" ng-if="applications!=null" ng-repeat="applications1 in applications">
       
        <div classs="col-md-12" style="text-align: left;">

            <div class="col-xs-3 col-md-3 application remove_padding_Left remove_padding_right">
                <span class="application_font applic_padd" ng-bind="applications1.year"></span>
            </div>
            <div class="col-xs-3 col-md-3 application remove_padding_right">
                <span class="application_font" ng-bind="applications1.make"></span>
            </div>
            <div class="col-xs-3 col-md-3 application remove_padding_right">
                <span class="application_font" ng-bind="applications1.model"></span>
            </div>
            <div class="col-xs-3 col-md-3 application remove_padding_Left remove_padding_right">
                <span class="application_font" ng-bind="applications1.engine"></span>
            </div>
    

     </div>
    </div>

  </div>

<!-- <div class="" ng-repeat="applications1 in applications" ng-init="diff=applications1.maxyear-applications1.minyear+1">
    <div class="row year_search" ng-if="diff==applications1.numberofrec && applications1.maxyear!=applications1.minyear">
        
        <div classs="col-md-12" style="text-align: left;">

            <div class="col-xs-3 col-md-3 application remove_padding_Left remove_padding_right">
                <span class="application_font applic_padd">{{applications1.maxyear}}-{{applications1.minyear}}</span>
            </div>
            <div class="col-xs-3 col-md-3 application remove_padding_right">
                <span class="application_font" ng-bind="applications1.make"></span>
            </div>
            <div class="col-xs-3 col-md-3 application remove_padding_right">
                <span class="application_font" ng-bind="applications1.model"></span>
            </div>
            <div class="col-xs-3 col-md-3 application remove_padding_Left remove_padding_right">
                <span class="application_font" ng-bind="applications1.engine"></span>
            </div>

      
       </div>
    </div>
</div> -->
    
</div>
</div>
</div>



</div>
</div>
<?php @include('footer.blade.php') ?> 
</body>
</html>
 

