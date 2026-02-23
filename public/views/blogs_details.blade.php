<div class="static_pages_main" ng-controller="blogdetailsController">
    <?php @include('heroSection.blade.php') ?>
    <div class="row bulletins_main_row">
        <div class="container-fluid">
            <div class="loader" id="loader"></div>
            <div class="col-md-10  col-sm-10 col-md-offset-1 col-sm-offset-1">
                <div class="col-md-12">
                    <div class="row filter_search margin_bottm">
                        <a href="blogs" class="removeBorder"><span class="selected_product1">
                        <
                        </span></a>
                        <span class="close_icon1">
                            Back
                        </span>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-10 col-md-10 col-lg-11 techtipscontent">
                    <h1 class="">{{titles}}</h1><br>
                    <img src="{{srcimage}}" class="techtipimage" alt="Blog Image"><br>
                    <div class="techtipparagraph" ng-bind-html="finaldata"></div>
                    <div class="row">
                        <div class="col-sm-3 all_images_div"  ng-if="all_images.length > 0" ng-repeat="imgsrc in all_images">
                            <img ng-src="{{imgsrc}}"  class="blogsimage" alt="Blog Image"><br>
                        </div>
                    </div>
                  
                </div>
            </div>
        </div>
    </div>
    <?php @include('footer.blade.php') ?>
</div>





