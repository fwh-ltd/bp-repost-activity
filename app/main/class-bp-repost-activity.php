<?php // @codingStandardsIgnoreLine
/**
 * Class for repost methods.
 *
 * @package Bp_Repost_Activity
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// If class is exist, then don't execute this.
if ( ! class_exists( 'BP_Repost_Activity' ) ) {

	/**
	 * Class for Activity Re-post.
	 */
	class BP_Repost_Activity {

		/**
		 * Constructor for class.
		 */
		public function __construct() {

			// Add custom script.
			add_action( 'wp_enqueue_scripts', array( $this, 'bprpa_enqueue_styles_scripts' ), 99 );

			// Add content for public activity.
			add_action( 'bp_activity_new_update_content', array( $this, 'bprpa_repost_activity_content' ), 10 );

			// Add content for group activity.
			add_action( 'groups_activity_new_update_content', array( $this, 'bprpa_repost_activity_content' ), 10 );

			// Add popup mokup in footer.
			add_action( 'wp_footer', array( $this, 'bprpa_popup_markup' ) );

			// rtMedia save.
			add_action( 'bp_activity_posted_update', array( $this, 'bprpa_save_media' ), 10, 3 );

			// Save repost activity status in meta.
			add_action( 'bp_activity_posted_update', array( $this, 'bprpa_save_repost_status' ), 10, 3 );
			add_action( 'bp_groups_posted_update', array( $this, 'bprpa_save_group_repost_status' ), 10, 4 );

			// Add repost status on Activity Header.
			add_filter( 'bp_get_activity_action', array( $this, 'bprpa_show_repost_status' ), 10 );

			// Repost button.
			add_action( 'bp_activity_entry_meta', array( $this, 'bprpa_repost_button_new' ) );

			// Save meta data for user repost activity.
			add_action( 'bp_activity_posted_update', array( $this, 'bprpa_copy_meta_data_profile' ), 10, 3 );

			// Save meta data for group repost activity.
			add_action( 'bp_groups_posted_update', array( $this, 'bprpa_copy_meta_data_group' ), 10, 4 );

			// Repost action.
			add_filter( 'bp_get_activity_action', array( $this, 'bprpa_repost_activity_action' ), 10, 2 );

			add_filter( 'bp_get_activity_content_body', array( $this, 'bprpa_repost_activity_content_body' ), 999, 2 );

			add_action( 'wp_head', array( $this, 'bbrpa_repost_custom_style' ) );
		}

		/**
		 * Markup for popup.
		 */
		public function bprpa_popup_markup() {

			// Bail, if anything goes wrong.
			if ( ! $this->bprpa_is_activity_strem() || ! function_exists( 'buddypress' ) ) {
				return;
			}
			$if_bp_has_group = bp_is_active( 'groups' ) && bp_has_groups( 'user_id=' . bp_loggedin_user_id() . '&type=alphabetical&max=100&per_page=100&populate_extras=0&update_meta_cache=0' );
			?>
			<div id="repost-box" class="modal" role="dialog">
				<div class='modal-dialog'>
					<form id="repost-activity-form">
						<!-- Modal content-->
						<div class="modal-content">
							<div class="modal-header">
								<span type="button" class="close" data-dismiss="modal">&times;</span>
								<?php esc_html_e( 'Post in', 'bp-repost-activity' ); ?>:
								<select class="form-control" name="posting_at" id="posting_at">
									<option value="">
										<?php esc_html_e( 'Public', 'bp-repost-activity' ); ?>
									</option>
									<?php if ( $if_bp_has_group ) : ?>
									<option value="groups">
										<?php esc_html_e( 'Group', 'bp-repost-activity' ); ?>
									</option>
									<?php endif; ?>
								</select>
								<?php if ( $if_bp_has_group ) : ?>
								<select name="rpa_group_id" id="rpa_group_id" style="display: none;">
									<?php while ( bp_groups() ) : ?>
										<?php bp_the_group(); ?>
										<option value="<?php bp_group_id(); ?>"><?php bp_group_name(); ?></option>
									<?php endwhile; ?>
								</select>
								<?php endif; ?>
							</div>
							<div class="modal-body">
								<input type="hidden" name="original_item_id" id="original_item_id" value="" />
								<div class="content"></div>
							</div>
							<div class="modal-footer">
								<button type="button" id="bprpa-close-modal" class="btn btn-default" data-dismiss="modal"><?php esc_html_e( 'Close', 'bp-repost-activity' ); ?></button>
								<button type="submit" class="button" id="repost-activity" name="repost-activity"><?php esc_html_e( 'Re-Post', 'bp-repost-activity' ); ?></button>
							</div>
						</div>
					</form>
				</div><!-- End .modal-dialog -->
			</div> <!-- End #repost-box -->
			<?php
		}

		/**
		 * Button for re-post activity.
		 *
		 * @param array $buttons     The list of buttons.
		 * @param int   $activity_id The current activity ID.
		 */
		public function bprpa_repost_button( $buttons, $activity_id ) {

			// Bail, if anything goes wrong.
			if ( ! $this->bprpa_is_activity_strem() || ( function_exists( 'bp_get_activity_type' ) && 'activity_update' !== bp_get_activity_type() ) ) {
				return $buttons;
			}

			$buttons['bp_activity_report'] = array(
				'id'                => 'bp_activity_report',
				'position'          => 99,
				'component'         => 'activity',
				'parent_element'    => 'div',
				'parent_attr'       => array(),
				'must_be_logged_in' => true,
				'button_element'    => 'a',
				'button_attr'       => array(
					'class'            => 'button item-button bp-secondary-action bp-tooltip bp-repost-activity',
					'id'               => esc_attr( 'bp_activity_repost_' . $activity_id ),
					'data-bp-tooltip'  => esc_html__( 'Re-post', 'bp-repost-activity' ),
					'data-activity_id' => esc_attr( $activity_id ),
					'aria-pressed'     => 'false',

				),
				'link_text'         => sprintf(
					'<span class="bp-screen-reader-text">%s</span>',
					esc_html__( 'Re-Post', 'bp-repost-activity' )
				),
			);

			return $buttons;
		}

		/**
		 * Add scripts & css related to re-post button.
		 */
		public function bprpa_enqueue_styles_scripts() {

			// Bail, if anything goes wrong.
			if ( ! $this->bprpa_is_activity_strem() ) {
				return;
			}

			// Custom plugin script.
			wp_enqueue_style(
				'repost-style',
				BPRPA_URL . 'build/style-bp-repost-activity.css',
				array(),
				BPRPA_VERSION
			);

			// Plugin script.
			wp_enqueue_script(
				'repost-script',
				BPRPA_URL . 'build/bp-repost-activity.js',
				array( 'jquery' ),
				BPRPA_VERSION,
				true
			);

			// Set params to be used in custom script.
			$params = array(
				'theme_package_id' => function_exists( 'bp_get_option' )
					? bp_get_option( '_bp_theme_package_id', 'legacy' )
					: 'legacy',
			);

			wp_localize_script( 'repost-script', 'RE_Post_Activity', $params );
		}

		/**
		 * Set content from original activity.
		 *
		 * @param  string $content Activity content.
		 * @return string
		 */
		public function bprpa_repost_activity_content( $content ) {

			// Bail, if anything goes wrong.
			if ( ! $this->bprpa_is_activity_strem() ) {
				return $content;
			}

			// Get activity id which we are going to re-post.
			$original_item_id = filter_input( INPUT_POST, 'original_item_id', FILTER_SANITIZE_NUMBER_INT );

			// Return if it's blank.
			if ( empty( $original_item_id ) ) {
				return $content;
			}

			// Get activity by activity ID.
			$activity = $this->bprpa_get_activity( $original_item_id );

			if ( empty( $activity ) ) {
				return $content;
			}

			// Get content.
			$content = ! empty( $activity->content ) ? $activity->content : '&nbsp;';

			/**
			 * Filters the new activity content for reposted activity item.
			 *
			 * @param string $content Activity content from original activity.
			 */
			$content = apply_filters( 'bprpa_activity_content', $content, $original_item_id );

			/**
			 * To allow media to be saved while re-posting.
			 * Removed this action, because while reposting medias,
			 * we will have links in our copied content.
			 * So we don't want to moderate those media links while re-posting.
			 */
			remove_action( 'bp_activity_before_save', 'bp_activity_check_moderation_keys', 2, 1 );

			return $content;
		}

		/**
		 * Get activity by activity id.
		 *
		 * @param  int $activty_id Activity ID.
		 * @return obj
		 */
		public function bprpa_get_activity( $activty_id = '' ) {

			// Bail, if anything goes wrong.
			if ( ! $this->bprpa_is_activity_strem() || empty( $activty_id ) ) {
				return;
			}

			// Get result from transient.
			$activity = get_transient( 'bprpa_activity_' . $activty_id );

			if ( false !== $activity ) {
				return $activity;
			}

			global $bp, $wpdb;

			// Activity table.
			$activty_table = $bp->activity->table_name;

			// Sql query for getting activity record by activity id.
			$activity = $wpdb->get_row( // @codingStandardsIgnoreLine
				$wpdb->prepare(
					"SELECT * FROM {$activty_table} WHERE id = %d", // @codingStandardsIgnoreLine
					intval( $activty_id )
				)
			);

			// Set transient.
			if ( ! empty( $activity ) ) {
				set_transient( 'bprpa_activity_' . $activty_id, $activity, 24 * HOUR_IN_SECONDS );
			}

			return $activity;
		}

		/**
		 * Get activity by activity id.
		 *
		 * @param  int $activity_id Activity ID.
		 * @return obj
		 */
		public function bprpa_get_media( $activity_id = '' ) {

			// Bail, if anything goes wrong.
			if ( ! $this->bprpa_is_activity_strem() || empty( $activity_id ) ) {
				return;
			}

			$media = get_transient( 'bprpa_media_activity_' . $activity_id );

			if ( false !== $media ) {
				return $media;
			}

			global $wpdb;

			// Activity table.
			$media_table = $wpdb->prefix . 'rt_rtm_media';

			// Sql query for getting activity record by activity id.
			$media = $wpdb->get_results( // @codingStandardsIgnoreLine
				$wpdb->prepare(
					"SELECT * FROM {$media_table} WHERE activity_id = %d", // @codingStandardsIgnoreLine
					intval( $activity_id )
				),
				ARRAY_A
			);

			if ( ! empty( $media ) ) {
				set_transient( 'bprpa_media_activity_' . $activity_id, $media, 24 * HOUR_IN_SECONDS );
			}

			return $media;
		}

		/**
		 * Check if the page is activity stream of user activity, group activity or main activity.
		 *
		 * @return bool
		 */
		public function bprpa_is_activity_strem() {

			// Bail, if anything goes wrong.
			if ( ! function_exists( 'bp_is_current_component' ) ||
				! function_exists( 'bp_is_single_activity' ) ||
				! function_exists( 'bp_is_group_activity' ) ||
				! function_exists( 'bp_get_option' ) ) {

				return false;

			}

			// Get option value for settings enabled or not.
			$option_value = bp_get_option( '_bprpa_enable_setting', 1 );
			// error_log("_bprpa_enable_setting: " . var_export($option_value, true));
			// var_dump($option_value); die;

			// If settings is disable, then false.
			if ( 1 != $option_value ) {
				return false;
			}

			// If it's activity stram of user activity, group activity or main activity.
			if ( is_user_logged_in() &&
				bp_is_current_component( 'activity' ) &&
				( ! bp_is_single_activity() ||
				bp_is_group_activity() ) ) {

				return true;

			}

			return false;
		}

		/**
		 * Clone rtmedia data.
		 *
		 * @param  string $updated_content Activity content.
		 * @param  int    $user_id         User ID.
		 * @param  int    $activity_id     Activity ID.
		 * @return void
		 */
		public function bprpa_save_media( $updated_content, $user_id, $activity_id ) {

			// Bail, if anything goes wrong.
			if ( ! class_exists( 'RTMediaBuddyPressActivity' ) ||
				empty( $user_id ) ||
				empty( $activity_id ) ) {
				return;
			}

			// Get activity id which we are going to re-post.
			$original_item_id = filter_input( INPUT_POST, 'original_item_id', FILTER_SANITIZE_NUMBER_INT );

			if ( empty( $original_item_id ) ) {
				return;
			}

			/* Save media */
			$media = $this->bprpa_get_media( $original_item_id );

			if ( ! empty( $media ) ) {

				global $wpdb;

				// Media table.
				$media_table = $wpdb->prefix . 'rt_rtm_media';

				foreach ( $media as $copied_media ) {

					if ( isset( $copied_media['id'] ) ) {
						unset( $copied_media['id'] );
					}

					// Set new activity id.
					if ( isset( $copied_media['activity_id'] ) ) {
						unset( $copied_media['activity_id'] );
						$copied_media['activity_id'] = $activity_id;
					}

					// Set new activity author id.
					if ( isset( $copied_media['media_author'] ) ) {
						unset( $copied_media['media_author'] );
						$copied_media['media_author'] = $user_id;
					}

					// Insert data.
					$wpdb->insert( // @codingStandardsIgnoreLine
						$media_table,
						$copied_media
					);

				}

				$media_activity_text = bp_activity_get_meta( $original_item_id, 'bp_activity_text' );

				// Update activity text.
				if ( ! empty( $media_activity_text ) ) {
					bp_activity_update_meta( $activity_id, 'bp_activity_text', bp_activity_filter_kses( $media_activity_text ) );
				}
			}
			/* End Save media */
		}

		/**
		 * Save Repost activity status in meta.
		 *
		 * @param  string $updated_content Activity content.
		 * @param  int    $user_id         User ID.
		 * @param  int    $activity_id     Activity ID.
		 * @return void
		 */
		public function bprpa_save_repost_status( $updated_content, $user_id, $activity_id ) {
			if ( 'repost' === $updated_content ) {
				bp_activity_update_meta( $activity_id, 'bp_activity_reposted', true );
			}
		}

		/**
		 * Save Group repost activity status in meta.
		 *
		 * @param  string $updated_content Activity content.
		 * @param  int    $user_id         User ID.
		 * @param  int    $group_id        Group ID.
		 * @param  int    $activity_id     Activity ID.
		 * @return void
		 */
		public function bprpa_save_group_repost_status( $updated_content, $user_id, $group_id, $activity_id ) {
			if ( 'repost' === $updated_content ) {
				bp_activity_update_meta( $activity_id, 'bp_activity_reposted', true );
			}
		}

		/**
		 * Show Reposted Text on Activity header.
		 *
		 * @param string $text Previous bp_get_activity_action Text.
		 */
		public function bprpa_show_repost_status( $text ) {
			$activity_id   = bp_get_activity_id();
			$repost_status = bp_activity_get_meta( $activity_id, 'bp_activity_reposted', true );

			if ( ! $repost_status ) {
				return $text;
			}

			$repost_status_icon = sprintf(
				'<span class="dashicons dashicons-controls-repeat bprpa-share-icon bp-tooltip" data-bp-tooltip="%1$s"></span>',
				esc_html__( 'Reposted', 'bp-repost-activity' )
			);

			return $text . ' ' . $repost_status_icon;
		}

		/**
		 * Repost Button.
		 *
		 * @return void
		 */
		public function bprpa_repost_button_new() {

			// Bail, if anything goes wrong.
			if ( ! $this->bprpa_is_activity_strem() || ( function_exists( 'bp_get_activity_type' ) && 'activity_update' !== bp_get_activity_type() && 'rtmedia_update' !== bp_get_activity_type() ) ) {
				return;
			}

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			printf(
				'<a href="#" class="button item-button bp-secondary-action bp-tooltip bp-repost-activity" data-bp-tooltip="%1$s" data-activity_id="%2$s" aria-pressed="false"><span class="bb-icon-repeat"></span> <span class="repost-button">%3$s</span><span class="bp-screen-reader-text">%3$s</span></a>',
				esc_attr__( 'Re-post', 'bp-repost-activity' ),
				esc_attr( bp_get_activity_id() ),
				esc_html__( 'Re-Post', 'bp-repost-activity' )
			);
		}

		/**
		 * Copy activity metadata.
		 *
		 * @param  string $updated_content Activity content.
		 * @param  int    $user_id         User ID.
		 * @param  int    $activity_id     Activity ID.
		 * @return void
		 */
		public function bprpa_copy_meta_data_profile( $updated_content, $user_id, $activity_id ) {

			// Bail, if anything goes wrong.
			if ( empty( $user_id ) ||
				empty( $activity_id ) ||
				! function_exists( 'bp_activity_get_meta' ) ||
				! function_exists( 'bp_activity_update_meta' ) ) {
				return;
			}

			// Get original activity ID.
			$original_activity_id = filter_input( INPUT_POST, 'original_item_id', FILTER_SANITIZE_NUMBER_INT );

			// If not copied, then don't do anything.
			if ( empty( $original_activity_id ) ) {
				return;
			}

			// In case to identify.
			bp_activity_update_meta( $activity_id, 'bp_original_activity_id', $original_activity_id );

			// Copy metadata.
			$this->bprpa_copy_activity_meta_data( $original_activity_id, $activity_id );
		}

		/**
		 * Copy metadata.
		 *
		 * @param string  $content Activity Content.
		 * @param integer $user_id User ID.
		 * @param integer $group_id Group ID.
		 * @param integer $activity_id Activity ID.
		 * @return void
		 */
		public function bprpa_copy_meta_data_group( $content, $user_id, $group_id, $activity_id ) {

			// Bail, if anything goes wrong.
			if ( empty( $user_id ) ||
				empty( $group_id ) ||
				empty( $activity_id ) ||
				! function_exists( 'bp_activity_get_meta' ) ||
				! function_exists( 'bp_activity_update_meta' ) ) {
				return;
			}

			// Get original activity ID.
			$original_activity_id = filter_input( INPUT_POST, 'original_item_id', FILTER_SANITIZE_NUMBER_INT );

			// If not copied, then don't do anything.
			if ( empty( $original_activity_id ) ) {
				return;
			}

			// In case to identify.
			bp_activity_update_meta( $activity_id, 'bp_original_activity_id', $original_activity_id );

			// Copy metadata.
			$this->bprpa_copy_activity_meta_data( $original_activity_id, $activity_id );
		} // End bprpa_copy_meta_data_group().

		/**
		 * Add reost icon to activity.
		 *
		 * @param  string $action   Action.
		 * @param  object $activity Activity object.
		 * @return string
		 */
		public function bprpa_repost_activity_action( $action, $activity ) {

			// Bail, if anything goes wrong.
			if ( empty( $activity ) || ! function_exists( 'bp_activity_get_meta' ) ) {
				return $action;
			}

			// Get original activity ID.
			$original_activity_id = bp_activity_get_meta( $activity->id, 'bp_original_activity_id' );

			// If not rposted, then don't do anything.
			if ( empty( $original_activity_id ) ) {
				return $action;
			}

			// Reposted action.
			$action = '<span class="bb-icon-repeat"></span>&nbsp;' . $action;

			return $action;
		}

		/**
		 * Repost content.
		 *
		 * @param string $content Activity Content.
		 * @param object $activity Activity Object.
		 * @return html
		 */
		public function bprpa_repost_activity_content_body( $content, $activity ) {

			// If share as current user, don't do anything.
			$share_as_you_enabled = function_exists( 'bbslt_get_custom_setting' )
				? bbslt_get_custom_setting( 'activity_setting', 're_share_as_loggedin' )
				: false;

			if ( $share_as_you_enabled ) {
				return $content;
			}

			// Bail, if anything goes wrong.
			if ( ! function_exists( 'bp_activity_get_meta' ) || empty( $activity ) || empty( $content ) ) {
				return $content;
			}

			// Get original activity ID.
			$original_activity_id = bp_activity_get_meta( $activity->id, 'bp_original_activity_id' );

			// Bail, if no original activity ID.
			if ( empty( $original_activity_id ) ) {
				return $content;
			}

			// Get original activity object.
			$original_activity = $this->bprpa_bp_activity_get_original( array( 'in' => $original_activity_id ) );

			if ( empty( $original_activity ) ) {
				return $content;
			}

			// Original activity author.
			$orig_activity_author = $original_activity->user_id;

			// Get original activity time since.
			$orig_activity_time_since = bp_core_time_since( $original_activity->date_recorded );

			// Get original activity author details.
			$orig_activity_author_link = bp_core_get_user_domain( $orig_activity_author );
			$orig_activity_author_name = bp_core_get_user_displayname( $orig_activity_author );

			ob_start();
			?>
			<div class="bbars-repost-content">
			<?php
			printf(
				'<p class="bprpa-repost-info">%1$s <a href="%2$s" class="bp-tooltip" data-bp-tooltip="%3$s">@%3$s</a> - <a href="%4$s" class="bprpa-repost-time">%5$s</a> %6$s</p>',
				esc_html__( 'Reposted from', 'bp-repost-activity' ),
				esc_url( $orig_activity_author_link ),
				esc_html( $orig_activity_author_name ),
				esc_url( bp_activity_get_permalink( $original_activity_id ) ),
				esc_html( $orig_activity_time_since ),
				// translators: Reposted content.
				sprintf( esc_html__( 'wrote: %s', 'bp-repost-activity' ), '' )
			);

			$activity_content = $this->bprpa_get_activity( $original_activity_id );

			if ( isset( $activity_content->content ) && ! empty( $activity_content->content ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo apply_filters( 'bp_get_activity_content_body', $activity_content->content, $activity_content );
			}
			?>
			</div>
			<?php
			$content = ob_get_clean();

			return $content;
		}

		/**
		 * Custom styles.
		 *
		 * @return void
		 */
		public function bbrpa_repost_custom_style() {
			?>
			<style>
				.activity-item .bbars-repost-content {
					border: 1px solid var(--bb-content-border-color);
					border-radius: var(--bb-block-radius);
					padding: 15px 15px 10px;
				}
				.activity-item .activity-meta .repost-button {
					color: var(--bb-headings-color) !important;
				}
				.activity-meta .generic-button .bb-icon-repeat:before {
					font-size: 24px;
				}
				.modal-content .content {
					border: 1px solid var(--bb-content-border-color);
					padding: 15px 15px 10px;
					border-radius: var(--bb-block-radius-inner);
				}
				.modal-content .content .activity-discussion-title-wrap a {
					color: var(--bb-headings-color);
				}
				.modal-content .content .bb-content-inr-wrap {
					display: table;
					width: 100%;
					box-sizing: border-box;
				}
				.modal-content .content .bb-content-inr-wrap .gamipress-buddypress-user-details {
					margin-left: 10px;
					position: relative;
					display: -ms-flexbox;
					display: flex;
					-ms-flex-wrap: wrap;
					flex-wrap: wrap;
					-webkit-box-align: center;
					-ms-flex-align: center;
					align-items: center;
					margin: 10px 0 0;
				}
				.modal-content .content .bb-content-inr-wrap .gamipress-buddypress-user-details .gamipress-buddypress-ranks {
					-webkit-box-pack: start;
					-ms-flex-pack: start;
					justify-content: flex-start;
				}
				.modal-content .content .bb-content-inr-wrap .gamipress-buddypress-user-details .gamipress-buddypress-ranks .gamipress-buddypress-rank {
					display: -webkit-inline-box;
					display: -ms-inline-flexbox;
					display: inline-flex;
					-webkit-box-align: center;
					-ms-flex-align: center;
					align-items: center;
					border: 1px solid var(--bb-content-border-color);
					padding: 3px 4px;
					box-shadow: 0 1px 2px rgba(18, 43, 70, .12);
					font-size: 13px;
					color: var(--bb-body-text-color);
					background-color: var(--bb-content-background-color);
					border-radius: var(--bb-block-radius-inner);
					line-height: 1.5;
					-webkit-transition: all ease .3s;
					transition: all ease .3s;
				}
				.modal-content .content .bb-content-inr-wrap .gamipress-buddypress-user-details {
					margin-left: 10px;
					position: relative;
				}
				.modal-content .content .activity-inner-meta {
					padding: 12px;
					border-top: 1px solid var(--bb-content-border-color);
					display: -webkit-box;
					display: -ms-flexbox;
					display: flex;
					-webkit-box-align: center;
					-ms-flex-align: center;
					align-items: center;
					-ms-flex-flow: row wrap;
					flex-flow: row wrap;
					margin-bottom: 15px
				}
				.modal-content .content .activity-inner-meta .generic-button {
					margin: 0 10px 0 0;
				}
				.modal-content .content .activity-inner-meta .generic-button a.button {
					border: none;
					padding: 0;
					margin: 0;
					min-height: auto;
					min-width: inherit;
					color: var(--bb-primary-color);
					background-color: transparent;
				}
				.modal-content .content .activity-inner-meta .generic-button a.button .comment-count {
					margin-bottom: 0;
					position: relative;
					padding-left: 25px;
					font-weight: 400;
					font-size: 13px;
					color: var(--bb-alternate-text-color);
				}
				.modal-content .content .activity-inner-meta .generic-button a.button:before {
					content: '';
				}
				.modal-content .content .activity-inner-meta .generic-button a.button.bb-icon-comments-square .comment-count:before {
					content: "\ee37";
				}
				.modal-content .content .activity-inner-meta .generic-button a.button.bb-icon-comment .comment-count:before {
					content: "\e979";
				}
				.modal-content .content .activity-inner-meta .generic-button a.button .comment-count:before {
					font-size: 18px;
					font-family: bb-icons;
					display: inline-block;
					position: absolute;
					left: 0;
					top: 50%;
					margin-top: -14px;
					line-height: 1.6;
					color: var(--bb-primary-color);
				}
			</style>
			<?php
		}

		/**
		 * Get original activity.
		 *
		 * @param array $args Array of arguments.
		 * @return object
		 */
		public function bprpa_bp_activity_get_original( $args ) {

			if ( empty( $args ) || ! function_exists( 'bp_activity_get' ) ) {
				return;
			}

			$activity   = '';
			$activities = bp_activity_get( $args );

			if ( ! empty( $activities ) ) {
				$activity = isset( $activities['activities'][0] )
					? $activities['activities'][0]
					: '';
			}

			return $activity;
		}

		/**
		 * Copy activity meta data.
		 *
		 * @param int $original_activity_id Activity id copy from.
		 * @param int $copy_activity_id     Activity id copy to.
		 * @return void
		 */
		public function bprpa_copy_activity_meta_data( $original_activity_id, $copy_activity_id ) {

			// Bail, if anything goes wrong.
			if ( empty( $copy_activity_id ) ||
				empty( $original_activity_id ) ||
				! function_exists( 'bp_activity_get_meta' ) ||
				! function_exists( 'bp_activity_update_meta' ) ) {
				return;
			}

			// Get activity meta.
			$activity_meta = bp_activity_get_meta( $original_activity_id );

			if ( ! empty( $activity_meta ) ) {

				$not_allowed_meta = array(
					'_link_embed',
					'_link_preview_data',
					'bp_favorite_users',
					'favorite_count',
					'activity_bump_date',
				);

				// Save activity meta.
				foreach ( $activity_meta as $key => $value ) {

					if ( ! empty( $value ) ) {

						// Skip saving.
						if ( in_array( $key, $not_allowed_meta, true ) ) {
							continue;
						}

						$final_val = maybe_unserialize( $value[0] );

						// Update meta.
						bp_activity_update_meta( $copy_activity_id, $key, $final_val );

					}
				}
			}
		}
	}
}

new BP_Repost_Activity();
