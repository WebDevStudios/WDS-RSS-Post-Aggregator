<div id="find-posts" class="find-box rss-search-modal" style="display:none;">
	<div id="find-posts-head" class="find-box-head">
		<?php esc_html_e( 'Select RSS Feed', 'wds-rss-post-aggregator' ); ?>
		<div id="find-posts-close"></div>
	</div>
	<div class="find-box-inside">
		<div class="find-box-search add-feed-form">
			<?php wp_nonce_field( 'rss_save', 'rss_save', false ); ?>
			<label class="screen-reader-text" for="find-posts-input"><?php esc_html_e( 'Add RSS Feed', 'wds-rss-post-aggregator' ); ?></label>
			<input type="text" id="rss-save-feed-input" name="rss_save_feed" value="" placeholder="<?php esc_attr_e( 'enter feed url', 'wds-rss-post-aggregation' ); ?>" />
			<input type="button" id="rss-save-feed" value="<?php esc_attr_e( 'Add Feed', 'wds-rss-post-aggregator' ); ?>" class="button" />
			<div class="clear"></div>
		</div>
		<hr>
		<div class="find-box-search">
			<select id="select-feed"></select>
			<input type="hidden" name="affected" id="affected" value="" />
			<?php wp_nonce_field( 'rss_search', 'rss_search', false ); ?>
			<label class="screen-reader-text" for="find-posts-input"><?php esc_html_e( 'Search', 'wds-rss-post-aggregation' ); ?></label>
			<input type="text" id="find-posts-input" name="ps" value="" placeholder="<?php esc_attr_e( 'start typing to filter results', 'wds-rss-post-aggregation' ); ?>" />
			<div class="clear"></div>
		</div>
		<div id="find-posts-response">
			<table class="widefat"><thead><tr><th class="found-radio"><br></th><th><?php esc_html_e( 'Title', 'wds-rss-post-aggregator' ); ?></th><th class="no-break"><?php esc_html_e( 'Source', 'wds-rss-post-aggregator' ); ?></th><th class="no-break"><?php esc_html_e( 'Date', 'wds-rss-post-aggregation' ); ?></th></tr></thead><tbody>

			<tr class="spinner-row error"><td colspan="4"><p><?php esc_html_e( 'No feed data found', 'wds-rss-post-aggregator' ); ?></p></td></tr>
			</tbody></table>
		</div>
	</div>
	<div class="find-box-buttons">
		<a href="<?php echo esc_url( admin_url( '/edit-tags.php?taxonomy=' . $this->tax->taxonomy() . '&post_type=' . $this->cpt->post_type() ) ); ?>" class="button-secondary manage-feed-links"><?php esc_html_e( 'Manage RSS Feeds', 'wds-rss-post-aggregator' ); ?></a>
		<a href="<?php echo esc_url( admin_url( '/post-new.php?post_type=' . $this->cpt->post_type() ) ); ?>" class="button-secondary manage-feed-links"><?php esc_html_e( 'Add Post Manually', 'wds-rss-post-aggregator' ); ?></a>
		<div class="spinner" style="display:none;"></div>
		<?php submit_button( esc_html__( 'Select to Import', 'wds-rss-post-aggregator' ), 'button-primary alignright', 'find-posts-submit', false ); ?>
		<div class="clear"></div>
	</div>
</div>
<div class="ui-find-overlay" style="display:none;"></div>


<!-- #tmpl-rssitem Underscore Template -->
<script type="text/template" id="tmpl-rssitem">
	<td class="found-radio">
		<input id="found-{{{ data.index }}}" type="checkbox" name="found_post_index" value="{{{ data.index }}}">
	</td>
	<td><label for="found-{{{ data.index }}}">{{{ data.title }}}</label></td>
	<td class="no-break">{{{ data.source }}}</td>
	<td class="no-break" class="rss-item-date">{{{ data.date }}}</td>
</script>
<!-- #tmpl-rssitem Underscore Template ### END -->
