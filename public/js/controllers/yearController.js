app.controller('YearController', function ($scope, $http, $location, $window,$timeout,clickservice) {
  $scope.items = [];
  
  
    $window.sessionStorage.removeItem('qualifier');
    $window.sessionStorage.removeItem('parttype');
    $window.sessionStorage.removeItem('hidden_parttype');
    $window.sessionStorage.removeItem('dyanamic');
    $window.sessionStorage.removeItem('product');
  
  
    let selectedvehicle_type = JSON.parse($window.localStorage.getItem("vehicletype"));
        
    if (selectedvehicle_type == null) {
            $scope.vehicle_type = 'Light Duty';
            $scope.vehicle_typeid='2';
            $window.localStorage.setItem('vehicletype', JSON.stringify({ VehicleTypeGroupID: $scope.vehicle_typeid,vehicletypeGroupName: $scope.vehicle_type}))
    } else {
            $scope.vehicle_type = selectedvehicle_type.vehicletypeGroupName;
            $scope.vehicle_typeid=selectedvehicle_type.VehicleTypeGroupID;
    }

  
  
  
    if($window.sessionStorage.length!=0 || $window.sessionStorage.length==0) {
        
        

        $http.get('yearCollection/'+$scope.vehicle_typeid).then(function (response) {
            
           

            $scope.items = response.data;
            
            

        });

        $scope.displayMake = function (year, ind) {
            angular.element('#yearId' + ind).addClass("divBackground").animate({
                padding: '5px'
            }, 300, function () {
                //$(this).click();
                callAtTimeout();
            });

            function callAtTimeout() {
                $window.sessionStorage.setItem('year', JSON.stringify({yearId: year}))
                $timeout(function () {
                    $location.path('/make');
                })
            }

        }

        $scope.removeSelection = function (toberemoved) {
            $location.path(toberemoved);
        }
    }
  });
