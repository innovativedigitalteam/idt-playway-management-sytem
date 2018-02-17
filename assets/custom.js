var templateUrl = path.pluginUrl;

jQuery(document).ready(function() {
    var myItems;

    jQuery.getJSON(templateUrl+'assets/country-list/countries.json', function(data) {
        var countryHTML = '<option>Select Country</option>';
        jQuery.each( data.countries, function(i, val ) {
		
		 countryHTML += '<option value="'+val.name+'" data-id="'+val.id+'">'+val.name+'</option>';
          
		 
		});
         jQuery('#country-list').html(countryHTML);
    });
    jQuery(document).on('change',  '#country-list', function() {
	 	var country_id = jQuery("#country-list option:selected").attr('data-id') ;
	 	var stateHTML = '<option>Select State</option>';
	 	jQuery.getJSON(templateUrl+'assets/country-list/states.json', function(states) {
      
        //console.log(states);
        jQuery.each( states.states, function(key, state ) {
		if (state.country_id == country_id) {
			stateHTML += '<option value="'+state.name+'" data-id="'+state.id+'">'+state.name+'</option>';
		}
		  
          
		 
		 });
         jQuery('#province-list').html(stateHTML);
    });
	});
});