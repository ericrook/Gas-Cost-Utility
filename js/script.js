$(document).ready(function() {
	var min = 1990,
	years = "",
	max = new Date().getFullYear()+2;
	//alert(max);
	
	for (var i = max; i>=min; i--){
		years += "<li><a href='#' class='car_year' rel='"+ i +"'>"+ i +"</a></li>";
	}
	$("#years").html(years);	
	
	$(document).keypress(function(event) {
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if (keycode == 120) {
			closeResponseContainer();
    	}
	});
	
	$('.car_year').on('click',function(e) {
		e.preventDefault();
		$('#select_year').text($(this).text());
		$('#select_year').attr('rel', $(this).attr('rel'));
		
		$('#select_make').text('Select Make');
		$('#car_make').empty();
		
		$('#select_model').text('Select Model');
		$('#car_model').empty();
		
		if($('#submit').hasClass('active')){
			$('#submit').toggleClass('active');	
			$('#submit').text('Make Selections Above');
		}
		
		$.ajax({
			method: 'post',
			dataType: 'html',
			
			data: 'year=' + $(this).text(),
			url: 'get_makes.php',
			success: function(data) {
				
				$('#car_make').empty();
				$('#car_make').append(data);
				carMake();
			},
			error: function(data) {
				console.log('error');
			}
		});
	});
	$('.gas_left').on('click',function(e) {
		e.preventDefault();
		$('#gas_left').text($(this).text());
		$('#gas_left').attr('rel', $(this).attr('rel'));
	});
	$('.close,#responsebg').on('click',function(e) {
		e.preventDefault();
		closeResponseContainer();
	});
	
});
function closeResponseContainer(){
	$('#responseContainer').delay(500).stop().fadeOut(300);
	$('#responsebg').delay(500).stop().fadeOut(100);
}

/**************************/
/******Make Selection******/
/**************************/
function carMake(){
	$('.car_make').on('click',function(e) {
		e.preventDefault();
		$('#select_make').text($(this).text());
		$('#select_make').attr('rel', $(this).attr('rel'));
		
		$('#select_model').text('Select Model');
		$('#car_model').empty();
		
		if($('#submit').hasClass('active')){
			$('#submit').toggleClass('active');	
			$('#submit').text('Make Selections Above');
		}
		
		$.ajax({
			method: 'post',
			dataType: 'html',
			
			data: 'year=' + $('#select_year').text() + '&make=' + $(this).attr('rel'),
			url: 'get_models.php',
			success: function(data) {
				$('#car_model').empty();
				$('#car_model').append(data);
				carModel();
			},
			error: function(data) {
				console.log('error');
			}
		});
	});
}

/***************************/
/******Model Selection******/
/***************************/
function carModel(){
	$('.car_model').on('click',function(e) {
		e.preventDefault();
		$('#select_model').text($(this).text());
		$('#select_model').attr('rel', $(this).attr('rel'));

		$('#submit').text('It\'s Go Time');
		
		if($('#submit').hasClass('active')){
			//Do nothing
		}
		else{
			$('#submit').toggleClass('active');	
		}
		submitFunction();
	});
}

/***********************/
/******Submit Form******/
/***********************/
function submitFunction(){
	$('#submit').on('click',function(e) {
		e.preventDefault();
		$('#response').html('<img src="img/loading.gif" />');
		$('html, body').animate({scrollTop: '0px'}, 0);
		$('#responsebg').delay(500).stop().fadeIn(100);
		$('#responseContainer').delay(500).stop().fadeIn(300);
		
		$.ajax({
			method: 'post',
			dataType: 'html',
			data: 'year=' + $('#select_year').attr('rel') + '&make=' + $('#select_make').attr('rel') + '&name=' + $('#select_model').attr('rel') + '&gas=' + $('#gas_left').attr('rel') + '&origin=' + $('#origin').val() + '&destination=' + $('#destination').val() + '&fromLat=' + $('input#fromLat').val() + '&fromLon=' + $('input#fromLon').val(),
			url: 'get_results.php',
			success: function(data) {
				$('#response').empty();
				$('#response').append(data);
				
			},
			error: function(data) {
				console.log('error');
			}
		});
	});
}

/***************************/
/******Dropdown Script******/
/***************************/
function DropDown(el) {
	this.dd = el;
	this.initEvents();
}

DropDown.prototype = {
	initEvents : function() {
		var obj = this;
		obj.dd.on('click', function(event){
			$(this).toggleClass('active');
			event.stopPropagation();
		});	
	}
}

$(function() {
	var dd = new DropDown( $('#dd1,#dd2,#dd3,#dd4') );
	$(document).click(function() {
		$('.wrapper-dropdown-5').removeClass('active');
	});
});

/***************************/
/******Get Geolocation******/
/***************************/
navigator.geolocation.getCurrentPosition(showPosition, errorMan);

function showPosition(location){	
	var lat = location.coords.latitude;
	var lon = location.coords.longitude;
	$('.fromAddress').hide(400);
	$('.fromAddress').html('<input type="hidden" name="fromAddress" id="origin" value="'+ lat + ',' + lon + '"/>');
	$('.fromLat').html('<input type="hidden" name="fromLat" id="fromLat" value="'+ lat + '"/>');
	$('.fromLon').html('<input type="hidden" name="fromLon" id="fromLon" value="'+ lon + '"/>');
}
 
function errorMan(){
	//Do nothing in error cases
}
 
function getCurrentPosition(){
	if(navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(showPosition, errorMan);
	}
	else{
		alert('Your browser does not support the Geo-Location feature');
	}
}	