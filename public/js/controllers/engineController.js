
app.controller('EngineController', function ($scope, $http, $location, $window, $timeout,clickservice) {


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

    $window.sessionStorage.removeItem('qualifier');
    $window.sessionStorage.removeItem('parttype');
   
    let selected_year = JSON.parse($window.sessionStorage.getItem("year"));
    let selected_make = JSON.parse($window.sessionStorage.getItem("make"));
    let selected_model = JSON.parse($window.sessionStorage.getItem("model"));
        
    $scope.selected_year = selected_year.yearId;
    $scope.selected_make = selected_make.makeName;
    $scope.selected_model = selected_model.modelName;


    if($scope.selected_year !== null || $scope.selected_make !== null || $scope.selected_model !== null){
        $http({
            method: 'GET',
            url: 'engineCollection/'+selected_model.baseVehicleID
        }).then(function(response) {

            var update_engine=response.data;

            for(var i=0;i<update_engine.length;i++)
            {
                if(update_engine[i].engine=="")
                {
                    update_engine[i].engine='-L';
                }
                else
                {
                    update_engine[i].engine=update_engine[i].engine;
                }
            }



            $scope.engine = update_engine;
        });



            $scope.displayNote = function(engine,engineId,ind){

                let selected_qualifier=[];
                selected_qualifier.push("noqualifier");

	            angular.element('#engineId' + ind).addClass("divBackground").animate({
	                padding: '5px'
	            }, 300, function () {
	                //$(this).click();
	                callAtTimeout();
	            });






            function callAtTimeout() {


                let parttype=0;
                let partsubtype=0;
                var response1='';


                $http({
            method: 'GET',
            url: 'qualifierCollection/'+selected_model.baseVehicleID+"/"+engineId+"/"+encodeURIComponent(selected_qualifier)+"/"+parttype+"/"+partsubtype,
        }).then(function(response) {
            
            
            // var response1=response.data.replace(/['"]+/g, '');
            
            
            
            if(typeof response.data=='object')
            {
                response1=response.data;
            }
            else
            {
                response1=response.data.replace(/['"]+/g, '');
            }
            
           
            $window.sessionStorage.setItem('engine', JSON.stringify({ engineId: engineId, engineName: engine }))

                if(response1=='fail')
                {
                    
                    $timeout(function()
                    {
                        $location.path('/productlist');
                    })
                }
                else
                {
                 
                   $timeout(function()
                    {
                        $location.path('/qualifier');
                    })
                }
        });





            }
        }
        $scope.removeSelection =  function(toberemoved){
            $location.path(toberemoved);
        }




    }

    }
    else{
        $location.path('/');
    }
});
