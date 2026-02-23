app.controller('vinLoadCamera', function ($scope,$route, $http, $location, $window,$timeout,clickservice) {


    const html5QrCode = new Html5Qrcode("reader");
       
        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
    
            var vinnumber = decodedText
            html5QrCode.stop().then((ignore) => {
                // QR Code scanning is stopped.
              }).catch((err) => {
                // Stop failed, handle it.
              });
    
              if(vinnumber!="")
                    {
                        $("#loader").show();
                        $http({
                            method: 'GET',
                            url: 'vinCollection/'+vinnumber     
                        }).then(function(response) 
                        {
                            console.log(response);
    
                            let save_result=[];
                            let save_result1=[];
                            var selected_qualifier='noqualifier';
                            save_result=response.data.res;
    
                            if(save_result=='nodata')
                            {
                                $window.sessionStorage.setItem('novin', JSON.stringify({searchterm:vinnumber}));
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
    
                                console.log(response);
                    
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
            /* handle success */
        };

        const scanner_config = { fps: 50 , qrbox: { width: window.screen.width - 30 , height: 150 } , rememberLastUsedCamera : true};
        html5QrCode.start({ facingMode: "environment" }, scanner_config, qrCodeSuccessCallback);
        
        screen.orientation.addEventListener('change', function(e) { 
            html5QrCode.stop();
            html5QrCode.clear();

            var ore_type = e.target.type;
            var set_width = $(window).width();

            if(ore_type.includes('landscape')) {
                var scanner_config1 = { fps: 50 , qrbox: { width: 450 , height: 150 } , rememberLastUsedCamera : true};
                html5QrCode.start({ facingMode: "environment" }, scanner_config1, qrCodeSuccessCallback);
            }
            else {
                var scanner_config1 = { fps: 50 , qrbox: { width: set_width - 30 , height: 150 } , rememberLastUsedCamera : true};
                html5QrCode.start({ facingMode: "environment" }, scanner_config1, qrCodeSuccessCallback);
            }
        })
});