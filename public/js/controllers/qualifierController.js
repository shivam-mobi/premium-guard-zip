
app.controller('QualifierController', function ($scope, $http, $location, $window, $timeout,clickservice) {


let selectedvehicle_type = JSON.parse($window.localStorage.getItem("vehicletype"));

let selected_vin = JSON.parse($window.sessionStorage.getItem("vin"));

    if(selected_vin!=null) {
        $scope.vin_selected = 'vin';
    }
    else {
        $scope.vin_selected = '';
    }
    

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

    let selected_year = JSON.parse($window.sessionStorage.getItem("year"));
    let selected_make = JSON.parse($window.sessionStorage.getItem("make"));
    let selected_model = JSON.parse($window.sessionStorage.getItem("model"));
    let selected_engine = JSON.parse($window.sessionStorage.getItem("engine"));
    let qualifier_clicked = JSON.parse($window.sessionStorage.getItem("qualifier"));
    let parttype=JSON.parse($window.sessionStorage.getItem("parttype"));
    let dyanamic= JSON.parse($window.sessionStorage.getItem("dyanamic"));
   


    if(dyanamic==null)//if classic search
    {
        $scope.d=null;
    }
    else//dyanamic search
    {
        $scope.d=dyanamic.dyanamic;
    }

   

    $scope.pt="";
    $scope.ptsub="";

    if(parttype!=null)
    {
        $scope.pt=parttype.partType;

        $scope.ptsub=parttype.partSubType;
    }
    else
    {
        $scope.pt=0;
        $scope.ptsub=0;
    }
    
    let get_all=[];
    let store_id=[];
    let store_value=[];
    let send_id="";

    if(qualifier_clicked==null)//if no qualifier selected till now
    {
        send_id="noqualifier";
    }
    else
    {              

                 for(let i=0;i<qualifier_clicked.length;i++)
                {
                    get_all.push(qualifier_clicked[i]);
                }

                
                for (let j=0;j<get_all.length;j++)
                {
                    store_id.push(get_all[j].qualifier_id);
                    store_value.push(get_all[j].selected);
                }

                if(store_id.length>1)
                {
                    send_id=store_id.join();
                }
                else
                {   
                    send_id=store_id[0];
                }


    }
    
    

    $scope.selected_q=store_value;
    $scope.selected_year = selected_year.yearId;
    $scope.selected_make = selected_make.makeName;
    $scope.selected_model = selected_model.modelName;
    $scope.selected_engine = selected_engine.engineName;

     if($scope.selected_engine=='')
    {
        $scope.selected_engine='noengine';
    }
    
    $scope.selected_engine_id=selected_engine.engineId;
  
    

    $scope.qualifier=[];
    $scope.name='';
    
   
    if ($scope.selected_year !== '' && $scope.selected_make!=='' && $scope.selected_model!=='' && ($scope.selected_engine!=='' || $scope.selected_engine_id!='')) 
    {


          
         $http({
            method: 'GET',
            url: 'qualifierCollection/'+selected_model.baseVehicleID+"/"+selected_engine.engineId+"/"+encodeURIComponent(send_id)+"/"+$scope.pt+"/"+$scope.ptsub,       
        }).then(function(response) {




           


           


            if(parttype!=null)
            {
                    if(response.data=='fail')
                {
                    $timeout(function()
                    {
                        $location.path('/productlist1'); 
                    })
                }
                else
                {
                    qualifiervalueone();
                }

            }
            else
            {
                if(response.data=='fail')
                {
                    $timeout(function()
                    {
                        $location.path('/productlist'); 
                    })
                }
                else
                {
                    qualifiervalueone();
                }
            }
            
            function qualifiervalueone()
            {

                    let store_q_value=response.data.values;

                    if(store_q_value.length==1)
                    {

                        let store_qid='';
                        let store_qvalue='';
                        for(i=0;i<store_q_value.length;i++)
                        {
                            
                            store_qid=store_q_value[i].id;
                            store_qvalue=store_q_value[i].values;
                        }
                        
                        $scope.ifvalueone=1;
                        $scope.displayProductList(store_qid,store_qvalue,9999); //random number used 9999 for sending as a parameter

                    }
                    else
                    {
                        $scope.name=response.data.name[0];
                        console.log($scope.name);
                        $scope.qualifier=response.data.values;
                    }

            }



    });





    }


    $scope.ifvalueone=0;
    $scope.displayProductList = function(id,values,ind)
    {

            let collect_qualifier=[];
            let current="";
            let original_qualifier="";
            let store_id=[];
            let store_value=[];
            let send_id="";
            let qualifier_clicked=JSON.parse($window.sessionStorage.getItem("qualifier"));
            var pieces = id.split(/[\s,]+/);
            var th=pieces[pieces.length-1];
            var res = th.split(":");
            var rest=res[0];

        var optionsnew={
        AspirationID:['',''],
        BedLengthID:['','Inch Bed'],
        BedTypeID:['',''],
        BodyNumDoorsID: ['','Doors'],
        BodyTypeID:['','Body'],
        BrakeABSID:['','Brakes'],
        BrakeSystemID: ['','Brakes'],
        CylinderHeadTypeID: ['',''],
        DriveTypeID: ['',''],
        EngineBaseID:['',''],
        EngineDesignationID: ['','Engine'],
        EngineMfrID: ['','Engine'],
        EngineVersionID:['','Engine'],
        EngineVINID:['VIN:',''],
        FrontBrakeTypeID: ['','Front Brakes'],
        FrontSpringTypeID:['','Front Springs'],
        FuelDeliverySubTypeID:['',''],
        FuelDeliveryTypeID:['',''],
        FuelSystemControlTypeID:['','Fuel System'],
        FuelSystemDesignID:['','Fuel System'],
        FuelTypeID:['',''],
        IgnitionSystemTypeID: ['',''],
        MfrBodyCodeID: ['Body Code:',''],
        PowerOutputID: ['','HP'],
        RearBrakeTypeID:['','Rear Brakes'],
        RearSpringTypeID:['','Rear Springs'],
        RegionID: ['',''],
        SteeringSystemID: ['','Steering'],
        SteeringTypeID: ['','Steering'],
        SubModelID: ['',''],
        TransElecControlledID: ['Electronically Controlled Transmission:',''],
        TransmissionBaseID: ['',''],
        TransmissionControlTypeID: ['','Transmission'],
        TransmissionMfrCodeID: ['Transmission Code:',''],
        TransmissionMfrID: ['','Transmission'],
        TransmissionNumSpeedsID: ['','Speed'],
        TransmissionTypeID:['',''],
        // ValvesID: ['','Valve'],
        ValvesPerEngineID: ['','Valve'],
        VehicleTypeID:['Type:',''],
        WheelBaseID: ['','Inch Wheelbase'],
    };
    
        if (rest in optionsnew){
            var valuesnew=optionsnew[rest][0]+" "+values+" "+optionsnew[rest][1];
        }

            
      
            
            if(qualifier_clicked!=null)
            {
                current={selected:valuesnew,qualifier_id:id,original_qualifier:values};
                

                if(qualifier_clicked.length>0)
                {
                            for(let i=0;i<qualifier_clicked.length;i++)
                        {
                            collect_qualifier.push(qualifier_clicked[i]);
                        }
                        collect_qualifier.push(current);
                        $window.sessionStorage.setItem('qualifier',JSON.stringify(collect_qualifier));



                        for (let j=0;j<collect_qualifier.length;j++)
                        {
                            store_id.push(collect_qualifier[j].qualifier_id);
                        }

                        send_id=store_id.join();

                }
                // else
                // {
                //         for(let i=0;i<qualifier_clicked.length;i++)
                //         {
                //             collect_qualifier.push(qualifier_clicked[i]);
                //         }
                //         collect_qualifier.push(current);
                //         $window.sessionStorage.setItem('qualifier',JSON.stringify(collect_qualifier));


                //         for (let j=0;j<collect_qualifier.length;j++)
                //         {
                //             store_id.push(collect_qualifier[j].qualifier_id);
                //         }

                //         send_id=store_id.join();
                // }


                
            }
            else
            {
                send_id=id;
                collect_qualifier.push({selected:valuesnew,qualifier_id:id,original_qualifier:values});
                $window.sessionStorage.setItem('qualifier', JSON.stringify(collect_qualifier));
                
            }

           

             if($scope.ifvalueone==0)
            {
                angular.element('#qualifierId' + ind).addClass("divBackground").animate({
                    padding: '0px'
                }, 300, function () {
                    
                    
                    callAtTimeout();
                    
                });

            }
            else
            {
                callAtTimeout();
            }


             

            function callAtTimeout() 
            {

                
                $http({
            method: 'GET',
            url: 'qualifierCollection/'+selected_model.baseVehicleID+"/"+selected_engine.engineId+"/"+encodeURIComponent(send_id)+"/"+$scope.pt+"/"+$scope.ptsub,       
        }).then(function(response) {

           


            
                if(response.data.values=="")
                {
                    if($scope.pt!=0)
                    {
                         $timeout(function()
                        {
                            $location.path('/productlist1'); 
                        })
                    }
                    else
                    {
                        $timeout(function()
                    {
                        $location.path('/productlist'); 
                    })
                    }
                }
                else
                {
                    let store_q_value=response.data.values;

                    if(store_q_value.length==1)
                    {

                        let store_qid='';
                        let store_qvalue='';
                        for(i=0;i<store_q_value.length;i++)
                        {
                            
                            store_qid=store_q_value[i].id;
                            store_qvalue=store_q_value[i].values;
                        }
                        
                        $scope.ifvalueone=1;
                        $scope.displayProductList(store_qid,store_qvalue,9999);


                    }
                    else
                    {
                                $timeout(function()
                            {
                                window.location.reload();
                                
                            })  

                    }

                                    
                   
                    
                }
            

                });

                

            }


            
            
    }

    $scope.removeSelection =  function(toberemoved)
            {

               
                if(toberemoved!='year' && toberemoved!='make' && toberemoved!='model' && toberemoved!='engine' &&  toberemoved!='vehicletype')
                        {
                             let store_index=0;
                             let qualifier_to_send=[];
                          
                            var clicked1 = qualifier_clicked.filter(function(q){

                                if(toberemoved==q.selected)
                                {
                                    store_index=1;
                                }
                                else
                                {
                                
                                    if(!store_index==1)
                                    {
                                        qualifier_to_send.push(q);
                                    }
                                   
                                }

                             });

                             if(qualifier_to_send.length==0)
                             {
                                $window.sessionStorage.removeItem('qualifier');
                             }
                             else
                             {
                                $window.sessionStorage.setItem('qualifier',JSON.stringify(qualifier_to_send));
                             }
                           
                             $window.location.reload();

                        }
                        else
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
