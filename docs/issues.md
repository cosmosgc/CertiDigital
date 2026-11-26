# Code review issues

Findings from reviewing the current application code. No application code was changed as part of this review.

## 1. Admin API authorization can be bypassed with `X-User-Id` — P1

**Location:** `app/Http/Middleware/AdminOnly.php:27-34`, used by the admin API group in `routes/api.php:75-79`.

When there is no authenticated session, `AdminOnly` trusts the caller-supplied `X-User-Id` header and loads that user before checking for the admin role. An unauthenticated caller can set this header to the ID of any admin and pass the authorization check, gaining access to the admin API, including user/role management, attendance changes, and grading. Do not treat an identity supplied by the request as authentication; require a verified session or API token.

## 2. Course API exposes multiple-choice answer keys publicly — P1

**Location:** `app/Http/Controllers/Api/CourseController.php:42-47`; public route at `routes/api.php:23-27`. Also returned by student progress at `app/Http/Controllers/Api/ExerciseSubmissionController.php:68-75, 101-108, 124-131`.

The public course detail endpoint eager-loads `courseExercises.exercise.options`, and student progress does the same for course and attendance exercises. `ClassExerciseOption` serializes its `is_correct` field (`app/Models/ClassExerciseOption.php:12-20`), so unauthenticated callers can fetch `/api/courses/{id}` and see the correct answers for every linked multiple-choice exercise; students can also inspect the progress response before answering. Remove correctness metadata from student/public responses and expose it only to authorized graders.

## 3. Exercise progress and submission APIs allow student impersonation — P1

**Location:** `app/Http/Controllers/Api/ExerciseSubmissionController.php:15-33, 58-61, 192-205`; routes at `routes/api.php:49-50`.

`resolveStudent()` accepts any `student_id` supplied in the request when the authenticated user has no linked student, without checking that the caller is an admin or owns that student record. An ordinary authenticated account can request another student's progress and submit/overwrite work as that student. The later enrollment check only confirms that the *selected* student is enrolled, not that the caller is that student. Ignore caller-supplied IDs for students and enforce ownership before reading or writing student records.

## 4. Any authenticated user can download another student's submission — P1

**Location:** `routes/api.php:52`; `app/Http/Controllers/Api/ExerciseSubmissionController.php:483-494`.

The download route is in the general authenticated student group, and `downloadSubmission()` serves the selected submission's file without checking that it belongs to the current user's linked student or that the user is an authorized grader. A logged-in user who guesses or obtains another submission ID can download its uploaded work. Add an ownership/role check before returning either the default file or a `?file=N` attachment.

## 5. Custom HTML exercise scores are trusted from the browser — P1

**Location:** `app/Http/Controllers/Api/ExerciseSubmissionController.php:341-355`.

For `custom_html` exercises, the request's `result_score` is accepted as the grade and stored as `graded` after only clamping it to the exercise's maximum points. The student controls this request field and can submit the maximum score without completing the exercise. Calculate/validate scores on the server, or treat client-provided scores as untrusted submissions that require grading.

## 6. Session-authenticated API mutations do not validate CSRF tokens — P2

**Location:** `routes/api.php:33-41, 71-79`.

These API groups explicitly start the web session and authenticate with its cookie, but do not apply Laravel's CSRF middleware. The comment says the Blade clients send `X-CSRF-TOKEN`, but sending that header provides no protection unless the server validates it. A cross-site request from a same-site origin (or when cookies are configured for cross-site use) can invoke session-authenticated mutations such as enrollment requests, claims, role changes, and grading. Apply CSRF protection to cookie-authenticated mutations or use a stateless token-authenticated API.

## 7. Feature tests cannot migrate the configured SQLite database — P2

**Location:** `database/migrations/2026_03_16_000000_replace_progress_percent_with_progress_hours_in_course_enrollments_table.php:15-22` (and the rollback SQL at lines 35-42); SQLite test configuration at `phpunit.xml:26-27`.

The migration uses MySQL's `UPDATE ... INNER JOIN` syntax, which SQLite rejects. Running `php artisan test` currently yields **29 failed, 1 passed**; all feature tests fail during database setup with `near "INNER": syntax error`, before their assertions run. Rewrite the data migration in a SQLite-compatible form (or otherwise provide a supported test database/migration path) so the feature suite can execute.
