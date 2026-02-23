app.controller('PartInterchange', function ($scope, $http, $location, $window,$timeout,clickservice) {


    if($window.sessionStorage.length!=0)
    {


        let dyanamic= JSON.parse($window.sessionStorage.getItem("dyanamic"));
        let partinterchangeid=JSON.parse($window.sessionStorage.getItem("partinterchnage"));


        if(dyanamic!=null)
        {
            $scope.d=dyanamic.dyanamic;


            if(partinterchangeid!=null)
            {
                let store_id_partintno=partinterchangeid.partintno_id;
                let split_id_name=store_id_partintno.split("===");
                let split_intname=split_id_name[0].trim();
                let split_intno=split_id_name[1].trim();
                
              


                if(split_intname!="" && split_intno!="")
                {

                    $http({
                        method: 'GET',
                        url: 'partInterchangeResponse/' +encodeURIComponent(split_intname)+"/"+split_intno,
                    }).then(function (response) {

                        $scope.interchange=response.data.res;

                       

                    });



                }

            }

            
        }




        $scope.getProduct = function(partIntPartNumber,interchange_name,partNumber,ind){

            $("#loader").show();
            $scope.pnum=partNumber.replace("/","+");


            $http({
                method: 'GET',
                url: 'partInterchangeSuperseded/' +encodeURIComponent(partNumber),
            }).then(function (response) {

                $scope.superseded=response.data[0];

                $scope.superseded_pt=$scope.superseded.partType;
                $scope.superseded_parttypename=$scope.superseded.partTypename;
                $scope.showPart=$scope.pnum;

                $http({
                    method: 'GET',
                    url: 'premiumCollection/'+encodeURIComponent(partNumber),       
                }).then(function(response) {


                    $scope.productCount=response.data[0];

                    var PartTypeName='';
                    var ptypefordis='';
                    var partT='';


              if($scope.superseded_pt=="4")
              {
                PartTypeName="Fuel-Filter";
                ptypefordis=$scope.showPart.substr(0, 2);
                partT=ptypefordis.toUpperCase();

                    if(partT=="DF")
                    {
                        PartTypeName="Diesel-Fuel-Filter";
                    }
              }
              else if($scope.superseded_pt=='1' || $scope.superseded_pt=='7' || $scope.superseded_pt=='12')
              {
                PartTypeName = "Oil-Filter";
              }
              else if($scope.superseded_pt=='2' || $scope.superseded_pt=='13')
              {
                PartTypeName="Air-Filter";
              }
              else if($scope.superseded_pt == '3' || $scope.superseded_pt == '16')
              {
                PartTypeName="Cabin-Air-Filter";
              }else if($scope.superseded_pt=='10' || $scope.superseded_pt == '18')
              {
                PartTypeName = "Drain-Plug";
              }
              else if($scope.superseded_pt=='14')
              {
                PartTypeName="Housing-Cap";
              }
              else if($scope.superseded_pt=='8' || $scope.superseded_pt=='9' || $scope.superseded_pt=='11' || $scope.superseded_pt=='15' || $scope.superseded_pt=='17')
              {
                PartTypeName = "Wiper-Blade";
              }
              else
              {
                PartTypeName=$scope.superseded_parttypename.replace(" ","-");
              }

              var ymmt='';
              var ymm='';


              


              if($scope.productCount.year!='')
              {
                    ymmt=$scope.productCount.year+"-"+$scope.productCount.make.replace(" ","-")+"-"+$scope.productCount.model.replace(" ","-");
                    ymm=ymmt.replace(" ","-");
                    ymm=ymmt.replace("/","+");



                    if($scope.showPart!="NSO" && $scope.showPart!="NSA" && $scope.showPart!="NSC" && $scope.showPart!="NSF" && $scope.showPart!="NST" && $scope.showPart!="TBA")
                {
                    $window.sessionStorage.setItem('product', JSON.stringify({partNumber:$scope.showPart,partTypeName:PartTypeName,ymm:ymm}));

                            $("#loader").hide();
                            $timeout(function()
                                {
                                   $location.path('/product-page/'+$scope.showPart+"/"+ymm+"/"+PartTypeName); 
                                })
                }



              }
              else
              {
                ymm='';
              }


           


              




                });





            });

        }

       



    }
    else
    {
            $location.path('/');
    }


});