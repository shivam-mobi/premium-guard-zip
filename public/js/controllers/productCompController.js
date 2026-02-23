app.controller('productComp', function ($scope,$route, $http, $location, $window,$timeout,clickservice) {


    $window.sessionStorage.removeItem('dyanamic');
    $window.sessionStorage.removeItem('partinterchnage');
    $window.sessionStorage.removeItem('product');

    let productcomp_present =  $window.sessionStorage.getItem("productcomp");
   
    $('#make').select2({
        placeholder: "Select Make",
        closeOnSelect: false,
    });
    $('#model').select2({
        placeholder: "Select Model",
        closeOnSelect: false
    });
    $('#partnumber').select2({
        placeholder: "Select Part Number",
        closeOnSelect: false
    });

    $("#loader").hide();

    $scope.makeIds = '';
    $scope.modelIds = '';
    $scope.partNumber = '';
    $scope.makeValues = '';
    $scope.modelValues = '';
    $scope.partnumberValues = '';
    $scope.note_result = '';

    $scope.makeflag = true;
    $scope.modelflag = true;
    $scope.ptflag = true;

    $scope.session_makenote = null;
    $scope.session_modelnote = null;
    $scope.session_ptnote = null;
    
    $scope.isDisable = true; //disable dropdown of from year and to year


    //reset the flags (set when redirected back from product page)
    $scope.resetFlag = function() {
        if(productcomp_present == null) {
            $scope.makeflag = true;
            $scope.modelflag = true;
            $scope.ptflag = true;
       }
    }

    //get all years and parttypes
    $http.get('allyears').then(function (response) {
        $scope.years = response.data.years;
        $scope.parttypes = response.data.parttypes;
    });

    //parttype changed
    $scope.productChange = function() {

        sessionStorage.clear();
       
        $scope.makeIds = '';
        $scope.modelIds = '';
        $scope.partNumber = '';
        $scope.make = [];
        $scope.model = [];
        $scope.partnumber = [];
        $scope.selectedYearFrom = '';
        $scope.selectedYearTo = '';
        $scope.isDisable = false;
        $scope.showProducts(0);
    }

    //make changed
    $scope.changeMake = function(makeIds, makeflag) {

        $('.select2-search__field').attr('style','width:0em !important;');

            $scope.resetFlag();
        
            //set the current value of flag
            $scope.makeflag = makeflag;
       
            $window.sessionStorage.setItem("comp_makes",JSON.stringify(makeIds));
            
            var store_ma = $('#make').select2('data');

            var makestore = [];
            store_ma.forEach(function (item) { 
                makestore.push(item.text.trim());
            })
    
            //showing selected make text in note
            if(makestore.length > 0) {
                $scope.makeValues = makestore.join(" ");
            }
            else {
                $scope.makeValues = '';        
            }
    
            if(makeIds.length == 0) {
                $scope.makeIds = '';
                $scope.modelIds = '';
                $scope.partNumber = '';
                $scope.make = [];
                $scope.model = [];
                $scope.partnumber = [];
                $scope.makeValues= '';
                $scope.modelValues= '';
                $scope.partnumberValues = '';
            }
            else {
    
                if(makeIds.length > 1) {
                    $scope.makeIds = makeIds.join();
                }
                else {
                    $scope.makeIds = makeIds[0];
                }

                var send_make = { 
                    parttype: $scope.parttype , 
                    fromyear:$scope.fromyear , 
                    toyear:$scope.toyear,
                    makemodel_changed:'make',
                    make_selected:$scope.makeIds
                };

                $scope.modelIds = '';
                $scope.partNumber = '';
                    
                
                $scope.model = [];
                $scope.partnumber = [];
                     
                send_make.makemodel_changed = 'make';

                //if changeMake not called manually from JS file
                if($scope.makeflag == true) {
                        $http.post('allmakemodel', send_make).then(function(response) {
                            $scope.model = response.data.model;
                            $scope.partnumber = response.data.partnumber;
                        });
                }

            }

         
            //if changeMake not called manually from JS file
            if($scope.makeflag == true) {
                $scope.showProducts(1);
            }
            
    }


    //model changed
    $scope.changeModel = function(modelIds,modelflag) {

        $('.select2-search__field').attr('style','width:0em !important;');

            $scope.resetFlag();
            $scope.modelflag = modelflag;
          
            $window.sessionStorage.setItem("comp_models",JSON.stringify(modelIds));
    
            var store_mo = $('#model').select2('data');
            var modelstore = [];
            store_mo.forEach(function (item) { 
                modelstore.push(item.text.trim());
            })
    
          
            //showing selected model text in note
            if(modelstore.length > 0) {
                $scope.modelValues = modelstore.join(" ");
            }
            else {
                $scope.modelValues = '';        
            }
    
    
            var send_details = { 
                parttype: $scope.parttype , 
                fromyear:$scope.fromyear , 
                toyear:$scope.toyear,  
                make_selected:$scope.makeIds,
                model_selected:''
            };
    
       
            //if partnumber value set in session 
     
            $scope.partNumber = '';
            $scope.partnumber = [];
            
 
            if(modelIds.length == 0) {
    
                $scope.modelIds = '';
                $scope.modelValues= '';
                $scope.partnumberValues = '';
                
              
                    send_details.makemodel_changed = 'make';

                    if($scope.modelflag==true) {
                        $http.post('allmakemodel', send_details).then(function(response) {
                            $scope.partnumber = response.data.partnumber;
                        });
                    }

            }
            else {
    
                if(modelIds.length > 1) {
                    $scope.modelIds = modelIds.join();
                }
                else {
                    $scope.modelIds = modelIds[0];
                }

                send_details.model_selected = $scope.modelIds;
    
                send_details.makemodel_changed = 'model';

                //if changeModel not called manually from JS file
                if($scope.modelflag==true) {
                        $http.post('allmakemodel', send_details).then(function(response) {
                            $scope.partnumber = response.data.partnumber;
                        });
                }

            }

            //if changeModel not called manually from JS file
            if($scope.modelflag==true) {
                $scope.showProducts(1);
            }

    }

    //partnumber changed
    $scope.changePartNumber = function(partNumber,ptflag) {

        $('.select2-search__field').attr('style','width:0em !important;');

            $scope.resetFlag();
            $scope.ptflag = ptflag;

            $window.sessionStorage.setItem("comp_partnumbers",JSON.stringify(partNumber));
    
            var store_pt = $('#partnumber').select2('data');
            var partnumberstore = [];
            store_pt.forEach(function (item) { 
                partnumberstore.push(item.text.trim());
            })
    
            //showing selected partnumber text in note
            if(partnumberstore.length > 0) {
                $scope.partnumberValues = partnumberstore.join(" ");
            }
            else {
                $scope.partnumberValues = '';        
            }
    
            if(partNumber.length == 0) {
                $scope.partNumber = '';
                $scope.partnumberValues = '';
            }
            else {
                if(partNumber.length > 1) {
                    $scope.partNumber = partNumber.join();
                }
                else {
                    $scope.partNumber = partNumber[0];
                }
    
               
            }

          //if changePartNumber not called manually from JS file
            if($scope.ptflag == true) {
                $scope.showProducts(1);
            }
           
    }

    $scope.showProducts = function(flag_id) {

       //flag_id 0 when parttype/year changed
        if(flag_id == 0) {

            $scope.makeIds = '';
            $scope.modelIds = '';
            $scope.partNumber = '';
            $scope.make = [];
            $scope.model = [];
            $scope.partnumber = [];
            $scope.makeValues= '';
            $scope.modelValues= '';
            $scope.partnumberValues = '';
            $scope.resetFlag();
        }

        $scope.parttype = $scope.selectedProduct;
        $window.sessionStorage.setItem('comp_parttype',$scope.selectedProduct);

        $scope.fromyear = '';
        $scope.toyear = '';

 
        //if one of the year is empty
        if($scope.selectedYearFrom!='' && $scope.selectedYearTo=='') {
            return;
        }

        if($scope.selectedYearFrom=='' && $scope.selectedYearTo!='') {
            return;
        }


        if($scope.selectedYearFrom == '') {
            $scope.fromyear = '';
        }
        else {
            $scope.fromyear = parseInt($scope.selectedYearFrom);
        }

        if($scope.selectedYearTo == '') {
            $scope.toyear = '';
        }
        else {
            $scope.toyear = parseInt($scope.selectedYearTo);
        }



        if($scope.fromyear!='' && $scope.toyear!='') {

            if($scope.fromyear > $scope.toyear) {
                $("#alertmodalvisual").modal("show");
                $scope.makeIds = '';
                $scope.modelIds = '';
                $scope.partNumber = '';
                $scope.make = [];
                $scope.model = [];
                $scope.partnumber = [];
                $window.sessionStorage.removeItem('comp_fromyear');
                $window.sessionStorage.removeItem('comp_toyear');
                return;
            }

            $window.sessionStorage.setItem("comp_fromyear",$scope.selectedYearFrom);
            $window.sessionStorage.setItem("comp_toyear",$scope.selectedYearTo);
        }


        if($scope.parttype!='') {
           
            var data = {
                parttype: $scope.parttype , 
                fromyear:$scope.fromyear , 
                toyear:$scope.toyear , 
                make_selected:$scope.makeIds , 
                model_selected: $scope.modelIds,
                partnumbers_selected : $scope.partNumber
            };

            $("#loader").show();


            //at first load all makes and models
            if($scope.makeIds == '' && $scope.modelIds == '' && $scope.partNumber == '') {
              
                $http.post('allmakemodel', data).then(function(response) {
                    $scope.make = response.data.make;
                    $scope.model = response.data.model;
                    $scope.partnumber = response.data.partnumber;
                });
            }else {

                //if make/mode/pt function called manually
                if($scope.makeflag == false || $scope.modelflag == false || $scope.ptflag == false) {

                    //set make note value from session
                    if($scope.session_makenote!=null) {
                        $scope.makeValues = $scope.session_makenote;
                    }

                    if($scope.session_modelnote!=null) {
                        $scope.modelValues = $scope.session_modelnote;
                    }

                    if($scope.session_ptnote!=null) {
                        $scope.partnumberValues = $scope.session_ptnote;
                    }
                    
                    $http.post('allmakemodel', data).then(function(response) {
                        $scope.make = response.data.make;
                        $scope.model = response.data.model;
                        $scope.partnumber = response.data.partnumber;
                    });
                }
                else {
                    console.log('all true');
                }
            }

            //load all product details (ymm,parttype,partnumber,image link,spes)
            $http.post('allproducts', data).then(function(response) {
                $scope.product_result = response.data;  
                
                if($scope.partnumberValues == '') {
                    var partnumber_value = '';
                    //if pt note present remove it
                    $window.sessionStorage.removeItem('pt_note');
                } else {
                    var partnumber_value = ", "+$scope.partnumberValues;
                    //set the pt value into session
                    $window.sessionStorage.setItem('pt_note',$scope.partnumberValues);
                }

                if($scope.makeValues == '') {
                    var make_value = '';
                    $window.sessionStorage.removeItem('make_note');
                }
                else {
                    var make_value = ", "+$scope.makeValues;
                    $window.sessionStorage.setItem('make_note',$scope.makeValues);
                }

                if($scope.modelValues == '') {
                    var model_value = '';
                    $window.sessionStorage.removeItem('model_note');
                }
                else {
                    var model_value = ", "+$scope.modelValues;
                    $window.sessionStorage.setItem('model_note',$scope.modelValues);
                }

                if($scope.fromyear == '' && $scope.toyear == '') {
                    var year_value = '';
                }
                else {
                    var year_value = ", "+$scope.fromyear+"-"+$scope.toyear;
                }

                $scope.note_result = '';

                //showing whole note
                if(make_value == '' && model_value == '' && partnumber_value== '') {
                    $scope.note_result  = $scope.parttype+year_value;
                }
                else if(make_value!='' && model_value=='') {
                    $scope.note_result = $scope.parttype+year_value+make_value+partnumber_value;
                }
                else if(make_value=='' && model_value!='') {
                    $scope.note_result = $scope.parttype+year_value+model_value+partnumber_value;
                } else {
                    $scope.note_result = $scope.parttype+year_value+make_value+model_value+partnumber_value;
                }

                $window.sessionStorage.removeItem('productcomp');
                productcomp_present = null;
                 
                
            }).then(function(res){

              
                if(window.innerWidth>991) {
                   
                        $timeout(function()
                    {
                    
                        let rows1 = document.getElementsByClassName("specs-div-all");
                        var height = [];
                        //convert the html collection to array
                        Array.from(rows1).forEach(function(ele){
                            height.push(ele.offsetHeight);
                        });

                        var no_of_cards = 4;

                        var j_object = document.getElementsByClassName("specs-div-all");

                        for (var i = 0; i < height.length; i += no_of_cards) {

                            //get height of first 4 elements in row
                            var inner_array = height.slice(i, i + no_of_cards);
                            var dv = Array.from(j_object);
                            var div_object = dv.slice(i , i + no_of_cards);

                            //get max height from 4 elements in row
                            var maxHeight = Math.max.apply(null, inner_array);

                            //apply max height to all 4 elements in row
                            div_object.forEach(function(ob) {
                                ob.style.height = maxHeight+ 'px';
                            });
                        }

                        $("#loader").hide();
                    
                    },3000)
                }
                else {
                    $("#loader").hide();
                }
                
            });
        }
    }


    $scope.Productpage = function(partnumber,year,make,model,parttypename) {

        let make_update = make.replace(/ /g,"+");
        let model_update = model.replace(/ /g,"+");

        let ymm = year+"-"+make_update+"-"+model_update;
        console.log(ymm)

        var productcomp="productcomp";
        $window.sessionStorage.setItem('productcomp', JSON.stringify({productcomp:productcomp}));
        $window.sessionStorage.setItem('product', JSON.stringify({partNumber: partnumber,partTypeName:parttypename,ymm:ymm}));
   
        $timeout(function()
        {
           $location.path('/product-page/'+partnumber+"/"+ymm+"/"+parttypename); 
        })
    }


   
    //if clicked on product page and redirected back
    if(productcomp_present != null) {

        
        var hidden_pt =  $window.sessionStorage.getItem("comp_parttype");
        var hidden_from =  $window.sessionStorage.getItem("comp_fromyear");
        var hidden_to =  $window.sessionStorage.getItem("comp_toyear");
        var hidden_makes =  JSON.parse($window.sessionStorage.getItem("comp_makes"));
        var hidden_models =  JSON.parse($window.sessionStorage.getItem("comp_models"));
        var hidden_partnumbers =  JSON.parse($window.sessionStorage.getItem("comp_partnumbers"));
        var make_note = $window.sessionStorage.getItem("make_note");
        var model_note = $window.sessionStorage.getItem("model_note");
        var pt_note = $window.sessionStorage.getItem("pt_note");

        $scope.session_makenote = make_note;
        $scope.session_modelnote = model_note;
        $scope.session_ptnote = pt_note;

        if(hidden_pt != null) {
            $scope.selectedProduct = hidden_pt;
            $scope.parttype = hidden_pt;
            $scope.isDisable = false;
    

            //if no year selected
            if(hidden_from != null && hidden_to!=null) {
                $scope.selectedYearFrom = hidden_from;
                $scope.selectedYearTo = hidden_to;
            }
            else {
                $scope.selectedYearFrom = '';
                $scope.selectedYearTo = '';
            }
            
    
            //if make value present in session
            if(hidden_makes != null) {

                //sets the value in input text field
                $scope.selectedMake = hidden_makes;
                $scope.changeMake(hidden_makes,false);

                if(hidden_models!= null) {
                    $scope.selectedModel = hidden_models;
                    $scope.changeModel(hidden_models,false);
                }
    
                if(hidden_partnumbers != null) {
                   $scope.selectedPartNumber = hidden_partnumbers;
                    $scope.changePartNumber(hidden_partnumbers,false);
                }

                $scope.showProducts(1);

            }
            //if model value present in session
            else if(hidden_models != null) {
    
                $scope.selectedModel = hidden_models;
                $scope.changeModel(hidden_models,false);

                if(hidden_partnumbers != null) {
                    $scope.selectedPartNumber = hidden_partnumbers;
                    $scope.changePartNumber(hidden_partnumbers,false);
                }

                $scope.showProducts(1);

            }
            //if pt value present in session
            else if(hidden_partnumbers != null) {
                
                $scope.selectedPartNumber = hidden_partnumbers;
                $scope.changePartNumber(hidden_partnumbers,false);

                $scope.showProducts(1);
            }
            else {
                $scope.showProducts(0);
            }
        }
    } else {
        sessionStorage.clear();
    }

});