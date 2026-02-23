<div class="backgr_color" ng-controller="ProductListController">
<?php @include('heroSection.blade.php') ?>
<div class="row">
   <div class="col-md-12">
     <div class="row filter_search margin_bottm">

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
        <div  ng-if="selected_engine!='noengine'">
            <span class="selected_product selected_filters" ng-bind="selected_engine"></span>
        </div>
        
        <div  ng-if="store_value!=null" ng-repeat="q in store_value">
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
        <div ng-if="store_value!=null" ng-repeat="q in store_value">
            <span class="selected_product selected_filters" ng-bind="q"></span>
            <span class="close_icon closeBtnPadd"  ng-click="removeSelection(q)">X</span> 
        </div>
      </div>


     <div class="row filter_search">
        <div class="col-md-12" style="text-align: left;">
            <div class="col-md-5 filter_text_product">
                <div class="subcontent_font productPadd">Product</div>
            </div>
            <div class="col-md-5 filters_arrow filter_product">
                <div class="row">
                  <div class="col-md-12 div_padd remove_padding_Left remove_padding_right">                  
                      <div class="col-md-6 partno_padd subcontent_font">
                        Part #
                      </div>
                      <!-- <div class="col-md-4 image_width">
                        <img class="year_right_arrow" src="{{publicUrl}}image/right_arrow.png" alt="Right Arrow Icon"> 
                      </div> -->
                  </div>
                </div> 
            </div>
       </div>
    </div>
    
    <div class="searchPaddingYear" style="padding-left: 0" ng-if="setflag==true">
        <div class="row filter-font" style="text-align: center;padding: 0px 10px;">We do not have information available for your vehicle at this time. Please check back later, as we are always adding new products.</div> 
    </div>
    
    
<div class="searchPaddingYear" style="padding-left: 0" ng-if="setflag==false && no_filters==false">
    
    
    <div class="row filter_font" style="text-align: center;">Filters</div>
    
    <div class="row filter_search" ng-if="filters1.partNumber!=null" ng-repeat="filters1 in filters" id="filter{{$index}}"  ng-click="getFilters(filters1.partNumber,filters1.partTypename,filters1.partType,filters1.partSubtype,$index)">
          <div class="col-md-12 filterPointer" style="text-align: left;">
            <div class="col-md-5 filter_text">
                <div class="subcontent_font subcontentPadd" ng-bind="filters1.partTypename"></div>
                <div class="note_font" ng-bind="filters1.note"></div>

                <div class="note_font" ng-if="filters1.partNumber=='Need More Info'" ng-bind="filters1.partTypename" style="color:#656161;"></div>
                <!-- <div class="note_font" ng-if="filters1.partNumber=='Need More Info'" ng-bind="filters1.partNumber" style="color:#656161;"></div> -->
            </div>


             <div class="col-md-5 filters_arrow filter_column">
                <div class="row">
                  <!-- <div class="col-md-12 remove_padding_Left remove_padding_right"> -->

                      <div class="col-md-6 partno_padd subcontent_font remove_padding_right partnoPaddTop" ng-bind="filters1.partNumber" ng-if="filters1.partNumber=='Need More Info'" style="font-size:13px;color: #dca942;">
                      </div>          
                      <div class="col-md-6 partno_padd subcontent_font partnoPaddTop" ng-bind="filters1.partNumber" ng-if="filters1.partNumber!='Need More Info'">
                      </div>
                      <div class="col-md-4 image_width" ng-if="filters1.partNumber!=''">
                       <img class="year_right_arrow" src="{{publicUrl}}image/right_arrow.png" alt="Right Arrow Icon">
                      </div>
                  <!-- </div> -->
                </div> 
            </div>
       </div>
    </div>



</div> 
<div class="searchPaddingYear" style="padding-left: 0" ng-if="setflag==false && no_caps==false">
    <div class="row filter_font" style="text-align: center;">Caps and Plugs</div> 
    <div class="row filter_search" ng-repeat="caps1 in caps" id="caps{{$index}}" ng-click="getCaps(caps1.partNumber,caps1.partTypename,caps1.partType,caps1.partSubtype,$index)">
        <div class="col-md-12 filterPointer" style="text-align: left;" ng-if="caps1.partNumber!=null">
            <div class="col-md-5 filter_text">
                <div class="subcontent_font subcontentPadd" ng-bind="caps1.partTypename"></div>
                <div class="note_font" ng-bind="caps1.note"></div>


                 <div class="note_font" ng-if="caps1.partNumber=='Need More Info'" ng-bind="caps1.partTypename" style="color:#656161;"></div>
                <!-- <div class="note_font" ng-if="filters1.partNumber=='Need More Info'" ng-bind="filters1.partNumber" style="color:#656161;"></div> -->
            </div>
             <div class="col-md-5 filters_arrow filter_column">
                <div class="row">
                  <div class="col-md-12 remove_padding_Left remove_padding_right">                  
                      
                      <div class="col-md-6 partno_padd subcontent_font remove_padding_right partnoPaddTop" ng-bind="caps1.partNumber" ng-if="caps1.partNumber=='Need More Info'" style="font-size:13px;color: #dca942;">
                      </div>          
                      <div class="col-md-6 partno_padd subcontent_font partnoPaddTop" ng-bind="caps1.partNumber" ng-if="caps1.partNumber!='Need More Info'"></div>


                      <div class="col-md-4 image_width">
                       <img class="year_right_arrow" src="{{publicUrl}}image/right_arrow.png" alt="Right Arrow Icon">
                      </div>
                  </div>
                </div> 
            </div>
       </div>
    </div>
</div> 
<div class="searchPaddingYear" style="padding-left: 0" ng-if="setflag==false && no_wipers==false">
    <div class="row filter_font" style="text-align: center;">Wiper Blades</div> 
    <div class="row filter_search" ng-repeat="wipers1 in wipers" id="wiper{{$index}}" ng-click="getWipers(wipers1.partNumber,wipers1.partTypename,wipers1.partType,wipers1.partSubtype,$index)">
        <div class="col-md-12 filterPointer" style="text-align: left;" ng-if="wipers1.partNumber!=null">

            <div class="col-md-5 filter_text">
                <div class="subcontent_font subcontentPadd" ng-bind="wipers1.partTypename"></div>


                <div class="note_font" ng-bind="wipers1.note"></div>
                

                <div class="note_font" ng-if="wipers1.partNumber=='Need More Info'" ng-bind="wipers1.partTypename" style="color:#656161;"></div>
                <!-- <div class="note_font" ng-if="filters1.partNumber=='Need More Info'" ng-bind="filters1.partNumber" style="color:#656161;"></div> -->
            </div>
             <div class="col-md-5 filters_arrow filter_column">
                <div class="row">
                  <div class="col-md-12 remove_padding_Left remove_padding_right">


                      <div class="col-md-6 partno_padd subcontent_font remove_padding_right partnoPaddTop" ng-bind="wipers1.partNumber" ng-if="wipers1.partNumber=='Need More Info'" style="font-size:13px;color: #dca942;">
                      </div>          
                      <div style="white-space: nowrap;" class="col-md-6 partno_padd subcontent_font partnoPaddTop" ng-bind="wipers1.partNumber" ng-if="wipers1.partNumber!='Need More Info'"></div>
                      <div class="col-md-4 image_width">
                       <img class="year_right_arrow" src="{{publicUrl}}image/right_arrow.png" alt="Right Arrow Icon">
                      </div>
                  </div>
                </div> 
            </div>
       </div>
    </div>
</div>


<div class="searchPaddingYear" style="padding-left: 0" ng-if="setflag==false && no_fluids==false">
    <div class="row filter_font" style="text-align: center;">Fluids</div>
    <div class="row filter_search"  ng-if="fluids1.partnumber!=null" ng-repeat="fluids1 in fluids" id="fluid{{$index}}" ng-click="getFluids(fluids1.partnumber,fluids1.partTypename,fluids1.partTypeid,fluids1.partSubtype,$index)">
          <div class="col-md-12 filterPointer" style="text-align: left;" ng-if="fluids1.note!='' || fluids1.partnumber!=''">
            <div class="col-md-5 filter_text">
                <div class="subcontent_font subcontentPadd" ng-bind="fluids1.partTypename"></div>


               
                <div class="note_font_hide" ng-if="fluids1.partTypename.length<=fluids1.partnumber.length && fluids1.note==''" ng-bind="fluids1.partTypename" style="color:#656161;"></div>
                <div class="note_font" ng-bind="fluids1.note"></div>
                <div class="note_font_hide" ng-if="fluids1.partnumber=='Need More Info'" ng-bind="fluids1.partTypename" style="color:#656161;"></div>

            </div>


             <div class="col-md-5 filters_arrow filter_column">
                <div class="row">
                  <div class="col-md-12 remove_padding_Left remove_padding_right">

                      <div class="col-md-6 partno_padd subcontent_font remove_padding_right partnoPaddTop" ng-bind="fluids1.partnumber" ng-if="fluids1.partnumber=='Need More Info'" style="font-size:13px;color: #dca942;">
                      </div>          
                      <div class="col-md-6 partno_padd subcontent_font partnoPaddTop" ng-bind-html="fluids1.partnumber" ng-if="fluids1.partnumber!='Need More Info'">
                      </div>
                      <div class="col-md-4 image_width" ng-if="fluids1.partnumber=='Need More Info'">
                       <img class="year_right_arrow" src="{{publicUrl}}image/right_arrow.png" alt="Right Arrow Icon">
                      </div>
                  </div>
                </div> 
            </div>
       </div>
    </div>


</div> 


</div>
</div>
</div>
<?php @include('footer.blade.php') ?> 
</div>


