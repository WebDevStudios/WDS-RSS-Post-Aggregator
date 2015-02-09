# WDS RSS Post Aggregator #
**Contributors:**     [jtsternberg](https://github.com/jtsternberg), [JayWood](https://github.com/JayWood), [stacyk](https://github.com/stacyk), [blobaugh](https://github.com/blobaugh), [lswilson](https://github.com/lswilson), [imBigWill](https://github.com/ImBigWill), [coreymcollins](https://github.com/coreymcollins)   
**Tags:**  
**Requires at least:** 3.6.0  
**Tested up to:**      3.6.0  
**Stable tag:**        0.1.0  
**License:**           GPLv2 or later  
**License URI:**       http://www.gnu.org/licenses/gpl-2.0.html  

Allows you to selectively import posts to your WordPress installation from RSS Feeds and save them locally so they're never lost.

## Description ##

**REQUIRES** [CMB2 WordPress Plugin](https://wordpress.org/plugins/cmb2/) to function properly.

## Installation ##

### Including Posts ###
To include RSS Posts in a loop, you only need to add the post-type of `rss-posts` to the query, here's an example:
```
// Add query to main loop on homepage.
add_action( 'pre_get_posts', 'wds_get_my_posts' );
function wds_get_my_posts( $query ){
	if( $query->is_home() && $query->is_main_query() ){
		$query->set( 'post_type', array( 'post', 'rss-posts' ) );
	}
}
```

### Manual Installation ###

1. Upload the entire `/rss-post-aggregation` directory to the `/wp-content/plugins/` directory.
2. Activate RSS Post Aggregator through the 'Plugins' menu in WordPress.

## Frequently Asked Questions ##
[Open A Ticket](https://github.com/WebDevStudios/WDS-RSS-Post-Aggregator/issues)

* None Yet 

## Screenshots ##

![Importing RSS Posts](https://raw.githubusercontent.com/WebDevStudios/WDS-RSS-Post-Aggregator/master/screenshot-1.jpg)   
"Add New RSS Post" dialog

![RSS Feed Links](https://raw.githubusercontent.com/WebDevStudios/WDS-RSS-Post-Aggregator/master/screenshot-2.jpg)   
"RSS Feed Links" page, very similar to tags/categories

![RSS Feed Categories](https://raw.githubusercontent.com/WebDevStudios/WDS-RSS-Post-Aggregator/master/screenshot-3.jpg)      
"RSS Feed Categories" page

![Imported Posts](https://raw.githubusercontent.com/WebDevStudios/WDS-RSS-Post-Aggregator/master/screenshot-4.jpg)   
Imported posts with imported featured image ( It's automatic!!! )

![Post Edit Screen](https://raw.githubusercontent.com/WebDevStudios/WDS-RSS-Post-Aggregator/master/screenshot-5.jpg)      
Post Edit Screen - Manually set RSS feed link.


## Changelog ##

### 0.1.0 ###
* First release
