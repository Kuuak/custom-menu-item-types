<?php
/**
 * Main plugin file.
 *
 * @package Menu_Item_Types
 */

namespace required\Custom_Menu_Item_Types;

defined( 'WPINC' ) or die;

/**
 * Menu_Item_Types class.
 */
class Custom_Menu_Items {

	/**
	 * Add menu meta box
	 */
	public function add_meta_box() {
		add_meta_box(
			'r_custom_item_types',
			__( 'Custom Menu Types', 'polylang' ),
			array( $this, 'r_custom_item_types' ),
			'nav-menus',
			'side',
			'high'
		);
	}

	/**
	 * Change item label depending on the link
	 */
	public function customize_menu_item_label( $menu_item ) {
		if ( 'custom' !== $menu_item->type ) {
			return $menu_item;
		}
		switch ( $menu_item->url ) {
			case '#line_break':
				$menu_item->type_label = __( 'Line Break', 'menu-item-types' );
				break;
			case '#column_end':
				$menu_item->type_label = __( 'Column End', 'menu-item-types' );
				break;
			case '#custom_headline':
				$menu_item->type_label = __( 'Headline', 'menu-item-types' );
				break;
			case '#pll_switcher':
				$menu_item->type_label = __( 'Language Switcher', 'menu-item-types' );
				break;
		}
		$menu_item->rcmit_type = ! isset( $menu_item->rcmit_type ) ? get_post_meta( $menu_item->ID, '_menu_item_rcmit_type', true ) : $menu_item->rcmit_type;
		$menu_item->rcmit_button_text = ! isset( $menu_item->rcmit_button_text ) ? get_post_meta( $menu_item->ID, '_menu_item_rcmit_button_text', true ) : $menu_item->rcmit_button_text;
		$menu_item->rcmit_shortcode = ! isset( $menu_item->rcmit_shortcode ) ? get_post_meta( $menu_item->ID, '_menu_item_rcmit_shortcode', true ) : $menu_item->rcmit_shortcode;
		$menu_item->rcmit_column = ! isset( $menu_item->rcmit_column ) ? get_post_meta( $menu_item->ID, '_menu_item_rcmit_column', true ) : $menu_item->rcmit_column;
		switch ( $menu_item->rcmit_type ) {
			case 'highlight_box':
				$menu_item->type_label = __( 'Highlight Box', 'menu-item-types' );
				break;
			case 'newsletter_box':
				$menu_item->type_label = __( 'Newsletter Box', 'menu-item-types' );
				break;
		}
		return $menu_item;
	}

	public function nav_menu_start_el( $item_output, $item, $depth, $args ){
		if ( 'custom' !== $item->type ) {
			return $item_output;
		}
		/** This filter is documented in wp-includes/post-template.php */
		$title = apply_filters( 'the_title', $item->title, $item->ID );
		/** This filter is documented in wp-includes\nav-menu-template.php */
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );
		$item->rcmit_type = ! isset( $item->rcmit_type ) ? get_post_meta( $item->ID, '_menu_item_rcmit_type', true ) : $item->rcmit_type;
		$item->rcmit_button_text = ! isset( $item->rcmit_button_text ) ? get_post_meta( $item->ID, '_menu_item_rcmit_button_text', true ) : $item->rcmit_button_text;
		$item->rcmit_shortcode = ! isset( $item->rcmit_shortcode ) ? get_post_meta( $item->ID, '_menu_item_rcmit_shortcode', true ) : $item->rcmit_shortcode;
		switch ( $item->url ) {
			case '#line_break':
				$item_output = '<hr>';
				break;
			case '#column_end':
				$item_output = '';
				break;
			case '#custom_headline':
				$item_output = '<h4>' . $item->post_title . '</h4>';
				break;
		}

		switch ( $item->rcmit_type ) {
			case 'highlight_box':
				$item_output = $args->before;
				$item_output .= '<h4>' . $title . '</h4>';
				$item_output .= '<p>' . esc_html( $item->description ) . '</p>';
				$item_output .= '<a class="button" href="' . esc_url( $item->url ) . '">';
				$item_output .= $args->link_before . esc_html( $item->rcmit_button_text ) . $args->link_after;
				$item_output .= '</a>';
				$item_output .= $args->after;
				break;
			case 'newsletter_box':
				$item_output = $args->before . '<div><h4>' . esc_html( $title ) . '</h4><p>' . esc_html( $item->description ) . '</p>' . do_shortcode( $item->rcmit_shortcode ) . '</div>' . $args->after;
				break;
		}

		return $item_output;
	}

	public function wp_edit_nav_menu_walker( $class, $menu_id ) {
		return 'required\Custom_Menu_Item_Types\Walker_Custom_Item_Types';
	}

	public function wp_nav_menu_item_fields( $nav_menu_item_fields, $context ) {
		if ( 'custom' !== $context['item']->type ) {
			return $nav_menu_item_fields;
		}
		switch ( $context['item']->url ) {
			case '#column_end':
				ob_start(); ?>
					<p class="field-column description description-wide">
						<label for="edit-menu-item-column-<?php echo $context['item']->ID; ?>">
							<?php _e( 'Width of next column', 'menu-item-types' ); ?><br />
							<select name="menu-item-column[<?php echo $context['item']->ID; ?>]">
								<option value="col-3" <?php selected( $context['item']->rcmit_column, 'col-3' ) ?>>Col 3</option>
								<option value="col-4" <?php selected( $context['item']->rcmit_column, 'col-4' ) ?>>Col 4</option>
								<option value="col-6" <?php selected( $context['item']->rcmit_column, 'col-6' ) ?>>Col 6</option>
							</select>
						</label>
					</p>
				<?php $nav_menu_item_fields['column_width'] = ob_get_clean();
			case '#line_break':
				unset( $nav_menu_item_fields['css-classes'] );
				ob_start(); ?>
					<input type="hidden" id="edit-menu-item-title-<?php echo $context['item']->ID; ?>" class="widefat edit-menu-item-title" name="menu-item-title[<?php echo $context['item']->ID; ?>]" value="<?php echo esc_attr( $context['item']->title ); ?>" />
				<?php $nav_menu_item_fields['title'] = ob_get_clean();
			case '#custom_headline':
				unset( $nav_menu_item_fields['attr-title'] );
				unset( $nav_menu_item_fields['link-target'] );
				unset( $nav_menu_item_fields['xfn'] );
				unset( $nav_menu_item_fields['description'] );
				ob_start(); ?>
					<input type="hidden" id="edit-menu-item-url-<?php echo $context['item']->ID; ?>" class="widefat code edit-menu-item-url" name="menu-item-url[<?php echo $context['item']->ID; ?>]" value="<?php echo esc_attr( $context['item']->url ); ?>" />
				<?php $nav_menu_item_fields['custom'] = ob_get_clean();
				break;
		}
		if ( ! empty( $context['item']->rcmit_type ) ) { ?>
			<input class="menu-item-data-rcmit-type" type="hidden" name="menu-item-rcmit-type[<?php echo $context['item']->ID; ?>]" value="<?php echo $context['item']->rcmit_type; ?>" />
			<?php
			unset( $nav_menu_item_fields['title'] );
			unset( $nav_menu_item_fields['custom'] );
			unset( $nav_menu_item_fields['attr-title'] );
			unset( $nav_menu_item_fields['link-target'] );
			unset( $nav_menu_item_fields['xfn'] );
			$new_nav_menu_item_fields = array();
			if ( 'highlight_box' === $context['item']->rcmit_type ) {
				ob_start(); ?>
				<p class="field-title description description-wide">
					<label for="edit-menu-item-title-<?php echo $context['item']->ID; ?>">
						<?php _e( 'Box Header', 'menu-item-types' ); ?><br />
						<input type="text" id="edit-menu-item-title-<?php echo $context['item']->ID; ?>" class="widefat edit-menu-item-title" name="menu-item-title[<?php echo $context['item']->ID; ?>]" value="<?php echo esc_attr( $context['item']->title ); ?>" />
					</label>
				</p>
				<?php $new_nav_menu_item_fields['title'] = ob_get_clean(); ?>
				<?php ob_start(); ?>
				<p class="field-button-text description description-wide">
					<label for="edit-menu-item-button-text-<?php echo $context['item']->ID; ?>">
						<?php _e( 'Button Text' ); ?><br />
						<input type="text" id="edit-menu-item-button-text-<?php echo $context['item']->ID; ?>" class="widefat code edit-menu-item-button-text" name="menu-item-button-text[<?php echo $context['item']->ID; ?>]" value="<?php echo esc_attr( $context['item']->rcmit_button_text ); ?>" />
					</label>
				</p>
				<?php $new_nav_menu_item_fields['button_text'] = ob_get_clean(); ?>
				<?php ob_start(); ?>
				<p class="field-url description description-wide">
					<label for="edit-menu-item-url-<?php echo $context['item']->ID; ?>">
						<?php _e( 'Button URL', 'menu-item-types' ); ?><br />
						<input type="text" id="edit-menu-item-url-<?php echo $context['item']->ID; ?>" class="widefat code edit-menu-item-url" name="menu-item-url[<?php echo $context['item']->ID; ?>]" value="<?php echo esc_attr( $context['item']->url ); ?>" />
					</label>
				</p>
				<?php $new_nav_menu_item_fields['highlight_box'] = ob_get_clean(); ?>
				<?php $new_nav_menu_item_fields['description'] = $nav_menu_item_fields['description'];
			}
			if ( 'newsletter_box' === $context['item']->rcmit_type ) {

				ob_start(); ?>
				<p class="field-title description description-wide">
					<label for="edit-menu-item-title-<?php echo $context['item']->ID; ?>">
						<?php _e( 'Header' ); ?><br />
						<input type="text" id="edit-menu-item-title-<?php echo $context['item']->ID; ?>" class="widefat edit-menu-item-title" name="menu-item-title[<?php echo $context['item']->ID; ?>]" value="<?php echo esc_attr( $context['item']->title ); ?>" />
					</label>
				</p>
				<?php $new_nav_menu_item_fields['title'] = ob_get_clean(); ?>
				<?php $new_nav_menu_item_fields['description'] = $nav_menu_item_fields['description']; ?>
				<?php ob_start(); ?>
				<p class="field-shortcode description description-wide">
					<label for="edit-menu-item-shortcode-<?php echo $context['item']->ID; ?>">
						<?php _e( 'Shortcode', 'menu-item-types' ); ?><br />
						<input type="text" id="edit-menu-item-shortcode-<?php echo $context['item']->ID; ?>" class="widefat code edit-menu-item-shortcode" name="menu-item-shortcode[<?php echo $context['item']->ID; ?>]" value="<?php echo esc_attr( $context['item']->rcmit_shortcode ); ?>" />
					</label>
				</p>
				<?php $new_nav_menu_item_fields['newsletter_box_shortcode'] = ob_get_clean();
			}
			$nav_menu_item_fields = array_merge( $new_nav_menu_item_fields, $nav_menu_item_fields );
		}
		return $nav_menu_item_fields;
	}

	public function customize_nav_menu_available_item_types( $item_types ) {
		// This would work if could query the custom items from somewhere.
		return $item_types;
	}

	/**
	 * Displays a metabox for the custom links menu item.
	 *
	 * @global int        $_nav_menu_placeholder
	 * @global int|string $nav_menu_selected_id
	 */
	public function r_custom_item_types() {
		global $_nav_menu_placeholder, $nav_menu_selected_id;

		$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;

		?>
		<div class="posttypediv" id="custom-item-types">
			<div id="tabs-panel-custom-item-types" class="tabs-panel tabs-panel-active">
				<ul id ="custom-item-types-checklist" class="categorychecklist form-no-clear">
					<li>
						<label class="menu-item-title">
							<input type="radio" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" value="-1"> <?php _e( 'Column End', 'menu-item-types' ); ?>
						</label>
						<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="custom">
						<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php _e( 'Column End', 'menu-item-types' ); ?>">
						<input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="#column_end">
					</li>
					<li>
						<label class="menu-item-title">
							<input type="radio" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" value="-1"> <?php _e( 'Line Break', 'menu-item-types' ); ?>
						</label>
						<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="custom">
						<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php _e( 'Line Break', 'menu-item-types' ); ?>">
						<input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="#line_break">
					</li>
					<li>
						<label class="menu-item-title">
							<input type="radio" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" value="-1"> <?php _e( 'Headline', 'menu-item-types' ); ?>
						</label>
						<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="custom">
						<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php _e( 'Headline', 'menu-item-types' ); ?>">
						<input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="#custom_headline">
					</li>
					<li>
						<label class="menu-item-title">
							<input type="radio" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" value="-1"> <?php _e( 'Highlight Box', 'menu-item-types' ); ?>
						</label>
						<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="custom">
						<input type="hidden" class="menu-item-rcmit-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="highlight_box">
						<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php _e( 'Highlight Box', 'menu-item-types' ); ?>">
					</li>
					<li>
						<label class="menu-item-title">
							<input type="radio" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" value="-1"> <?php _e( 'Newsletter Box', 'menu-item-types' ); ?>
						</label>
						<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="custom">
						<input type="hidden" class="menu-item-rcmit-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="newsletter_box">
						<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php _e( 'Newsletter Box', 'menu-item-types' ); ?>">
					</li>
				</ul>
			</div>
			<input type="hidden" value="custom" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" />

			<p class="button-controls wp-clearfix">
				<span class="add-to-menu">
					<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'menu-item-types' ); ?>" name="add-custom-menu-item" id="submit-custom-item-types" />
					<span class="spinner"></span>
				</span>
			</p>

		</div><!-- /.custom-item-types -->
		<?php
	}

	public function wp_update_nav_menu_item( $menu_id = 0, $menu_item_db_id = 0, $args ) {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}
		// Add new menu item via ajax.
		if ( isset( $_REQUEST['menu-settings-column-nonce'] ) && wp_verify_nonce( $_REQUEST['menu-settings-column-nonce'], 'add-menu_item' ) ) {
			if ( ! empty( $_POST['menu-item'][ '-1' ]['menu-item-url'] ) && in_array( $_POST['menu-item'][ '-1' ]['menu-item-url'], array( 'newsletter_box', 'highlight_box' ) ) ) {
				update_post_meta(
					$menu_item_db_id,
					'_menu_item_rcmit_type',
					sanitize_text_field( $_POST['menu-item'][ '-1' ]['menu-item-url'] )
				);
				update_post_meta(
					$menu_item_db_id,
					'_menu_item_url',
					''
				);
			}
		}
		// Updaate settings for existsing menu items.
		if ( isset( $_REQUEST['update-nav-menu-nonce'] ) && wp_verify_nonce( $_REQUEST['update-nav-menu-nonce'], 'update-nav_menu' ) ) {
			if ( ! empty( $_POST['menu-item-button-text'][ $menu_item_db_id ] ) ) {
				update_post_meta(
					$menu_item_db_id,
					'_menu_item_rcmit_button_text',
					sanitize_text_field( $_POST['menu-item-button-text'][ $menu_item_db_id ] )
				);
			}
			if ( ! empty( $_POST['menu-item-shortcode'][ $menu_item_db_id ] ) ) {
				update_post_meta(
					$menu_item_db_id,
					'_menu_item_rcmit_shortcode',
					sanitize_text_field( $_POST['menu-item-shortcode'][ $menu_item_db_id ] )
				);
			}
			if ( ! empty( $_POST['menu-item-column'][ $menu_item_db_id ] ) ) {
				update_post_meta(
					$menu_item_db_id,
					'_menu_item_rcmit_column',
					sanitize_text_field( $_POST['menu-item-column'][ $menu_item_db_id ] )
				);
			}
		}

	}

}
