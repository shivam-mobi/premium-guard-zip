
app.controller('ModelController', function ($scope, $http, $location, $window, $timeout,clickservice) {

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
        // var x=document.URL;

    $window.sessionStorage.removeItem('qualifier');
    $window.sessionStorage.removeItem('parttype');
   
    let selected_year = JSON.parse($window.sessionStorage.getItem("year"));
    let selected_make = JSON.parse($window.sessionStorage.getItem("make"));
       
    $scope.selected_year = selected_year.yearId;
    $scope.selected_make = selected_make.makeName;

    if($scope.selected_year !== null || $scope.selected_make !== null){
        $http({
            method: 'GET',
            url: 'modelCollection/'+$scope.vehicle_typeid+'/'+$scope.selected_year+'/'+selected_make.makeID,
        }).then(function(response) {
            $scope.model = response.data;
        });










        $scope.displayEngine = function(model,baseVehicleID,ind) {

            $window.sessionStorage.setItem("model",JSON.stringify({baseVehicleID:baseVehicleID,modelName:model}));
            $http({
                method: 'GET',
                url: 'engineCheckCollection/'+baseVehicleID,
            }).then(function(response) {




                angular.element('#modelId'+ind).addClass("divBackground").animate({
                   padding:'5px'
               },300, function() {
                 //$(this).click();
                 callAtTimeout();
               });


            function callAtTimeout() {
                var abc= response.data;
                let labelcheck=0;

                for(let i=0;i<abc.length;i++)
                {
                    if(abc[i].engine=="")
                    {
                        labelcheck=1;
                    }
                    else
                    {
                        labelcheck=0;
                    }

                }

                if(abc.length!=0)
                {
                    var engineid=abc[0]['EngineConfigID'];
                }
                else
                {
                    var engineid="";
                }


                let selected_qualifier=[];
                selected_qualifier.push("noqualifier");

                if(engineid!="")
                {

                    let parttype=0;
                    let partsubtype=0;
                    var response1='';

                    if(labelcheck==0)
                        {
                                   $timeout(function()
                            {
                                $location.path('/engine');
                            })
                        }
                        else
                        {

                            $http({
                                method: 'GET',
                                url: 'qualifierCollection/'+baseVehicleID+"/"+engineid+"/"+encodeURIComponent(selected_qualifier)+"/"+parttype+"/"+partsubtype,
                            }).then(function(response) {

                                if(typeof response.data=='object')
                                {
                                    response1=response.data;
                                }
                                else
                                {
                                    response1=response.data.replace(/['"]+/g, '');
                                }
                
                
                                if(response1=='fail')
                                {
                                    
                                    $window.sessionStorage.setItem('engine', JSON.stringify({ engineId: engineid, engineName:'noengine'}))
                                        $timeout(function()
                                    {
                                        $location.path('/productlist');
                                    })
                                }
                                else
                                {
                                     
                                        $window.sessionStorage.setItem('engine', JSON.stringify({ engineId: engineid, engineName:'noengine'}))
                                    $timeout(function()
                                    {
                                        $location.path('/qualifier');
                                    })
    
                                }

                            });

                           

                        }

                }
                else
                {

                        $timeout(function()
                            {
                                $location.path('/no_information');
                            })
                }
            }


            });
        }


        $scope.removeSelection =  function(toberemoved)
        {
            $location.path(toberemoved);
        }
    }


    }
    else
    {
        $location.path('/');
    }
});
