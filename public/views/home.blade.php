<div class="main-div" >
    <div class="backgr_color" style="overflow-x: hidden;">
        <?php @include('heroSection.blade.php') ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="row homediv" ng-app="myApp" ng-controller="LateModelHeroController">
                        <?php @include('latemodel_include.blade.php') ?>
                    </div>
                    <a href="year">
                        <div class="col-sm-12 col-md-12">
                            <div class="row"> 
                                <div class="col-md-12 searchPadding">   
                                        <div class="col-md-6 vehicle_textcolumn">
                                            Search By Vehicle
                                        </div>
                                        <div class="col-md-4 vehicle_arrow_column">
                                        <img class="right_arrow" src="{{publicUrl}}image/right_arrow.png" alt="Right Arrow Icon"> 
                                        </div> 
                                </div>
                            </div>
                        </div>
                    </a>
                    <div class="row">
                        <div class="col-md-12 div_padding"> 
                            <div class="dynamic_search">Dynamic Search</div>
                        </div>
                    </div>
                    <div class="row">
                            <div ng-controller="DynamicSearch">
                                <div class="col-md-12" style="margin-top: 3px;"> 
                                    <input  type="text" placeholder="Part Number, Applications, Interchanges" class="keywords_text" ng-model="searchtext" ng-model-options="{ debounce: 400 }" ng-change="complete(searchtext)" ng-minlength="3">
                                    <!-- <i class="fa fa-search fa-rotate-90 search_icon" aria-hidden="true"></i> -->
                                    <a href="javascript:void(0)" ng-click="microhoneClicked()" class="anchor-microphone"><i id="microphone-icon" class="fa fa-microphone microphone-icon"  style="color:#dca942;font-size:20px;"></i></a>
                                    <!-- <i id="loader-icon" class="fa fa-spinner fa-spin microphone-icon" aria-hidden="true" style="font-size: 24px;"></i> -->
                                        <ul class="list-group" style="background-color: white" ng-if="showResult">  
                                                <li class="suggestionlist" ng-if="filterdata.length<=0"  id="listofproducts{{$index}}">No results found</li>
                                                <li class="suggestionlist" ng-class="product.class_name" ng-if="filterdata.length>0" ng-repeat="product in filterdata" ng-click="!product.disabled && displayResult(product.id,$index)"  id="listofproducts{{$index}}">{{product.label}}</li>
                                        </ul> 
                                </div>


                                <div class="modal fade in" tabindex="-1" id="listenmodal" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close" ng-click="closeModal()">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <p style="font-size:20px;">Listening... <img src="{{publicUrl}}image/microphone.gif" alt="Microphone Icon" style="width: 15%;"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                    </div>
                
                
                    <div class="row">
                                    <div class="col-md-12 div_padding"> 
                                        <div class="dynamic_search">Vin Search</div>
                                    </div>
                    </div>
                    <div class="row">
                                        <div ng-controller="vinSearch">
                                            <div class="col-md-12" style="margin-top: 3px;text-align:center"> 
                                                    <input  type="text" placeholder="Enter Vin Number" class="keywords_text" ng-model="searchtextvin"><i ng-click="vinSearch(searchtextvin)" class="fa fa-search search_icon fa-rotate-90" aria-hidden="true"></i>
                                                    <div class="loader" id="loader">Please wait</div>
                                                    <!--<div id="scanner-container"></div>
                                                    <button class="camera_button"  ng-click="vinCameraSearch()">Scan Vin No<i  class="fa fa-camera vinsearch-icon" aria-hidden="true"></i></button>-->
                                                    <!-- <button   class="keywords_text_vin">Search</button> -->
                                                    
                                            </div>
                                        </div>
                    </div>


                    <div class="row vin-productcomp-div">
                        <div ng-controller="DynamicSearch">
                            <div class="col-xs-5 col-md-5 scanvin-maindiv" style="padding-right: 6px;"  ng-controller="DynamicSearch">
                                <button aria-label="Scan Vin" class="productcomp_vin_button"  ng-click="VinClick()">Scan VIN<i  class="fa fa-camera vinsearch-icon" aria-hidden="true"></i></button>
                            </div>
                            <div class="col-xs-7 col-md-7 product-comp-div" style="padding-left: 6px;" ng-controller="DynamicSearch">
                                <button aria-label="Product Comparison" class="productcomp_vin_button"  ng-click="ProductComp()">Product Comparison</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="row row_position">
                <div class="col-md-12 image_cloumn" style="padding-left: 0px;">
                    <img src="{{publicUrl}}image/home_page_img.webp" alt="Home Page Image" class="home_pageImg">
                    <img src="{{publicUrl}}image/car-2.webp" alt="Car Image" class="car_image2">
                </div>
            </div>
        <?php @include('footer.blade.php') ?> 
    </div>
</div>


