app.controller('NoVin', function ($scope, $http, $location, $window,$timeout,clickservice) {


    let searched_vin = JSON.parse($window.sessionStorage.getItem("novin"));

    $scope.setflag=false;

    $scope.search=searched_vin.searchterm;
    
    
    if($scope.search==null)
    {
        $scope.setflag=true;
    }

    console.log($scope.search);


});