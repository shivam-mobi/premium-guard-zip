<div class="static_pages_main" ng-controller="staticPagesController">
<?php @include('heroSection.blade.php') ?>
<div class="row">
	
  
    <div class="container-fluid">
    <div class="row">
        <div class="col-md-10  col-sm-10 col-md-offset-1 col-sm-offset-1">
            <div class="col-md-12">
                <h1 class="heading-style-behead">Be Ahead – 1st to Market</h1>
            </div>
            <div class="row first-section-beahead" ng-repeat="b in beahead">

                <div class="col-md-4 sub-section-beahead" ng-if="b.pgpartnumber!=null">
                    <a   ng-click="Productpage(b.partNumber,b.partTypename,b.partType)" ng-init="myFileName = 'https://www.pgfilters.com/wp-content/uploads/'">
                      <img class="left-right-padding" ng-src="{{myFileName}}{{b.filename}}" alt="Beahead Image"></a>
                </div>
                <div class="col-md-4 sub-section-beahead" ng-if="b.pgpartnumber==null">
                      <a   ng-click="" ng-init="myFileName = 'https://www.pgfilters.com/wp-content/uploads/'"><img class="left-right-padding" ng-src="{{myFileName}}{{b.filename}}" alt="Beahead Image"></a>
                </div>
                <div class="col-md-8 sub-section-beahead">

                    <table class="table table-striped table-bordered first-table">
                          
                          <tbody>
                            <tr ng-if="b.pgpartnumber!=null">
                              <th class="th-width">PG Part No.</th>
                              <td class="td-width"><a  ng-click="Productpage(b.partNumber,b.partTypename,b.partType)" class="linktoproduct">{{b.partNumber}}</a></td>
                            </tr>
                            <tr ng-if="b.pgpartnumber==null">
                              <th class="th-width">PG Part No.</th>
                              <td class="td-width">{{b.partNumber}}</td>
                            </tr>
                            <tr>
                              <th class="th-width">OE Part No.</th>
                              <td class="td-width">{{b.OE_partno}}</td>
                            </tr>
                            <tr>
                            
                              <th class="th-width">Description</th>
                              <td class="td-width">{{b.Description}}</td>
                            </tr>
                            <tr>
                              <th class="th-width">Primary Applications</th>
                              <td class="td-width">{{b.application}}</td>
                            </tr>
                          </tbody>
                        </table>
                
                </div>
            </div>        
</div>
</div>
</div>
</div>
<?php @include('footer.blade.php') ?> 
</div>