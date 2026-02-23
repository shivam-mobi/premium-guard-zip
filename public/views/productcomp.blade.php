<div class="backgr_color" ng-controller="productComp">
    <?php @include('heroSection.blade.php') ?>
        <div class="loader" id="loader"></div>    

        <div class="row back-button-div">
            <div style="padding: 0px 27px;">
                <div class="col-md-12">
                    <a href="/" class="removeBorder">
                        <span class="selected_product1"><</span>
                        <span class="close_icon1">
                            Back
                        </span>
                    </a>
                </div>
            </div>
        </div>

        <div class="searchPaddingYear_comp">
            <div class="search-div">
                <div class="row">
                    <div class="col-sm-12 col-md-4 col-lg-4">
                        <div class="col-md-12">
                            <h1 class="product_comp_heading">PRODUCT COMPARISON</h1>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <div class="col-md-12 col-lg-11">
                            <h4>Product</h4>
                            <div class="row">
                                <select class="orginal-select" id="parttype" style="color: grey;" ng-model="selectedProduct" ng-change="productChange()">
                                    <option value="">Select Part Type</option>
                                    <option ng-repeat="p in parttypes" value="{{p.partTypename}}">{{p.partTypename}}</option>
                                </select>
                                <div class="select__arrow select__arrow__pt"></div>
                            </div> 
                        </div>
                    </div>
                </div>
                <div class="row">
                    
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <div class="col-md-12 col-lg-12">
                            <h4>Vehicles</h4>
                        </div>
                        <div class="col-sm-12 col-md-5 col-lg-5">
                            <select class="orginal-select" style="color: grey;" ng-model="selectedYearFrom" ng-disabled="isDisable" ng-change="showProducts(0)" id="fromyear">
                                    <option value="">Select From Year</option>
                                    <option ng-repeat="f in years" value="{{f.year}}">{{f.year}}</option>
                            </select>
                            <div class="select__arrow select__arrow__year"></div>
                        </div>
                        <div class="col-sm-12 col-md-2 col-lg-1 to-div">
                            <p>To</p>
                        </div>
                        <div class="col-sm-12 col-md-5 col-lg-5">
                            <select class="orginal-select" style="color: grey;" ng-model="selectedYearTo" ng-disabled="isDisable" ng-change="showProducts(0)" id="toyear">
                                    <option value="">Select To Year</option>
                                    <option ng-repeat="t in years" value="{{t.year}}">{{t.year}}</option>
                            </select>
                            <div class="select__arrow select__arrow__year"></div>
                        </div>
                        <div class="col-md-12 col-lg-11">
                            <select style="width:100%;" multiple="multiple" ng-model="selectedMake" ng-change="changeMake(selectedMake,true)" class="" id="make">
                                <option ng-repeat="m in make" ng-value="m.MakeID">
                                    {{m.MakeName}}
                                </option>
                            </select>
                            <div class="select__arrow"></div>
                        </div>
                        <div class="col-md-12 col-lg-11">
                            <select style="width:100%;" multiple="multiple" ng-model="selectedModel" ng-change="changeModel(selectedModel,true)" class="" id="model">
                                <option ng-repeat="mo in model" ng-value="mo.ModelID">
                                    {{mo.ModelName}}
                                </option>
                            </select>
                            <div class="select__arrow"></div>
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <div class="col-md-12 col-lg-11">
                                <h4>Part Numbers</h4>
                                <select style="width:100%;" multiple="multiple" ng-model="selectedPartNumber" ng-change="changePartNumber(selectedPartNumber,true)" class="" id="partnumber">
                                    <option ng-repeat="p in partnumber" ng-value="p.partnumber">
                                        {{p.partnumber}}
                                    </option>
                                </select>
                                <div class="select__arrow select__arrow__pt"></div>
                        </div>
                    </div>

                </div>
            </div>
            
            <div class="result-div imgbox_margin ng-scope">
                <div class="row">
                    <div class="col-md-12 search_result_div" ng-if="note_result!=''">
                        <p><span class="sub-text-result">SEARCH RESULT FOR: </span>{{note_result}}</p>
                    </div>
                    <div class="col-xs-12 col-md-3 cards-div-comp"  ng-repeat="pr in product_result" ng-init="myFileName = 'https://www.pgfilters.com/wp-content/resources-pdfs/'">
                        <div class="specs-div-all">
                            <div class="">
                                <a class="comp-parttype-anchor" ng-click="Productpage(pr.partnumber,pr.year,pr.make,pr.model,pr.partTypeName)">
                                    <p class="heading-part" style="font-weight: bold;">{{pr.partnumber}}</p>
                                </a>
                            </div>
                            <div class="productImgLateModel">
                                <a  ng-click="Productpage(pr.partnumber,pr.year,pr.make,pr.model,pr.partTypeName)">
                                    <img class="partimages_comp" ng-src="{{pr.imagePath}}" alt="Product Comparison Images">
                                </a>
                            </div>
                           

                            <table ng-if="pr.spec.length > 0 " class="table-product-comp">
                                    <tbody>
                                        <tr class="filter_search_comp ng-scope" ng-repeat="product_specs1 in pr.spec" id="product_comp{{$index}}" ng-if="product_specs1.product_specs_value && product_specs1.product_specs_name">
                                                <td class="filter_text_comp"  ng-bind="product_specs1.product_specs_name"></td>
                                                <td class="filter_column_comp"  ng-bind="product_specs1.product_specs_value"></td>
                                        </tr>
                                    </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="modal fade in" tabindex="-1" id="alertmodalvisual" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>From year is greater than To year</p>
                    </div>
                </div>
            </div>
        </div>

        
</div>

        
<?php @include('footer.blade.php') ?> 
 

