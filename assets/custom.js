var templateUrl = path.pluginUrl;

jQuery(document).ready(function() {
	var country = jQuery("#country").val() ;
    var country_state = jQuery("#state").val() ;
    var city = jQuery("#city").val() ;
    // get country list
    jQuery.getJSON(templateUrl+'assets/country-list/countries.json', function(data) {
        var countryHTML = '<option>Select Country</option>';

        jQuery.each( data.countries, function(i, val ) {
			if (country != '' && val.id  == country ) {
				var selected = 'selected';
			}
		 countryHTML += '<option value="'+val.name+'" data-id="'+val.id+'" '+selected+'>'+val.name+'</option>';
          
		 
		});
         jQuery('#country-list').html(countryHTML);
    });
     // get state list
    jQuery(document).on('change',  '#country-list', function() {
	 	var country_id = jQuery("#country-list option:selected").attr('data-id') ;
	 	jQuery("#country").val(country_id) ;
	 	var stateHTML = '<option>Select State</option>';
	 	jQuery.getJSON(templateUrl+'assets/country-list/states.json', function(states) {
      	
       
			jQuery.each( states.states, function(key, state ) {
        	
			if (state.country_id == country_id) {
				stateHTML += '<option value="'+state.name+'" data-province="'+state.id+'" >'+state.name+'</option>';

			}
		  
		});
		
      
        
         jQuery('#province-list').html(stateHTML);
    });
	});

    // get cities list

    jQuery(document).on('change',  '#province-list', function() {
    	var province_id = jQuery("#province-list option:selected").attr('data-province') ;
    	jQuery("#state").val(province_id) ;
    	var cityHTML = '<option>Select City</option>';

    	jQuery.getJSON(templateUrl+'assets/country-list/cities.json', function(cities) {
    		
		 	jQuery.each( cities.cities, function(key, city ) {
			
				if (city.state_id  == province_id ) {
				cityHTML += '<option value="'+city.name+'" data-city="'+city.id+'">'+city.name+'</option>';
				}
				
			
			   
			});
		 	jQuery('#cities-list').html(cityHTML);
    	});
    });

    jQuery(document).on('change',  '#cities-list', function() {

    	var city_id = jQuery("#cities-list option:selected").attr('data-city') ;
    	jQuery("#city").val(city_id) ;
    });



    jQuery.getJSON(templateUrl+'assets/country-list/states.json', function(states) {
    	if (country_state != ''  ) {
			var stateHTML = '<option>Select Province</option>';
						
			jQuery.each( states.states, function(key, state ) {
				if (state.country_id == country ) {
					if (state.id  == country_state) {	
						var selected ='selected';
					}	
					stateHTML += '<option value="'+state.name+'" data-province="'+state.id+'" '+selected+'>'+state.name+'</option>';

				}
			
			});	
			jQuery('#province-list').html(stateHTML);
		}
	});	

    jQuery.getJSON(templateUrl+'assets/country-list/cities.json', function(cities) {
    	if (city != ''  ) {
			var cityHTML = '<option>Select City</option>';
					
			jQuery.each( cities.cities, function(key, city_data ) {
				if ((city_data.state_id == country_state)) {
					if ( (city_data.id  == city) ) {	
						var selected ='selected';
					}	
					cityHTML += '<option value="'+city_data.name+'" data-city="'+city_data.id+'" '+selected+'>'+city_data.name+'</option>';

				}
		
			});	
			jQuery('#cities-list').html(cityHTML);
		}
	});	

});