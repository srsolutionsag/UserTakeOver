var uto = function () {

	var
		css     = {
			engaged: 'engaged'
		},
		actions = {
			init: function (entry_id) {
				let search_field = $(entry_id).find("input[data-role='uto-search-input-field']");
				let url = search_field.data('action');

				search_field.tinyAutocomplete({
					wrapClasses:   'uto-search-field',
					closeOnSelect: false,
					url:           url,
					method:        'get',
					minChars:      3,
					keyboardDelay: 400,
					itemTemplate:  '<a style="" class="il-link link-bulky" href="goto.php?track=1&target=usr_takeover_{{usr_id}}">\n' +
									   '<span class="bulky-label">{{firstname}} {{lastname}} ({{login}})</span>\n' +
									   '</a>',

					showNoResults:     true,
					noResultsTemplate: '<a style="" class="il-link link-bulky" href="#">\n' +
										   '<span class="bulky-label">...</span>\n' +
										   '</a>',
				}).on("receivedata", function (ev, tinyAutocomplete, json) {
					tinyAutocomplete.el.unbind();

					tinyAutocomplete.el.on("keyup", ".autocomplete-field", $.proxy(tinyAutocomplete.onKeyUp, tinyAutocomplete));
					tinyAutocomplete.el.on(
						"keydown",
						".autocomplete-field",
						$.proxy(tinyAutocomplete.onKeyDown, tinyAutocomplete)
					);
					tinyAutocomplete.el.on(
						"mousedown",
						".autocomplete-item",
						$.proxy(tinyAutocomplete.onClickItem, tinyAutocomplete)
					);


					// tinyAutocomplete.el.find('.autocomplete-field').unbind();
					$("html").one("click", function () {
						search_field.val('');
					});
				});
			}
		};

	return {
		init: actions.init,
	};
};

export default uto;
