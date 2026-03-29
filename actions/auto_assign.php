<?php
// actions/auto_assign.php
// Called internally after a new report is submitted.
// Finds the best available agent for the report's category and assigns it.

function autoAssignReport(PDO $pdo, int $reportId): bool
{
    // 1. Get the report's category → service
    $report = $pdo->prepare("
        SELECT r.id, r.category_id, c.service_id
        FROM reports r
        LEFT JOIN categories c ON c.id = r.category_id
        WHERE r.id = :id LIMIT 1
    ");
    $report->execute([':id' => $reportId]);
    $report = $report->fetch(PDO::FETCH_ASSOC);

    if (!$report || !$report['service_id']) {
        return false; // No category/service → skip
    }

    $serviceId = $report['service_id'];

    // 2. Find the agent in that service with the fewest active reports
    //    (load balancing — pick least busy agent)
    $agentStmt = $pdo->prepare("
        SELECT u.id, COUNT(r2.id) AS active_count
        FROM users u
        LEFT JOIN reports r2 ON r2.assigned_to = u.id
                             AND r2.status IN ('Open', 'In progress')
        WHERE u.role = 'agent'
          AND u.service_id = :sid
        GROUP BY u.id
        ORDER BY active_count ASC
        LIMIT 1
    ");
    $agentStmt->execute([':sid' => $serviceId]);
    $agent = $agentStmt->fetch(PDO::FETCH_ASSOC);

    if (!$agent) {
        return false; // No agent available for this service
    }

    // 3. Assign the report + set deadline (7 days from now)
    $deadline = date('Y-m-d H:i:s', strtotime('+7 days'));

    $upd = $pdo->prepare("
        UPDATE reports
        SET assigned_to = :agent, deadline = :deadline, status = 'In progress'
        WHERE id = :id AND assigned_to IS NULL
    ");
    $upd->execute([':agent' => $agent['id'], ':deadline' => $deadline, ':id' => $reportId]);

    if ($upd->rowCount() === 0) {
        return false; // Already assigned
    }

    // 4. Log the assignment
    $pdo->prepare("
        INSERT INTO report_logs (report_id, changed_by, old_status, new_status, note)
        VALUES (:rid, :uid, 'Open', 'In progress', 'Auto-assigned by system')
    ")->execute([':rid' => $reportId, ':uid' => $agent['id']]);

    return true;
}
?>