


app.service('clickservice', function() 
{
       $('.clickable-link').click(function(){
			     $('.clickable-link').removeClass("change-color");
			    $(this).addClass("change-color");
			    $('#mynavbar1').removeClass('in');

	   });
	   
	   
 });