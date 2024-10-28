
jQuery(document).ready(function($) {
	
	
	jQuery('#byt-widget-field-container-city input.widefat').autoComplete({
		minChars: 3,
		delay: 500,
		source: function(term, response){
			jQuery.ajax({
				type: 'GET',
				dataType: 'json',
				url: ajaxurl,
				data: 'action=byt_ajax_city_search&q='+term,
				success: function(data) {
					//console.log(data);
					response(data);
				}
			});
		},
		renderItem: function (item, search){
			return '<div class="autocomplete-suggestion" data-cityname="'+item[1]+'" data-widgeturl="'+item[5]+'" data-locationurl="'+item[4]+'" data-val="'+item[1]+'">'+item[1]+", "+item[2]+", "+item[3]+'</div>';
		},
		onSelect: function(e, term, item){
			jQuery(".byt-widget-widgeturl").val(item.data('widgeturl'));
			jQuery(".byt-widget-locationurl").val(item.data('locationurl'));
		}
	});
	
	jQuery('#byt-widget-field-container-country input.widefat').autoComplete({
		minChars: 3,
		delay: 300,
		source: function(term, response){
			jQuery.ajax({
				type: 'GET',
				dataType: 'json',
				url: ajaxurl,
				data: 'action=byt_ajax_country_search&q='+term,
				success: function(data) {
					//console.log(data);
					response(data);
				}
			});
		},
		renderItem: function (item, search){
			return '<div class="autocomplete-suggestion" data-countryname="'+item[1]+'" data-widgeturl="'+item[3]+'" data-locationurl="'+item[2]+'" data-val="'+item[1]+'">'+item[1]+'</div>';
		},
		onSelect: function(e, term, item){
			jQuery(".byt-widget-widgeturl").val(item.data('widgeturl'));
			jQuery(".byt-widget-locationurl").val(item.data('locationurl'));
		}
	});
	
	
	
});