
<div class="col-md-12 remove_padding_Left remove_padding_right late-model-container">
    <div class="text text-overlay">
            <div class="newLateModelSearch">
                    <p class="late-model-heading">LATE MODEL COVERAGE</p>
                    <p class="radio-button-para" ng-repeat ="ly in late_years" style="">
                            <input type="radio" id="{{ly.year}}" name="yearradio" ng-checked="false" ng-value="{{ly.year}}" ng-model="yearValue" ng-change="changeLateModel(ly.year)">
                            <label for="{{ly.year}}" class="newstyleforlabel">
                                <b class="newclassforcolor">{{ly.year}}</b>
                            </label>
                    </p>
            </div>
    </div>
</div>