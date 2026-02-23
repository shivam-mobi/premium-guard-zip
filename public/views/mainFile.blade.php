<?php 
$publicUrl='public'
?>
<!DOCTYPE html>
<html lang="en" ng-app="pgfilterApp">
<head>
   <base href="/">
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
   <!-- <title>Premium Guard</title> -->
   <title ng-bind="title"></title>
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <meta name="description" content="Premium Guard product line is positioned as a leader in the automotive filters aftermarket">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
   <link rel="stylesheet" type="text/css" href="css/jquery-ui.css"/>
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
   <link rel="stylesheet" type="text/css" href="<?php echo config('constants.public_url');?>css/bootstrap.css"/>
   <link rel="stylesheet" type="text/css" href="<?php echo config('constants.public_url');?>css/static_pages.css"/>
   <link rel="stylesheet" type="text/css" href="<?php echo config('constants.public_url');?>css/style.css?<?php echo time();?>"/>
   <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Lora:400,700,400italic|Oswald:400,700" media="screen">
   <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
   <link rel="canonical" href="https://www.pgfilters.com/">
   <style>
      @font-face 
      {
         font-family: "Helvetica Condensed Black";
         src: url(<?php echo config('constants.public_url');?>fonts/HelveticaCondensedBlack.ttf);
      }
   </style>   
</head>
<body  class="backgr_color">
<header id="main-header" class="header">
<div class="container container_width">
   <div class="logo_container">
      <a href="https://m.pgfilters.com"><img src="<?php echo config('constants.public_url');?>image/logo.png" alt="Premium Guard Logo" class="logo"></a>
      <button aria-label="Menu Dropdown" class="sandwich_btn" type="button" data-target="#mynavbar1" data-toggle="collapse"><span class="dark-blue-text"><i class="fa fa-bars"></i></span>
      </button>
</div> 
<div id="mynavbar1" class="collapse collapse_menu" style="background-color: rgba(0, 0, 0, 0.7);">
          <div class="mynavbar"></div>
          
          <ul class="ul_padding_top">
            <li class="nav-item active">
               <a class="nav-link clickable-link" href="https://m.pgfilters.com">Home</a>
            </li>
         </ul>
         <ul>
         <li class="nav-item">
            <a class="nav-link clickable-link" href="be-ahead3">Be Ahead</a>
         </li>
         </ul>
         <ul>
         	<li class="nav-item">
         		<li class="nav-item-link" data-toggle="collapse" data-target="#products" aria-controls="navbarContent" aria-label="Toggle navigation">
            		<a class="nav-link">Products <i class="arrow right"></i> </a>
            	</li>
            		<ul id="products" class="collapse">
            			<a class="nav-link" data-toggle="collapse" data-target="#filters" aria-controls="navbarContent" aria-label="Toggle navigation">Filters <i class="arrow right"></i> </a>
            				<li id="filters" class="collapse nav-item-link">
		            			<ul>
			            			<li class="nav-item-link">
					                  <a class="nav-link clickable-link" href="oil-filters">Oil Filters</a>
					               </li>
				           		</ul>
				           		<ul>
					               <li class="nav-item-link">
					                  <a class="nav-link clickable-link" href="air-filters">Air Filters</a>
					               </li>
				           		</ul>
				           		<ul>
					               <li class="nav-item-link">
					                  <a class="nav-link clickable-link" href="cabin-air-filters">Cabin Air Filters</a>
					               </li>
				           		</ul>
				           		<ul>
						           <li class="nav-item-link">
					                  <a class="nav-link clickable-link" href="pureflow-cabin-filter">Pureflow Air Filters</a>
					               </li>
					             </ul>
					             <ul>
					               <li class="nav-item-link">
					                  <a class="nav-link clickable-link" href="fuel-filters">Fuel Filters</a>
					               </li>
					             </ul>
			                       <ul>
			                         <li class="nav-item-link">
			                            <a class="nav-link clickable-link" href="transmission-filters">Transmission Filters</a>
			                         </li>
			                       </ul>
							</li>
							 <li class="nav-item-link">
					            <a class="nav-link clickable-link" href="wiper-blades">Wiper Blades</a>
					         </li>
					         <li class="nav-item-link">
					            <a class="nav-link clickable-link" href="disposable-gloves">Disposable Gloves</a>
					         </li>
					         <li class="nav-item-link">
					            <a class="nav-link clickable-link" href="hose-clamps">Hose Clamps</a>
					         </li>
					</ul>
				</li>
				
				
				<li class="nav-item">
             		<li class="nav-item-link" data-toggle="collapse" data-target="#resources" aria-controls="navbarContent" aria-label="Toggle navigation">
                		<a class="nav-link">Resources <i class="arrow right"></i> </a>
                	</li>
            	<ul id="resources" class="collapse">
							 <li class="nav-item-link">
					            <a class="nav-link clickable-link" href="technical-bulletins">Technical Bulletins</a>
					      </li>
					      <li class="nav-item-link">
					            <a class="nav-link clickable-link" href="videos">Videos</a>
					      </li>
					      <li class="nav-item-link">
					            <a class="nav-link clickable-link" href="blogs">Blogs</a>
					      </li>
                     <li class="nav-item-link">
					            <a class="nav-link clickable-link" href="tech-tips">Tech Tips</a>
					      </li>
                     <li class="nav-item-link">
					            <a class="nav-link clickable-link" href="https://www.pgfilters.com/wp-content/uploads/PG_WarrantyCertificate.pdf">Warranty</a>
					      </li>
					</ul>
				</li>
				
				
				
		   </ul>
		         
        <ul>
         <li class="nav-item">
            <a class="nav-link clickable-link" href="about">About</a>
         </li>
         </ul>
         <ul class="ul_padding_bottom">
         <li class="nav-item">
            <a class="nav-link clickable-link" href="contact">Contact Us</a>
         </li>
      </ul>
       
      <div class="mynavbar1"></div>
</div>
</header>
  <div ng-view></div>

   <script type="text/javascript" src="//code.jquery.com/jquery-1.11.0.min.js"></script>
   <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular.min.js">
   </script>
   <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular-route.min.js"></script> 
   <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
   <script defer src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
   <script defer type="text/javascript" src="<?php echo config('constants.public_url');?>js/html5-qrcode.min.js" ></script>
   <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/mainApp.js?<?php echo time();?>" ></script>
   <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/factorycreate.js" ></script>
   <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/yearController.js?<?php echo time();?>"></script>
   <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/makeController.js?<?php echo time();?>"></script>
   <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/modelController.js?<?php echo time();?>"></script>
   <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/engineController.js?<?php echo time();?>"></script>
   <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/qualifierController.js?<?php echo time();?>"></script>
   <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/productListController.js?<?php echo time();?>"></script>
   <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/productListController1.js?<?php echo time();?>"></script>
   <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/productPageController.js?<?php echo time();?>"></script>
   <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/applicationsController.js?<?php echo time();?>"></script>
    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/competitorsController.js?<?php echo time();?>"></script>
    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/staticpagesController.js?<?php echo time();?>"></script>
    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/dyanamicSearch.js?<?php echo time();?>"></script>
    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/contactController.js"></script>
    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/vehicletypeController.js?<?php echo time();?>"></script>
    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/techtipsdetailsController.js?<?php echo time();?>"></script>
    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/blogdetailsController.js?<?php echo time();?>"></script>
    <script defer type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/readMore.js?<?php echo time();?>"></script>
    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/vinSearch.js?<?php echo time();?>"></script>
    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/novinController.js?<?php echo time();?>"></script>
    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/partInterchange.js?<?php echo time();?>"></script>
    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/vinLoadCamera.js?<?php echo time();?>"></script>
    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/productCompController.js?<?php echo time();?>"></script>
    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/lateModelController.js?<?php echo time();?>"></script>
    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/lateModelHeroController.js?<?php echo time();?>"></script>

    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/voice/volume-meter.js?<?php echo time();?>"></script>
    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/voice/audioDetectionConfig.js?<?php echo time();?>"></script>
    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/voice/audioDetection.js?<?php echo time();?>"></script>
    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/voice/audioStream.js?<?php echo time();?>"></script>
    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/voice/demoAudioRecorder.js?<?php echo time();?>"></script>
    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/voice/demoAudioDetectionListeners.js?<?php echo time();?>"></script>

 
 <!--    <script type="text/javascript" src="<?php echo config('constants.public_url');?>js/controllers/premiumContoller.js"></script> -->
   <!-- 
   <script type="text/javascript" src="public/assets/js/noteController.js"/></script>
   <script type="text/javascript" src="public/assets/js/filterController.js"></script>
   <script type="text/javascript" src="public/assets/js/applicationsController.js"></script>
   <script type="text/javascript" src="public/assets/js/competitorsController.js"></script> -->
</body>
</html>
