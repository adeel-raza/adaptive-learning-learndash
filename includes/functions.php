<?php
namespace AL;

if ( !defined( "ABSPATH" ) ) exit;

/**
 * Upgrade function
 */
function al_upgrade () {
    // upgrade process
}

/**
 * Checks if a parent course or child course
 */
function is_parent_course ( $course ) {
	if ( !$course->ID ) {

		return false;
	}

	$post_meta = get_post_meta ( $course->ID, "_sfwd-courses", 1 );
	if ( !$post_meta["sfwd-courses_course_prerequisite"] ) {

		return true;
	} else {

		return false;
	}
}

/**
 * Fetches quiz IDs by course ID
 */
function get_quiz_ids_for_course ( $id ) {
	
	if ( !$id ) {
		return false;
	}
	
	$arr = array();
	$quizzes = learndash_get_course_quiz_list( $id, null );
	if ( is_array( $quizzes ) && count( $quizzes ) > 0 ) {
		foreach ( $quizzes as $quiz ) {
			$quiz_rec = $quiz['post'];
			$arr[] = $quiz_rec->ID;
		}
	}
	
	$lessons = learndash_get_lesson_list( $id );
	if( is_array( $lessons ) && count( $lessons ) > 0 ) {
		foreach( $lessons as $lesson ) {
			$topics  = learndash_get_topic_list( $lesson->ID, $id ); 
			if( is_array( $topics ) && count( $topics ) > 0 ) {
				foreach ( $topics as $topic ) {
					$quizzes = learndash_get_lesson_quiz_list( $topic->ID, null, $id );
					if ( is_array( $quizzes ) && count( $quizzes ) > 0 ) {
						foreach ( $quizzes as $quiz ) {
							$quiz_post = $quiz['post'];
							$arr[] = $quiz_post->ID;
						}
					}
				}
			}

			$quizzes = learndash_get_lesson_quiz_list( $lesson->ID, null, $id );
			if ( is_array( $quizzes ) && count( $quizzes ) > 0 ) {
				foreach ( $quizzes as $quiz ) {
					$quiz_post = $quiz['post'];
					$arr[] = $quiz_post->ID;
				}
			}
		}
	}
	return $arr;
}