<div class="backgr_color" ng-controller="VoiceSearchController">
<?php @include('heroSection.blade.php') ?>
    <div class="row" >
        <div class="col-md-12" >
            <div class="row filter_search margin_bottm">
                <a href="/" class="removeBorder"><span class="selected_product1">
                <
                </span></a>
                <span class="close_icon1">
                    Back 
                </span>
            </div>
            <div class="row">
                <div class="col-md-12" style="margin-top: 3px;text-align:center;background-color:white;padding:20px 0px;"> 
                   <p style="color:black;">{{ voice_result }}</p>
                <i id="microphone-icon" class="fa fa-microphone microphone-icon" ng-click="microhoneClicked()" style="color:#dca942;font-size:20px;"></i>
                        <i id="loader-icon" class="fa fa-spinner fa-spin microphone-icon" aria-hidden="true" style="font-size: 24px; display: none;"></i>                           
                </div>
            </div>
        </div>
    </div>
<?php @include('footer.blade.php') ?> 
</div>