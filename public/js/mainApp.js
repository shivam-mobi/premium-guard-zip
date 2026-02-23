var app = angular.module("pgfilterApp", ["ngRoute"]);
let env = "local";
//let env='production'
let publicUrl = "";
if (env == "production") {
    publicUrl = "/public/";
}
app.run(function ($rootScope, $location, $routeParams) {
    var date = new Date();
    var year = date.getFullYear();
    $rootScope.footerYear = year;
    $rootScope.publicUrl = "";
    if (env == "production") {
        $rootScope.publicUrl = "/public/";
    }

    $rootScope.$on("$routeChangeSuccess", function (event, current, previous) {
        $rootScope.title = current.$$route.title;

        $rootScope.noindex = current.$$route.index;
        console.log($rootScope.noindex);

        if ($rootScope.noindex != undefined) {
            if ($('meta[name="robots"]').length == 0) {
                $("head").append(
                    '<meta name="robots" content="noindex,nofollow">'
                );
            }
        } else {
            if ($('meta[name="robots"]').length > 0) {
                console.log("meta robot tag already present");
                $('meta[name="robots"]').remove();
            }
        }

        // if($rootScope.title=='Product Page') {

        //   var partnumber = $routeParams.PartNumber_send;
        //   var ymm = $routeParams.ymm;
        //   var parttype_name = $routeParams.PartTypeName_send;
        //   $rootScope.canonicalurl = 'https://www.pgfilters.com/product-page/'+partnumber+'/'+ymm+'/'+parttype_name+'/';
        // }
        // else {
        //   $rootScope.canonicalurl = current.$$route.canonicalUrl;
        // }

        $rootScope.canonicalurl = current.$$route.canonicalUrl;

        //add dynamic canonical tag for urls which are in menu
        $('link[rel="canonical"]').attr("href", $rootScope.canonicalurl);
    });
});

console.log(publicUrl);

app.config(function ($routeProvider, $locationProvider) {
    $routeProvider
        .when("/", {
            title: "Home",
            canonicalUrl: "https://www.pgfilters.com/",
            templateUrl: publicUrl + "views/home.blade.php",
        })
        .when("/year", {
            title: "Year",
            index: "noindex",
            templateUrl: publicUrl + "views/year.blade.php",
        })
        .when("/vehicletype", {
            title: "Vehicle Type",
            index: "noindex",
            templateUrl: publicUrl + "views/vehicletype.blade.php",
        })
        .when("/make", {
            title: "Make",
            index: "noindex",
            templateUrl: publicUrl + "views/make.blade.php",
        })
        .when("/model", {
            title: "Model",
            index: "noindex",
            templateUrl: publicUrl + "views/model.blade.php",
        })
        .when("/engine", {
            title: "Engine",
            index: "noindex",
            templateUrl: publicUrl + "views/engine.blade.php",
        })
        .when("/qualifier", {
            title: "Qualifier",
            index: "noindex",
            templateUrl: publicUrl + "views/qualifier.blade.php",
        })
        .when("/productlist", {
            title: "Product List",
            index: "noindex",
            templateUrl: publicUrl + "views/productlist.blade.php",
        })
        .when("/productlist1", {
            title: "Product List",
            index: "noindex",
            templateUrl: publicUrl + "views/productlist1.blade.php",
        })
        .when("/product-page/:PartNumber_send/:ymm/:PartTypeName_send", {
            title: "Product Page",
            templateUrl: publicUrl + "views/product_page.blade.php",
        })
        .when("/applications", {
            title: "Applications",
            index: "noindex",
            templateUrl: publicUrl + "views/applications.blade.php",
        })
        .when("/competitors", {
            title: "Competitors",
            index: "noindex",
            templateUrl: publicUrl + "views/competitors.blade.php",
        })
        .when("/no_information", {
            title: "No Information",
            index: "noindex",
            templateUrl: publicUrl + "views/no_information.blade.php",
        })
        .when("/oil-filters", {
            title: "Oil Filters",
            canonicalUrl: "https://www.pgfilters.com/oil-filters/",
            templateUrl: publicUrl + "views/oil-filters.blade.php",
        })
        .when("/air-filters", {
            title: "Air Filters",
            canonicalUrl: "https://www.pgfilters.com/air-filters/",
            templateUrl: publicUrl + "views/air-filters.blade.php",
        })
        .when("/cabin-air-filters", {
            title: "Cabin Air Filters",
            canonicalUrl: "https://www.pgfilters.com/cabin-air-filters/",
            templateUrl: publicUrl + "views/cabin-filters.blade.php",
        })
        .when("/fuel-filters", {
            title: "Fuel Filters",
            canonicalUrl: "https://www.pgfilters.com/fuel-filters/",
            templateUrl: publicUrl + "views/fuel-filters.blade.php",
        })
        .when("/transmission-filters", {
            title: "Transmission Filters",
            canonicalUrl: "https://www.pgfilters.com/transmission-filters/",
            templateUrl: publicUrl + "views/transmission-filters.blade.php",
        })
        .when("/pureflow-cabin-filter", {
            title: "Pureflow Cabin Filters",
            canonicalUrl: "https://www.pgfilters.com/pureflow-cabin-filter/",
            templateUrl: publicUrl + "views/pureflow-cabin.blade.php",
        })
        .when("/about", {
            title: "About",
            canonicalUrl: "https://www.pgfilters.com/about/",
            templateUrl: publicUrl + "views/about.blade.php",
        })
        .when("/be-ahead3", {
            title: "Be Ahead",
            canonicalUrl: "https://www.pgfilters.com/be-ahead3/",
            templateUrl: publicUrl + "views/beahead.blade.php",
        })
        .when("/wiper-blades", {
            title: "Wiper Blades",
            canonicalUrl: "https://www.pgfilters.com/wiper-blades/",
            templateUrl: publicUrl + "views/wiper-blades.blade.php",
        })
        .when("/disposable-gloves", {
            title: "Disposable Gloves",
            canonicalUrl: "https://www.pgfilters.com/disposable-gloves/",
            templateUrl: publicUrl + "views/disposable-gloves.blade.php",
        })
        .when("/hose-clamps", {
            title: "Hose Clamps",
            canonicalUrl: "https://www.pgfilters.com/hose-clamps/",
            templateUrl: publicUrl + "views/hose-clamps.blade.php",
        })
        .when("/contact", {
            title: "Contact",
            canonicalUrl: "https://www.pgfilters.com/contact/",
            templateUrl: publicUrl + "views/contact.blade.php",
        })
        .when("/technical-bulletins", {
            title: "Technical Bulletins",
            canonicalUrl: "https://www.pgfilters.com/technical-bulletins/",
            templateUrl: publicUrl + "views/technical-bulletins.blade.php",
        })
        .when("/blogs", {
            title: "Blogs",
            canonicalUrl: "https://www.pgfilters.com/blogs/",
            templateUrl: publicUrl + "views/blogs.blade.php",
        })
        .when("/tech-tips", {
            title: "Tech Tips",
            canonicalUrl: "https://www.pgfilters.com/category/tech-tips/",
            templateUrl: publicUrl + "views/technical-tips.blade.php",
        })
        .when("/tech-tips/:postid", {
            title: "Tech Tips",
            index: "noindex",
            templateUrl: publicUrl + "views/tech-tips_details.blade.php",
        })
        .when("/blogs/:blogid", {
            title: "Blogs",
            index: "noindex",
            templateUrl: publicUrl + "views/blogs_details.blade.php",
        })
        .when("/videos", {
            title: "Videos",
            canonicalUrl: "https://www.pgfilters.com/videos/",
            templateUrl: publicUrl + "views/videos.blade.php",
        })

        .when("/no-vin", {
            title: "No Vin",
            index: "noindex",
            templateUrl: publicUrl + "views/no_vin.blade.php",
        })

        .when("/partinterchange", {
            title: "Part Interchange",
            index: "noindex",
            templateUrl: publicUrl + "views/partinterchange.blade.php",
        })

        .when("/vinloadcamera", {
            title: "Vin Scan",
            index: "noindex",
            templateUrl: publicUrl + "views/vinloadcamera.blade.php",
        })

        .when("/productcomp", {
            title: "Product Comparison",
            index: "noindex",
            templateUrl: publicUrl + "views/productcomp.blade.php",
        })

        .when("/voicesearch", {
            title: "Voice Search",
            index: "noindex",
            templateUrl: publicUrl + "views/voice-search.blade.php",
        })

        .when("/latemodel", {
            title: "Late Model Coverage",
            index: "noindex",
            templateUrl: publicUrl + "views/latemodel.blade.php",
        });

    $locationProvider.html5Mode(true);
});
