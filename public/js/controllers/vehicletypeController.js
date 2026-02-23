app.controller('VehicleTypeController', function ($scope, $http, $location, $window,$timeout,clickservice) {
    $scope.items = [];
    $http.get('vehicletypeCollection').then(function (response) {
         //let selected_vehicletype[0]=

        $window.sessionStorage.clear();
        $scope.items = response.data;

    });

    $scope.displayYear = function (vehicletypeID,vehicletype, ind) {
        angular.element('#vehicletypeGroupId' + ind).addClass("divBackground").animate({
            padding: '5px'
        }, 300, function () {
            //$(this).click();
            callAtTimeout();
        });
        function callAtTimeout() {
            $window.localStorage.setItem('vehicletype', JSON.stringify({ VehicleTypeGroupID: vehicletypeID,vehicletypeGroupName: vehicletype }))
            console.log(localStorage);
            $timeout(function()
            {
                $location.path('/year');
            })
        }

    }

    $scope.removeSelection = function (toberemoved) {
        $location.path(toberemoved);
    }
});
