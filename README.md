# adaptive-learning-learndash
Adaptive learning with LearnDash enables admin/s to design courses in a non-linear fashion, there can be a variety of child courses each with a distinct course level, the student will be assigned child course based on their performance in the quiz/quizzes of the prerequisite/deterministic/parent course.

# PREREQUISITES:
 - LearnDash

# HOW IT WORKS:
**Parent Courses:**
In Adaptive Learning With LearnDash, one needs to determine the behavior of each student and understand the learning pace/style of each student. To determine the behavior of the student the admin must create a parent/deterministic course
in LearnDash. This will be a regular LearnDash course but It should be designed in a way to determine the level of student for the various child courses which will be assigned to a student based on the performance in this “parent” course.

**Child Courses:**
LearnDash provides the capability to map any course as a prerequisite course. We will call this prerequisite course as our parent/deterministic course. The courses which will be assigned this prerequisite course will be called the “child courses”.

The plugin creates a new submenu called “course levels” inside the LearnDash menu. admin can create any number of course levels here,
LearnDash -> Course levels. Each course level corresponds to a certain “from” and “to” percentage value. This percentage value is the percentage score obtained by the student in the parent course quiz/quizzes.

For.eg : In a parent course, say, Course 1, there is one quiz and a student obtains a result of 20% in that quiz, now, If there is a course level corresponding to this range and If such level is associated with any of the child courses by the admin, the completion of Course 1 will automatically assign the specific child course corresponding to that course level and the student will be able to access that particular child course “only”.

Note: It is important to note that the child courses should be created with the LearnDash course type “closed” so that only the child course corresponding to the specific course level is assigned to the student and other child courses would remain inaccessible.

# FEATURES:
Admin can create any number of course levels corresponding to different results in the parent course
Admin can view students adaptive learning stats in the user profile section.
