(function ($) {
	'use strict';

	$(function () {
		var root = $('#xva-native-gallery');
		if (!root.length || !window.wp || !wp.media) {
			return;
		}

		var input = root.find('input[name="xva_exhibition_gallery_ids"]');
		var preview = root.find('#xva-native-gallery-preview');
		var frame = null;

		function ids() {
			var value = input.val() || '';
			return value.split(',').map(function (id) { return parseInt(id, 10); }).filter(function (id) { return id > 0; });
		}

		function render(attachments) {
			preview.empty();
			if (!attachments.length) {
				preview.append($('<li />', { 'class': 'xva-native-gallery__empty', text: 'No gallery images selected yet.' }));
				return;
			}
			attachments.forEach(function (attachment) {
				var data = attachment.toJSON ? attachment.toJSON() : attachment;
				var thumbnail = data.sizes && data.sizes.thumbnail ? data.sizes.thumbnail.url : data.url;
				var item = $('<li />', { 'class': 'xva-native-gallery__item' }).attr('data-id', data.id);
				$('<img />', { 'class': 'xva-native-gallery__image', src: thumbnail, alt: data.alt || '' }).appendTo(item);
				$('<button />', { 'class': 'button-link-delete xva-native-gallery__remove', type: 'button', text: 'Remove' }).attr('data-remove', data.id).appendTo(item);
				preview.append(item);
			});
		}

		root.on('click', '#xva-native-gallery-select', function (event) {
			event.preventDefault();
			if (!frame) {
				frame = wp.media({
					title: (window.xvaNativeGallery && window.xvaNativeGallery.title) || 'Select exhibition gallery images',
					button: { text: (window.xvaNativeGallery && window.xvaNativeGallery.button) || 'Use selected images' },
					library: { type: 'image' },
					multiple: true
				});
				frame.on('open', function () {
					var selection = frame.state().get('selection');
					ids().forEach(function (id) {
						var attachment = wp.media.attachment(id);
						attachment.fetch();
						selection.add(attachment);
					});
				});
				frame.on('select', function () {
					var selected = frame.state().get('selection').toArray();
					input.val(selected.map(function (attachment) { return attachment.id; }).join(','));
					render(selected);
				});
			}
			frame.open();
		});

		root.on('click', '#xva-native-gallery-clear', function (event) {
			event.preventDefault();
			input.val('');
			render([]);
		});

		root.on('click', '.xva-native-gallery__remove', function (event) {
			event.preventDefault();
			var removeId = parseInt($(this).attr('data-remove'), 10);
			input.val(ids().filter(function (id) { return id !== removeId; }).join(','));
			$(this).closest('.xva-native-gallery__item').remove();
			if (!preview.children().length) {
				render([]);
			}
		});
	});
}(jQuery));
