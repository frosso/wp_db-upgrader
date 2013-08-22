jQuery(function($) {
	var $info = $("#upgrader-content");
	$info.dialog({
		'dialogClass' : 'wp-dialog no-close alert',
		'modal' : true,
		'autoOpen' : true,
		'closeOnEscape' : false,
		'bgiframe' : true,
		'title' : 'Upgrade Plugin',
		'draggable' : false,
		'resizable' : false,
	});
	jQuery(".ui-dialog-titlebar-close").hide();
	jQuery("#upgrader-content").upgrader('run');
});

/**
 * Upgrader script
 */
(function($) {

	var methods = {

		/**
		 * Run all steps
		 *
		 */
		'run' : function() {
			return this.each(function() {
				var wrapper = jQuery(this);

				var next_action = function() {
					var next_action_item = wrapper.find('ul#upgrader_actions_list li.not_done:first');

					if (next_action_item.length == 1) {
						next_action_item.removeClass('not_done').addClass('doing');

						var original_text = next_action_item.text();

						var counter = 0;
						var interval = setInterval(function() {
							counter++;

							if (counter <= 3) {
								next_action_item.text(next_action_item.text() + '.');
							} else {
								next_action_item.text(original_text);
								counter = 0;
							} // if
						}, 500);

						jQuery.ajax({
							'url' : upgrader_js_object.ajax_url,
							'type' : 'post',
							'data' : {
								action : 'upgrader-next',
								next_group : next_action_item.attr('upgrade_group'),
								next_action : next_action_item.attr('upgrade_action'),
								submitted : 'submitted',
							},
							'success' : function(response) {
								clearInterval(interval);
								next_action_item.text(original_text).removeClass('doing').addClass('done').addClass('ok');

								next_action();
							},
							'error' : function(response) {
								console.log(response);
								clearInterval(interval);
								next_action_item.text(original_text + ' (' + response.responseText + ')').removeClass('doing').addClass('done').addClass('error');
							},
						});
					} else {
						wrapper.find('ul#upgrader_actions_list').after('<p>' + upgrader_translation.all_done + '</p>');
						jQuery(".ui-dialog-titlebar-close").show();
					} // if
				};

				next_action();
			});
		},
	};

	// Definition and dispatcher
	jQuery.fn.upgrader = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if ( typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			throw 'Method ' + method + ' does not exist on jQuery.upgrader';
		}
		// if

	};

})(jQuery);
