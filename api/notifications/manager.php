<?php

declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$managerUser = requireRole('manager');

$managerEmployeeId = trim(
    (string)($managerUser['employee_id'] ?? '')
);

if ($managerEmployeeId === '') {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Manager session not found.'
    ]);

    exit;
}

try {

    /*
    |--------------------------------------------------------------------------
    | Find manager employee record
    |--------------------------------------------------------------------------
    */

    $managerStmt = $pdo->prepare("
        SELECT id
        FROM employees
        WHERE employee_id = :employee_id
        LIMIT 1
    ");

    $managerStmt->execute([
        ':employee_id' => $managerEmployeeId
    ]);

    $manager =
        $managerStmt->fetch(PDO::FETCH_ASSOC);

    if (!$manager) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Manager record not found.'
        ]);

        exit;
    }

    $managerId =
        (int)$manager['id'];

    /*
    |--------------------------------------------------------------------------
    | Mark notification as read
    |--------------------------------------------------------------------------
    */

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $notificationType =
            trim(
                (string)(
                    $_POST['notification_type'] ?? ''
                )
            );

        $allowedTypes = [
            'leave',
            'schedule',
            'analytics'
        ];

        if (
            !in_array(
                $notificationType,
                $allowedTypes,
                true
            )
        ) {

            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => 'Invalid notification type.'
            ]);

            exit;
        }

        $readStmt = $pdo->prepare("
            INSERT INTO manager_notification_reads
                (manager_id, notification_type, read_at)
            VALUES
                (:manager_id, :notification_type, NOW())
            ON DUPLICATE KEY UPDATE
                read_at = NOW()
        ");

        $readStmt->execute([
            ':manager_id' => $managerId,
            ':notification_type' => $notificationType
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Notification marked as read.'
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Pending Leave Requests
    |--------------------------------------------------------------------------
    */

    $leaveStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM leave_requests lr

        INNER JOIN employees e
            ON e.employee_id = lr.employee_id

        WHERE e.manager_id = :manager_id
          AND lr.status = 'pending'
    ");

    $leaveStmt->execute([
        ':manager_id' => $managerId
    ]);

    $pendingLeave =
        (int)$leaveStmt->fetchColumn();

        /*
|--------------------------------------------------------------------------
| Latest Approved / Declined Team Leave Request
|--------------------------------------------------------------------------
*/

$leaveUpdateStmt = $pdo->prepare("
    SELECT
        lr.id,
        lr.leave_type,
        lr.start_date,
        lr.end_date,
        lr.status,
        lr.updated_at,

        CONCAT_WS(
            ' ',
            e.first_name,
            e.middle_name,
            e.last_name
        ) AS employee_name

    FROM leave_requests lr

    INNER JOIN employees e
        ON e.employee_id = lr.employee_id

    WHERE e.manager_id = :manager_id

      AND lr.status IN (
          'approved',
          'rejected'
      )

    ORDER BY
        lr.updated_at DESC,
        lr.id DESC

    LIMIT 1
");

$leaveUpdateStmt->execute([
    ':manager_id' => $managerId
]);

$latestLeaveUpdate =
    $leaveUpdateStmt->fetch(PDO::FETCH_ASSOC)
    ?: null;

    /*
    |--------------------------------------------------------------------------
    | Pending Shift Requests
    |--------------------------------------------------------------------------
    */

    $shiftStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM shift_requests sr

    INNER JOIN employees e
        ON e.employee_id = sr.employee_id

    WHERE e.manager_id = :manager_id
      AND sr.status = 'pending'
");

$shiftStmt->execute([
    ':manager_id' => $managerId
]);

$pendingShift =
    (int)$shiftStmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Latest pending shift request
|--------------------------------------------------------------------------
|
| This lets us detect a NEW request even when the manager
| has already read an earlier shift notification.
|
*/

$latestShiftStmt = $pdo->prepare("
    SELECT
        sr.created_at,
        sr.requested_date,
        sr.requested_shift,

        CONCAT_WS(
            ' ',
            e.first_name,
            e.middle_name,
            e.last_name
        ) AS employee_name

    FROM shift_requests sr

    INNER JOIN employees e
        ON e.employee_id = sr.employee_id

    WHERE e.manager_id = :manager_id
      AND sr.status = 'pending'

    ORDER BY
        sr.created_at DESC,
        sr.id DESC

    LIMIT 1
");

$latestShiftStmt->execute([
    ':manager_id' => $managerId
]);

$latestShift =
    $latestShiftStmt->fetch(PDO::FETCH_ASSOC)
    ?: null;

    /*
    |--------------------------------------------------------------------------
    | Read status
    |--------------------------------------------------------------------------
    */

    $readStmt = $pdo->prepare("
        SELECT
            notification_type,
            read_at
        FROM manager_notification_reads
        WHERE manager_id = :manager_id
    ");

    $readStmt->execute([
        ':manager_id' => $managerId
    ]);

    $readRows =
        $readStmt->fetchAll(PDO::FETCH_ASSOC);

    $readStatus = [];

    foreach ($readRows as $row) {

        $readStatus[
            $row['notification_type']
        ] = $row['read_at'];
    }

    /*
    |--------------------------------------------------------------------------
    | Notification state
    |--------------------------------------------------------------------------
    */


    $leaveReadAt =
    $readStatus['leave'] ?? null;

$scheduleReadAt =
    $readStatus['schedule'] ?? null;

$analyticsReadAt =
    $readStatus['analytics'] ?? null;


/*
 * Leave notification is unread when:
 *
 * 1. There are pending team leave requests and
 *    the manager has never read the leave notification.
 *
 * OR
 *
 * 2. Admin has approved or declined a team member's
 *    leave request after the manager last read the
 *    leave notification.
 */
$leaveUnread = false;


/*
 * Existing pending leave behavior.
 */
if ($pendingLeave > 0) {

    if (!$leaveReadAt) {

        $leaveUnread = true;

    }

}


/*
 * New Admin approval/decline behavior.
 */
if (
    $latestLeaveUpdate &&
    !empty($latestLeaveUpdate['updated_at'])
) {

    $leaveUpdatedAt =
        strtotime(
            (string)$latestLeaveUpdate['updated_at']
        );


    if (!$leaveReadAt) {

        $leaveUnread = true;

    } elseif (
        $leaveUpdatedAt >
        strtotime(
            (string)$leaveReadAt
        )
    ) {

        $leaveUnread = true;

    }

}

    /*
 * A shift notification is unread when:
 *
 * 1. There is at least one pending request, AND
 * 2. The manager has never read the schedule notification, OR
 * 3. The newest pending request was created AFTER the
 *    manager last read the schedule notification.
 */
$scheduleUnread = false;

if ($pendingShift > 0) {

    if (!$scheduleReadAt) {

        $scheduleUnread = true;

    } elseif (
        $latestShift &&
        !empty($latestShift['created_at'])
    ) {

        $scheduleUnread =
            strtotime(
                (string)$latestShift['created_at']
            ) >
            strtotime(
                (string)$scheduleReadAt
            );
    }
}


    /*
 * Workforce Analytics becomes unread again
 * 24 hours after the manager last read it.
 */
$analyticsUnread =
    $analyticsReadAt === null ||
    strtotime((string)$analyticsReadAt) <=
        (time() - (24 * 60 * 60));

    $notifications = [

        [
            'type' => 'analytics',
            'title' => 'Workforce analytics',
            'message' =>
                'Open the team attendance analytics and review current workforce activity.',
            'unread' => $analyticsUnread
        ],

        [
    'type' => 'leave',
    'title' => 'Leave requests',
    'message' => $latestLeaveUpdate
        ? (
            $latestLeaveUpdate['employee_name']
            . "'s "
            . $latestLeaveUpdate['leave_type']
            . " leave request was "
            . (
                $latestLeaveUpdate['status'] === 'approved'
                    ? 'approved'
                    : 'declined'
            )
            . " by Admin."
        )
        : (
            $pendingLeave > 0
                ? "{$pendingLeave} leave request(s) need your review."
                : 'No pending leave requests. Open Leave Management to review team requests.'
        ),
    'unread' => $leaveUnread
],

        [
    'type' => 'schedule',

    'title' =>
        'Shift request',

    'message' =>
        $pendingShift > 0
            ? (
                $latestShift
                    ? (
                        "{$latestShift['employee_name']} submitted a shift request" .
                        " for " .
                        date(
                            'M j, Y',
                            strtotime(
                                (string)$latestShift['requested_date']
                            )
                        ) .
                        " (" .
                        $latestShift['requested_shift'] .
                        ")."
                    )
                    : (
                        "{$pendingShift} shift request" .
                        ($pendingShift === 1 ? '' : 's') .
                        " need your review."
                    )
            )
            : 'No pending shift requests.',

    'unread' =>
        $scheduleUnread
]
    ];

   

    /*
    |--------------------------------------------------------------------------
    | Total unread
    |--------------------------------------------------------------------------
    */

    $unreadCount = 0;

    foreach ($notifications as $notification) {

        if ($notification['unread']) {
            $unreadCount++;
        }
    }

    echo json_encode([
        'success' => true,
        'unread_count' => $unreadCount,
        'data' => $notifications
    ]);

} catch (Throwable $e) {

    error_log(
        'Manager notification API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' =>
            'Unable to load manager notifications.'
    ]);
}