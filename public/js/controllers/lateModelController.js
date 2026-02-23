app.controller('LateModelController', function ($scope,$route, $http, $location, $window,$timeout,clickservice) {
        
        //when page loaded when clicked year from home page
        $("#loader").show();

        $http.get('lateyears').then(function (response) {
            $scope.late_years = response.data.years;
        });

        var latemodel = $window.sessionStorage.getItem("latemodel");
        $scope.index = JSON.parse($window.sessionStorage.getItem("index"));
        latemodel = parseInt(latemodel);
        $scope.yearValue = latemodel;

        $scope.getResult = function() {

        
            //if come back from product page
            if($scope.index!=null) {
                
                $scope.active = $scope.index.index;
                $window.sessionStorage.setItem('index',JSON.stringify({index:$scope.index.index,parttypename:$scope.index.parttypename}));
                var send_index = $scope.index.index + 1;
                var send_parameter = 'lateallresult/'+$scope.yearValue+'/'+send_index+'/'+$scope.index.parttypename;
                $scope.index = null;
            }
            else {
                $scope.active = 0;
                $window.sessionStorage.setItem('index',JSON.stringify({index:0,parttypename:'Oil Filter'}));
                var send_parameter = 'lateallresult/'+$scope.yearValue;
            }

            $http.get(send_parameter).then(function (response) {
                $scope.allresults = response.data.allresult;
                $scope.allparttypes = response.data.parttypes;

                $("#loader").hide();
            });
        }

        $scope.getResult();
        

        //when year changed
        $scope.changeLateModel = function(year) {

            $scope.active = 0;

            $scope.yearValue = year;
            $window.sessionStorage.setItem('latemodel',$scope.yearValue);
            $("#loader").show();
            $scope.yearValue = parseInt(year);
            $scope.getResult();
        }


        //when parttype changed
        $scope.tabChanged = function(index,parttypename) {

            $("#loader").show();

            $window.sessionStorage.setItem('index',JSON.stringify({index:index,parttypename:parttypename}));

            var send_index = index + 1;
            $http.get('lateallresult/'+$scope.yearValue+'/'+send_index+'/'+parttypename).then(function (response) {
                $scope.allresults = response.data.allresult;

                $("#loader").hide();
            });

            
        }

        $scope.Productpage = function(partnumber,year,make,model,parttypename) {

            $("#loader").show();
            let make_update = make.replace(/ /g,"+");
            let model_update = model.replace(/ /g,"+");
    
            let ymm = year+"-"+make_update+"-"+model_update;
            console.log(ymm);
    
            var latemodel="latemodel";
            $window.sessionStorage.setItem('latemodelcoverage', JSON.stringify({latemodel:latemodel}));
            $window.sessionStorage.setItem('product', JSON.stringify({partNumber: partnumber,partTypeName:parttypename,ymm:ymm}));
       
            $timeout(function()
            {
               $("#loader").hide();
               $location.path('/product-page/'+partnumber+"/"+ymm+"/"+parttypename); 
            })
        }
                
});