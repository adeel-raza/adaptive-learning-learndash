<?php
namespace AL;
/**
 * Adaptive Learning With LearnDash Post Types
 *
 * @author   WooNinjas
 * @category Admin
 * @package  AdaptiveLearningWithLearnDash/Admin/PostTypes
 * @version  1.4
 */

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

/**
 * AL_Post_Types Class.
 */
class AL_Post_Types {

    /**
     * Hook in tabs.
     */
    public function __construct () {
        add_filter ( "learndash_post_args", array( __CLASS__, "register_post_types" ), 10, 1 );
        add_filter ( "from_percentage_option_html", array( __CLASS__, "percentage_to_field" ), 10, 1 );
        add_action ( "current_screen", array( __CLASS__, "add_help_tab" ) );
    }

    public function percentage_to_field ( $html ) {
        $html = "%";
        return $html;
    }

    /**
     * Course Level Post Type
     *
     * @param $post_args
     * @return mixed
     */
    public static function register_post_types ( $post_args ) {

        $course_levels_labels = array (
            "name" 					=> 	_x ( "Course Levels", "ld-adaptive-learning" ),
            "singular_name" 		=> 	_x ( "Course Level", "ld-adaptive-learning" ),
            "add_new" 				=> 	_x ( "Add New Level", "Add New Course Level", "ld-adaptive-learning" ),
            "add_new_item" 			=> 	_x ( "Add New Course Level", "Add New Course Label", "ld-adaptive-learning" ),
            "edit_item" 			=> 	_x ( "Edit Course Level", "Edit Course Label", "ld-adaptive-learning" ),
            "new_item" 				=> 	_x ( "New Course Level", "New Course Label", "ld-adaptive-learning" ),
            "all_items" 			=> 	_x ( "Course Levels", "al" ),
            "view_item" 			=> 	_x ( "View Course Level", "View Course Label", "ld-adaptive-learning" ),
            "search_items" 			=> 	_x ( "Search Course Level", "Search Courses Label", "ld-adaptive-learning" ),
            "not_found" 			=> 	_x ( "No Course Level found", "No Courses found Label", "ld-adaptive-learning" ),
            "not_found_in_trash" 	=> 	_x ( "No Course Level found in Trash", "No Courses found in Trash Label", "ld-adaptive-learning" ),
            "parent_item_colon" 	=> 	"",
        );

        $course_capabilities = array (
            "read_post"                 =>  "read_course",
            "publish_posts"             =>  "publish_courses",
            "edit_posts"                =>  "edit_courses",
            "edit_others_posts"         =>  "edit_others_courses",
            "delete_posts"              =>  "delete_courses",
            "delete_others_posts"       =>  "delete_others_courses",
            "read_private_posts"        =>  "read_private_courses",
            "edit_private_posts"        =>  "edit_private_courses",
            "delete_private_posts"      =>  "delete_private_courses",
            "delete_post"               =>  "delete_course",
            "edit_published_posts"      =>  "edit_published_courses",
            "delete_published_posts"    =>  "delete_published_courses",
        );

        $post_args["sfwd-courses-levels"] = array (
            "plugin_name"           =>  __( "course levels", "ld-adaptive-learning" ),
            "slug_name"             =>  __( "course-levels", "ld-adaptive-learning" ),
            "post_type"             =>  "sfwd-courses-levels",
            "template_redirect"     =>  true,
            "cpt_options"           =>  array (
                "hierarchical"          =>  false,
                "has_archive"           =>  false,
                "supports"              =>  array ( "title", "editor", "page-attributes" ),
                "labels"                =>  $course_levels_labels,
                "capability_type"       =>  "course",
                "show_in_menu"          =>  "learndash-lms",
                "exclude_from_search"   =>  true,
                "capabilities"          =>  $course_capabilities,
                "map_meta_cap"          =>  true
            ),
            "fields"                =>  array (
                "from_percentage"       =>  array (
                    "name"              =>  __( "From Course Level", "ld-adaptive-learning" ),
                    "type"              =>  "number",
                    "help_text"         =>  __( "Enter minimum percentage without % sign like 30.", "ld-adaptive-learning" ),
                    "default"           =>  "0"
                ),
                "to_percentage"         =>  array (
                    "name"              =>  __( "To Course Level", "ld-adaptive-learning" ),
                    "type"              =>  "number",
                    "help_text"         =>  __( "Enter maximum percentage without % sign like 50.", "ld-adaptive-learning" ),
                    "default"           =>  "100"
                )
            )
        );
        return $post_args;
    }

    /**
     * Add Help Tab
     */
    public static function add_help_tab () {
        $screen = get_current_screen();
        if ( $screen->base != "post" || $screen->post_type != "sfwd-courses-levels" )
            return;

        $screen->add_help_tab ( array (
            "id"	    => "al-ld-course-levels",
            "title"	    => __( "Course Levels", "ld-adaptive-learning" ),
            "content"	=>
                  "<p>" . __( "Adaptive Learning With LearnDash creates a new field inside each LearnDash course, this 
                field is called the course level, this field shows up only for a course assigned a prerequisite course, that is to say It is a “child course”.","ld-adaptive-learning" ) . "</p>".
                  "<p>" . __( "When a user completes a parent course and passes quiz/quizzes in that course the result
                 percentage is calculated and if there are more than 1 quizzes in the course the average percentage 
                 is determined.", "ld-adaptive-learning" ) . "</p>" .
                "<p>" . __( "The course levels provide “from” and “to” percentage 
                 mapping for the parent course results. The from and to percentage field can define a “level” for the 
                 child course. Whenever the parent results correspond to the percentages defined for a course level,
                  then, that particular “child course” which was assigned that course level will be assigned 
                  to the student.", "ld-adaptive-learning" ) . "</p>" .
                "<p>" . __( "Therefore, students will be assigned corresponding child courses depending on 
                their performance in the deterministic/prerequisite course.", "ld-adaptive-learning" ) . "</p>"
        ) );
        /*$screen->add_help_tab ( array(
            "id"	    => "al-ld-description",
            "title"	    => __( "Description", "ld-adaptive-learning" ),
            "content"	=>
                "<h3>" . __( "Description", "ld-adaptive-learning" ) . "</h3>" .
                "<p>" . __( "When a user completes a parent course and passes quiz/quizzes in that course the result
                 percentage is calculated and if there are more than 1 quizzes in the course the average percentage 
                 is determined.", "ld-adaptive-learning" ) . "</p>" .
                "<p>" . __( "The course levels provide “from” and “to” percentage 
                 mapping for the parent course results. The from and to percentage field can define a “level” for the 
                 child course. Whenever the parent results correspond to the percentages defined for a course level,
                  then, that particular “child course” which was assigned that course level will be assigned 
                  to the student.", "ld-adaptive-learning" ) . "</p>" .
                "<p>" . __( "Therefore, students will be assigned corresponding child courses depending on 
                their performance in the deterministic/prerequisite course.", "ld-adaptive-learning" ) . "</p>"
        ) );*/
        $screen->add_help_tab ( array(
            "id"	    => "al-ld-rules",
            "title"	    => __( "Rules", "ld-adaptive-learning" ),
            "content"	=>
                "<h3>" . __( "Fields", "ld-adaptive-learning" ) . "</h3>" .
                "<p>" . __( "You can set minimum and maximum percentages for levels", "ld-adaptive-learning" ) . "</p>" .
                "<p>" . __( "<strong>From Course Level: </strong>Minimum percentage required to assinged this level to students", "ld-adaptive-learning" ) . "</p>" .
                "<p>" . __( "<strong>To Course Level: </strong>Miximum percentage required to assinged this level to students", "ld-adaptive-learning" ) . "</p>" .
                "<h4>" . __( "Rules:", "ld-adaptive-learning" ) . "</h4>" .
                "<p><ul><li>" . __( "Enter percentage number without % sign, i,e 80 for 80%", "ld-adaptive-learning" ) . "</li>" .
                "<li>" . __( "Minimum percentage should not be less than 0", "ld-adaptive-learning" ) . "</li>" .
                "<li>" . __( "Miximum percentage should not be greater than 100", "ld-adaptive-learning" ) . "</li>" .
                "<li>" . __( "Minimum percentage should not be greater than maximum percentage.", "ld-adaptive-learning" ) . "</li></ul></p>"
        ) );
    }
}