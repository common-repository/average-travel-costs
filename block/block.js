

(function (blocks, editor, components, i18n, element) {
	var el = wp.element.createElement;
  	var registerBlockType = wp.blocks.registerBlockType;
  	var RichText = wp.editor.RichText;
  	var BlockControls = wp.editor.BlockControls;
  	var AlignmentToolbar = wp.editor.AlignmentToolbar;
  	var MediaUpload = wp.editor.MediaUpload;
  	var InspectorControls = wp.editor.InspectorControls;
  	var TextControl = components.TextControl;
	var SelectControl = components.SelectControl;
	var CheckboxControl = components.CheckboxControl;
	var apiFetch = wp;
	
	var selectionTimerId = 0;

	
	// load the list of countries
	var allCountries = [
		{ label: 'Select a country', value: '', dataCountrycode: '', dataCountryname: '', dataCountryurl: '' }
	];
	
	var countries_source_url = ajaxurl+"?action=byt_ajax_country_list";
	const response = fetch( countries_source_url , {
		cache: 'no-cache',
		headers: {
			'user-agent': 'WP Block',
			'content-type': 'application/json'
		  },
		method: 'GET',
		redirect: 'follow', 
		referrer: 'no-referrer', 
	})
	.then((resp) => resp.json())
	.then(function(data){
		for(var i = 0; i < data.length; i++)
		{
			allCountries.push({label:data[i][1], value: data[i][0], dataCountrycode: data[i][0], dataCountryname: data[i][1], dataCountryurl: data[i][2]});
		}
	});


	/*
	register the country travel costs block
	*/
	registerBlockType(
		'budgetyourtrip/average-travel-costs-country', 
		{
			title: i18n.__( 'Average Travel Costs (Country)' ), 
			description: i18n.__('Displays the average daily travel costs for the specified country from BudgetYourTrip.com.'),
			icon: { src: 'location-alt', foreground: '#0066ff' }, // Block icon from Dashicons. https://developer.wordpress.org/resource/dashicons/
			category: 'budget-travel',
			keywords: [ i18n.__( 'travel' ), i18n.__( 'prices' ), i18n.__( 'budget' ) ],
			anchor: true,
			html: false, // a user could easily break the html tags, making the widget non-functional
			attributes: {
				countrycode: {
					type: 'text',
					default: '',
				},
				countryname: {
					type: 'text',
					default: '',
				},
				countryurl: {
					type: 'text',
					default: '',
				},
				hidecategories: {
					type: 'boolean',
					default: false,
				},
				defaultcurrency: {
					type: 'text',
					default: '',
				}
			},

			// Defines the block within the editor.
			
			edit: function( props ) {
				var attributes = props.attributes;
				var countrycode = props.attributes.countrycode;
				var countryname = props.attributes.countryname;
				var countryurl = props.attributes.countryurl;
				var hidecategories = props.attributes.hidecategories;
				var defaultcurrency = props.attributes.defaultcurrency;

				function onChangeCountry( newCountry ) {
					var selectedCountryObj = allCountries.find( c => c.value == newCountry );
					var newCountryname = selectedCountryObj.dataCountryname;
					var newCountryurl = selectedCountryObj.dataCountryurl;
					props.setAttributes({
						countrycode: newCountry,
						countryname: newCountryname,
						countryurl: newCountryurl
					});
				}
				
				function onChangeHideCategories( newValue ) {
					props.setAttributes( { hidecategories: newValue } );
				}
				
				function onChangeDefaultCurrency( newValue ) {
					props.setAttributes( { defaultcurrency: newValue } );
				}

				return[
					
					
					
					el(components.BaseControl, {
							label: i18n.__('Country Selection'),
							id: 'byt-country-selection-list',
							help: i18n.__('Select a country for the display of the average travel cost widget.'),
							
						},
						
					
						// Country field option.
						el(SelectControl, {
							id: 'byt-country-selection-list',
							//label: i18n.__('Country'),
							value: props.attributes.countrycode,
							onChange: onChangeCountry,
							options: allCountries
							
						})
					),
					
					
					
					el(components.BaseControl, {
							label: i18n.__('Default Currency'),
							id: 'byt-country-default-currency',
							help: i18n.__('The default currency to display in the widget.'),
							
						},
						
					
						// Currency field option.
						el(SelectControl, {
							id: 'byt-country-default-currency',
							value: props.attributes.defaultcurrency,
							onChange: onChangeDefaultCurrency,
							options: [
								{ label: 'Local Currency', value: '' },
								{ label: 'Dollar (United States)', value: 'USD' },
								{ label: 'Euro', value: 'EUR' },
								{ label: 'Pound Sterling (UK)', value: 'GBP' },
								{ label: 'Dollar (Australia)', value: 'AUS' },
							]
							
						})
					),
					
					el(CheckboxControl, {
						label: i18n.__('Hide Categories'),
						id: 'byt-country-hide-categories',
						onChange: onChangeHideCategories,
						checked: props.attributes.hidecategories
					}),
					
					
					el('p', {className: props.className}, ((attributes.countryname &&
						el('div', {},
							el('b', {}, attributes.countryname ),
							el('div', { className: 'preview-image'}, ''),
							el('p', {}, i18n.__('The travel cost widget will appear here on the published post.'))
						)
					) || i18n.__('A country has not been selected.') )),
				
				];
			},


			// Defines the saved block.
			save: function( props ) {
				var attributes = props.attributes;
				var countrycode = props.attributes.countrycode;
				var countryname = props.attributes.countryname;
				var countryurl = props.attributes.countryurl;
				var hidecategories = props.attributes.hidecategories;
				var defaultcurrency = props.attributes.defaultcurrency;
				
				if(attributes.countrycode)
				{
					var hidecatsparam = "";
					if(hidecategories)
					{
						hidecatsparam = "&hidecategories=1";
					}
					var defaultcurparam = "";
					if(defaultcurrency != "")
					{
						defaultcurparam = "&defaultcurrency=" + defaultcurrency;
					}
					return(
						el('div', { className: props.className },
							el('div', { className: 'budgetyourtrip-average-travel-costs-country' },
								
								el('script', { async: true, src: 'https://widget.budgetyourtrip.com/location-widget-js/'+attributes.countrycode+hidecatsparam+defaultcurparam, type: 'text/javascript'}, ''),
								el('a', { href: attributes.countryurl, target: '_blank', rel: 'noopener', className: 'budgetyourtrip-logo-pushdown'}, attributes.countryname + ' Travel Costs')
								 
							)
						)
					);
				}
				else
				{
					return el('div', {}, i18n.__('Select a country to display.') )
				}
			},
		}
	);
	
	/*
	register the city travel costs block
	*/
	registerBlockType(
		'budgetyourtrip/average-travel-costs-city', 
		{
			title: i18n.__( 'Average Travel Costs (City)' ), 
			description: i18n.__('Displays the average daily travel costs for the specified city from BudgetYourTrip.com.'),
			icon: { src: 'location-alt', foreground: '#0066ff' }, // Block icon from Dashicons. https://developer.wordpress.org/resource/dashicons/
			category: 'budget-travel',
			keywords: [ i18n.__( 'travel' ), i18n.__( 'prices' ), i18n.__( 'budget' ) ],
			anchor: true,
			html: false, // a user could easily break the html tags, making the widget non-functional
			attributes: {
				geonameid: {
					type: 'text',
					default: '',
				},
				cityname: {
					type: 'text',
					default: '',
				},
				cityurl: {
					type: 'text',
					default: '',
				},
				citysearch: {
					type: 'text',
					default: '',
				},
				hidecategories: {
					type: 'boolean',
					default: false,
				},
				defaultcurrency: {
					type: 'text',
					default: '',
				}
			},

			// Defines the block within the editor.
			
			edit: function( props ) {
				var attributes = props.attributes;
				var geonameid = props.attributes.geonameid;
				var cityname = props.attributes.cityname;
				var cityurl = props.attributes.cityurl;
				var citysearch = props.attributes.citysearch;
				var hidecategories = props.attributes.hidecategories;
				var defaultcurrency = props.attributes.defaultcurrency;
				
				var dynamicList = el('div', { id: 'budgetyourtrip-selection-list-'+props.clientId, className: 'budgetyourtrip-selection-list'}, el('div', { className: 'empty-placeholder' }, i18n.__('Search results will appear here.') ));

				
				function onClickCitySelect(newValue)
				{
					//console.log(newValue.target);
					var c = el(newValue.target);
					//console.log(c);
					var geonameid = c.type.attributes.dataGeonameid.value;
					var cityname = c.type.attributes.dataCityname.value;
					var cityurl = c.type.attributes.dataCityurl.value;
					
					props.setAttributes({
						geonameid: geonameid,
						cityname: cityname,
						cityurl: cityurl,
						citysearch: cityname
					});
					
					var foundCities = el('div',{},'');
					wp.element.render(foundCities, document.getElementById('budgetyourtrip-selection-list-'+props.clientId));
					
				}
				
				function onChangeHideCategories( newValue ) {
					props.setAttributes( { hidecategories: newValue } );
				}
				
				function onChangeDefaultCurrency( newValue ) {
					props.setAttributes( { defaultcurrency: newValue } );
				}
				
				
				
				function doOnChangeCityTextField(newValue)
				{
					if(newValue.length >= 3)
					{
						var foundCitiesLoading = el('div', { className: 'empty-placeholder' }, i18n.__('Loading...') );
						wp.element.render(foundCitiesLoading, document.getElementById('budgetyourtrip-selection-list-'+props.clientId));
						var cities_source_url = ajaxurl+"?action=byt_ajax_city_search&q="+newValue;
						//console.log(cities_source_url);
						const response = fetch( cities_source_url , {
							cache: 'no-cache',
							headers: {
								'user-agent': 'WP Block',
								'content-type': 'application/json'
							  },
							method: 'GET',
							redirect: 'follow', 
							referrer: 'no-referrer', 
						})
						.then((resp) => resp.json())
						.then(function(data)
						{
							
							if(data)
							{
								var foundCities = [];
								for(var i = 0; i < data.length; i++)
								{
									foundCities.push(el('div', {className: 'city-search-result' },
										el('a', {className: 'city-name', dataGeonameid: data[i][0], dataCityname: data[i][1], dataCityurl: data[i][4], onClick: onClickCitySelect}, data[i][1]),
										el('div', {className: 'state-name'}, data[i][2]),
										el('div', {className: 'country-name'}, data[i][3]),
									));
									
									
								}
								//console.log(foundCities);
								wp.element.render(foundCities, document.getElementById('budgetyourtrip-selection-list-'+props.clientId));
							}
							else
							{
								var foundCities = el('div', { className: 'empty-placeholder' }, i18n.__('Nothing found, please try again.') );
								wp.element.render(foundCities, document.getElementById('budgetyourtrip-selection-list-'+props.clientid));
							}
							
						});
					}
					else
					{
						var foundCities = el('div', { className: 'empty-placeholder' }, i18n.__('Search results will appear here.') );
						wp.element.render(foundCities, document.getElementById('budgetyourtrip-selection-list-'+props.clientId));
					}
					
				}
				
				function onChangeCityTextField(newValue)
				{
					props.setAttributes({
						citysearch: newValue
					});
					clearTimeout(selectionTimerId);
					selectionTimerId = setTimeout ( doOnChangeCityTextField, 500, newValue ); // in milliseconds
				}

				return[
					
					
					
					el(components.BaseControl, {
							label: i18n.__('City Selection'),
							id: 'byt-city-selection-list',
							help: i18n.__('Search for a place by typing 3 or more letters. Then select the location from the list below.'),
							
						},
						
					
						// City field option.
						el(TextControl, {
							id: 'byt-city-selection-list',
							type: 'text',
							value: props.attributes.citysearch,
							onChange: onChangeCityTextField,

							
						})
					),
					
					dynamicList,
					
					el(components.BaseControl, {
							label: i18n.__('Default Currency'),
							id: 'byt-country-default-currency',
							help: i18n.__('The default currency to display in the widget.'),
							
						},
						
					
						// Currency field option.
						el(SelectControl, {
							id: 'byt-country-default-currency',
							value: props.attributes.defaultcurrency,
							onChange: onChangeDefaultCurrency,
							options: [
								{ label: 'Local Currency', value: '' },
								{ label: 'Dollar (United States)', value: 'USD' },
								{ label: 'Euro', value: 'EUR' },
								{ label: 'Pound Sterling (UK)', value: 'GBP' },
								{ label: 'Dollar (Australia)', value: 'AUS' },
							]
							
						})
					),
					
					el(CheckboxControl, {
						label: i18n.__('Hide Categories'),
						id: 'byt-country-hide-categories',
						onChange: onChangeHideCategories,
						checked: props.attributes.hidecategories
					}),
					
					
					el('p', {className: props.className}, ((attributes.cityname &&
						el('div', {},
							el('b', {}, attributes.cityname ),
							el('div', { className: 'preview-image'}, ''),
							el('p', {}, i18n.__('The travel cost widget will appear here on the published post.'))
						)
					) || i18n.__('A city has not been selected.') )),
				
				];
			},


			// Defines the saved block.
			save: function( props ) {
				var attributes = props.attributes;
				var geonameid = props.attributes.geonameid;
				var cityname = props.attributes.cityname;
				var cityurl = props.attributes.cityurl;
				var hidecategories = props.attributes.hidecategories;
				var defaultcurrency = props.attributes.defaultcurrency;
							
				if(attributes.geonameid)
				{
					var hidecatsparam = "";
					if(hidecategories)
					{
						hidecatsparam = "&hidecategories=1";
					}
					var defaultcurparam = "";
					if(defaultcurrency != "")
					{
						defaultcurparam = "&defaultcurrency=" + defaultcurrency;
					}
					return(
						el('div', { className: props.className },
							el('div', { className: 'budgetyourtrip-average-travel-costs-city' },
								
								el('script', { async: true, src: 'https://widget.budgetyourtrip.com/location-widget-js/'+attributes.geonameid+hidecatsparam+defaultcurparam, type: 'text/javascript'}, ''),
								el('a', { href: attributes.cityurl, target: '_blank', rel: 'noopener', className: 'budgetyourtrip-logo-pushdown'}, attributes.cityname + ' Travel Costs')
								 
							)
						)
					);
				}
				else
				{
					return el('p', {}, i18n.__('Select a city to display.') )
				}
			},
		}
	);
})(
  window.wp.blocks,
  window.wp.editor,
  window.wp.components,
  window.wp.i18n,
  window.wp.element
);


