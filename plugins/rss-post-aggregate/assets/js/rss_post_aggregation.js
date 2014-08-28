/**
 * RSS Post Aggregation - v0.1.0 - 2014-08-28
 * http://webdevstudios.com
 *
 * Copyright (c) 2014;
 * Licensed GPLv2+
 */
/*jslint browser: true */
/*global jQuery:false */

window.RSS_Post_Aggregation = (function(window, document, $, undefined){
	'use strict';

	function Selector_Cache() {
		var elementCache = {};

		var get_from_cache = function( selector, $ctxt, reset ) {
			if ( 'boolean' === typeof $ctxt ) {
				reset = $ctxt;
			}
			var cacheKey = $ctxt ? $ctxt.selector + ' ' + selector : selector;

			if ( undefined === elementCache[ cacheKey ] || reset ) {
				var $element = $ctxt ? $ctxt.find( selector ) : jQuery( selector );
				elementCache[ cacheKey ] = $element;
			}
			return elementCache[ cacheKey ];
		};
		return get_from_cache;
	}

	function log() {
		log.history = log.history || [];
		log.history.push( arguments );
		if ( window.console && l10n.debug ) {
			window.console.log( Array.prototype.slice.call(arguments) );
		}
	}

	var l10n = window.RSSPost_l10n;
	var app = {
		$ : new Selector_Cache()
	};

	app.init = function() {
		log( app );
	};

	$(document).ready( app.init );

	return app;

	/**
	 * Utilities
	 */

})(window, document, jQuery);
