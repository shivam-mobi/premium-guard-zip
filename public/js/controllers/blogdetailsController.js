app.controller('blogdetailsController', function ($scope, $http, $location, $window,$timeout,clickservice,$sce) {

    $scope.currlocation=window.location.href;
    $scope.pathArray = $scope.currlocation.split( '/' ).pop();
    $scope.finaldata='';

    console.log($scope.pathArray);
    $("#loader").show();

    $http.get('geteachpostdetails/'+$scope.pathArray).then(function (response) {

        var eachblog = response.data;
       
        var blog_title = eachblog.title['rendered'];
        $scope.titles = blog_title.replace("&#8217;", '');
        $scope.titles = blog_title.replace("&#8211;", '-');
        var blog_content = eachblog.content['rendered'];
       

        blog_content = blog_content.toString().replace(/(\[.+\])/g, '');
        $scope.finaldata = $sce.trustAsHtml(blog_content);
        

        $http.get('geteachpostimage/'+$scope.pathArray).then(function (response) {
       
        if(response.data) {
            if(response.data._embedded['wp:featuredmedia'] != undefined) {
                var image = response.data._embedded['wp:featuredmedia']['0'].source_url;
                $scope.srcimage=image;
    
                $('.wp-block-image img').each(function(index, obj){
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
});
