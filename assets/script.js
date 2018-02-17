jQuery(document).ready(function() {
    (function ($) {
        
        $(document).on("click", ".add_field_button", function(e){
            e.preventDefault();

            var wrapper = $(this).parent('.nappies-wrapper');
            if (wrapper.find('.nappies:last-child').length == 0) {
                var id = wrapper.find('input.nappies-id').val();
                var counter = 0;
            } else {
                var last_nappy = wrapper.find('.nappies:last-child').find('input.nappies-counter').val();
                var id = wrapper.find('input.nappies-id').val();
                var counter = Number(last_nappy)+1;
            }

            wrapper.append(`
                 <div class="nappies">
                        <input type="hidden" class="nappies-counter" value="` + counter + `">
                        <input type="hidden" class="nappies-id" value="` + id +`">
                        <input type="text" name="gnappies` + counter + `[` + id + `]" id="gnappies` + counter + `-` + id + `"></input><br />
                        <label for="nappies` + counter + `-` + id + `">Dry</label>
                        <input type="radio" name="nappies` + counter +  `[` + id +`]" id="nappies` + counter + `-` + id + `" value="dry"></input>
                        <label for="nappies` + counter + `-` + id + `">Wet</label>
                        <input type="radio" name="nappies` + counter + `[` + id + `]" id="nappies` + counter + `-` + id +  `" value="wet"></input>
                        <label for="nappies` + counter + `-` + id + `">Soiled</label>
                        <input type="radio" name="nappies` + counter + `[` + id +  `]" id="nappies` + counter + `-` + id + `" value="soiled"></input>
                        <br/>   
                    </div>
                `);
        });

        if ($('#yard_check').attr('checked') == 'checked') {
            $('.yardsam').removeClass('hidden');
            $('.add_yardam_button').removeClass('hidden');
        }

        if ($('#pm_yard').attr('checked') == 'checked') {
            $('.yardspm').removeClass('hidden');
            $('.add_yardpm_button').removeClass('hidden');
        }

        $(document).on("click", "#yard_check", function(e){
            var wrapper = $('.yardsam');
            var button = $('.add_yardam_button');
            
            if ($(this).attr('checked') == 'checked') {
                
                if (wrapper.children().length == 0) {
                    wrapper.append(`
                        <div class="yardam">
                            <input type="hidden" class="yardam-counter" value="0">
                            <b><label for="yardam0">Check 1: </label></b>
                            <input type="text" name="yardam0" id="yardam0" value="" ></input>
                        </div>
                        <div class="yardam">
                            <input type="hidden" class="yardam-counter" value="1">
                            <b><label for="yardam1">Check 2: </label></b>
                            <input type="text" name="yardam1" id="yardam1" value="" ></input>
                        </div>
                        <div class="yardam">
                            <input type="hidden" class="yardam-counter" value="2">
                            <b><label for="yardam2">Check 3: </label></b>
                            <input type="text" name="yardam2" id="yardam2" value="" ></input>
                        </div>
                    `
                    );
                }

                wrapper.removeClass('hidden');
                button.removeClass('hidden');

            } else {
                wrapper.addClass('hidden');
                button.addClass('hidden');
            }

        });

        $(document).on("click", ".add_yardam_button", function(e){
            e.preventDefault();

            var wrapper = $('.yardsam');

             if (wrapper.children().length == 0) {
                var counter = 0;
            } else {
                var last_am = wrapper.find('.yardam:last-child').find('input.yardam-counter').val();
                var counter = Number(last_am)+1;
            }

            wrapper.append(`
                 <div class="yardam">
                        <input type="hidden" class="yardam-counter" value="` + counter + `">
                        <b><label for="yardam` + counter +`">Check ` + String(Number(counter) + 1) +`: </label></b>
                        <input type="text" name="yardam` + counter + `" id="yardam` + counter + `" value="" ></input>
                    </div>
                `);
        });

        $(document).on("click", "#pm_yard", function(e){
            var wrapper = $('.yardspm');
            var button = $('.add_yardpm_button');
            
            if ($(this).attr('checked') == 'checked') {
                
                if (wrapper.children().length == 0) {
                    wrapper.append(`
                        <div class="yardpm">
                            <input type="hidden" class="yardpm-counter" value="0">
                            <b><label for="yardpm0">Check 1: </label></b>
                            <input type="text" name="yardpm0" id="yardpm0" value="" ></input>
                        </div>
                        <div class="yardpm">
                            <input type="hidden" class="yardpm-counter" value="1">
                            <b><label for="yardpm1">Check 2: </label></b>
                            <input type="text" name="yardpm1" id="yardpm1" value="" ></input>
                        </div>
                        <div class="yardpm">
                            <input type="hidden" class="yardpm-counter" value="2">
                            <b><label for="yardpm2">Check 3: </label></b>
                            <input type="text" name="yardpm2" id="yardpm2" value="" ></input>
                        </div>
                    `
                    );
                }

                wrapper.removeClass('hidden');
                button.removeClass('hidden');

            } else {
                wrapper.addClass('hidden');
                button.addClass('hidden');
            }

        });

        $(document).on("click", ".add_yardpm_button", function(e){
            e.preventDefault();

            var wrapper = $('.yardspm');

             if (wrapper.children().length == 0) {
                var counter = 0;
            } else {
                var last_pm = wrapper.find('.yardpm:last-child').find('input.yardpm-counter').val();
                var counter = Number(last_pm)+1;
            }

            wrapper.append(`
                 <div class="yardpm">
                        <input type="hidden" class="yardpm-counter" value="` + counter + `">
                        <b><label for="yardpm` + counter +`">Check ` + String(Number(counter) + 1) +`: </label></b>
                        <input type="text" name="yardpm` + counter + `" id="yardpm` + counter + `" value="" ></input>
                    </div>
                `);
        });


    })(jQuery);
});