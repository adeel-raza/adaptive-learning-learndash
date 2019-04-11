<?php
namespace AL;
/**
 * Installation related functions and actions.
 *
 * @author   WooNinjas
 * @category Admin
 * @package  AdaptiveLearningWithLearnDash/Classes
 * @version  1.4
 */

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

/**
 * AL_Install Class.
 */
class AL_Install {

    /**
     * Hook in tabs.
     */
    public function __construct () {
        add_filter ( "learndash_post_args", array( __CLASS__, "add_course_level_field" ), 6, 1 );
    }

    /**
     * Add level field to courses
     *
     * @param $post_args
     * @return bool
     */
    public static function add_course_level_field ( $post_args ) {
        if ( !$post_args["sfwd-courses"]["fields"] ) {
            
            return false;
        }

        $courses_level_args = array (
            "posts_per_page"   =>  -1,
            "post_type"     =>  "sfwd-courses-levels",
            "post_status"   =>  "publish"
        );
        $courses_level = get_posts ( $courses_level_args );

        $options = array ( "select_level" => "Select Level" );
        foreach ( $courses_level as $course_level ) {
            $options[$course_level->ID] = $course_level->post_title;
        }

        $field_tool["name"] = "Course Level";
        $field_tool["type"] = "select";
        $field_tool["initial_options"] = $options;
        $field_tool["help_text"] = "Associate a course level with this child course";

        $fields = $post_args["sfwd-courses"]["fields"];
        $new_fields = array();
        foreach ( $fields as $key => $field ) {
            if ( $key == "course_prerequisite" ) {
                $new_fields[$key] = $field;
                $new_fields["course_level"] = $field_tool;
            } else {
                $new_fields[$key] = $field;
            }
        }

        $post_args["sfwd-courses"]["fields"] = $new_fields;

        return $post_args;
    }
}