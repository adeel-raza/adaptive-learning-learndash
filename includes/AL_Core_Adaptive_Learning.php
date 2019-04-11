<?php
namespace AL;
/**
 * Assign child course based on parent course performance/adaptive learning implementation
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
 * AL_Core_Adaptive_Learning Class.
 */
class AL_Core_Adaptive_Learning {

    static protected $user;
    static protected $user_id;
    static protected $parent_course;
    static protected $parent_course_id;
    static protected $child_course;
    static protected $level;
    static protected $from_perc;
    static protected $to_perc;
    static protected $avg_perc;
    static protected $compare_field = "sfwd-courses_course_prerequisite_compare";

    public function __construct () {
        add_action( "init", array ( __CLASS__, "set_user_id" ) );
        add_action( "learndash_course_completed", array ( __CLASS__, "course_completed" ), 10, 1 );
        add_action( "show_user_profile", array( __CLASS__, "al_stats_fields" ) );
        add_action( "edit_user_profile", array( __CLASS__, "al_stats_fields" ) );
        add_action( "edit_user_profile_update", array( __CLASS__, "admin_user_update" ) );
        add_action( "admin_init", array( __CLASS__, "dismiss_branding_notification" ) );
        add_action( "admin_notices", array( __CLASS__, "furthur_assistance_review" ) );
        add_action( "wp_footer", array( __CLASS__, "course_assign_notification_initiate" ) );
        add_filter( 'ld_after_course_status_template_container', array ( __CLASS__, 'add_content_after_course_status' ), 10, 4 );
    }

    /**
     * Display course assigning Notification
     *
     * @return bool
     */
    public function course_assign_notification_initiate() {

        if( ! is_singular( 'sfwd-courses' ) ) {
            return false;
        }

        if( ! is_user_logged_in() ) {
            return false;
        }

        $course_id = get_the_ID();
        if( !$course_id || get_post_type( $course_id ) != 'sfwd-courses' ) {
            return false;
        }

        $user_id = get_current_user_id();

        $meta_key = 'notified_' . $course_id;

        $course_status = learndash_course_status( $course_id, $user_id );
        if( 'completed' != strtolower( $course_status ) ) {
            delete_user_meta( $user_id, $meta_key );
            return false;
        }

        $notification_detail = get_user_meta( $user_id, 'ld_adaptive_learning_stats', true );
        if( ! $notification_detail ) {
            return false;
        }

        if( 'true' == get_user_meta( $user_id, $meta_key, true ) ) {
            return false;
        }

        foreach ( $notification_detail as $p_course_name => $stat ) {
            $course_detail = get_page_by_title( $p_course_name, OBJECT, get_post_type( $course_id ) );

            if( $course_detail->ID == $course_id ) {
                $notification_message =  self::get_notification_message( $course_status, $course_id, $user_id );
                ?>
                <script>
                    var bodyElement = document.getElementsByTagName("body");
                    bodyElement[0].innerHTML += '<div class="course-assign-notification-wrapper">' +
                        '<div class="assign-notification"><?php echo $notification_message; ?></div>' +
                        '</div>';

                    var elem = document.getElementsByClassName( 'course-assign-notification-wrapper' );
                    var pos = -400;
                    var id = setInterval( slideAni, 1 );
                    function slideAni() {
                        if ( pos == 50 ) {
                            clearInterval( id );
                        } else {
                            pos += 10;
                            elem[0].style.right = pos + 'px';
                        }
                    }



                    setTimeout( function() {
                        var pos = 50;
                        var id = setInterval( slideAniback, 1 );
                        function slideAniback() {
                            if ( pos == -400 ) {
                                clearInterval( id );
                            } else {
                                pos -= 10;
                                elem[0].style.right = pos + 'px';
                            }
                        }
                    }, 5000 );

                    setTimeout( function() {
                        elem[0].remove();
                    }, 5500 );
                </script>
                <?php
                update_user_meta( $user_id, $meta_key, 'true' );
            }
        }
    }

    /**
     * Show user stats as a table
    */
    public static function al_stats_fields () {
        $stats = get_user_meta( self::$user_id, "ld_adaptive_learning_stats", 1 );
        
        if( !$stats ) {
            return false;
        }

        echo "<h2>" . __( "Adaptive Learning Stats:", "ld-adaptive-learning" ) . "</h2> <hr />";

        foreach ( $stats as $p_course_name => $stat ) {
            ?>
            <h3><?php _e ( $p_course_name ); ?></h3>
            <div class="stats_wrapper">
                <p><strong><?php _e ( "Child Course", "ld-adaptive-learning" ); ?> :</strong> <?php _e ( $stat["child"] ); ?></p>
                <p><strong><?php _e ( "Child Level", "ld-adaptive-learning" ); ?> :</strong> <?php _e ( $stat["level"] ); ?></p>
                <p><strong><?php _e ( "From Percentage", "ld-adaptive-learning" ); ?> :</strong> <?php _e ( $stat["from_perc"] ); ?></p>
                <p><strong><?php _e ( "To Percentage", "ld-adaptive-learning" ); ?> :</strong> <?php _e ( $stat["to_perc"] ); ?></p>
                <p><strong><?php _e ( "Avg. Course Percenatge", "ld-adaptive-learning" ); ?> :</strong> <?php _e ( $stat["avg_perc"] ); ?></p>
                <p><strong><?php _e ( "Course Assignment Time", "ld-adaptive-learning" ); ?> :</strong> <?php _e ( $stat["time"] ); ?></p>
            </div>

            <?php
        }
    }

    /**
     * Sets user ID variable on init
     */
    public static function set_user_id () {

        /**
         * If course completed from admin user_id from GET var
         */
        if ( isset ( $_POST["user_id"] ) ) {
            self::$user_id = intval( $_POST["user_id"] );
        } elseif ( isset ( $_GET["user_id"] ) ) {
            self::$user_id = intval ( $_GET["user_id"] );
        }

        if( !self::$user_id ) {
            self::$user = wp_get_current_user();
            self::$user_id = get_current_user_id();
        }

        return self::$user_id;
    }

    /**
     * Implements the LD `course_completed` hook
     *
     * @param course data $data
     * @return null
     */

    public static function course_completed ( $data ) {

        // If not parent no need to proceed with calculations
        $is_parent_course = is_parent_course ( $data["course"] );
        $course = $data["course"] ;

        if( !self::$user_id ) {
            self::$user_id = get_current_user_id();
        }
        if ( !self::$user_id || !$is_parent_course ) {
            return false;
        }

        //quiz ids for this course
        $quiz_ids = array();
        $course = get_post( $course->ID );
        $quiz_ids = get_quiz_ids_for_course ( $course->ID );
        $quiz_data = self::get_quiz_data ( self::$user_id );
        if ( !$quiz_data ) {
            return false;
        }

        foreach ( $quiz_data as $key => $single_quiz ) {
            if ( !in_array ( $single_quiz["quiz"], $quiz_ids ) ) {
                unset ( $quiz_data[$key] );
            }
        }

        if ( !$quiz_data ) {
            return false;
        }

        // save parent course ID and Name 
        self::$parent_course_id = $course->ID;
        self::$parent_course = $course->post_title;
        $course_level = self::get_course_level ( $quiz_data, self::$parent_course_id );

        $level_id = self::get_course_level_id ( $course_level );
        self::assigned_course ( $level_id );

        if ( !self::$child_course ) {
            return false;
        }

        $stats = get_user_meta ( self::$user_id, "ld_adaptive_learning_stats", 1 );
        
        if( ! isset( $stats ) || !is_array( $stats ) ) {
            $stats = [];
        }

        if( !isset( $stats[self::$parent_course] ) || !is_array( $stats[self::$parent_course] )  ) {
            $stats[self::$parent_course] = [];
        }

        $stats[self::$parent_course]["child"] = self::$child_course;
        $stats[self::$parent_course]["level"] = self::$level;
        $stats[self::$parent_course]["from_perc"] = self::$from_perc;
        $stats[self::$parent_course]["to_perc"] = self::$to_perc;
        $stats[self::$parent_course]["avg_perc"] = self::$avg_perc;
        $stats[self::$parent_course]["time"] = date ( "d M Y H:i:s", current_time ( "timestamp", 0 ) );
        
        $stats = apply_filters ( "ld_al_stats_array", $stats );

        /**
         * Save Stats Info in User Meta
         */
        update_user_meta ( self::$user_id, "ld_adaptive_learning_stats", $stats );
    }

    /**
     * Get Notification Message
     *
     * @param $course_status
     * @param $course_id
     * @param $user_id
     * @return bool|mixed|void
     */
    public static function get_notification_message( $course_status, $course_id, $user_id ) {
        if( ! is_singular( 'sfwd-courses' ) ) {
            return false;
        }

        if( ! is_user_logged_in() ) {
            return false;
        }

        if( !$course_id || get_post_type( $course_id ) != 'sfwd-courses' ) {
            return false;
        }

        if( 'complete' != strtolower( $course_status ) || 'completed' != strtolower( $course_status ) ) {

            $notification_detail = get_user_meta( $user_id, 'ld_adaptive_learning_stats', true );
            if( ! $notification_detail ) {
                return false;
            }

            if ( is_parent_course ( get_post( $course_id ) ) ) {
                foreach( $notification_detail as $key => $course_stats ) {
                    $assigned_course = $course_stats['child'];
                    $assigned_course = get_page_by_title( $assigned_course, OBJECT, get_post_type( $course_id ) );
                    return apply_filters( 'notification_message', __( 'You have been assigned to the ' . get_the_title( $assigned_course->ID ), 'ld-adaptive-learning' ), $user_id, $course_id );
                }
            }
        }
    }

    /**
     * Display Associated Course
     *
     * @param $output
     * @param $course_status
     * @param $course_id
     * @param $user_id
     * @return bool|string
     */
    public static function add_content_after_course_status( $output, $course_status, $course_id, $user_id ) {

        if( ! is_singular( 'sfwd-courses' ) ) {
            return false;
        }

        if( ! is_user_logged_in() ) {
            return false;
        }

        if( !$course_id || get_post_type( $course_id ) != 'sfwd-courses' ) {
            return false;
        }

        if( 'complete' != strtolower( $course_status ) ) {
            return false;
        }

        $notification_detail = get_user_meta( $user_id, 'ld_adaptive_learning_stats', true );
        if( ! $notification_detail ) {
            return false;
        }

        if ( is_parent_course ( get_post( $course_id ) ) ) {

            $notification_message = self::get_notification_message( $course_status, $course_id, $user_id );
            $output = '<p>';
            $output .= '<strong>'. __( 'Associated Course: ', 'ld-adaptive-learning' ) .'</strong>';
            $output .= $notification_message;
            $output .= '</p>';
        }

        return $output;
    }
    
    public static function child_message( $att ) {
        $content = "ok";
        $defaults = array(
			'content'	=> $content,
			'course_id' => 1861,
			'user_id'	=> get_current_user_id(),
			'autop'		=> true
        );
        $att .= $defaults;
        return  $att;

    }
    /**
     * Uses the code from `learndash_profile` shortcode in ld-course-info-widget.php
     *
     * @param $user_id
     * @return $quiz_attempts (Quiz datas)
     */
    public static function get_quiz_data ( $user_id ) {
        $atts["user_id"]= $user_id;

        $usermeta = get_user_meta ( $atts["user_id"], "_sfwd-quizzes", true );
        $quiz_attempts_meta = empty ( $usermeta ) ? false : $usermeta;
        $quiz_attempts = array();

        if ( ! empty( $quiz_attempts_meta ) ) {
            foreach ( $quiz_attempts_meta as $quiz_attempt ) {
                $c = learndash_certificate_details ( $quiz_attempt["quiz"], $atts["user_id"] );
                $quiz_attempt["post"] = get_post ( $quiz_attempt["quiz"] );
                $quiz_attempt["percentage"] = !empty ( $quiz_attempt["percentage"] ) ? $quiz_attempt["percentage"] : ( !empty ( $quiz_attempt["count"] ) ? $quiz_attempt["score"] * 100 / $quiz_attempt["count"] : 0 );
                
                if ( $atts["user_id"] == self::$user_id && !empty( $c["certificateLink"] ) && ( ( isset ( $quiz_attempt["percentage"] ) && $quiz_attempt["percentage"] >= $c["certificate_threshold"] * 100 ) ) ) {
                    $quiz_attempt["certificate"] = $c;
                }

                $quiz_attempts[] = $quiz_attempt;
            }
        }
        return $quiz_attempts;
    }

    /**
     * Evaluate Course level from Quiz Percentages
     *
     * @param $quiz_data
     * @return course level
     */
    public static function get_course_level ( $quiz_data, $p_course_id ) {
        if ( !$quiz_data || !is_array ( $quiz_data ) ) {
            return false;
        }
        $perc = 0;
        $total_quiz = count ( $quiz_data );

        // Calculates the avg perc for perc of all quiuzzes
        foreach ( $quiz_data as $data ) {
            $perc = $perc + $data["percentage"];
        }
        $avg_perc = $perc / $total_quiz;
        
        // save avg_perc
        self::$avg_perc = $avg_perc;
        
        $arr = get_user_meta( self::$user_id, "ld_al_pre_req_course_perc", 1 );

        $arr[$p_course_id] = self::$avg_perc;

        // save avg perc for the course
        update_user_meta( self::$user_id , "ld_al_pre_req_course_perc", $arr );
        return $avg_perc;
    }

    /**
     * Evaluate Course level for All Pre-Reqiuisite course quizzes
     *
     * @param $quiz_data
     * @return course level
     */
    public static function get_all_course_level ( $arr, $total_courses ) {

        // Calculates the avg perc for perc of all quiuzzes
        $total_perc = 0;
        if ( is_array( $arr ) && count( $arr ) > 0 ) {
            foreach ( $arr as $perc ) {
                $total_perc = $total_perc + $perc;
            }
        }

        $avg_perc = $total_perc / $total_courses;
        
        // save avg_perc
        self::$avg_perc = $avg_perc;

        return $avg_perc;
    }

    /**
     * Get course level id
     *
     * @param $course_level
     * @return bool
     */
    public static function get_course_level_id ( $course_level ) {
        if( !$course_level ) {
            if( $course_level != 0 ) {
            return false;
            }
        }
        $courses_level_args = array (
            "posts_per_page"   =>  -1,
            "post_type"     =>  "sfwd-courses-levels",
            "post_status"   =>  "publish"
        );
        $courses_levels = get_posts ( $courses_level_args );

        foreach ( $courses_levels as $courses_level ) {
            $level_meta = get_post_meta ( $courses_level->ID, "_sfwd-courses-levels", true );
            $from = $level_meta["sfwd-courses-levels_from_percentage"];
            $to = $level_meta["sfwd-courses-levels_to_percentage"];
            if ( $from <= $course_level && $to >= $course_level ) {
                // save level name
                self::$level = $courses_level->post_title;

                // save from perc
                self::$from_perc = $from;

                // save to perc
                self::$to_perc = $to;

                return $courses_level->ID;
            } else {
                continue;
            }
        }

        $course_level = round( $course_level );
        return self::get_course_level_id( $course_level );
    }

    /**
     * Assign child course to user
     *
     * @param $level_id
     * @return bool
     */
    public static function assigned_course ( $level_id ) {
        if ( !$level_id ) {
            return false;
        }

        $courses_args = array (
            "posts_per_page"   =>  -1,
            "post_type"     =>  "sfwd-courses",
            "post_status"   =>  "publish"
        );
        $courses = get_posts ( $courses_args );

        foreach ( $courses as $course ) {
            $is_parent_course = is_parent_course ($course);

            // If not parent no need to proceed with the loop
            if ( $is_parent_course ) {
                continue;
            }

            $course_meta = get_post_meta ( $course->ID, "_sfwd-courses", true );
            $course_prereq = $course_meta["sfwd-courses_course_prerequisite"];

            if ( ! is_array( $course_prereq ) ) {
                $p_course_id = $course_prereq;

                // If not the child of the completed course
                if ( $p_course_id != self::$parent_course_id ) {
                    continue;
                }
            } else {
                $p_course_id_arr = $course_prereq;
                
                // If not the child of the completed course
                if ( ! in_array( self::$parent_course_id, $p_course_id_arr ) ) {
                    continue;
                } 
            }

            if ( isset ( $course_meta[ self::$compare_field ] ) && $course_meta[ self::$compare_field ] == "ALL" ) {
                $total_prereq = count($course_prereq);
                $arr = get_user_meta( self::$user_id, "ld_al_pre_req_course_perc", 1 );

                $saved_prereq = 0;
                if( is_array( $arr ) ) {
                    $saved_prereq = count($arr);
                }

                // All Prereqs are completed
                if ( $saved_prereq ==  $total_prereq ) {
                    $course_level = self::get_all_course_level( $arr, $saved_prereq );   
                    $level_id = self::get_course_level_id( $course_level );
                }

            }

            $meta_level_id = (int) $course_meta["sfwd-courses_course_level"];

            if( $meta_level_id === $level_id ) {
                // save child course name
                self::$child_course = $course->post_title;

                do_action ( "ld_al_before_child_course_assign", self::$user_id, $course->ID );
                ld_update_course_access ( self::$user_id, $course->ID );
            }
        }
    }

    /**
     * Executes on user update on backend, checks if user data is deleted
    */
    public static function admin_user_update( $user_id ) {
        $logged_user_id = get_current_user_id ();
        
        if ( !current_user_can ( 'edit_user', $logged_user_id ) ) {
            return;
        }

        if ( ! learndash_is_admin_user () ) {
            return;
        }

        if ( ! empty( $user_id ) && ! empty( $_POST['learndash_delete_user_data'] ) && $user_id == $_POST['learndash_delete_user_data'] ) {
            // Remove stats if user data removed
            delete_user_meta ( $user_id, "ld_adaptive_learning_stats" ); 
        }
    }

    /**
     * Branding notification
     */
    public static function furthur_assistance_review() {
        $user_data = get_userdata( get_current_user_id() );
        $user_branding_meta = get_user_meta( get_current_user_id(), "rating_action", true );
        if( "confirmed" != $user_branding_meta && "temp_hide" != get_transient( "al-branding" ) ) {
            ?>
            <div class="notice notice-success" style="margin-top:20px;">
                <div class="dismiss-wrapper"><?php _e( 'Hi <strong>' . $user_data->user_nicename . '</strong>, If you like our plugin kindly take some time to leave a review and a rating for us <a href="https://wordpress.org/plugins/course-scheduler-for-learndash/" target="_blank" ><strong>here</strong></a> and for any <br> further support or assistance for the plugin kindly reach out to us at <br> <a href="http://wooninjas.com/contact-us" target="_blank" class="al_support" >Wooninjas</a> &nbsp;&nbsp; <form class="dismiss-form" method="post" action=""><input type="hidden" name="rating_action" value="dismiss" /><input type="hidden" name="user_id" value="'.get_current_user_id().'" /><input type="submit" class="al_no_thanks" value="No Thanks" /></form>', "al" ); ?></div>
            </div>
            <?php
            delete_transient( "al-branding" );
        }
    }

    /**
     * Dismiss branding notification
     */
    public static function dismiss_branding_notification() {
        if( isset( $_POST ) ) {
            if( isset( $_POST["rating_action"] ) && $_POST["rating_action"] == "dismiss" ) {
                if( isset( $_POST["user_id"] ) ) {
                    $user_branding_meta = get_user_meta( $_POST["user_id"], "rating_action", true );
                    if( empty( $user_branding_meta ) ) {
                        update_user_meta( $_POST["user_id"], "rating_action", "temp" );
                        if( "temp_hide" != get_transient( "al-branding" ) ) {
                            set_transient( "al-branding", "temp_hide", 604800 );
                        }
                    } elseif( "temp" ) {
                        update_user_meta( $_POST["user_id"], "rating_action", "confirmed" );
                    }
                }
            }
        }
    }
}