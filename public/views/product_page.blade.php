
<!DOCTYPE html>
<html>
<head>
   <!-- @include('header')-->
    <title>Pgfilter | Product Page</title>
</head>
<body class="backgr_color">
<!--@include('header1')-->
<?php @include('heroSection.blade.php') ?>
<div class="loader" id="loader"></div>    
<div class="row" ng-app="myApp" ng-controller="ProductPageController">
   <div class="col-md-12">

    
     
     <div class="row filter_search margin_bottm" ng-if="d=='dyanamic' && beahead==null">
          <span ng-if="interchange==''">
          <a href="/" class="removeBorder"><span class="selected_product1"><
          </span></a>
          <span class="close_icon1">
            Back
          </span>
        </span>

        <span ng-if="interchange=='int'">
          <a href="partinterchange" class="removeBorder"><span class="selected_product1"><
          </span></a>
          <span class="close_icon1">
            Back
          </span>
        </span>
    </div>

    <div class="row filter_search margin_bottm" ng-if="d=='latemodel' && beahead==null">
          <span ng-if="interchange==''">
          <a href="latemodel" class="removeBorder"><span class="selected_product1"><
          </span></a>
          <span class="close_icon1">
            Back
          </span>
        </span>
    </div>


    <div class="row filter_search margin_bottm" ng-if="d=='productcomp' && beahead==null">
          <span ng-if="interchange==''">
          <a href="productcomp" class="removeBorder"><span class="selected_product1"><
          </span></a>
          <span class="close_icon1">
            Back
          </span>
        </span>
    </div>

    <div class="row filter_search margin_bottm" ng-if="d==null  && beahead==null">
          <a href="productlist" class="removeBorder"><span class="selected_product1">
           <
          </span></a>
          <span class="close_icon1">
            Back
          </span>
    </div>


     <div class="row filter_search margin_bottm" ng-if="beahead!=null">
          <a href="be-ahead3" class="removeBorder"><span class="selected_product1">
           <
          </span></a>
          <span class="close_icon1">
            Back
          </span>
    </div>



    <div class="searchPaddingYear">
    <h1 ng-show="showApplications!=''" class="productpage_heading_font filter_font_showapp">
        {{selected_product}} - <span class="italic_font-showapp">{{selected_parttype_ymm}}</span>
    </h1>
    <h1 ng-show="showApplications==''" class="productpage_heading_font">
        {{selected_product}} - <span class="italic_font">{{selected_parttype_name}}</span>
    </h1>

    <!-- <div class="row imgbox_margin" style="background-color: white"  ng-repeat="imageInfo1 in imageInfo">

          <div class="col-xs-12 col-md-12 remove_padding_Left remove_padding_right" style="padding-top: 6px;" ng-if="!imageInfo1">

            <h3 class="noPhoto">Photo coming soon...</h3>

          </div>

        </div> -->

    <div class="row imgbox_margin" style="background-color: white" ng-if="imageInfo!=null">
        <div class="col-xs-12 col-md-12 remove_padding_Left remove_padding_right" style="padding-top: 6px;">
            <div class="col-xs-4 col-sm-2 col-md-1" style="color: black">
               <div class="thumbnailsDivImg" ng-repeat="imageInfo1 in imageInfo">
                 <div class="productImageTableDiv">
                   <div class="productImageThumbnailInside" ng-init="myFileName = 'https://www.pgfilters.com/wp-content/uploads1/assets/'">
                    <img  alt="{{selected_product}}_{{$index}}" class="img-responsive product-thumbnails" id="productthumb{{$index}}" ng-click="thumbnailImage(imageInfo1.fileName)" ng-src="{{myFileName}}{{imageInfo1.fileName}}">
                  </div>
                </div>
              </div>
            </div>

          <div class="col-xs-8 col-sm-9 col-md-11 img-zoom-container" style="text-align: center;">
                <img id="myImg" ng-src="{{path}}" class="thumbnail_image" alt="Thumbnail Image">
                <div id="myModal" class="modal">
                  <img class="modal-content" id="img01" alt="Modal Image">
                </div>
          </div>
            
        </div>

    </div>
   
    <div class="row year_search" ng-if="product_specs!=''">
        <div classs="col-md-12" style="text-align: left;" data-toggle="collapse" data-target="#product_spec" aria-controls="navbarContent" aria-label="Toggle navigation">
            <div class="col-md-6 year_arrow">
                <span class="subcontent_font">Product Specs</span>
            </div>
            <div class="col-md-4 vehicle_arrow">
               <a href=""><img class="RightYear_arrow" src="{{publicUrl}}image/down_arrow.png" alt="Down Arrow Icon"></a>
            </div>
            
       </div>
     </div>
 <div id="product_spec" class="collapse">
    <div class="searchPaddingProductSpecs" style="padding-left: 0">
          <div class="row filter_search ng-scope"  ng-repeat="product_specs1 in product_specs" ng-if="product_specs1.product_specs_name &&  product_specs1.product_specs_value" id="filter{{$index}}">
             <div class="col-md-12 filterPointer" style="text-align: left;">
                <div class="col-md-5 filter_text">
                    <div class="subcontent_font subcontentPadd" ng-bind="product_specs1.product_specs_name"></div>
                    <div class="subcontent_font subcontentPadd" ng-if="product_specs1.product_specs_value.length>=product_specs1.product_specs_name.length && windowWidth<768" ng-bind="product_specs1.product_specs_value" style="color:#656161;"></div>
                    
                </div>
                 <div class="col-md-5 filters_arrow filter_column">
                             
                          <div class="subcontent_font partnoPaddTop" ng-bind="product_specs1.product_specs_value">
                            
                          </div>
                          
                          
                     
                  
                </div>
            </div>
          
    </div>

  </div>
</div>

        
   
<div class="row   year_search" ng-if="package1!=''">
        <div classs="col-md-12" style="text-align: left;" data-toggle="collapse" data-target="#product_package" aria-controls="navbarContent" aria-label="Toggle navigation">
            <div class="col-md-6 year_arrow">
                <span class="subcontent_font">Package</span>
            </div>
            <div class="col-md-4 vehicle_arrow">
                <a href=""><img class="RightYear_arrow" src="{{publicUrl}}image/down_arrow.png" alt="Down Arrow Icon"></a>
            </div>
            
       </div>
</div>
<div id="product_package" class="collapse">
        <div class="searchPaddingProductSpecs" style="padding-left: 0">
          <div class="row filter_search" id="product_package{{$index}}" ng-click="" ng-repeat="(key,value) in package1">

            <div class="col-md-12 filterPointer" style="text-align: left;">
            <div class="col-md-5 filter_text">
                <div class="subcontent_font subcontentPadd" ng-bind="key"></div>
                
            </div>
             <div class="col-md-5 filters_arrow filter_column">
                
                                  
                      <div class="subcontent_font partnoPaddTop" ng-bind="value">
                        
                      </div>
                      
                  
            </div>
       </div>
       
    </div>
  </div>
</div>
<div class="row year_search" ng-click="getApplications()" id="applications{{$index}}" ng-if="applications!=null">
        <div classs="col-md-12" style="text-align: left;">
            <div class="col-md-6 year_arrow">
                <span class="subcontent_font">Applications</span>
            </div>
            <div class="col-md-4 vehicle_arrow">
                <img class="RightYear_arrow" src="{{publicUrl}}image/right_arrow.png" alt="Right Arrow Icon">
            </div>
       </div>
</div>

<div class="row year_search" ng-if="installation_instructions!=null">

            <div classs="col-md-12" style="text-align: left;" data-toggle="collapse" data-target="#installation_instructions" aria-controls="navbarContent" aria-label="Toggle navigation">

                <div class="col-md-6 year_arrow">
                    <span class="subcontent_font">Installation Instructions</span>
                </div>
                <div class="col-md-4 vehicle_arrow">
                    <img class="RightYear_arrow" src="{{publicUrl}}image/down_arrow.png" alt="Down Arrow Icon">
                </div>
            </div>
        </div>
        <div id="installation_instructions" class="collapse">
            <div class="searchPaddingProductSpecs" style="padding-left: 0">
                <div class="row filter_search" id="installation_instructions{{$index}}" ng-click="" ng-repeat="installation_instructions1 in installation_instructions">

                    <div class="col-md-12 filterPointer" style="text-align: left;">

                        <div class="col-md-12 filter_text_new">
                            <div class="productImageThumbnailInside" ng-init="myFileName = 'https://www.pgfilters.com/wp-content/resources-pdfs/'">
                                <a id="productthumb{{$index}}" ng-click="instructionPDF(installation_instructions1.resources_path)" ng-href="" ng-target="_blank">
                                    <div class="subcontent_font_new" ng-bind="installation_instructions1.resources_path"></div></a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

<div class="row year_search"  ng-click="getCometitiors()" id="competitors{{$index}}" ng-if="interchanges!=null">
        <div classs="col-md-12" style="text-align: left;">
            <div class="col-md-6 year_arrow">
                <span class="subcontent_font">Interchanges</span>
            </div>
            <div class="col-md-4 vehicle_arrow">
              <img class="RightYear_arrow" src="{{publicUrl}}image/right_arrow.png" alt="Right Arrow Icon">
            </div>
       </div>
    </div>
<div class="row year_search" ng-if="product_bulletins!=null">

            <div classs="col-md-12" style="text-align: left;" data-toggle="collapse" data-target="#product_bulletins" aria-controls="navbarContent" aria-label="Toggle navigation">

            <div class="col-md-6 year_arrow">
                    <span class="subcontent_font">Product Bulletins</span>
                </div>
                <div class="col-md-4 vehicle_arrow">
                    <img class="RightYear_arrow" src="{{publicUrl}}image/down_arrow.png" alt="Down Arrow Icon">
                </div>
            </div>
        </div>
        <div id="product_bulletins" class="collapse">
            <div class="searchPaddingProductSpecs" style="padding-left: 0">
                <div class="row filter_search" id="product_bulletins{{$index}}" ng-click="" ng-repeat="product_bulletins1 in product_bulletins">

                    <div class="col-md-12 filterPointer" style="text-align: left;">

                        <div class="col-md-12 filter_text_new">
                            <div class="productImageThumbnailInside" ng-init="myFileName = 'https://www.pgfilters.com/wp-content/resources-pdfs/'">
                                <a id="productthumb{{$index}}" ng-click="instructionPDF(product_bulletins1.resources_path)" ng-href="">
                                    <div class="subcontent_font_new" ng-bind="product_bulletins1.display_name"></div></a>
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
 <!--@include('footer')-->

<script type="text/javascript">
   // Get the modal
var modal = document.getElementById('myModal');

// Get the image and insert it inside the modal - use its "alt" text as a caption
var img = document.getElementById('myImg');
var modalImg = document.getElementById("img01");
var captionText = document.getElementById("caption");
img.onclick = function(){
    modal.style.display = "block";
    modalImg.src = this.src;
    modalImg.alt = this.alt;
    //captionText.innerHTML = this.alt;
}

// When the user clicks on <span> (x), close the modal
modal.onclick = function() {
    img01.className += " out";
    setTimeout(function() {
       modal.style.display = "none";
       img01.className = "modal-content";
     }, 400);
    // window.location.reload(true);
    
 }
 </script>
</body>
</html>
 

