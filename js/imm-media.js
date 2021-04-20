/**
 * Load media uploader on pages with our custom metabox
 * 
 * based on: https://gist.github.com/cferdinandi/86f6e326b30b8b5416c0a7e43271efa6
 * Author: Chris Ferdinandi, https://gist.github.com/cferdinandi
 */
jQuery(document).ready(function ($) {

	'use strict';

	// Instantiates the variable that holds the media library frame.
	var metaImageFrame;

	// Runs when the media button is clicked.
	$('body').click(function (e) {

		// Get the btn
		var btn = e.target;

		// Check if it's the upload button
		if (!btn || !$(btn).attr('data-media-uploader-target')) return;

		// Get the field target
		var field = $(btn).data('media-uploader-target');
		var images = $('#imm_project_media_images');

		// Prevents the default action from occuring.
		e.preventDefault();

		// Sets up the media library frame
		metaImageFrame = wp.media.frames.metaImageFrame = wp.media({
			title: 'Projektanhänge wählen',
			button: {text: 'Use this file'},
			multiple: true
		});

		// Runs when an image is selected.
		metaImageFrame.on('select', function () {

			// Grabs the attachment selection and creates a JSON representation of the model.
			// var media_attachment = metaImageFrame.state().get('selection').first().toJSON();
			var selection = metaImageFrame.state().get('selection');
			let imageHTML = '';
			const mediaIDs = [];
			selection.map(function (attachment) {
				attachment = attachment.toJSON();
				imageHTML += `<figure>
								<img id="${attachment.id}" src="${attachment.url}">
								<figcaption class="overlay">${attachment.title}</figcaption>
							  </figure>`;
				mediaIDs.push(attachment.id);
			});

			// Sends the attachment URL to our custom image input field.
			$(images).html(imageHTML);
			$(field).val(mediaIDs.join(','));

		});

		// Opens the media library frame.
		metaImageFrame.open();

	});

});
