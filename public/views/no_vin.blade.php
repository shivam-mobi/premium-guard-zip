<div class="backgr_color" ng-controller="NoVin">
<?php @include('heroSection.blade.php') ?>
<div class="row" >
   <div class="col-md-12">
      <div class="row filter_search margin_bottm">
          <a href="vinloadcamera" class="removeBorder"><span class="selected_product1">
           <
          </span></a>
          <span class="close_icon1">
            Back
          </span>
    </div>

   </div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="row filter_search margin_bottm" ng-if="setflag==false">
			<h5 id="no_information">We are unable to find {{search}}</h5>
		</div>
      <div class="row filter_search margin_bottm" ng-if="setflag==true">
			<h5 id="no_information">Input field cannot be blank</h5>
		</div>
	</div>
</div>


<?php @include('footer.blade.php') ?> 
</div>