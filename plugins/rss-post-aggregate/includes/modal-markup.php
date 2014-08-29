<div id="find-posts" class="find-box">
	<div id="find-posts-head" class="find-box-head">
		<?php _e( 'Select RSS Feed', 'rss_post_aggregation' ); ?>
		<div id="find-posts-close"></div>
	</div>
	<div class="find-box-inside">
		<div class="find-box-search">
			<?php wp_nonce_field( 'rss_save', 'rss_save', false ); ?>
			<label class="screen-reader-text" for="find-posts-input"><?php _e( 'Add RSS Feed', 'rss_post_aggregation' ); ?></label>
			<input type="text" id="rss-save-feed-input" name="rss_save_feed" value="" />
			<span class="spinner"></span>
			<input type="button" id="rss-save-feed" value="<?php esc_attr_e( 'Add Feed', 'rss_post_aggregation' ); ?>" class="button" />
			<div class="clear"></div>
		</div>
		<hr>
		<div class="find-box-search">
			<select id="select-feed"></select>
			<input type="hidden" name="affected" id="affected" value="" />
			<?php wp_nonce_field( 'rss_search', 'rss_search', false ); ?>
			<label class="screen-reader-text" for="find-posts-input"><?php _e( 'Search' ); ?></label>
			<input type="text" id="find-posts-input" name="ps" value="" />
			<span class="spinner"></span>
			<input type="button" id="find-posts-search" value="<?php esc_attr_e( 'Search' ); ?>" class="button" />
			<div class="clear"></div>
		</div>
		<div id="find-posts-response">
			<table class="widefat"><thead><tr><th class="found-radio"><br></th><th><?php _e( 'Title', 'rss_post_aggregation' ); ?></th><th class="no-break"><?php _e( 'Source', 'rss_post_aggregation' ); ?></th><th class="no-break">Date</th></tr></thead><tbody>

			<tr title="http://news.xbox.com/2014/08/games-doctor-who-on-minecraft" class="found-posts alternate"><td class="found-radio"><input type="checkbox" id="found-20872" name="found_post_index" value="20872"></td><td><label for="found-20872">Sci-fi fans can now channel ‘Doctor Who’ in ‘Minecraft: Xbox 360 Edition’</label></td><td class="no-break">news.xbox.com</td><td class="no-break">2014/08/28</td></tr>

			<tr title="microsoft.com" class="found-posts"><td class="found-radio"><input type="checkbox" id="found-16576" name="found_post_index" value="16576"></td><td><label for="found-16576">Get an early dose of ‘The Strain’ with exclusive, behind-the-scenes looks on Xbox</label></td><td class="no-break">microsoft.com</td><td class="no-break">2014/07/14</td></tr>

			<tr title="microsoft.com" class="found-posts alternate"><td class="found-radio"><input type="checkbox" id="found-16577" name="found_post_index" value="16577"></td><td><label for="found-16577">Bing scores big during the world’s premier soccer tournament</label></td><td class="no-break">microsoft.com</td><td class="no-break">2014/07/14</td></tr>

			<tr title="microsoft.com" class="found-posts"><td class="found-radio"><input type="checkbox" id="found-16578" name="found_post_index" value="16578"></td><td><label for="found-16578">Project Sienna Beta 3 now available for easily creating powerful mobile apps for enterprise</label></td><td class="no-break">microsoft.com</td><td class="no-break">2014/07/14</td></tr>

			<tr title="microsoft.com" class="found-posts alternate"><td class="found-radio"><input type="checkbox" id="found-16579" name="found_post_index" value="16579"></td><td><label for="found-16579">Internet of Things team is ready for you at Worldwide Partner Conference 2014</label></td><td class="no-break">microsoft.com</td><td class="no-break">2014/07/14</td></tr>

			<tr title="microsoft.com" class="found-posts"><td class="found-radio"><input type="checkbox" id="found-16580" name="found_post_index" value="16580"></td><td><label for="found-16580">Pro tips: How to keep on top of the Worldwide Partner Conference</label></td><td class="no-break">microsoft.com</td><td class="no-break">2014/07/14</td></tr>

			<tr title="microsoft.com" class="found-posts alternate"><td class="found-radio"><input type="checkbox" id="found-16581" name="found_post_index" value="16581"></td><td><label for="found-16581">Cortana to get an advanced degree this fall</label></td><td class="no-break">microsoft.com</td><td class="no-break">2014/07/14</td></tr>

			<tr title="microsoft.com" class="found-posts"><td class="found-radio"><input type="checkbox" id="found-16582" name="found_post_index" value="16582"></td><td><label for="found-16582">Mobile-first cloud-first world front and center at Worldwide Partner Conference</label></td><td class="no-break">microsoft.com</td><td class="no-break">2014/07/14</td></tr>
			</tbody></table>
		</div>
	</div>
	<div class="find-box-buttons">
		<?php submit_button( __( 'Select to Import', 'rss_post_aggregation' ), 'button-primary alignright', 'find-posts-submit', false ); ?>
		<div class="clear"></div>
	</div>
</div>
<div class="ui-find-overlay"></div>


<!-- #tmpl-rssitem Underscore Template -->
<script type="text/template" id="tmpl-rssitem">
	<td class="found-radio">
		<input id="found-{{{ data.index }}}" type="checkbox" name="found_post_index" value="{{{ data.index }}}">
	</td>
	<td><label for="found-{{{ data.index }}}">{{{ data.title }}}</label></td>
	<td class="no-break">{{{ data.urlhost }}}</td>
	<td class="no-break" class="rss-item-date">{{{ data.date }}}</td>
</script>
<!-- #tmpl-rssitem Underscore Template ### END -->

<!-- #tmpl-rssfeedoption Underscore Template -->
<script type="text/template" id="tmpl-rssfeedoption">
	<option value"{{{ data.url }}}">{{{ data.url }}}</option>
</script>
<!-- #tmpl-rssfeedoption Underscore Template ### END -->

