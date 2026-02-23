app.controller('DynamicSearch', function ($scope, $http, $location, $window, $timeout,clickservice){

  
  $("#microphone-icon").show();
  $("#listenmodal").modal("hide");
  
  document.addEventListener("touchstart", function(){}, true)
  // $('#loader-icon').hide();

  let alldata=[];





  $scope.show_dynamic_data = function(res) {

    let alldata=res.data.filter(function(e) {
          
      var bvid=[];
      var engineid=[];
      if(e.id=="partnumber")
      {
        e.label=e.label;
        e.id=e.id+"!"+e.label+"!"+e.value;
        e.disabled = false;
        e.class_name = '';
      }
      else if(e.id=="partintnumber")
      {
        e.label=e.label;
        e.id=e.id+"!"+e.label+"!"+e.value;
        e.disabled = false;
        e.class_name = '';
      }
      else if(e.id=="label")
      {
        e.label=e.label;
        e.disabled = true;
        e.class_name = 'visual-text-bold';
      }
      else
      {
        e.label=e.label;
        var bvid=e.value.split("===");
        var engineid=e.id.split("!");
        var engineconid=engineid.slice(-1)[0];
        e.id=bvid[0]+"!"+engineconid+"!"+bvid[1]+"!"+engineid[1];
        e.disabled = false;
        e.class_name = '';
      }
      return e;
    });

    $scope.filterdata=alldata;

  }


  $scope.complete = function(string)
  {
  
        $window.sessionStorage.clear();//clear all session data stored previously
        var searchterm=string;//string searched

        if(searchterm!="" && searchterm!=undefined)
        {
          
          $scope.showResult = true;

          $http({
              method: 'GET',
              async:false,
              url: 'dyanamicCollection_new/'+searchterm       
          }).then(function(response) {


            if(response.data.length==0) {

                  $http({
                    method: 'GET',
                    async:false,
                    url: 'dyanamicCollection/pt/'+searchterm       
                  }).then(function(response1) {

                    if(response1.data.length==0) {

                      $scope.showResult = true;
                      $scope.filterdata = []; //Show not result found
        
                      $http({
                        method: 'GET',
                        url: 'notFoundCount/'+searchterm
                      }).then(function(response) {
                      });
                    }
                    else {

                      //If response is not related to partype
                      if(response1.data[0].parttype!='No') {
                        $scope.showResult = true;
                        $scope.show_dynamic_data(response1);   
                      }
                      else {
                        $scope.showResult = false;
                      }
        
                    }

                  });
              
            }
            else {
                $scope.show_dynamic_data(response);  
            }
    
             
          });
  
        }
        else
        { 
          $scope.showResult = false;
        }
      
  }
  
  
        $scope.VinClick = function() {
          $timeout(function()
          {
              $location.path('/vinloadcamera'); 
          })
        }

        $scope.ProductComp = function() {
          $timeout(function() 
          {
              $location.path('/productcomp');
          })
        }

        
  
        $scope.displayResult = function(id,ind){
  
            var splitid=[];
            splitid=id.split("!");
  
     
  
            var selected_qualifier='noqualifier';
  
  
  
  
             angular.element('#listofproducts' + ind).addClass("divBackground1").animate({
                    padding: '0px'
                },0, function () {
                    //$(this).click();
                    callAtTimeout();
                });
  
  
  
              //angular.element('#listofproducts' + ind).addClass("divBackground1");
  
              // //$(this).click();
              //     callAtTimeout();
  
                  function callAtTimeout() {
  
                
          
                   if(splitid[0]!="partnumber" && splitid[0]!="partintnumber")
                   {
            
                var bvid=splitid[0];
              var engineid=splitid[1];
              var year=splitid[2];
              var make=splitid[3];
              var model=splitid[4];
              var engine=splitid[5];
              var makeID=splitid[6];
              var parttype=0;
              var partsubtype=0;
              var response1='';
  
              var dyanamicsearch="dyanamic";
              $window.sessionStorage.setItem('dyanamic', JSON.stringify({dyanamic:dyanamicsearch}));
              $window.sessionStorage.setItem('year', JSON.stringify({ yearId: year }));
              $window.sessionStorage.setItem('make', JSON.stringify({makeId: makeID,makeName: make }));
              $window.sessionStorage.setItem("model",JSON.stringify({baseVehicleID:bvid,modelName:model}));
              $window.sessionStorage.setItem('engine', JSON.stringify({ engineId: engineid, engineName: engine }));
  
  
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
  
  
              //$location.path('productlist');
            }
            else
            {
  
              if(splitid[0]=="partnumber")//for partnumber
              {
                
                var partnumber=splitid[1];
                var parttypename=splitid[2];
                var splitpart=parttypename.split("===");
                var storeparttypename=splitpart[0];
                var storepart=splitpart[1];
                var PartTypeName="";
                
                
                $http({
                      method: 'GET',
                      url: 'partNumberCount/'+partnumber
                  }).then(function(response) {
                      
                  });
  
  
  
                if(storepart=="4"){
        PartTypeName="Fuel-Filter";
     }else if(storepart=='1' || storepart=='7' || storepart=='12'){
        PartTypeName = "Oil-Filter";
     }else if(storepart=='2' || storepart=='13'){
        PartTypeName="Air-Filter";
     }else if(storepart == '3' || storepart == '16'){
         PartTypeName="Cabin-Air-Filter";
     }else if(storepart=='10' || storepart == '18'){
        PartTypeName = "Drain-Plug";
     }else if(storepart=='14'){
        PartTypeName="Housing-Cap";
     }else if(storepart=='8' || storepart=='9' || storepart=='11' || storepart=='15' || storepart=='17'){
        PartTypeName = "Wiper-Blade";
     }else{
    PartTypeName=storeparttypename.replace(" ","-");
  }
  
  
  
  
  
  
  
  
  
                
  
  
               $http({
              method: 'GET',
              url: 'premiumCollection/'+partnumber,       
          }).then(function(response) {
  
       
  
            var storeymm_data=response.data[0];
            var store_year=storeymm_data.year;
            var store_make=storeymm_data.make;
            store_make=store_make.replace(/ /g,"+");
            var store_model=storeymm_data.model;
            store_model=store_model.replace(/ /g,"+");
  
  
  
  
            let ymm=store_year+"-"+store_make+"-"+store_model;
  
  
            
  
              var dyanamicsearch="dyanamic";
              $window.sessionStorage.setItem('dyanamic', JSON.stringify({dyanamic:dyanamicsearch}));
              $window.sessionStorage.setItem('product', JSON.stringify({partNumber: partnumber,partTypeName:PartTypeName,ymm:ymm}));
                      
                      $timeout(function()
                                  {
                                     $location.path('/product-page/'+partnumber+"/"+ymm+"/"+PartTypeName); 
                                  })
              
          });
  
              //$location.path('product_page');
  
             
              }
              else//for partintnumber
              {
                  
                  
                    var store_part=splitid[1];
                    var store_partintno_id=splitid[2];
                    
                    
  
                    $window.sessionStorage.setItem('partinterchnage', JSON.stringify({partintno_id:store_partintno_id}));
  
                    var dyanamicsearch="dyanamic";
                    $window.sessionStorage.setItem('dyanamic', JSON.stringify({dyanamic:dyanamicsearch}));
  
                    $timeout(function()
                    {
                      $location.path('partinterchange');
                    })
          
              
  
              }
              
            }
                   
            }
              
        }


      $scope.closeModal = function() {
          if(recorder.state == 'recording') {
            abortRecording();
            suspendRecording();
          }
      }
        
        
        //voice search code
      $scope.microhoneClicked = function() {

          // $scope.searchtext = '';
          // $scope.filterdata = [];
          $scope.showResult = false;
       
          resumeRecording();
      
          // monkeypatch Web Audio
          window.AudioContext = window.AudioContext || window.webkitAudioContext;
          // grab an audio context
          audioContext = new AudioContext();
  
          try {
  
              navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia ||
              navigator.mozGetUserMedia;
  
  
              navigator.mediaDevices.getUserMedia({ audio: true,video:false })
              .then((stream) => {
  
                  function onRecordingReadyMain(e) {
  
                   
                      if (audioPlay) {
  
                          suspendRecording()

                          $("#loader").show();
  
                          let browsername="";
                          if (navigator.userAgent.search("Safari") >= 0 && navigator.userAgent.search("Chrome") < 0) 
                          {
                              browsername="Safari";
                          }
                          else if (navigator.userAgent.search("Firefox") >= 0 && navigator.userAgent.search("Chrome") < 0) 
                          {
                              browsername="Firefox";
                          }
                          audioChunks = [];
                          audioChunks.push(e.data);
                          const audioBlob = new Blob(audioChunks);
                  
                          var fd = new FormData();
                          fd.append('file', audioBlob);
                          fd.append('browsername', browsername);

                          var request = {
                            method: 'POST',
                            url: 'voicesearch',
                            data: fd,
                            headers: {
                              'Content-Type': undefined
                            }
                          };

                          //call api
                          $http(request).then(function (response){

                           
                            if(response.data != '') {
                              $scope.searchtext = response.data;
                              $scope.complete($scope.searchtext);
                              $("#listenmodal").modal("hide");
                              // $('#loader-icon').hide();
                              
                            }
                            else {
                              $("#listenmodal .modal-body").html("<p style='font-size:20px;'>Sorry didn't get that.Please try again</p>");
                              $("#listenmodal").modal("show");
                            }

                            $("#loader").hide();

                            
                            },function (error){
                              alert(error);
                          });
  
                      }
                      
                  }
  
                  audioStream(stream)

                  audioDetection(DEFAULT_PARAMETERS_CONFIGURATION)

                  //add public/ while pushing to server at start of img tag

                  setTimeout(function() {
                    $("#listenmodal .modal-body").html('<p style="font-size:20px;">Listening... <img src="public/image/microphone.gif" alt="Microphone Icon"  style="width: 15%;"></p>');
                    $("#listenmodal").modal("show");
                  }, 1000)
              
                  let options;
                  if (navigator.userAgent.search("Safari") >= 0 && navigator.userAgent.search("Chrome") < 0) 
                  {
                      options = {mimeType: 'audio/mp4'}; 
                  }
                  else if (navigator.userAgent.search("Firefox") >= 0 && navigator.userAgent.search("Chrome") < 0) 
                  {
                      console.log('in Firefox');
                      options = {mimeType: 'audio/mp4'}; 
                  }
                  else{
                      console.log('elseeeeeee');
                      options = {mimeType: 'audio/webm;codecs=opus'};
                  }
  
                  recorder = new MediaRecorder(stream)
                  recorder.addEventListener('dataavailable', onRecordingReadyMain)
  
              })
      
          }
          catch(e) {
              alert('getUserMedia threw exception :' + e);
          }
         
      }
  
  });
  
  