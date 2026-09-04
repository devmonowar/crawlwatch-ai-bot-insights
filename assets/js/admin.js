/* CrawlWatch admin JS. Vanilla JS only. Currently progressive enhancement only; filters are plain links. */
(function () {
	'use strict';
	document.addEventListener( 'DOMContentLoaded', function () {
		var wrap = document.querySelector( '.crawlwatch' );
		if ( ! wrap ) {
			return;
		}
		wrap.addEventListener( 'click', function ( e ) {
			var btn = e.target.closest( '[data-crawlwatch-copy]' );
			if ( ! btn ) {
				return;
			}
			e.preventDefault();
			var text = btn.getAttribute( 'data-crawlwatch-copy' );
			if ( navigator.clipboard && text ) {
				navigator.clipboard.writeText( text );
				btn.textContent = ( window.crawlwatchAdmin && window.crawlwatchAdmin.copiedLabel ) || 'Copied!';
			}
		} );
	});
})();
