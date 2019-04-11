<?php
namespace AL;
/**
 * Adaptive Learning With LearnDash Script Localization
 *
 * @author   WooNinjas
 * @category Admin
 * @package  AdaptiveLearningWithLearnDash/Admin/LocalizeScript
 * @version  1.4
 */

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

/**
 * AL_Localize_Script Class.
 */
class AL_Localize_Script {

    /**
     * Hook in tabs.
     */
    public function __construct () {
        add_action ( "admin_enqueue_scripts", array( $this, "localize_main_js" ), 15 );
    }

    /**
     * Localize main script
     */
    public static function localize_main_js () {
        wp_localize_script ( "al-admin-js", "globalData", self::get_localization_data() );
    }

    /**
     * Return localize data
     *
     * @return array
     */
    public static function get_localization_data () {
        $return = array();
        $screen = get_current_screen();
        if ( $screen->base != "post" || $screen->post_type != "sfwd-courses-levels" )
            return;

        $course_levels = get_posts ( array ( "post_status" => "publish", "post_per_page" => -1, "post_type" => "sfwd-courses-levels" ) );
        foreach ( $course_levels as $course_level ) {
            $return["post_obj"][$course_level->ID] = $course_level;
            $return["levels_data"][$course_level->ID] = get_post_meta ( $course_level->ID, "_sfwd-courses-levels", true );
        }
        $return["current_post"] = get_the_ID();

        return $return;
    }
}

