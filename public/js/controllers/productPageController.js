app.controller('ProductPageController', function ($scope, $http, $location, $window,$timeout,clickservice) {

	console.log("h1");
    $("#loader").hide();

    if($window.sessionStorage.length!=0)
    {
            $scope.windowWidth=$(document).width();
            let selected_year = JSON.parse($window.sessionStorage.getItem("year"));
            let selected_make = JSON.parse($window.sessionStorage.getItem("make"));
            let selected_model = JSON.parse($window.sessionStorage.getItem("model"));
            let selected_engine = JSON.parse($window.sessionStorage.getItem("engine"));
            let selected_product = JSON.parse($window.sessionStorage.getItem("product"));
            let dyanamic= JSON.parse($window.sessionStorage.getItem("dyanamic"));
            let productcomp= JSON.parse($window.sessionStorage.getItem("productcomp"));
            let latemodel= JSON.parse($window.sessionStorage.getItem("latemodelcoverage"));
            let partinterchangeid=JSON.parse($window.sessionStorage.getItem("partinterchnage"));
            let beahead= JSON.parse($window.sessionStorage.getItem("beahead"));
            let selected_vin = JSON.parse($window.sessionStorage.getItem("vin"));

            $scope.interchange='';


        if(beahead==null)
        {
                    $scope.beahead=null;
                
                        if((dyanamic==null && productcomp==null && latemodel==null) || selected_year!=null)//classic search or d.search normal
                    {
                        $scope.selected_year = selected_year.yearId;
                        $scope.selected_make = selected_make.makeName;
                        $scope.selected_model = selected_model.modelName;
                        $scope.selected_engine = selected_engine.engineName;

                        $scope.d=null;
                        
                    }
                    else//partnumber or partintnumber
                    {
                        $scope.selected_year=null;

                        if(productcomp!=null) {
                            $scope.d=productcomp.productcomp;
                        }
                        else if(latemodel!=null) {
                            $scope.d=latemodel.latemodel;
                        }
                        else {
                            $scope.d=dyanamic.dyanamic;
                        }
                        
                        
                        if(partinterchangeid!=null)
                        {
                            $scope.interchange='int';
                        }
                    }

                    var parttype = selected_product.partTypeName.replace(/-/g, ' ');
                    
                    if(selected_year!=null && selected_make!=null && selected_model!=null && selected_product!=null && selected_vin==null) {
                        $scope.showApplications = 'show';
                        var pt_ymm = selected_product.ymm.replace(/-/g, ' ');
                        pt_ymm = pt_ymm.replace(/\+/g, ' ');
                        $scope.selected_parttype_ymm = pt_ymm+" "+parttype;
                    }
                    else {
                        $scope.showApplications = '';
                        $scope.selected_parttype_name = parttype;
                    }

                    $scope.selected_product = selected_product.partNumber;
                    
        }
        else
        {
                    var parttype = selected_product.partTypeName.replace(/-/g, ' ');
                    $scope.showApplications = '';
                    let beahead= JSON.parse($window.sessionStorage.getItem("beahead"));
                    $scope.beahead=beahead.partNumber;
                    $scope.selected_product = beahead.partNumber;
                    $scope.selected_parttype_name =  parttype;

        }





    

    $scope.product_specs="";
    $scope.product_specs1="";
    $scope.imageInfo="";
    $scope.package="";
    $scope.path="";
    $scope.package1="";
    //let Math = window.Math;
    let product_value="";
    let product_value1="";
    let PAID=[14,19,24,25,31,531,534,568,569,577,578,579,1172,9036];
    let PAID1=[];
    let s1=0;

    
    //var f="";
    
    if ($scope.selected_product!='') {


        $("#loader").show();

        $http({
            method: 'GET',
            url: 'productPage/' + $scope.selected_product,
        }).then(function (response)
        {
            if(response.data.product_specs.length>0)
            {

                $scope.product_specs = response.data.product_specs;
                
                
            //     // added by aarti for dimensions
            // for(let i=0;i<response.data.product_specs.length;i++){
            //     if(response.data.product_specs[i].PAID==534 || response.data.product_specs[i].PAID==9036){
            //         if(response.data.product_specs[i].value.includes('MM')){
            //             response.data.product_specs[i].value=response.data.product_specs[i].value.split(" ");
            //             console.log(response.data.product_specs[i].value);
            //             response.data.product_specs[i].value=response.data.product_specs[i].value[0]/25.4;
            //             response.data.product_specs[i].value=response.data.product_specs[i].value.toFixed(2);
            //             response.data.product_specs[i].value=response.data.product_specs[i].value+' IN';
            //         }
            //         else{
            //            response.data.product_specs[i].value=response.data.product_specs[i].value;
            //         }
            //     }

            //     if(response.data.product_specs[i].PAID==627 || response.data.product_specs[i].PAID==-627){
            //          if(response.data.product_specs[i].value.includes('KG')){
            //              response.data.product_specs[i].value=response.data.product_specs[i].value.split(" ");
            //              response.data.product_specs[i].value=response.data.product_specs[i].value[0]*2.205;
            //              response.data.product_specs[i].value=response.data.product_specs[i].value.toFixed(2);
            //              response.data.product_specs[i].value=response.data.product_specs[i].value+' LBS';
            //          }
            //          else if(response.data.product_specs[i].value.includes('PG')){
            //              response.data.product_specs[i].value=response.data.product_specs[i].value.split("PG");
            //              response.data.product_specs[i].value=response.data.product_specs[i].value[0]*2.20462E-15;
            //              response.data.product_specs[i].value=response.data.product_specs[i].value*1E15;
            //              response.data.product_specs[i].value=response.data.product_specs[i].value.toFixed(2);
            //              response.data.product_specs[i].value=response.data.product_specs[i].value+' LBS';
            //          }
            //          else{
            //              response.data.product_specs[i].value=response.data.product_specs[i].value;
            //          }
            //     }
            // }
            // // added by aarti for dimensions end
            
            
            
            
            //    let product_specs1=response.data.product_specs.filter(function (e)
            //    {
            //     if(PAID.indexOf(e.PAID)!=-1)
            //     {
                    
            //         product_value=e.value.replace("IN", " ");
            //         product_value1=parseFloat(product_value).toFixed(2);
            //         e.value=product_value1;
            //     }
            //     else
            //     {
            //         product_value=e.value.replace("IN", " ");
            //         function uppercase(str)
            //             {

            //               var array1 = str.split(' ');
            //               var newarray1 = [];
                            
            //               for(var x = 0; x < array1.length; x++)
            //               {
            //                   newarray1.push(array1[x].charAt(0).toUpperCase()+array1[x].slice(1));
            //               }
            //               return newarray1.join(' ');
            //             }

            //         e.value=uppercase(product_value);
            //     }

            //     let all_specs=0;
            //     if(e.PAID == 577)
            //     {
            //             e.PAName="Length";
            //             PAID1.push(577);
            //     }
            //     else if(e.PAID == 578)
            //     {
            //         e.PAName="Width"; 
            //         PAID1.push(578);
            //     }
            //     else if(e.PAID == 579)
            //     {
            //         e.PAName="Height";
            //         s1=1;
            //         PAID1.push(579);
            //     }
            //     else
            //     {
            //         if(e.PAID == 31)
            //         {
            //            s1=1;
            //         }

            //     }

            //     if((e.PAID == 19 && s1==1) || (PAID1.indexOf(577)!=-1 && e.PAID == 14) || (PAID1.indexOf(578) !=-1 && e.PAID == 24) || (PAID1.indexOf(579)!=-1 && e.PAID == 31) ){
            //                                       all_specs=1;
            //                                   }
            //     return (all_specs==0);

            // });
               
            // $scope.product_specs=product_specs1;

            }
            
            if(response.data.imageInfo.length>0)
            {
               $scope.imageInfo=response.data.imageInfo;
               $scope.names = []; 
               var flag=0; 

                angular.forEach($scope.imageInfo,function(value,index){

                    if(flag==0)
                    {
                        $scope.names.push(value.fileName);
                    }
                    flag++;
                });

                $scope.path="https://www.pgfilters.com/wp-content/uploads1/assets/"+$scope.names;


            }
            else
            {
                $scope.imageInfo=null;
            }

            if(response.data.installation_instructions.length>0)
            {
                $scope.installation_instructions=response.data.installation_instructions;
                console.log(response.data.installation_instructions);
            }
            else
            {
                $scope.installation_instructions=null;
            }

            if(response.data.product_bulletins.length>0)
            {
                $scope.product_bulletins1=response.data.product_bulletins;


                $scope.product_bulletins1.forEach(function (e,index)
                {
                    var path_PDF = e.resources_path;
                    var PDF_split_path = path_PDF.split("/");
                    e.display_name = PDF_split_path.slice(-1)[0]; 

                });

                $scope.product_bulletins = $scope.product_bulletins1;
                console.log(response.data.product_bulletins);
            }
            else
            {
                $scope.product_bulletins=null;
            }

            if(response.data.package.length>0)
            {
                $scope.package=response.data.package;
                $scope.package1 = $scope.package[0]; 
            }

            $("#loader").hide();


        $("#loader").show();
        $http({
            method: 'GET',
            url: 'prodapplicationCount/' + $scope.selected_product,
        }).then(function (response) 
        {
            if(response.data.length > 0 ){
                var row_cnt  = parseInt(response.data[0].row_count);
                console.log(row_cnt);
                
                if(row_cnt!=0) {
                    $scope.applications=row_cnt;
                }
                else {
                    $scope.applications=null;
                }
            }
            else {
                $scope.applications=null;
            }

            $("#loader").hide();

        });



         $http({
                method: 'GET',
                url: 'partInterchanges/' + $scope.selected_product,
            }).then(function (response) 
            {

                if(response.data.product.length>0)
                {
                   $scope.interchanges=response.data.product;
                }
                else
                {
                    $scope.interchanges=null;
                }

            });
            
        });

    }


        $scope.getApplications = function ()
        {
            angular.element('#applications').addClass("divBackground").animate({
                padding: '0px'
            }, 300, function () {             
                //$(this).click();
                callAtTimeout();
            });
            function callAtTimeout() {

                $timeout(function()
                    {
                        $location.path('/applications'); 
                    })
                //$location.path('applications');
            }
        }

        $scope.getCometitiors = function(){

            angular.element('#competitors').addClass("divBackground").animate({
                padding: '0px'
            }, 300, function () {
                //$(this).click();
                callAtTimeout();
            });
            function callAtTimeout() {

                $timeout(function()
                    {
                        $location.path('/competitors'); 
                    })
                //$location.path('competitors');
            }
        }

        $scope.removeSelection = function (toberemoved) {
            $location.path(toberemoved);
        };

        $scope.thumbnailImage = function(imagepath){
        $scope.path="https://www.pgfilters.com/wp-content/uploads1/assets/"+imagepath;
      
        };

    }
    else
    {
        $location.path('/');
    }


    $scope.instructionPDF = function(inspdfpath) {
            // var path = "https://www.pgfilters.com/wp-content/resources-pdfs/"+inspdfpath;

           
            var path = "https://www.pgfilters.com/"+inspdfpath;
            $window.open(path, '_blank');
    };



  
       

//        
 });
