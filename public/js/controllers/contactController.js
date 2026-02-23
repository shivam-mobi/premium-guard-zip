app.controller('ContactController', function ($scope, $http, $location, $window,clickservice) {

    $scope.contact_form = {};
    $scope.successMsg="";
    $scope.submitForm = function() {
        $("#loader").show();
        $http({
          method  : 'POST',
          url     : 'postContactForm',
          data    : $scope.contact_form,
          headers : { 'Content-Type': 'application/x-www-form-urlencoded' } 
         })
          .then(function(data) {
         $scope.successMsg="Form submitted successfully.";
         $scope.contact_form = {};
         $scope.contact_form.emma_subscribe=true;
         $("#loader").hide();
        });
    }

});