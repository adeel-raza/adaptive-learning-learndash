=== Adaptive Learning With LearnDash ===
Contributors: wooninjas
Tags: learning, lms, learndash, adaptive-learning, adaptive-learning-with-learndash, learndash-adaptive-learning, education
Requires at least: 4.0
Tested up to: 5.1.1
Stable tag: 1.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adaptive learning with LearnDash enables admin to apply the concept of adaptive learning to LearnDash and make non linear course pattern for the students. It enables admin to create multiple child courses and associate distinct levels with each child course.

== Description ==

Adaptive learning with LearnDash enables admin/s to design courses in a non-linear fashion, there can be a variety of child courses each with a distinct course level, the student will be assigned child course based on their performance in the quiz/quizzes of the prerequisite/deterministic/parent course.

= Prerequisites: =

* LearnDash

= How It Works: =

* Parent Courses:
In Adaptive Learning With LearnDash, one needs to determine the behavior of each student and understand the learning pace/style of each student. To determine the behavior of the student the admin must create a parent/deterministic course
in LearnDash. This will be a regular LearnDash course but It should be designed in a way to determine the level of student for the various child courses which will be assigned to a student based on the performance in this "parent" course.


* Child Courses:
LearnDash provides the capability to map any course as a prerequisite course. We will call this prerequisite course as our parent/deterministic course. The courses which will be assigned this prerequisite course will be called the “child courses”.

* The plugin creates a new submenu called "course levels" inside the LearnDash menu. admin can create any number of course levels here, 
LearnDash -> Course levels. Each course level corresponds to a certain "from" and "to" percentage value. This percentage value is the percentage score obtained by the student in the parent course quiz/quizzes.

For.eg : In a parent course, say, Course 1, there is one quiz and a student obtains a result of 20% in that quiz, now, If there is a course level corresponding to this range and If such level is associated with any of the child courses by the admin, the completion of Course 1 will automatically assign the specific child course corresponding to that course level and the student will be able to access that particular child course "only".

Note: It is important to note that the child courses should be created with the LearnDash course type "closed" so that only the child course corresponding to the specific course level is assigned to the student and other child courses would remain inaccessible.


= Features: =

* Admin can create any number of course levels corresponding to different results in the parent course
* Admin can view students adaptive learning stats in the user profile section.

For any further support or assistance for the plugin kindly reach out us at [WooNinjas](http://wooninjas.com/contact-us)

== Installation ==

Before installation please make sure you have latest LearnDash installed.

1. Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress

== Frequently Asked Questions ==

= How many child courses can be assigned to a prerequisite course?

The admins can create any number of child courses and “levels” for these “child courses”.

= what happens if we have multiple quizzes in the parent course?

Incase of multiple quizzes in the parent course, the average will be calculated from these quizzes and the average percentage value will be considered as the final percentage score to be associated with a course level.


== Screenshots ==

1. Course Level Menu assets/course-level-menu.png
2. Course Level Page assets/course-level-page.png
3. In course options, selecting a prerequisite course will enable admin to choose a course level for this "child course" assets/prerequisite-course.png
4. Adaptive learning stats will be displayed on the admin user profile page for each user who completed the prerequisite course and is assigned one of the child course assets/admin-user-profile-stats-section.png

== Changelog ==

= 1.4 =
* Fix: Remove direct db call, used LearnDash provided functions
* Fix: Fix ajax 500 error issue

= 1.3 =
* Fix: Made add-on compatible with WordPress 5.1 and Learndash 2.6.4
* New: Added animated notification when enrolled to associated course
* New: Displayed associated course on course detail page
* New: Added filter "notification_message" to override the notification message

= 1.2 =
* Added plugin branding

= 1.1 =
* Made compatible with LearnDash v2.4

= 1.0 =
* Initial
