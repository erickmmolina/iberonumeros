jQuery(document).ready(function($) {
    var $document = $(document);
    var countries = [];
    var $widget = $('.iberonumeros-widget');
    var $countryInput = $widget.find('.iberonumeros-country-input');
    var $countryHidden = $widget.find('input[name="country"]');
    var $dropdown = $widget.find('.iberonumeros-country-dropdown');
    var defaultCountry = typeof iberonumerosDefaultCountry !== 'undefined' ? iberonumerosDefaultCountry : 'US';

    // Mapeo de banderas y códigos telefónicos
    var iberonumerosCountryData = {
        'US': { flag: '🇺🇸', calling_code: '+1' },
        'CL': { flag: '🇨🇱', calling_code: '+56' },
        'ES': { flag: '🇪🇸', calling_code: '+34' },
        // Añade más si necesitas
    };

    // Cargar países al iniciar
    $.post(iberonumerosAjax.ajax_url, {
        action: 'iberonumeros_get_countries',
        nonce: iberonumerosAjax.nonce
    }, function(response) {
        if (response.success) {
            var countriesObj = response.data.countries; 
            // countriesObj es { 'US': 'United States', 'CL': 'Chile', ... }
            // Convertir en array para ordenar
            for (var code in countriesObj) {
                countries.push({ code: code, name: countriesObj[code] });
            }
            // Ordenar alfabéticamente por nombre
            countries.sort(function(a, b) {
                return a.name.localeCompare(b.name);
            });

            // Seleccionar país por defecto
            setSelectedCountry(defaultCountry);
            // Buscar números por defecto
            searchNumbers();

        } else {
            var $messages = $widget.find('.iberonumeros-messages');
            $messages.html('<div class="iberonumeros-message">Error fetching countries: '+ response.data.message +'</div>');
        }
    });

    function setSelectedCountry(code) {
        var country = countries.find(function(c) { return c.code === code; });
        if (!country) {
            // Si no existe el país (raro), elegir el primero
            country = countries[0];
        }
        $countryHidden.val(country.code);
        $countryInput.val(formatCountryDisplay(country));
    }

    function formatCountryDisplay(country) {
        var data = iberonumerosCountryData[country.code];
        var flag = data ? data.flag : '';
        var calling = data ? ' ('+data.calling_code+') ' : ' ';
        return (flag ? flag + calling : '') + country.name + ' - ' + country.code;
    }

    // Mostrar dropdown filtrado
    function showDropdown(filter) {
        var f = filter.toLowerCase();
        var html = '';
        countries.forEach(function(country) {
            if (country.name.toLowerCase().indexOf(f) > -1) {
                html += '<div class="iberonumeros-country-option" data-code="'+country.code+'">'+formatCountryDisplay(country)+'</div>';
            }
        });
        $dropdown.html(html).show();
    }

    $countryInput.on('focus click', function() {
        showDropdown($countryInput.val());
    });

    $countryInput.on('input', function() {
        showDropdown($countryInput.val());
    });

    // Seleccionar país desde el dropdown
    $document.on('click', '.iberonumeros-country-option', function() {
        var code = $(this).data('code');
        setSelectedCountry(code);
        $dropdown.hide();
        // Una vez seleccionado un país distinto, podemos disparar la búsqueda por defecto
        searchNumbers();
    });

    // Ocultar dropdown si se hace clic afuera
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.iberonumeros-country-dropdown-container').length) {
            $dropdown.hide();
        }
    });

    function searchNumbers() {
        var $form = $widget.find('.iberonumeros-search-form');
        var data = {
            action: 'iberonumeros_search_numbers',
            nonce: iberonumerosAjax.nonce,
            country: $form.find('input[name="country"]').val(),
            search_criteria: $form.find('input[name="search_criteria"]').val(),
            voice: $form.find('input[name="voice"]').is(':checked'),
            sms: $form.find('input[name="sms"]').is(':checked'),
            mms: $form.find('input[name="mms"]').is(':checked'),
            fax: $form.find('input[name="fax"]').is(':checked')
        };
    
        var $results = $widget.find('.iberonumeros-results');
        $results.html('<p>Searching <span class="iberonumeros-spinner"></span></p>');
    
        $.post(iberonumerosAjax.ajax_url, data, function(response) {
            if (response.success) {
                var numbers = response.data.numbers;
                var whatsappNumber = response.data.whatsapp_number; // Aquí obtienes el número desde la respuesta AJAX
                /*console.log('Número de WhatsApp:', whatsappNumber);*/
        
                if (numbers.length > 0) {
                    var html = '<table class="iberonumeros-table"><thead><tr>';
                    html += '<th>Number</th><th>Type</th><th>Capabilities</th><th>Address Requirement</th><th>Price (Monthly)</th><th></th>';
                    html += '</tr></thead><tbody>';
        
                    numbers.forEach(function(num) {
                        var caps = num.capabilities || {};
                        var capabilitiesHtml = '<div class="caps-grid">';
                        capabilitiesHtml += '<div class="cap-col">'+(caps.voice ? '✔️' : '❌')+'</div>';
                        capabilitiesHtml += '<div class="cap-col">'+(caps.sms ? '✔️' : '❌')+'</div>';
                        capabilitiesHtml += '<div class="cap-col">'+(caps.mms ? '✔️' : '❌')+'</div>';
                        capabilitiesHtml += '<div class="cap-col">'+(caps.fax ? '✔️' : '❌')+'</div>';
                        capabilitiesHtml += '</div>';
        
                        var addressReq = num.address_requirements ? num.address_requirements : 'None';
        
                        // Crear el enlace de WhatsApp dinámico usando la variable whatsappNumber obtenida
                        var message = encodeURIComponent('Hola, me interesa el número: ' + num.phone_number);
                        var whatsappLink = 'https://wa.me/' + whatsappNumber + '?text=' + message;
        
                        html += '<tr>';
                        html += '<td>'+ num.friendly_name +'</td>';
                        html += '<td>Local</td>'; // Cambiar dinámicamente si Twilio lo indica
                        html += '<td>'+ capabilitiesHtml +'</td>';
                        html += '<td>'+ addressReq +'</td>';
                        html += '<td>$'+ num.price.toFixed(2) +'</td>';
                        html += '<td><a href="'+ whatsappLink +'" class="iberonumeros-me-interesa-btn" target="_blank">Me interesa</a></td>';
                        html += '</tr>';
                    });
        
                    html += '</tbody></table>';
        
                    $results.html(html);
                } else {
                    $results.html('<p>No results found.</p>');
                }
            } else {
                $results.html('<div class="iberonumeros-message">Error: '+ response.data.message +'</div>');
            }
        });
        
        
    }
    

    // Botón Search manual
    $document.on('click', '.iberonumeros-search-btn', function(e) {
        e.preventDefault();
        searchNumbers();
    });

    // Comprar número
    $document.on('click', '.iberonumeros-buy-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var phone_number = $btn.data('number');

        var data = {
            action: 'iberonumeros_buy_number',
            nonce: iberonumerosAjax.nonce,
            phone_number: phone_number
        };

        $btn.text('Buying...');

        $.post(iberonumerosAjax.ajax_url, data, function(response) {
            if (response.success) {
                alert('Purchased successfully!');
                $btn.text('Bought');
                $btn.prop('disabled', true);
            } else {
                alert('Error: ' + response.data.message);
                $btn.text('Buy');
            }
        });
    });
});
