app.controller('LateModelHeroController', function ($scope,$route, $http, $location, $window,$timeout,clickservice) {


    sessionStorage.clear();


    //get latest years
    $http.get('lateyears').then(function (response) {
        $scope.late_years = response.data.years;
    });

    $scope.changeLateModel = function(year) {
        $("#loader").show();
        $scope.year_send = year;
        $window.sessionStorage.setItem('latemodel',$scope.year_send);
        $timeout(function()
        {
            $location.path('/latemodel'); 
        })
    }
});