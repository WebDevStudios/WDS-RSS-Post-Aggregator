/**
 * RSS Post Aggregation - v0.1.0 - 2014-08-29
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

	var bb = window.Backbone;
	var l10n = window.RSSPost_l10n;
	var app = {
		Post  : {},
		Posts : {},
		Views : {
			Row    : {},
			Modal  : {} // prototype
		},
		PostsView : {}, // instantiated
		$ : new Selector_Cache(),
		feeds : l10n.feeds
	};

	/**
	 * Model
	 */
	app.Post = bb.Model.extend({
		defaults: {
			index   : 0,
			url     : '',
			img     : '',
			title   : '',
			urlhost : '',
			date    : ''
		},
		idAttribute: 'index'
	});

	/**
	 * Collection
	 */
	app.Posts = bb.Collection.extend({ model : app.Post });

	/**
	 * All posts view
	 */
	app.Views.Modal = bb.View.extend({

		events : {
			'click #find-posts-submit' : 'submit'
		},

		optionsTemplate  : wp.template( 'rssfeedoption' ),

		initialize: function() {
			this.$feedSelector = this.$( '#select-feed' );
			this.$response     = this.$( '#find-posts-response' );
			this.listenTo( this, 'render', this.render );
			this.render();
		},

		setupSelect: function() {
			// var fragment = document.createDocumentFragment();
			var self = this;
			var html = '';
			var feed_url = '';

			_.each( app.feeds, function( feed ) {
				feed_url = ! feed_url ? feed : feed_url;
				html += self.optionsTemplate( { 'url': feed } );
			});

			this.$feedSelector.html( html );
			this.fetchAndPopulate( feed_url );
			return this;
		},

		fetchAndPopulate: function( feed_url ) {
			log( 'feed_url', feed_url );
			var self = this;

			$.ajax({
				type     : 'post',
				dataType : 'json',
				url      : l10n.ajaxurl,
				data     : {
					'action'   : 'rss_get_data',
					'feed_url' : feed_url
				},
				success: function( response ) {
					// bb.trigger( 'closeModals' );

					if ( response.success ) {
						log( 'response.success', response );
						self.populateItems( response.data );
						// Send data back to editor
						// app.editor().trigger( 'updateFromSearch', response.data );
					} else {
						log( 'response', response );
					}

				}
			});

		},

		populateItems: function( items ) {
			this.collection = new app.Posts( items );
			this.optionsTemplate( { 'url': items } );
		},

		submit: function( evt ) {
			evt.preventDefault();
		},

		render: function() {
			this.setupSelect();

			var addedElements = document.createDocumentFragment();
			// render each row, appending to our root element
			/*this.collection.each( function( model ) {
				var row = new app.Views.Row({ model: model });
				addedElements.appendChild( row.render().el );
			});*/

			this.$response.html( addedElements );
		}
	});

	/**
	 * Single post view
	 */
	app.Views.Row = bb.View.extend({
		tagName: 'tr',
		template  : wp.template( 'rssitem' ),

		id: function() { return 'rss-item-'+ this.model.get( 'index' ); },

		className: function() {
			return 'found-posts '+ ( this.model.get( 'index' ) % 2 === 0 ? '' : 'alternate' );
		},

		attributes: function() {
			return {
				'title'    : this.model.get( 'url' ),
				'data-img' : this.model.get( 'img' )
			};
		},

		render: function() {
			this.$el.html( this.template( this.model.toJSON() ) );
			return this;
		}
	});

	app.init = function() {
		log( app );
		// app.$( '.add-new-h2' ).on( 'click', app.openModal );

		log( 'l10n.feeds', l10n.feeds );
		var feeds = _.toArray( l10n.feeds );

		// Uh-oh, something's wrong
		if ( ! feeds.length ) {
			log( 'no feeds!' );
			return;
		}


		app.PostsView = new app.Views.Modal({
			el: '#find-posts'
			// collection: new app.Posts()
		});

	};

	$(document).ready( app.init );

	return app;

	/**
	 * Utilities
	 */

})(window, document, jQuery);
