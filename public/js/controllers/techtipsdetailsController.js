app.controller('techtipsdetailsController', function ($scope, $http, $location, $window,$timeout,clickservice,$sce) {

    $scope.currlocation=window.location.href;
    $scope.pathArray = $scope.currlocation.split( '/' ).pop();
    $scope.finaldata='';
    
    console.log($scope.pathArray);
    $("#loader").show();

    $http.get('geteachpostdetails/'+$scope.pathArray).then(function (response) {

        var eachtechtip = response.data;

        var techtip_title = eachtechtip.title['rendered'];
        techtip_title = techtip_title.replace("&#8217;", '');
        $scope.titles = techtip_title.replace("&#8211;", '-');
        var techtip_content = eachtechtip.content['rendered'];
        
        techtip_content = techtip_content.toString().replace(/(\[.+\])/g, '');
        $scope.finaldata = $sce.trustAsHtml(techtip_content);

        $http.get('geteachpostimage/'+$scope.pathArray).then(function (response) {

        if(response.data) {
            if(response.data._embedded['wp:featuredmedia'] != undefined) {
                var image = response.data._embedded['wp:featuredmedia']['0'].source_url;
                $scope.srcimage=image;
    
                $('.wp-block-image img').each(function(index, obj){
                        var get_src = $(this).attr("data-lazy-src");
                        $(this).attr("src",get_src);
                });

                $('.image_div_post img').each(function(index, obj){
                    var get_src = $(this).attr("data-lazy-src");
                    $(this).attr("src",get_src);
                });
            }
            else {

                $http.get('getpostsimages/'+$scope.pathArray).then(function (response) {
                    var store_data = response.data;
                    var store_images = [];
                    for (i=0;i<store_data.length;i++){
                        var img_src = store_data[i]['guid']['rendered'];
                        store_images.push(img_src);
                    }

                    $scope.all_images = store_images;
                });

                
            }       
        }
            
        $("#loader").hide();
        
        });
       
    });

    // $http({
    //     method: 'GET',
    //     url: 'https://www.pgfilters.com/wp-json/wp/v2/posts/'+$scope.pathArray,
    // }).then(function (response)

    // {
    //     $scope.titles=response.data.title['rendered'];
    //     $scope.titles = $scope.titles.replace("&#8217;", '');
    //     $scope.finaldata=response.data.content['rendered'];
    //     $scope.finaldata=$scope.finaldata.toString().replace(/(\[.+\])/g, '');
    //     $scope.finaldata = $sce.trustAsHtml($scope.finaldata);
    //     $http({
    //         method: 'GET',
    //         url: response.data['_links']['wp:attachment'][0]['href'],
    //     }).then(function (response)

    //     {
    //         $scope.srcimage=response.data[0]['guid']['rendered'];

    //     });
    // });

});
