<div class="static_pages_main" ng-controller="ContactController">
<?php @include('heroSection.blade.php') ?>
<div class="row">
<div class="container-fluid">
<div class="row">
<div class="col-md-12 contact-page-main no-padding-contact">
<div class="col-md-6 paddingRemove">
<div class="col-md-12">
<div class="loader" id="loader"></div>
<h1 class="heading-style-contact">Contact Premium Guard Inc.<h1>
</div>
<div class="col-md-12">
<p>
At Premium Guard Inc., we are dedicated to creating consistent value for our customers in all areas of quality, service, and pricing by providing comprehensive first to market coverage for automotive applications. If you have a question, comment, concern, or just need more information about our company, then please fill out the form or contact us via email at <a href="mailto:info@pgfilters.com">pgfilters@premiumguard.com</a>
</p>
</div>
</div>
<div class="col-md-6 paddingRemove">
<form ng-submit="submitForm()" name="contactForm">
<div class="col-md-12">
<div class="col-md-12">
<h3 class="successMsg">{{successMsg}}</h3>
</div>
</div>
<div class="col-md-12 contact_form_row paddingRemove">
<div class="col-md-4">
<input type="text" name="full_name" placeholder="Full Name*" class="contact_form_fields" ng-model="contact_form.full_name" required>
</div>
<div class="col-md-4 contact_form_row_mobile">
<input type="text" name="phone" placeholder="Phone*" class="contact_form_fields" ng-model="contact_form.phone" required>
</div>
<div class="col-md-4 contact_form_row_mobile">
<input type="email" name="email" placeholder="Email*" class="contact_form_fields" ng-model="contact_form.email" required>
</div>
</div>
<div class="col-md-12 contact_form_row paddingRemove">
<div class="col-md-12">
<input type="text" name="company" placeholder="Company*" class="contact_form_fields" ng-model="contact_form.company" required>
</div>
</div>
<div class="col-md-12 contact_form_row paddingRemove">
<div class="col-md-12">
<input type="text" name="message" placeholder="Message*" class="contact_form_fields" ng-model="contact_form.message" required>
</div>
</div>
<div class="col-md-12 contact_form_row paddingRemove">
<div class="col-md-12">
<span>PG Filters Newsletter – OptIn:</span> <input type="checkbox" value="1" name="emma_subscribe" class="optIn" ng-model="contact_form.emma_subscribe" ng-init="contact_form.emma_subscribe=true">
</div>
</div>
<div class="col-md-12 contact_form_row paddingRemove">
<div class="col-md-12">
<input type="submit" class="contact_form_submit" value="Submit" ng-disabled="contactForm.full_name.$invalid || contactForm.phone.$invalid || contactForm.email.$invalid || contactForm.company.$invalid || contactForm.message.$invalid ">
</div>
</div>
</form>
</div>
</div>
</div>
</div>
</div>
<?php @include('footer.blade.php') ?> 
</div>

 

