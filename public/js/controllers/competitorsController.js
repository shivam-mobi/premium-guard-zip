app.controller('CompetitorsController', function ($scope, $http, $location, $window,$timeout,clickservice) {

   
    if($window.sessionStorage.length!=0)
    {
        let selected_product = JSON.parse($window.sessionStorage.getItem("product"));
        console.log(selected_product);
        let beahead= JSON.parse($window.sessionStorage.getItem("beahead"));



    if(beahead==null)
    {
        $scope.selected_product = selected_product.partNumber;
        $scope.selected_parttype_name = selected_product.partTypeName;
         $scope.ymm=selected_product.ymm;

    }
    else
    {
                    let beahead= JSON.parse($window.sessionStorage.getItem("beahead"));
                    $scope.selected_product = beahead.partNumber;
                    $scope.selected_parttype_name = beahead.partTypeName;
                    $scope.ymm=selected_product.ymm;
    }
        $scope.interchanges="";
        if ($scope.selected_product!='') {
            $http({
                method: 'GET',
                url: 'partInterchanges/' + $scope.selected_product,
            }).then(function (response) {
                

                 if(response.data.product.length>0){
                   $scope.interchanges=response.data.product;
                }
               
            });


            $scope.removeSelection = function (toberemoved) {
                        $location.path(toberemoved);
                    };

            }
   
    }
    else 
    {
        $location.path('/');
    }

//        
 });