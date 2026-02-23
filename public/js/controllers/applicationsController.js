app.controller('ApplicationsController', function ($scope, $http, $location, $window,$timeout,clickservice) {



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
   
    
    $scope.applications="";
    
    
    let applications1=[];
    let min="";
    let max="";
    let diff="";
    
    
    if ($scope.selected_product!='') {
        

        $("#loader").show();
        $http({
            method: 'GET',
            url: 'prodApplication_new/' + $scope.selected_product,
        }).then(function (response) {
           
           
            if(response.data.applications.length>0){
                $scope.applications=response.data.applications;
            }
            else {
                $scope.applications=null;
            }

            $("#loader").hide();
                       
        });


        $scope.removeSelection = function (toberemoved) 
        {
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