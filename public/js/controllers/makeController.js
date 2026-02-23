app.controller('MakeController', function ($scope, $http, $location, $window,$timeout,clickservice) {

    let selectedvehicle_type = JSON.parse($window.localStorage.getItem("vehicletype"));

    if (selectedvehicle_type == null) {
        $scope.vehicle_type = 'Light Duty';
        $scope.vehicle_typeid='2';
        $window.localStorage.setItem('vehicletype', JSON.stringify({ VehicleTypeGroupID: $scope.vehicle_typeid,vehicletypeGroupName: $scope.vehicle_type}))
    } else {
        $scope.vehicle_type = selectedvehicle_type.vehicletypeGroupName;
        $scope.vehicle_typeid=selectedvehicle_type.VehicleTypeGroupID;
    }

    if($window.sessionStorage.length!=0)
    {
        

     let selected_year = JSON.parse($window.sessionStorage.getItem("year"));
    
        
    $scope.selected_year = selected_year.yearId;

    if ($scope.selected_year !== '') {
        $http({
            method: 'GET',
            url: 'makeCollection/'+ $scope.vehicle_typeid+'/'+$scope.selected_year,
        }).then(function (response) {
            $scope.makes = response.data;
          
        });

        $scope.displayModel = function (make, makeID, ind) {
            angular.element('#makeId' + ind).addClass("divBackground").animate({
                padding: '5px'
            }, 300, function () {
                //$(this).click();
                callAtTimeout();
      
            });
            function callAtTimeout() {
                $window.sessionStorage.setItem('make', JSON.stringify({ makeID: makeID, makeName: make }))
                $timeout(function()
                    {
                        $location.path('/model');
                    })
            }
        }

        $scope.removeSelection = function (toberemoved) {
            $location.path(toberemoved);
        };
    }

    }
    else
    {
        $location.path('/');
    }

});
