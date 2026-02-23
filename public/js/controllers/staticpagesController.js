app.controller('staticPagesController', function ($scope, $http, $location, $window,$timeout,clickservice,$sce) {


$scope.goToByScroll=function(id){
        $('html,body').animate({
            scrollTop: $(id).offset().top},
            'slow');
}


  
$scope.storelinks=[{link:"oil-filters",name:'Oil'},{link:"air-filters",name:'Air'},{link:"cabin-air-filters",name:'Cabin'},{link:"fuel-filters",name:'Fuel'},{link:"transmission-filters",name:'Trans'},{link:"pureflow-cabin-filter",name:'PureFlow Cabin'}];

$scope.storelinks_wipers=[{id:"#standard-blades",name:'Standard Blades'},{id:"#beam-blades",name:'Beam Blades'},{id:"#hybrid-blades",name:'Hybrid blades'},{id:"#rear-blades",name:'Rear Blades'}];

$http.get('beaheadCollection').then(function (response) 
{
    $scope.beahead = response.data;
});


var current_location = $location.path();




    //videos
    $http.get('getvideos').then(function (response)
    {
            
            $scope.get_videossearchterm=null;
            $scope.videos = response.data;
            $scope.videos = $sce.trustAsResourceUrl($scope.videos[0].video_link);
            $scope.video_title=response.data[0].video_name;
            $scope.video_desc=response.data[0].video_description;
            $scope.video_id1 =response.data[0].video_id;
            
            $http({
                method: 'GET',
                url: 'videosone/'+$scope.video_id1,
            }).then(function(response) {
                
                //console.log(response.data);
                

                $scope.othervideos=response.data;
                
                if($scope.othervideos.length==0)
                {
                    $scope.othervideos=null;
                }
                else
                {
                    $scope.othervideos=response.data;
                }
                
            
                let vidlinkarr=[];
                let thumbnailarr=[];
                for(i=0;i<$scope.othervideos.length;i++){
                    $scope.othervideoslink = $sce.trustAsResourceUrl($scope.othervideos[i].video_link);
                    vidlinkarr.push($scope.othervideoslink);
                    $scope.youtubeThumbnail=$scope.othervideos[i].video_link.split("/");
                    $scope.youtubeThumbnail1=$scope.youtubeThumbnail[($scope.youtubeThumbnail.length)-1];
                    $scope.othervideos[i].video_link=$scope.youtubeThumbnail1;
                    thumbnailarr.push($scope.youtubeThumbnail1);
                }
                $scope.othervideoslink=vidlinkarr;
                $scope.youtubeThumbnail1=thumbnailarr

            });

    });

    $scope.displaySelectedVideo = function(video_id)
    {
            $scope.get_videossearchterm=null;
            $scope.other_video_id=video_id;
            $http({
                method: 'GET',
                url: 'videos/'+$scope.other_video_id,
            }).then(function(response) {
                $scope.videos = response.data;
                $scope.videos = $sce.trustAsResourceUrl($scope.videos[0].video_link);
                $scope.video_title=response.data[0].video_name;
                $scope.video_desc=response.data[0].video_description;
                $scope.video_id1 =response.data[0].video_id;
                $http({
                    method: 'GET',
                    url: 'videosone/'+$scope.video_id1,
                }).then(function(response) {

                    $scope.othervideos=response.data;
                    let vidlinkarr=[];
                    let thumbnailarr=[];
                    for(i=0;i<$scope.othervideos.length;i++){
                        $scope.othervideoslink = $sce.trustAsResourceUrl($scope.othervideos[i].video_link);
                        vidlinkarr.push($scope.othervideoslink);
                        $scope.youtubeThumbnail=$scope.othervideos[i].video_link.split("/");
                        $scope.youtubeThumbnail1=$scope.youtubeThumbnail[($scope.youtubeThumbnail.length)-1];
                        $scope.othervideos[i].video_link=$scope.youtubeThumbnail1;
                        thumbnailarr.push($scope.youtubeThumbnail1);
                    }
                    $scope.othervideoslink=vidlinkarr;
                    $scope.youtubeThumbnail1=thumbnailarr
                });


            });


    }

    $scope.searchVideos = function()
    {
            $scope.get_videossearchterm=$scope.videoSearch;
            $("#loader").show();
            $http({
                method: 'GET',
                url: 'videoSearch/'+$scope.get_videossearchterm,
            }).then(function(response) {

                $scope.othervideos=response.data;
                let vidlinkarr=[];
                let thumbnailarr=[];
                for(i=0;i<$scope.othervideos.length;i++){
                    $scope.othervideoslink = $sce.trustAsResourceUrl($scope.othervideos[i].video_link);
                    vidlinkarr.push($scope.othervideoslink);
                    $scope.youtubeThumbnail=$scope.othervideos[i].video_link.split("/");
                    $scope.youtubeThumbnail1=$scope.youtubeThumbnail[($scope.youtubeThumbnail.length)-1];
                    $scope.othervideos[i].video_link=$scope.youtubeThumbnail1;
                    thumbnailarr.push($scope.youtubeThumbnail1);
                }
                $scope.othervideoslink=vidlinkarr;
                $scope.youtubeThumbnail1=thumbnailarr
                $("#loader").hide();
                $("#clearBtn").show();



            });


    }

    $scope.removevideoSearch = function()
    {
            $("#clearBtn").hide();
            $("#loader").show();
            $("#videoSearch").val("");
            $http.get('videos').then(function (response)
            {
                $scope.get_videossearchterm=null;
                $scope.videos = response.data;
                $scope.videos = $sce.trustAsResourceUrl($scope.videos[0].video_link);
                $scope.video_title=response.data[0].video_name;
                $scope.video_desc=response.data[0].video_description;
                $scope.video_id1 =response.data[0].video_id;
                $http({
                    method: 'GET',
                    url: 'videosone/'+$scope.video_id1,
                }).then(function(response) {

                    $scope.othervideos=response.data;
                    let vidlinkarr=[];
                    let thumbnailarr=[];
                    for(i=0;i<$scope.othervideos.length;i++){
                        $scope.othervideoslink = $sce.trustAsResourceUrl($scope.othervideos[i].video_link);
                        vidlinkarr.push($scope.othervideoslink);
                        $scope.youtubeThumbnail=$scope.othervideos[i].video_link.split("/");
                        $scope.youtubeThumbnail1=$scope.youtubeThumbnail[($scope.youtubeThumbnail.length)-1];
                        $scope.othervideos[i].video_link=$scope.youtubeThumbnail1;
                        thumbnailarr.push($scope.youtubeThumbnail1);
                    }
                    $scope.othervideoslink=vidlinkarr;
                    $scope.youtubeThumbnail1=thumbnailarr
                });
                $("#loader").hide();
            });
    }



if(current_location=='/technical-bulletins') {

    $("#loader").show();

    //technical bulletins
    $http.get('technicalBulletins').then(function (response) 
    {
        $scope.get_searchterm=[];

        if(response.data.length>0) {
            $scope.bulletins=response.data;
            $scope.save_all_bulletins = $scope.bulletins;
            
        }
        else {
            $scope.bulletins = [];
            $scope.save_all_bulletins = [];
        }
        

       
        // for(i=0;i<$scope.bull.length;i++)
        // {
        //     var path_PDF = $scope.bull[i].file_name;
        //     var PDF_split_path = path_PDF.split("/");
        //     $scope.bull[i].display_name = PDF_split_path.slice(-1)[0];
        // }
        
        
        // console.log($scope.bulletins);

        $("#loader").hide();
        
    });

    $scope.searchResources = function()
    {
        $scope.bull=[];
        $scope.get_searchterm=$scope.resourcesSearch;
        $("#loader").show();
        $http({
            method: 'GET',
            url: 'technicalBulletinsSearch/'+$scope.get_searchterm,
        }).then(function(response) {

          
            if(response.data.length>0 || response.data.length!=undefined) {

                $scope.bulletins=response.data;
            }
            else {
                $scope.bulletins = [];
            }

        // $scope.bulletins = response.data;
        
        // for(i=0;i<$scope.bulletins.length;i++)
        // {
        //     $scope.bull.push({resources_path:$scope.bulletins[i]});
        // }
        
        // $scope.bulletins=$scope.bull;
        
            $("#loader").hide();
            $("#clearBtn").show();
        });
    }
    
    $scope.removeSearch = function()
    {
        $("#clearBtn").hide();
        $("#loader").show();
        $("#resourcesSearch").val("");
        $("#techtipsSearch").val("");

        if($scope.save_all_bulletins.length > 0) {
            $scope.bulletins = $scope.save_all_bulletins;
        }
        else {
            $scope.bulletins = [];
        }

        $("#loader").hide();
        // searchResources("");
        // $http.get('technicalBulletins').then(function (response)
        // {
        //     $scope.get_searchterm=[];
        //     $scope.bulletins = response.data;
        //     $("#loader").hide();
        // });
    }

}


if(current_location=='/tech-tips') {

    $("#loader").show();

    $http.get('technicalTips').then(function (response)
    {
            //tech tips
            
            $scope.get_searchtechtipsterm=null;
            $scope.userData = response.data;

            for(i=0;i<$scope.userData.length;i++){
                $scope.newtitle=$scope.userData[i].title.rendered;
                $scope.newtitle=$scope.newtitle.replaceAll("&#8217;", '');
                $scope.newtitle=$scope.newtitle.replaceAll("&#8211;", '-');
                $scope.userData[i].title.rendered=$scope.newtitle;

            }

            setTimeout(function() {
                $("#loader").hide();
            }, 3000);

    });




    $scope.searchTechTips = function()
    {
            $scope.newstore='';
            $scope.get_searchtechtipsterm=$scope.techtipsSearch;
            $("#loader").show();
            $http({
                method: 'GET',
                url: 'techTipsSearch/'+$scope.get_searchtechtipsterm,
            }).then(function(response) {
                console.log(response.data);
                $scope.searchtermresult=response.data;
                $scope.searchuserdata='';
                //$scope.newstore='';
                let userdata1=[];
                for (i=0;i<$scope.searchtermresult.length;i++){

                    var get_techtips_title = $scope.searchtermresult[i].title['rendered'];
                    get_techtips_title = get_techtips_title.replaceAll("&#8217;", '');
                    get_techtips_title = get_techtips_title.replaceAll("&#8211;", '-');
                    $scope.searchtermresult[i].title['rendered'] = get_techtips_title;

                    var techtip_content = $scope.searchtermresult[i].content['rendered'];
                    techtip_content = techtip_content.toString().replace(/(\[.+\])/g, '');
                    if (techtip_content.toLowerCase().indexOf($scope.get_searchtechtipsterm.toLowerCase())!== -1){
                        $scope.searchuserdata=$scope.searchtermresult[i];
                        userdata1.push($scope.searchuserdata);
                    }
                    else if(get_techtips_title.toLowerCase().indexOf($scope.get_searchtechtipsterm.toLowerCase())!=-1) {
                        $scope.searchuserdata=$scope.searchtermresult[i];
                        userdata1.push($scope.searchuserdata);
                    }
                }

                console.log(userdata1);
                $scope.newstore=userdata1;
                $("#loader").hide();
                $("#clearBtn").show();

            });


    }

    $scope.removetechtipSearch = function()
    {
            $("#clearBtn").hide();
            $("#loader").show();
            $("#techtipsSearch").val("");
            $http.get('technicalTips').then(function (response)
            {
                $scope.get_searchtechtipsterm=null;
                $scope.userData = response.data;
                $("#loader").hide();

                for(i=0;i<$scope.userData.length;i++){
                    $scope.newtitle=$scope.userData[i].title.rendered;
                    $scope.newtitle=$scope.newtitle.replaceAll("&#8217;", '');
                    $scope.newtitle=$scope.newtitle.replaceAll("&#8211;", '-');
                    $scope.userData[i].title.rendered=$scope.newtitle;
        
                }

            });

    }

}


if(current_location=='/blogs') {

    $("#loader").show();

//blogs
    $http.get('getblogs').then(function (response)
    {
        
        $scope.get_searchblogsterm=null;
        $scope.userBlogData = response.data;

        if($scope.userBlogData.length > 0 ) {
            for(i=0;i<$scope.userBlogData.length;i++){
                $scope.newvarfortitle=$scope.userBlogData[i].title.rendered;
                $scope.newvarfortitle=$scope.newvarfortitle.replaceAll("&#8217;", '');
                $scope.newvarfortitle=$scope.newvarfortitle.replaceAll("&#8211;", '-');
                $scope.userBlogData[i].title.rendered=$scope.newvarfortitle;
    
            }
        }
        else {
            $scope.userBlogData = [];
        }   
            
        setTimeout(function() {
            $("#loader").hide();
        }, 3000);

    });


    $scope.searchBlogs = function()
    {
        if($scope.userBlogData.length>0) {
            $scope.newstoreblog='';
            $scope.get_searchblogsterm=$scope.blogSearch;
            $("#loader").show();

            $scope.searchblogtermresult=$scope.userBlogData;
              
                $scope.searchuserblogdata='';
                let userdatablog1=[];
                for (i=0;i<$scope.searchblogtermresult.length;i++){

                    $scope.newvarfortitle=$scope.searchblogtermresult[i].title['rendered'];
                    $scope.newvarfortitle=$scope.newvarfortitle.replaceAll("&#8217;", '');
                    $scope.newvarfortitle=$scope.newvarfortitle.replaceAll("&#8211;", '-');
                    $scope.searchblogtermresult[i].title['rendered']=$scope.newvarfortitle;

                    var blog_content = $scope.searchblogtermresult[i].content['rendered'];
                    blog_content = blog_content.toString().replace(/(\[.+\])/g, '');
                   
                    if (blog_content.toLowerCase().indexOf($scope.get_searchblogsterm.toLowerCase())!== -1){
                        $scope.searchuserblogdata=$scope.searchblogtermresult[i];
                        userdatablog1.push($scope.searchuserblogdata);
                    }
                    else if($scope.newvarfortitle.toLowerCase().indexOf($scope.get_searchblogsterm.toLowerCase())!=-1) {
                        $scope.searchuserblogdata=$scope.searchblogtermresult[i];
                        userdatablog1.push($scope.searchuserblogdata);
                    }
                }
                $scope.newstoreblog=userdatablog1;
                $("#loader").hide();
                $("#clearBtn").show();

            // $http({
            //     method: 'GET',
            //     url: 'blogSearch/'+$scope.get_searchblogsterm,
            // }).then(function(response) {

            //     $scope.searchblogtermresult=response.data;
              
                
            //     $scope.searchuserblogdata='';
            //     let userdatablog1=[];
            //     for (i=0;i<$scope.searchblogtermresult.length;i++){

            //         $scope.newvarfortitle=$scope.searchblogtermresult[i].title['rendered'];
            //         $scope.newvarfortitle=$scope.newvarfortitle.replaceAll("&#8217;", '');
            //         $scope.newvarfortitle=$scope.newvarfortitle.replaceAll("&#8211;", '-');
            //         $scope.searchblogtermresult[i].title['rendered']=$scope.newvarfortitle;

            //         var blog_content = $scope.searchblogtermresult[i].content['rendered'];
            //         blog_content = blog_content.toString().replace(/(\[.+\])/g, '');
                   
            //         if (blog_content.toLowerCase().indexOf($scope.get_searchblogsterm.toLowerCase())!== -1){
            //             $scope.searchuserblogdata=$scope.searchblogtermresult[i];
            //             userdatablog1.push($scope.searchuserblogdata);
            //         }
            //         else if($scope.newvarfortitle.toLowerCase().indexOf($scope.get_searchblogsterm.toLowerCase())!=-1) {
            //             $scope.searchuserblogdata=$scope.searchblogtermresult[i];
            //             userdatablog1.push($scope.searchuserblogdata);
            //         }
            //     }
            //     $scope.newstoreblog=userdatablog1;
            //     $("#loader").hide();
            //     $("#clearBtn").show();
            // });
        }
    }
        

    $scope.removeblogSearch = function()
    {

        if($scope.userBlogData.length>0) {
            $("#clearBtn").hide();
            $("#loader").show();
            $("#blogSearch").val("");

            $scope.get_searchblogsterm=null;
           // $scope.userBlogData = response.data;

            for(i=0;i<$scope.userBlogData.length;i++){
                $scope.newvarfortitle=$scope.userBlogData[i].title.rendered;
                $scope.newvarfortitle=$scope.newvarfortitle.replaceAll("&#8217;", '');
                $scope.newvarfortitle=$scope.newvarfortitle.replaceAll("&#8211;", '-');
                $scope.userBlogData[i].title.rendered=$scope.newvarfortitle;
            }

            $("#loader").hide();

        }
            // $http.get('blogs').then(function (response)
            // {
            //     $scope.get_searchblogsterm=null;
            //     $scope.userBlogData = response.data;
            //     for(i=0;i<$scope.userBlogData.length;i++){
            //         $scope.newvarfortitle=$scope.userBlogData[i].title.rendered;
            //         $scope.newvarfortitle=$scope.newvarfortitle.replaceAll("&#8217;", '');
            //         $scope.newvarfortitle=$scope.newvarfortitle.replaceAll("&#8211;", '-');
            //         $scope.userBlogData[i].title.rendered=$scope.newvarfortitle;

            //     }
            //     $("#loader").hide();

            // });


    }

}
    
$scope.Productpage = function(partnumber,parttypename,parttype)
{
   

    let storepart=parttype;

    if(storepart=="4")
            {
              PartTypeName="Fuel-Filter";
            }else if(storepart=='1' || storepart=='7' || storepart=='12')
            {
              PartTypeName = "Oil-Filter";
            }
            else if(storepart=='2' || storepart=='13')
            {
              PartTypeName="Air-Filter";
            }
            else if(storepart == '3' || storepart == '16')
            {
              PartTypeName="Cabin-Air-Filter";
            }else if(storepart=='10' || storepart == '18')
            {
              PartTypeName = "Drain-Plug";
            }
            else if(storepart=='14')
            {
              PartTypeName="Housing-Cap";
            }else if(storepart=='8' || storepart=='9' || storepart=='11' || storepart=='15' || storepart=='17')
            {
              PartTypeName = "Wiper-Blade";
            }else
            {
              PartTypeName=parttypename.replace(" ","-");
            }




            $http({
            method: 'GET',
            url: 'beaheadymm/'+partnumber,       
        }).then(function(response) {

          var storeymm_data=response.data[0];
          var store_year=storeymm_data.year;
          var store_make=storeymm_data.make;
          store_make=store_make.replace(/ /g,"-");
          var store_model=storeymm_data.model;
          store_model=store_model.replace(/ /g,"-");




            let ymm=store_year+"-"+store_make+"-"+store_model;

            var dyanamicsearch="dyanamic";
            $window.sessionStorage.setItem('dyanamic', JSON.stringify({dyanamic:dyanamicsearch}));
            $window.sessionStorage.setItem('product', JSON.stringify({partNumber:partnumber,partTypeName:PartTypeName,ymm:ymm}));
            $window.sessionStorage.setItem('beahead', JSON.stringify({partNumber: partnumber,partTypeName:PartTypeName}));
                    
                    $timeout(function()
                                {
                                   $location.path('/product-page/'+partnumber+"/"+ymm+"/"+PartTypeName); 
                                })
            
        });

}



});