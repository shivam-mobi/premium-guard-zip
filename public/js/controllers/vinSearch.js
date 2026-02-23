app.controller('vinSearch', function ($scope, $http, $location, $window, $timeout,clickservice){


                $scope.vinSearch = function(string)
            {
                $window.sessionStorage.clear();
                var searchterm_vin=string;
                
                if(searchterm_vin!="")
                {

                    $("#loader").show();

                    $http({
                        method: 'GET',
                        url: 'vinCollection/'+searchterm_vin      
                    }).then(function(response) 
                    {

                        let save_result=[];

                        let save_result1=[];
                        var selected_qualifier='noqualifier';

                        save_result=response.data.res;

                    if(save_result=='nodata')
                    {
                        $window.sessionStorage.setItem('novin', JSON.stringify({searchterm:searchterm_vin}));
                                $timeout(function()
                                   {
                                       $location.path('/no-vin'); 
                                   })

                    }
                    else
                    {

                        save_result1=save_result.split(',');
                        var bvid=save_result1[0];
                        var engineid=save_result1[1];
                        var engine=save_result1[2]+'L';
                        var model=save_result1[4];
                        var make=save_result1[3];
                        var year=save_result1[5];
                        var parttype=0;
                        var partsubtype=0;
                        var response1='';



                       var vinsearch="vin";
                       $window.sessionStorage.setItem('vin', JSON.stringify({vinsearch:vinsearch}));
                       $window.sessionStorage.setItem('year', JSON.stringify({ yearId: year }));
                       $window.sessionStorage.setItem('make', JSON.stringify({makeName: make }));
                       $window.sessionStorage.setItem("model",JSON.stringify({baseVehicleID:bvid,modelName:model}));
                       $window.sessionStorage.setItem('engine', JSON.stringify({engineId: engineid,engineName: engine }));




                       $http({
                           method: 'GET',
                           url: 'qualifierCollection/'+bvid+"/"+engineid+"/"+encodeURIComponent(selected_qualifier)+"/"+parttype+"/"+partsubtype,       
                       }).then(function(response) {


               
               
               
                         if(typeof response.data=='object')
                         {
                             response1=response.data;
                         }
                         else
                         {
                             response1=response.data.replace(/['"]+/g, '');
                         }


                         $("#loader").hide();
               
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
                        






                    });

                }



            }


            $scope.vinCameraSearch = function()
            {

                

            }



    });
    
    