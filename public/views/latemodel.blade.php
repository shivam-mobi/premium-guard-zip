<div class="backgr_color" ng-controller="LateModelController">
        <div class="row">
            <?php @include('heroSection.blade.php') ?>
            <div class="row back-button-div">
                <div style="padding: 0px 27px;">
                        <a href="/" class="removeBorder">
                            <span class="selected_product1"><</span>
                            <span class="close_icon1">
                                Back
                            </span>
                        </a>
                </div>
            </div>
        </div>
        <div class="loader" id="loader"></div>    
        <div class="searchPaddingYear_comp">
            <div class="row" style="text-align:center;" ng-app="myApp">
                <?php @include('latemodel_include.blade.php') ?>
            </div>
            <div class="result-div imgbox_margin ng-scope">
                    <div class="row latemodelrow">
                        <div class="col-sm-12 col-md-12">
                                <ul class="nav nav-pills nav-justified nav-tabs-latemodel">
                                    <li ng-repeat="p in allparttypes" class="nav-menu-li" ng-class="{'active': active===$index}" id="tab{{$index}}">
                                        <a class="anchorEngineFilter" data-toggle="tab" ng-click="tabChanged($index,p.partTypename)">{{p.partTypename}}</a>
                                    </li>
                                </ul>
                        </div>

                        <div class="col-xs-12 col-sm-4 col-md-3 cards-div-comp" style="padding-top:20px;"  ng-repeat="result in allresults">
                            <div class="specs-div-all" style="margin-bottom:10px">
                                <div class="">
                                    <a class="comp-parttype-anchor"  ng-click="Productpage(result.partnumber,result.year,result.make,result.model,result.partTypeName)">
                                        <p class="heading-part" style="font-weight: bold;">{{result.partnumber}}</p>
                                    </a>
                                </div>
                                <div class="productImgLateModel">
                                    <a  ng-click="Productpage(result.partnumber,result.year,result.make,result.model,result.partTypeName)">
                                        <img class="partimages_comp" ng-src="{{result.image_path}}" alt="Late Model Products">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
</div>



<?php @include('footer.blade.php') ?> 
 

