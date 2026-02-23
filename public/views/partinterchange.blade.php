<div class="backgr_color" ng-controller="PartInterchange">
<?php @include('heroSection.blade.php') ?>
<div class="row">
   <div class="col-md-12">
        <div class="row filter_search margin_bottm">

                    <div class="row filter_search margin_bottm" ng-if="d=='dyanamic'">
                        <div class="row">
                        <a href="/" class="removeBorder"><span class="selected_product1">
                        <
                        </span></a>
                        <span class="close_icon1">
                            Back
                        </span>
                        </div>
                    </div>
        
        </div>



        <div class="row">
        <div class="col-md-12 remove_padding_Left remove_padding_right">
            <div class="searchPaddingYear">
            <table style="width:100%;border-collapse: separate;border-spacing: 0px 10px;" class="table-int">
            
                <div class="loader" id="loader"></div>
                <tr>
                    <th class="interchange_text_hea"><span class="interchange_font applic_padd">Search No.</span></th>
                    <th class="interchange_text_hea"><span class="interchange_font applic_padd">Name</span></th>
                    <th class="interchange_text_hea"><span class="interchange_font applic_padd">Premium Guard</span></th>
                    <th class="interchange_text_hea"><span class="interchange_font applic_padd"></th>
                    </tr>
                
                    <tr ng-repeat="interchange1 in interchange" id="interchanges{{$index}}" ng-click="getProduct(interchange1.partIntPartNumber,interchange1.interchange_name,interchange1.partNumber,$index)">  
                        <td class="interchange_text inttext1"><span class="interchange_font applic_padd">{{ interchange1.partIntPartNumber }}</span></td>  
                        <td class="interchange_text inttext2"><span class="interchange_font applic_padd">{{ interchange1.interchange_name }}</span></td>
                        <td class="interchange_text inttext2"><span class="interchange_font applic_padd">{{interchange1.partNumber}}</span></td>
                        <td class="interchange_text inttext3"><span class="interchange_font applic_padd"><img class="year_right_arrow interchnage_arrow" src="{{publicUrl}}image/right_arrow.png" alt="Right Arrow Icon"></span></td>
                    </tr> 
            </table>
        </div>
        </div>
        </div>
        
    </div>


</div>
<?php @include('footer.blade.php') ?> 
</div>


