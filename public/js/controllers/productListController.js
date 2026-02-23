app.controller('ProductListController', function ($scope, $http, $location, $window,$timeout,clickservice,$sce) {


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
   
    
    let dyanamic= JSON.parse($window.sessionStorage.getItem("dyanamic"));
    
    let qualifier_clicked=JSON.parse($window.sessionStorage.getItem("qualifier"));
    let get_all=[];
    $scope.store_id=[];
    $scope.store_value=[];
    $scope.store_qua_value=[];
    $scope.send_id="";
    $scope.send_name="";
    
    
    $scope.no_fluids=false;
    $scope.no_filters=false;
    $scope.no_caps=false;
    $scope.no_wipers=false;

    if(qualifier_clicked==null)//if no qualifier selected till now
    {
        $scope.send_id="noqualifier";
        $scope.send_name="noqualifier";
    }
    else
    {

                 for(let i=0;i<qualifier_clicked.length;i++)
                {
                    get_all.push(qualifier_clicked[i]);
                }


                for (let j=0;j<get_all.length;j++)
                {
                    $scope.store_id.push(get_all[j].qualifier_id);
                    $scope.store_value.push(get_all[j].selected);
                    $scope.store_qua_value.push(get_all[j].original_qualifier);
                }

                if($scope.store_id.length>1)
                {
                    $scope.send_id=$scope.store_id.join();
                }
                else
                {
                    $scope.send_id=$scope.store_id[0];
                }


                if($scope.store_value.length>1)
                {
                    $scope.send_name=$scope.store_qua_value.join();
                }
                else
                {
                    $scope.send_name=$scope.store_qua_value[0];
                }






    }

   


    $scope.selected_year = selected_year.yearId;
    $scope.selected_make = selected_make.makeName;
    $scope.selected_model = selected_model.modelName;
    $scope.selected_engine = selected_engine.engineName;

    if($scope.selected_engine=='')
    {
        $scope.selected_engine='noengine';
    }
    $scope.selected_engine_id=selected_engine.engineId;

    if(dyanamic==null)//if classic search
    {
        $scope.d=null;
    }
    else//dyanamic search
    {
        $scope.d=dyanamic.dyanamic;
    }


    $scope.filters="";
    $scope.caps="";
    $scope.wipers="";
    $scope.fluids="";



    if ($scope.selected_year !== '' && $scope.selected_make!=='' && $scope.selected_model!=='' && ($scope.selected_engine!=='' || $scope.selected_engine_id)) {
        $http({
            method: 'GET',
            url: 'productList/' + selected_model.baseVehicleID+"/"+selected_engine.engineId+"/"+encodeURIComponent($scope.send_id)+"/"+encodeURIComponent(encodeURIComponent($scope.send_name)),
        }).then(function (response) {


            let sample_set="";
            let sample_variable=0;
            let set_variable=0;
            let pgcheck=0;

            let qualifier_parttype=[];

            let nrcarray=["NRC","NRA","NRT","NRO","NRF","NSO","NSA","NSC","NSF","NST","NR"];


            if(response.data.partTypeArray.length>0)
            {
                    let qualifier=[];
                    let selected=[];
                    let filter_data=response.data.partTypeArray;
                  
                    let save_pt_qt=[];
                    let storeparttype='';

                    filter_data.forEach(function (e,index)
                    {

                        if(e.note!="") {
                            var note_split = [];
                            note_split = e.note.split("; ");
                            var unique_string = note_split.filter((item, i, ar) => ar.indexOf(item) === i);
                            e.note = unique_string.join("; ");
                            e.note = e.note.replace('inches','"');
                        }

                        let partno_sample=e.partNumber;


                        if(e.partType!=storeparttype) {
                            pgcheck=0;
                            storeparttype=e.partType;
                        }




                            qualifier=e.q.split(",");

                            if(e.f!=undefined)
                            {
                            selected=e.f.split(",");
                            }

                            let unique = qualifier.filter((item, i, ar) => ar.indexOf(item) === i);
                            let unique_selected=selected.filter((item, i, ar) => ar.indexOf(item) === i);
                            let store=[];
                            let store_selected=[];
                            let store_next="";
                            let store_pre="";















                            for(let i=0;i<unique.length;i++)
                            {
                                    let explode1=unique[i].split(":");

                                    if(explode1[1]!="")
                                    {
                                        store.push(unique[i]);
                                    }
                                    else
                                    {
                                        continue;
                                    }
                            }

                            for(let i=0;i<unique_selected.length;i++)
                            {
                                    let explode1=unique_selected[i].split(":");

                                    if(explode1[1]!="")
                                    {
                                        store_selected.push(unique_selected[i]);
                                    }
                                    else
                                    {
                                        continue;
                                    }
                            }

                            let store_next_qualifier="";
                            let store_currnet_pt="";
                            let store_next_pt="";
                            let store_next_q="";
                            let next_partnumber="";
                            let split_qulifier=[];
                            var parttype_array=[1,2,3,7,12,13,16];
                            var check_in_array=parttype_array.includes(e.partType);


                            if(store.length==0)
                            {
                               
                                if(check_in_array==false) {

                                    for(var qc=sample_variable;qc<filter_data.length-1;qc++) {

                                        store_next_qualifier=filter_data[qc+1];

                                      
                                            store_currnet_pt=e.partType;
                                            store_next_pt = store_next_qualifier.partType;
                                            next_partnumber = store_next_qualifier.partNumber;
                                            
                                            if(store_next_pt==store_currnet_pt && nrcarray.includes(next_partnumber)==false)
                                            {

                                                store_next_q=store_next_qualifier.q;
                                                split_qulifier=store_next_q.split(",");
                                                let unique_next=split_qulifier.filter((item, i, ar) => ar.indexOf(item) === i);

                                                for(let i=0;i<unique_next.length;i++)
                                                {
                                                    let explode1=unique_next[i].split(":");
                                                    if(explode1[1]!="")
                                                    {
                                                        pgcheck=1;
                                                        break;
                                                    }
                                                    else
                                                    {
                                                        continue;
                                                    }
                                                }


                                                if(pgcheck==1) {
                                                    break;
                                                }


                                            }
                                            else {
                                                break;
                                            }

                                        
                                    }
                                }
                            }

                            let string_qualifier=store.sort().join();
                            let string_selected=store_selected.sort().join();


                            store_next=filter_data[sample_variable+1];
                            store_pre=filter_data[sample_variable-1];
                            let check_pre_parttype=0;
                            let check_next_parttype=0;



                            if(store_pre==undefined)
                            {
                                check_pre_parttype=0;
                            }
                            else
                            {
                                check_pre_parttype=store_pre.partType;
                            }

                            if(store_next==undefined)
                            {
                                check_next_parttype=0;
                            }
                            else
                            {
                                check_next_parttype=store_next.partType;
                            }




    let current_part_type=e.partType;

    if(nrcarray.includes(e.partNumber)!=false && (check_next_parttype==current_part_type || current_part_type==check_pre_parttype))
    {
        e.partNumber=null;
    }
    else
    {
        if((check_in_array==false && store.length>0) || pgcheck==1)
        {
               if(string_qualifier==string_selected && pgcheck==0)
               {
                    e.note=e.note;
                    e.partTypename=e.partTypename;
                    e.partNumber=e.partNumber;
                    e.partType=e.partType;
                    e.partSubtype=0;
               }
               else
               {
                    let parttype=e.partType;
                    if(sample_set==parttype)
                    {
                        e.partTypename=null;
                        e.note=null;
                        e.partNumber=null;
                        e.partSubType=null;
                        e.partType=null;

                    }
                    else
                    {
                        e.partTypename=e.partTypename;
                        e.note="";
                        e.partNumber="Need More Info";
                        save_pt_qt.push(e.partNumber);
                        save_pt_qt.push(e.partType);
                        e.partSubType=0;
                        e.partType=e.partType;
                        sample_set=parttype;
                        pgcheck=1;
                        qualifier_parttype.push(e.partType);



                    }
               }

        }
        else
        {
            
                    e.note=e.note;
                    e.partTypename=e.partTypename;
                    e.partType=e.partType;
                    e.partSubtype=0;
               
                    if(nrcarray.indexOf(e.partNumber)==-1)
                    {
                        e.partNumber=e.partNumber;

                    }
                    else
                    {
                        e.partNumber="";

                    }


        }


    }





                            sample_variable++;
                            return e;


                    });

                  
                    $scope.filters=filter_data;


            }
            else
            {
                $scope.no_filters=true;
            }






            sample_set=0;
            sample_variable=0;
            set_variable=0;
            pgcheck=0;
            next_partnumber = '';


            if(response.data.CapsAndPlugsArray.length>0)
            {
                    let qualifier=[];
                    let selected=[];
                    let caps_data=response.data.CapsAndPlugsArray;
                    let storeparttype='';

                    caps_data.forEach(function (e,index)
                    {


                            if(e.note!="") {
                                var note_split = [];
                                note_split = e.note.split("; ");
                                var unique_string = note_split.filter((item, i, ar) => ar.indexOf(item) === i);
                                e.note = unique_string.join("; ");
                                e.note = e.note.replace('inches','"');
                            }


                            if(e.partType!=storeparttype) {
                                pgcheck=0;
                                storeparttype=e.partType;
                            }

                            qualifier=e.q.split(",");

                            if(e.f!=undefined)
                            {
                            selected=e.f.split(",");
                            }

                            let unique = qualifier.filter((item, i, ar) => ar.indexOf(item) === i);
                            let unique_selected=selected.filter((item, i, ar) => ar.indexOf(item) === i);
                            let store=[];
                            let store_selected=[];


                            for(let i=0;i<unique.length;i++)
                            {
                                    let explode1=unique[i].split(":");

                                    if(explode1[1]!="")
                                    {
                                        store.push(unique[i]);
                                    }
                                    else
                                    {
                                        continue;
                                    }
                            }

                            for(let i=0;i<unique_selected.length;i++)
                            {
                                    let explode1=unique_selected[i].split(":");

                                    if(explode1[1]!="")
                                    {
                                        store_selected.push(unique_selected[i]);
                                    }
                                    else
                                    {
                                        continue;
                                    }
                            }

                            let store_next_qualifier="";
                            let store_currnet_pt="";
                            let store_next_pt="";
                            let store_next_q="";
                            let split_qulifier=[];
                            var parttype_array=[1,2,3,7,12,13,16];
                            

                            let string_qualifier=store.sort().join();
                            let string_selected=store_selected.sort().join();



                            if(store.length==0)
                            {

                                    for(var qc=sample_variable;qc<caps_data.length-1;qc++) {

                                        store_next_qualifier=caps_data[sample_variable+1];

                                      
                                            store_currnet_pt=e.partType;
                                            store_next_pt = store_next_qualifier.partType;
                                            next_partnumber = store_next_qualifier.partNumber;
                                            
                                            if(store_next_pt==store_currnet_pt)
                                            {

                                                store_next_q=store_next_qualifier.q;
                                                split_qulifier=store_next_q.split(",");
                                                let unique_next=split_qulifier.filter((item, i, ar) => ar.indexOf(item) === i);

                                                for(let i=0;i<unique_next.length;i++)
                                                {
                                                    let explode1=unique_next[i].split(":");
                                                    if(explode1[1]!="")
                                                    {
                                                        pgcheck=1;
                                                        break;
                                                    }
                                                    else
                                                    {
                                                        continue;
                                                    }
                                                }


                                                if(pgcheck==1) {
                                                    break;
                                                }


                                            }
                                            else {
                                                break;
                                            }

                                      
                                    }
                               
                            }

                            if(store.length>0 || pgcheck==1)
                            {

                                   if(string_qualifier==string_selected && pgcheck==0)
                                   {
                                        e.note=e.note;
                                        e.partTypename=e.partTypename;
                                        e.partNumber=e.partNumber;
                                        e.partType=e.partType;
                                        e.partSubtype=0;

                                   }
                                   else
                                   {
                                        let parttype=e.partType;
                                        if(sample_set==parttype)
                                        {
                                            e.partTypename=null;
                                            e.note=null;
                                            e.partNumber=null;
                                            e.partSubType=null;
                                            e.partType=null;

                                        }
                                        else
                                        {
                                            e.partTypename=e.partTypename;
                                            e.note="";
                                            e.partNumber="Need More Info";
                                            e.partSubType=0;
                                            e.partType=e.partType;
                                            sample_set=parttype;
                                            pgcheck=1;
                                            qualifier_parttype.push(e.partType);

                                        }
                                   }

                            }
                             else
                            {
                                        e.note=e.note;
                                        e.partTypename=e.partTypename;
                                        e.partNumber=e.partNumber;
                                        e.partType=e.partType;
                                        e.partSubtype=0;
                            }

                            sample_variable++;
                            return e;

                    });




               $scope.caps=caps_data;

            }
            else
            {
                $scope.no_caps=true;
            }




            sample_set=0;
            sample_variable=0;
            set_variable=0;
            pgcheck=0;
            next_partnumber='';

            if(response.data.WiperBlades.length>0)
            {

                    let qualifier=[];
                    let selected=[];
                    let wipers_data=response.data.WiperBlades;
                    let storeparttype='';


                    wipers_data.forEach(function (e,index)
                    {
                            if(e.note!="") {
                                var note_split = [];
                                note_split = e.note.split("; ");
                                var unique_string = note_split.filter((item, i, ar) => ar.indexOf(item) === i);
                                e.note = unique_string.join("; ");
                                e.note = e.note.replace('inches','"');
                            }

                            if(e.partType!=storeparttype) {
                                pgcheck=0;
                                storeparttype=e.partType;
                            }


                            qualifier=e.q.split(",");

                            if(e.f!=undefined)
                            {
                            selected=e.f.split(",");
                            }

                            let unique = qualifier.filter((item, i, ar) => ar.indexOf(item) === i);
                            let unique_selected=selected.filter((item, i, ar) => ar.indexOf(item) === i);
                            let store=[];
                            let store_selected=[];


                            for(let i=0;i<unique.length;i++)
                            {
                                    let explode1=unique[i].split(":");

                                    if(explode1[1]!="")
                                    {
                                        store.push(unique[i]);
                                    }
                                    else
                                    {
                                        continue;
                                    }
                            }

                            for(let i=0;i<unique_selected.length;i++)
                            {
                                    let explode1=unique_selected[i].split(":");

                                    if(explode1[1]!="")
                                    {
                                        store_selected.push(unique_selected[i]);
                                    }
                                    else
                                    {
                                        continue;
                                    }
                            }

                            let store_next_qualifier="";
                            let store_currnet_pt="";
                            let store_next_pt="";
                            let store_next_q="";
                            let split_qulifier=[];
                            

                             let string_qualifier=store.sort().join();
                             let string_selected=store_selected.sort().join();


                             if(store.length==0)
                             {
 
                                     for(var qc=sample_variable;qc<wipers_data.length-1;qc++) {
 
                                         store_next_qualifier=wipers_data[sample_variable+1];
 
                                             store_currnet_pt=e.partType;
                                             store_next_pt = store_next_qualifier.partType;
                                             next_partnumber = store_next_qualifier.partNumber;
                                             
                                             if(store_next_pt==store_currnet_pt)
                                             {
 
                                                 store_next_q=store_next_qualifier.q;
                                                 split_qulifier=store_next_q.split(",");
                                                 let unique_next=split_qulifier.filter((item, i, ar) => ar.indexOf(item) === i);
 
                                                 for(let i=0;i<unique_next.length;i++)
                                                 {
                                                     let explode1=unique_next[i].split(":");
                                                     if(explode1[1]!="")
                                                     {
                                                         pgcheck=1;
                                                         break;
                                                     }
                                                     else
                                                     {
                                                         continue;
                                                     }
                                                 }
 
 
                                                 if(pgcheck==1) {
                                                     break;
                                                 }
 
 
                                             }
                                             else {
                                                 break;
                                             }
 
                                   
                                     }
                                
                             }



                            
                            if(store.length>0 || pgcheck==1)
                            {

                            

                                   if(string_qualifier==string_selected && pgcheck==0)
                                   {
                                        e.note=e.note;
                                        e.partTypename=e.partTypename;
                                        e.partNumber=e.partNumber;
                                        e.partType=e.partType;
                                        e.partSubtype=0;
                                       
                                   }
                                   else
                                   {
                                        let parttype=e.partType;
                                        if(sample_set==parttype)
                                        {
                                            e.partTypename=null;
                                            e.note=null;
                                            e.partNumber=null;
                                            e.partSubType=null;
                                            e.partType=null;

                                        }
                                        else
                                        {
                                            e.partTypename=e.partTypename;
                                            e.note=e.partSubtypename;
                                            e.partNumber="Need More Info";
                                            e.partSubType=0;
                                            e.partType=e.partType;
                                            sample_set=parttype;
                                            pgcheck=1;
                                           
                                            qualifier_parttype.push(e.partType);

                                        }
                                   }

                            }
                             else
                            {
                                        e.note=e.note;
                                        e.partTypename=e.partTypename;
                                        e.partNumber=e.partNumber;
                                        e.partType=e.partType;
                                        e.partSubtype=0;
                                       
                            }

                            sample_variable++;
                            return e;

                    });

               $scope.wipers=wipers_data;

            }
            else
            {
                $scope.no_wipers=true;
            }


          
            
            sample_set=0;
            sample_variable=0;
            set_variable=0;
            pgcheck=0;
            next_partnumber='';



            if(response.data.Fluids.length>0)
            {

               
                    let qualifier=[];
                    let selected=[];
                    let fluids_data=response.data.Fluids;
                    let storeparttype='';
                 
          

                    fluids_data.forEach(function (e,index)
                    {
                            if(e.note!="") {
                                var note_split = [];
                                note_split = e.note.split("; ");
                                var unique_string = note_split.filter((item, i, ar) => ar.indexOf(item) === i);
                                e.note = unique_string.join("; ");
                                e.note = e.note.replace('inches','"');
                            }

                            if(e.partType!=storeparttype) {
                                pgcheck=0;
                                storeparttype=e.partTypeid;
                            }

                            qualifier=e.q.split(",");

                            if(e.f!=undefined)
                            {
                            selected=e.f.split(",");
                            }

                            let unique = qualifier.filter((item, i, ar) => ar.indexOf(item) === i);
                            let unique_selected=selected.filter((item, i, ar) => ar.indexOf(item) === i);
                            let store=[];
                            let store_selected=[];


                            for(let i=0;i<unique.length;i++)
                            {
                                    let explode1=unique[i].split(":");

                                    if(explode1[1]!="")
                                    {
                                        store.push(unique[i]);
                                    }
                                    else
                                    {
                                        continue;
                                    }
                            }

                            for(let i=0;i<unique_selected.length;i++)
                            {
                                    let explode1=unique_selected[i].split(":");

                                    if(explode1[1]!="")
                                    {
                                        store_selected.push(unique_selected[i]);
                                    }
                                    else
                                    {
                                        continue;
                                    }
                            }
                            
                            
                            let store_next_qualifier="";
                            let store_currnet_pt="";
                            let store_next_pt="";
                            let store_next_q="";
                            let split_qulifier=[];
                            


                            if(store.length==0)
                            {

                                    for(var qc=sample_variable;qc<fluids_data.length-1;qc++) {

                                        store_next_qualifier=fluids_data[sample_variable+1];

                                     
                                            store_currnet_pt=e.partTypeid;
                                            store_next_pt = store_next_qualifier.partTypeid;
                                            next_partnumber = store_next_qualifier.partNumber;
                                            
                                            if(store_next_pt==store_currnet_pt)
                                            {

                                                store_next_q=store_next_qualifier.q;
                                                split_qulifier=store_next_q.split(",");
                                                let unique_next=split_qulifier.filter((item, i, ar) => ar.indexOf(item) === i);

                                                for(let i=0;i<unique_next.length;i++)
                                                {
                                                    let explode1=unique_next[i].split(":");
                                                    if(explode1[1]!="")
                                                    {
                                                        pgcheck=1;
                                                        break;
                                                    }
                                                    else
                                                    {
                                                        continue;
                                                    }
                                                }


                                                if(pgcheck==1) {
                                                    break;
                                                }


                                            }
                                            else {
                                                break;
                                            }

                                       
                                    }
                               
                            }

                    
                           
                   
                            let string_qualifier=store.sort().join();
                            let string_selected=store_selected.sort().join();

                            

                            if(store.length>0 || pgcheck==1)
                            {
                                   if(string_qualifier==string_selected && pgcheck==0)
                                   {
                                        e.note=e.note;
                                        e.partTypename=e.partTypename;
                                        e.partnumber=$sce.trustAsHtml(e.partnumber.replace('(','<br>('));
                                        e.partTypeid=e.partTypeid;
                                        e.partSubtype=0;

                                   }
                                   else
                                   {
                                        let parttype=e.partTypeid;
                                
                                        if(sample_set==parttype)
                                        {
                                            e.partTypename=null;
                                            e.note=null;
                                            e.partnumber=null;
                                            e.partSubType=null;
                                            e.partTypeid=null;
                                            
                                        }
                                        else
                                        {
                                            e.partTypename=e.partTypename;
                                            e.note="";
                                            e.partnumber="Need More Info";
                                            e.partSubType=0;
                                            e.partTypeid=e.partTypeid;
                                            sample_set=parttype;
                                            pgcheck=1;
                                            qualifier_parttype.push(e.partTypeid);
                                            
                                        }
                                   }

                            }
                             else
                            {
                                        e.note=e.note;
                                        e.partTypename=e.partTypename;
                                        e.partnumber=$sce.trustAsHtml(e.partnumber.replace('(','<br>('));
                                        e.partTypeid=e.partTypeid;
                                        e.partSubtype=0;
                            }
                
                            sample_variable++;
                            return e;

                    });

                         


               $scope.fluids=fluids_data;
        
            }
            else
            {
                $scope.no_fluids=true;
            }
            
            


            qualifier_parttype = qualifier_parttype.filter((v, i, a) => a.indexOf(v) === i);
           

            if(qualifier_parttype.length>1)
            {
                 var allparttype=qualifier_parttype.join();

            $window.sessionStorage.setItem('hidden_parttype', JSON.stringify({parttype_hidden:allparttype}))
            }
            else
            {
                var allparttype=qualifier_parttype;
                $window.sessionStorage.setItem('hidden_parttype', JSON.stringify({parttype_hidden:allparttype}))
            }



            $scope.setflag=false;
            if(response.data.partTypeArray.length==0 && response.data.CapsAndPlugsArray.length==0 && response.data.WiperBlades.length==0 && response.data.Fluids.length==0)
                {
                    $scope.setflag=true;
                }




        });
        if (selected_engine.engineName==''){
            selected_engine.engineName='noengine';
        }
        


         $scope.getFilters = function (partnumber,parttypename,parttype,partsubtype,ind) {

            if(partnumber!="")
            {

                if(partsubtype==undefined)
                {
                    partsubtype=0;
                }


                let PartTypeName="";

                if(parttype == '1' || parttype == '7' || parttype == '12')
                {
                    PartTypeName="Oil-Filter";

                }
                else if (parttype == '2' || parttype == '13')
                {
                    PartTypeName="Air-Filter";
                }
                else if(parttype == '3' || parttype == '16')
                {
                    PartTypeName = "Cabin-Air-Filter";
                }
                else
                {
                    PartTypeName=parttypename.replace(" ","-");
                }


            let current_parttype=[];
            let convert_to_string="";
            let parttypearray=JSON.parse($window.sessionStorage.getItem("parttype"));


            angular.element('#filter' + ind).addClass("").animate({
                padding: '0px'
            }, 100, function () {
               
                callAtTimeout();
            });
            function callAtTimeout() {

                if(partnumber!="Need More Info")
                {

                    let PartNumber_send=partnumber;
                    let Year_send=$scope.selected_year;
                    let Make_send=$scope.selected_make;
                    Make_send=Make_send.replace(/ /g,"+");
                    let Model_send=$scope.selected_model;
                    Model_send=Model_send.replace(/ /g,"+");
                    let PartTypeName_send=PartTypeName;

                    let ymm=Year_send+"-"+Make_send+"-"+Model_send;

                    $window.sessionStorage.setItem('product', JSON.stringify({ partNumber: partnumber,partTypeName:PartTypeName,ymm:ymm}))

                    $timeout(function()
                                {
                                   $location.path('/product-page/'+PartNumber_send+"/"+ymm+"/"+PartTypeName_send);
                                })

                }
                else
                {

                            if(parttypearray!=null)
                            {


                                current_parttype.push(parttypearray.partType);
                                current_parttype.push(parttype);
                                convert_to_string=current_parttype.join();
                                $window.sessionStorage.setItem('parttype', JSON.stringify({partType:convert_to_string,partSubType:partsubtype}))
                                $timeout(function()
                                {
                                   $location.path('/qualifier');
                                })


                            }
                            else
                            {



                                current_parttype.push(parttype);

                                convert_to_string=current_parttype.join();

                                $window.sessionStorage.setItem('parttype', JSON.stringify({ partType:convert_to_string,partSubType:partsubtype}))

                                $timeout(function()
                                {
                                   $location.path('/qualifier');
                                })

                            }



                }

            }

            }
        }

        $scope.getCaps = function (partnumber,parttypename,parttype,partsubtype,ind) {




            if(partsubtype==undefined)
            {
                partsubtype=0;
            }
            let PartTypeName="";

                if (parttype == '10' || parttype== '18')
              {
                PartTypeName = "Drain-Plug";
              }
              else if (parttype == '14')
              {
                PartTypeName = "Housing-Cap";
              }
              else
              {
                PartTypeName=parttypename.replace(" ","-");
              }


            let current_parttype=[];
            let convert_to_string="";


            let parttypearray=JSON.parse($window.sessionStorage.getItem("parttype"));




             angular.element('#caps' + ind).addClass("").animate({
                padding: '0px'
            }, 100, function () {

                callAtTimeout();
            });



            function callAtTimeout() {

                if(partnumber!="Need More Info")
                {
                    let PartNumber_send=partnumber;
                    let Year_send=$scope.selected_year;
                    let Make_send=$scope.selected_make;
                    Make_send=Make_send.replace(/ /g,"+");
                    let Model_send=$scope.selected_model;
                    Model_send=Model_send.replace(/ /g,"+");

                    let PartTypeName_send=PartTypeName;

                    let ymm=Year_send+"-"+Make_send+"-"+Model_send;

                    $window.sessionStorage.setItem('product', JSON.stringify({ partNumber: partnumber,partTypeName:PartTypeName,ymm:ymm}))

                    $timeout(function()
                                {
                                   $location.path('/product-page/'+PartNumber_send+"/"+ymm+"/"+PartTypeName_send);
                                })
                }
                else
                {
                        if(parttypearray!=null)
                            {

                                current_parttype.push(parttypearray.partType);
                                current_parttype.push(parttype);
                                convert_to_string=current_parttype.join();
                                $window.sessionStorage.setItem('parttype', JSON.stringify({partType:convert_to_string,partSubType:partsubtype}))

                                $timeout(function()
                                {
                                   $location.path('/qualifier');
                                })


                            }
                            else
                            {

                                current_parttype.push(parttype);
                                convert_to_string=current_parttype.join();
                                $window.sessionStorage.setItem('parttype', JSON.stringify({ partType:convert_to_string,partSubType:partsubtype}))

                                $timeout(function()
                                {
                                   $location.path('/qualifier');
                                })

                            }
                }

            }
        }

          $scope.getWipers = function (partnumber,parttypename,parttype,partsubtype,ind) {





            if(partsubtype==undefined)
            {
                partsubtype=0;
            }


            let current_parttype=[];
            let convert_to_string="";
            let PartTypeName="Wiper-Blade";


            let parttypearray=JSON.parse($window.sessionStorage.getItem("parttype"));




             angular.element('#wiper' + ind).addClass("").animate({
                padding: '0px'
            }, 100, function () {

                callAtTimeout();
            });


             function callAtTimeout() {

                if(partnumber!="Need More Info")
                {

                    let PartNumber_send=partnumber;
                    let Year_send=$scope.selected_year;
                    let Make_send=$scope.selected_make;
                    Make_send=Make_send.replace(/ /g,"+");
                    let Model_send=$scope.selected_model;
                    Model_send=Model_send.replace(/ /g,"+");
                    let PartTypeName_send=PartTypeName;

                    let ymm=Year_send+"-"+Make_send+"-"+Model_send;


                    $window.sessionStorage.setItem('product', JSON.stringify({ partNumber: partnumber,partTypeName:PartTypeName,ymm:ymm}))

                    $timeout(function()
                                {
                                   $location.path('/product-page/'+PartNumber_send+"/"+ymm+"/"+PartTypeName_send);
                                })

                }
                else
                {
                        if(parttypearray!=null)
                            {

                                current_parttype.push(parttypearray.partType);
                                current_parttype.push(parttype);
                                convert_to_string=current_parttype.join();
                                $window.sessionStorage.setItem('parttype', JSON.stringify({partType:convert_to_string,partSubType:partsubtype}))

                                $timeout(function()
                                {
                                   $location.path('/qualifier');
                                })


                            }
                            else
                            {

                                current_parttype.push(parttype);
                                convert_to_string=current_parttype.join();
                                $window.sessionStorage.setItem('parttype', JSON.stringify({ partType:convert_to_string,partSubType:partsubtype}))

                                $timeout(function()
                                {
                                   $location.path('/qualifier');
                                })

                            }
                }

            }
        }
        
        
        
        $scope.getFluids = function (partnumber,parttypename,parttype,partsubtype,ind) {


          


            if(partsubtype==undefined)
            {
                partsubtype=0;
            }


            let current_parttype=[];
            let convert_to_string="";
            let PartTypeName=parttypename;


            let parttypearray=JSON.parse($window.sessionStorage.getItem("parttype"));


             angular.element('#fluid' + ind).addClass("").animate({
                padding: '0px'
            }, 100, function () {
               
                callAtTimeout();
            });
            

             function callAtTimeout() {
                
                if(partnumber!="Need More Info")
                {
                   
                    let PartNumber_send=partnumber;
                    let Year_send=$scope.selected_year;
                    let Make_send=$scope.selected_make;
                    Make_send=Make_send.replace(/ /g,"+");
                    let Model_send=$scope.selected_model;
                    Model_send=Model_send.replace(/ /g,"+");
                    let PartTypeName_send=PartTypeName;
                    
                    let ymm=Year_send+"-"+Make_send+"-"+Model_send;
                }
                else
                {
                        if(parttypearray!=null)
                            {

                                current_parttype.push(parttypearray.partType);
                                current_parttype.push(parttype);
                                convert_to_string=current_parttype.join();
                                $window.sessionStorage.setItem('parttype', JSON.stringify({partType:convert_to_string,partSubType:partsubtype}))
                             
                                $timeout(function()
                                {
                                   $location.path('/qualifier'); 
                                })
                        

                            }
                            else
                            {

                                current_parttype.push(parttype);
                                convert_to_string=current_parttype.join();
                                $window.sessionStorage.setItem('parttype', JSON.stringify({ partType:convert_to_string,partSubType:partsubtype}))
                             
                                $timeout(function()
                                {
                                   $location.path('/qualifier'); 
                                })

                            }
                }
                
            }
        }
        
        
        

      
        
                    $scope.removeSelection = function (toberemoved)
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
                         
                              $location.path('qualifier'); 

                        }
                        else
                        {
                            $location.path(toberemoved); 
                        }

                    };


    }


}
else
{
        $location.path('/');
}
});
