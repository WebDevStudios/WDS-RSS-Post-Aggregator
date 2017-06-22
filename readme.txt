=== WDS RSS Post Aggregator ===
Contributors:      jtsternberg
Tags:              post import, feed import, rss import, rss aggregator
Requires at least: 3.6.0
Tested up to:      4.8
Stable tag:        1.0.0
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

Aggregate posts from RSS Feeds

== Description ==

This project is hosted on [GitHub](http://github.com) feel free to [fork it and contribute](https://github.com/WebDevStudios/WDS-RSS-Post-Aggregator).

WDS RSS Post Aggregator provides site owners the ability to selectively import RSS posts to their blog using WordPress' built in post selection interface.  Once a feed is selected and a post is imported, the excerpt, title, and all the usual things you would expect are editable.  You can even categorize and tag the posts in their own taxonomies.

With RSS Post Aggregator, the following is pulled in during the import process:

* Post Title
* Original Post URL
* Post Content
* Post Thumbnail

== Installation ==

= Manual Installation =

1. Upload the entire `/rss-post-aggregator` directory to the `/wp-content/plugins/` directory.
2. Activate RSS Post Aggregator through the 'Plugins' menu in WordPress.

= Dev Documentation =
To include RSS Posts in a loop, you only need to add the post-type of `rss-posts` to the query, here's an example:
`
// Add query to main loop on homepage.
add_action( 'pre_get_posts', 'wds_get_my_posts' );
function wds_get_my_posts( $query ){
	if( $query->is_home() && $query->is_main_query() ){
		$query->set( 'post_type', array( 'post', 'rss-posts' ) );
	}
}
`

== Frequently Asked Questions ==
[Open A Ticket](https://github.com/WebDevStudios/WDS-RSS-Post-Aggregator/issues)

* None Yet

== Screenshots ==

1. "Add New RSS Post" dialog
2. "RSS Feed Links" page, very similar to tags/categories
3. "RSS Feed Categories" page
4. Imported posts with imported featured image ( It's Automatic!!! )
5. Post Edit Screen - Manually set RSS feed link

== Changelog ==

= 1.0.0 =
* Updated CPT Core
* Updated Taxonomy Core
* Refactored core code significantly.
* Fixed an issue where clicking "New RSS Post" wouldn't fire the dialog from post list page.

= 0.1.1 =
* Removed CMB2 dependancy - [Fixes #2](https://github.com/WebDevStudios/WDS-RSS-Post-Aggregator/issues/2)
* Code cleanup and docblocks

= 0.1.0 =
* First release

== Upgrade Notice ==

= 0.1.0 =
First Release
