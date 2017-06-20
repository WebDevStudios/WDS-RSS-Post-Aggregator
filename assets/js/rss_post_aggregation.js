/**
 * RSS Post Aggregator - v0.1.0 - 2017-06-20
 * http://webdevstudios.com
 *
 * Copyright (c) 2017;
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
			index    : 0,
			source   : '',
			link     : '',
			title    : '',
			date     : '',
			summary  : '',
			image    : '',
			author   : '',
			rss_link : ''
		},
		idAttribute: 'index'
	});

	/**
	 * Collection
	 */
	app.Posts = bb.Collection.extend({
		model : app.Post,
		search : function( letters ){
			if ( ! letters ) {
				return this;
			}

			var pattern = new RegExp( letters, 'gi' );
			return _( this.filter( function( model ) {
				return pattern.test( model.get( 'title' ) );
			} ) );
		},
		checked: function() {
			var filtered = this.filter(function(box) {
				return box.get( 'checked' ) === true;
			});
			return new app.Posts( filtered );
		}
	});

	/**
	 * All posts view
	 */
	app.Views.Modal = bb.View.extend({

		events: {
			'click #find-posts-submit'      : 'submit',
			'click #rss-save-feed'          : 'saveFeed',
			'keypress #rss-save-feed-input' : 'maybeSaveFeed',
			'click #find-posts-close'       : 'close',
			'change #select-feed'           : 'changeFeed',
			'keyup #find-posts-input'       : 'search'
		},

		selectorPopulated: false,
		timeout: false,
		feed_url: '',
		feed_id: '',

		initialize: function() {
			this.$feedInput    = this.$( '#rss-save-feed-input' );
			this.$feedSelector = this.$( '#select-feed' );
			this.$response     = this.$( '#find-posts-response tbody' );
			this.$search       = this.$( '#find-posts-input' );
			this.listenTo( this, 'render', this.render );
			this.listenTo( this, 'open', this.open );
			this.listenTo( this, 'close', this.close );

			this.populateFeedSelector();
			this.render();
		},

		open: function() {
			log( 'app.$overlay', app.$overlay.length );
			app.$overlay.show();
			this.$el.show();
		},

		close: function() {
			log( 'app.$overlay', app.$overlay.length );
			app.$overlay.hide();
			this.$el.hide();
		},

		search: function() {
			this.waiting();
			this.renderItems( this.collection.search( this.$search.val() ) );
		},

		saveFeed: function() {
			var url = this.$feedInput.val().trim();
			if ( url ) {
				this.feed_id = '';
				this.feed_url = url;
				log( 'saveFeed', this.feed_url );
				this.render();
			}
		},

		maybeSaveFeed: function( evt ) {
			if ( 13 === evt.which ) {
				this.saveFeed();
			}
		},

		changeFeed: function() {
			var selected = this.$feedSelector.find( 'option:selected' );
			this.feed_url = selected.text();
			this.feed_id = selected.val();

			log( 'changeFeed' );
			this.render();
		},

		waiting: function() {
			this.$response.html( '<tr class="spinner-row"><td colspan="4"><div class="spinner"></div></td></tr>' );
			this.$response.find( '.spinner' ).show();
		},

		timeLimit: function( limit ) {
			var self = this;
			this.timeout = window.setTimeout( function() {
				self.nope();
			}, limit );
		},

		nope: function() {
			this.$response.html( '<tr class="spinner-row error"><td colspan="4"><p>'+ l10n.no_data +'</p></td></tr>' );
		},

		render: function() {
			this.waiting();

			this.fetchAndPopulate();
			return this;
		},

		populateFeedSelector: function() {

			if ( ! app.feeds ) {
				return this.timeLimit( 1000 );
			}
			var html = '';
			var feed_url = '';
			var feed_id = '';
			// var self = this;

			_.each( app.feeds, function( feed, index ) {
				feed_url = ! feed_url ? feed : feed_url;
				feed_id = ! feed_id ? index : feed_id;

				html += '<option value="'+ index +'">'+ feed +'</option>';

			});
			this.$feedSelector.html( html );

			this.selectorPopulated = true;

			this.feed_id  = feed_id;
			this.feed_url = feed_url;
		},

		maybeAppendSelector: function( feed_url, feed_id ) {
			if ( ! this.selectorPopulated ) {
				this.populateFeedSelector();
			}

			if ( ! this.selectorPopulated || this.$feedSelector.find( 'option[value="'+ feed_id +'"]' ).length ) {
				log( 'feed exists' );
				return;
			}

			this.feed_id = feed_id;
			app.feeds[ feed_id ] = feed_url;
			log( 'append feed', app.feeds );

			this.$feedSelector.append( '<option value="'+ feed_id +'" selected="selected">'+ feed_url +'</option>' );
		},

		fetchAndPopulate: function() {
			if ( ! this.feed_url ) {
				return this.timeLimit( 1000 );
			}

			log( 'this.feed_url', this.feed_url );
			this.timeLimit( 4000 );
			var self = this;
			$.ajax({
				type     : 'post',
				dataType : 'json',
				url      : ajaxurl,
				data     : {
					'action'   : 'rss_get_data',
					'feed_url' : this.feed_url,
					'feed_id'  : this.feed_id
				},
				success: function( response ) {
					// bb.trigger( 'closeModals' );

					if ( response.success ) {
						window.clearTimeout( self.timeout );
						// log( 'response.success', response );
						self.populateItems( response.data.feed_items );
						self.maybeAppendSelector( response.data.feed_url, response.data.feed_id );
						// Send data back to editor
						// app.editor().trigger( 'updateFromSearch', response.data );
					} else {
						log( 'response', response );
					}

				}
			});

		},

		populateItems: function( items ) {
			this.collection = new app.Posts( _.toArray( items ) );
			log( 'this.collection', this.collection );
			this.renderItems();
		},

		submit: function( evt ) {
			evt.preventDefault();

			var checked = this.collection.checked();

			if ( ! checked ) {
				if ( window.confirm( l10n.nothing_checked ) ) {
					this.close();
				}
				return;
			}

			log( 'checked', checked );
			log( checked ? checked.toJSON() : 'nothing checked' );

			this.importPosts( checked.toJSON() );
		},

		importPosts: function( posts ) {
			this.$( '.find-box-buttons .spinner' ).show();
			var data = {
				'action'   : 'rss_save_posts',
				'to_add'   : posts,
				'feed_url' : this.feed_url,
				'feed_id'  : this.feed_id
			};
			log( 'data', data );

			$.ajax({
				type     : 'post',
				dataType : 'json',
				url      : ajaxurl,
				data     : data,
				success: function( response ) {
					log( 'response', response );

					if ( response.success ) {
						window.location.href = l10n.cpt_url;
					} else {
						// error message
						log( 'response', response );
					}

				}
			});
		},

		renderItems: function( toRender ) {

			toRender = toRender ? toRender : this.collection;
			var addedElements = document.createDocumentFragment();
			// render each row, appending to our root element
			toRender.each( function( model ) {
				var row = new app.Views.Row({ model: model });
				addedElements.appendChild( row.render().el );
			});

			this.timeout = false;
			this.$response.html( addedElements );
		}
	});

	/**
	 * Single post view
	 */
	app.Views.Row = bb.View.extend({
		tagName: 'tr',
		template  : wp.template( 'rssitem' ),
		checked : {},

		events: {
			// Enable whole row to be clicked
			'click' : 'clickRow'
		},

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

		clickRow: function( evt ) {
			var isChecked = this.$( '.found-radio input' ).prop( 'checked' );
			var $target = $( evt.target );
			var isNatural = $target.is( 'input' ) || $target.is( 'label' );
			var val;

			if ( isNatural ) {

				val = $target.val();

				if ( ! isChecked ) {
					this.model.set( 'checked', false );
				} else {
					this.model.set( 'checked', true );
				}
			} else {

				if ( isChecked ) {
					val = this.$( '.found-radio input' ).prop( 'checked', false ).val();
					this.model.set( 'checked', false );
				} else {
					val = this.$( '.found-radio input' ).prop( 'checked', true ).val();
					this.model.set( 'checked', true );
				}
			}

		},

		render: function() {
			this.$el.html( this.template( this.model.toJSON() ) );
			return this;
		}
	});

	app.openModal = function( evt ) {
		evt.preventDefault();
		app.PostsView.trigger( 'open' );
	};

	app.closeModal = function( evt ) {
		evt.preventDefault();
		app.PostsView.trigger( 'close' );
	};

	app.init = function() {
		log( app );
		var hrefSelector = '[href="post-new.php?post_type='+ l10n.cpt +'"]';
		app.$( '.add-new-h2, ' + hrefSelector ).on( 'click', app.openModal ); // @deprecated Class add-new-h2 removed in WP 4.3
		app.$( '.page-title-action, ' + hrefSelector ).on( 'click', app.openModal );

		log( 'l10n', l10n );
		var feeds = _.toArray( l10n.feeds );
		feeds = feeds.length ? feeds.length : {};

		app.$overlay = app.$( '.ui-find-overlay', app.$( 'body' ) ).on( 'click', app.closeModal );

		app.$( 'body' ).on( 'keyup', function( evt ) {
			if ( evt.which === 27 ) {
				app.PostsView.trigger( 'close' );
			} // close on Escape
		});

		app.PostsView = new app.Views.Modal({
			el: '#find-posts'
			// collection: new app.Posts()
		});

		if ( l10n.show_modal ) {
			app.PostsView.trigger( 'open' );
		}
	};

	$(document).ready( app.init );

	return app;

	/**
	 * Utilities
	 */

})(window, document, jQuery);
