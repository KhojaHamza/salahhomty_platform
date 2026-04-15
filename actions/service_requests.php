<?php
// actions/service_requests.php
// API backend pour les demandes de services (citoyen)
// Actions : list | cancel | rate | export

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

// ── Auth obligatoire pour toutes les actions ───────────────────────────────
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié. Veuillez vous connecter.']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

// ══════════════════════════════════════════════════════════════════════════════
// ACTION : list
// GET ?action=list[&status=][&category=][&search=]
// Retourne toutes les demandes du citoyen connecté + stats
// ══════════════════════════════════════════════════════════════════════════════
if ($action === 'list') {

    $status   = trim($_GET['status']   ?? '');
    $category = trim($_GET['category'] ?? '');
    $search   = trim($_GET['search']   ?? '');

    $conditions = ['sr.user_id = ?'];
    $params     = [$userId];

    // Filtre statut
    $validStatuses = ['pending', 'in_progress', 'done', 'overdue', 'cancelled'];
    if ($status !== '' && in_array($status, $validStatuses)) {
        $conditions[] = 'sr.status = ?';
        $params[]     = $status;
    }

    // Filtre catégorie
    if ($category !== '') {
        $conditions[] = 's.category = ?';
        $params[]     = $category;
    }

    // Filtre recherche (nom du service ou ID)
    if ($search !== '') {
        $conditions[] = '(s.name LIKE ? OR sr.id = ?)';
        $params[]     = '%' . $search . '%';
        $params[]     = is_numeric($search) ? (int)$search : 0;
    }

    $where = 'WHERE ' . implode(' AND ', $conditions);

    // ── Récupérer les demandes ──────────────────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT
            sr.id,
            sr.service_id,
            sr.status,
            sr.message          AS request_message,
            sr.admin_message,
            sr.created_at,
            sr.updated_at,
            s.name              AS service_name,
            s.category,
            s.description       AS service_description,
            s.region,
            TIME_FORMAT(s.availability_start, '%H:%i') AS availability_start,
            TIME_FORMAT(s.availability_end,   '%H:%i') AS availability_end,
            -- Vérifie si annulation encore possible (< 24h depuis création)
            TIMESTAMPDIFF(HOUR, sr.created_at, NOW()) AS hours_since_created,
            -- Infos évaluation existante
            r.id        AS rating_id,
            r.score     AS rating_score,
            r.comment   AS rating_comment,
            r.created_at AS rated_at
        FROM service_requests sr
        INNER JOIN services s ON s.id = sr.service_id
        LEFT JOIN service_ratings r
               ON r.service_request_id = sr.id AND r.user_id = ?
        $where
        ORDER BY sr.created_at DESC
    ");

    // Insérer le user_id pour le LEFT JOIN avant les autres params
    $allParams = array_merge([$userId], $params);
    $stmt->execute($allParams);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── Enrichir chaque ligne ───────────────────────────────────────────────
    foreach ($requests as &$req) {
        $hours = (int)$req['hours_since_created'];

        // Peut annuler : statut pending ET moins de 24h
        $req['can_cancel'] = ($req['status'] === 'pending' && $hours < 24);

        // Peut évaluer : statut done ET pas encore évalué
        $req['can_rate'] = ($req['status'] === 'done' && $req['rating_id'] === null);

        // Already rated
        $req['already_rated'] = ($req['rating_id'] !== null);

        // Format date lisible
        $req['created_at_formatted'] = date('M j, Y', strtotime($req['created_at']));
        $req['updated_at_formatted'] = $req['updated_at']
            ? date('M j, Y', strtotime($req['updated_at']))
            : null;

        // Référence affichable
        $req['reference'] = 'SRQ-' . date('Y', strtotime($req['created_at']))
                          . '-' . str_pad($req['id'], 3, '0', STR_PAD_LEFT);

        // Nettoyage
        unset($req['hours_since_created']);
    }
    unset($req);

    // ── Stats globales (indépendantes des filtres) ──────────────────────────
    $statsStmt = $pdo->prepare("
        SELECT
            COUNT(*)                                            AS total,
            SUM(status = 'pending')                             AS pending,
            SUM(status = 'in_progress')                         AS in_progress,
            SUM(status = 'done')                                AS done,
            SUM(status = 'overdue')                             AS overdue,
            SUM(status = 'cancelled')                           AS cancelled
        FROM service_requests
        WHERE user_id = ?
    ");
    $statsStmt->execute([$userId]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    // Convertir en int
    foreach ($stats as $k => $v) {
        $stats[$k] = (int)$v;
    }

    echo json_encode([
        'success'  => true,
        'requests' => $requests,
        'stats'    => $stats,
    ]);
    exit;
}

// ══════════════════════════════════════════════════════════════════════════════
// ACTION : cancel
// POST action=cancel & request_id=X
// Annule une demande si : appartient au user + status=pending + < 24h
// ══════════════════════════════════════════════════════════════════════════════
if ($action === 'cancel') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
        exit;
    }

    $requestId = (int)($_POST['request_id'] ?? 0);
    if ($requestId <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de demande invalide.']);
        exit;
    }

    // ── Vérifier que la demande appartient au user et est annulable ─────────
    $check = $pdo->prepare("
        SELECT id, status,
               TIMESTAMPDIFF(HOUR, created_at, NOW()) AS hours_since_created
        FROM service_requests
        WHERE id = ? AND user_id = ?
        LIMIT 1
    ");
    $check->execute([$requestId, $userId]);
    $req = $check->fetch(PDO::FETCH_ASSOC);

    if (!$req) {
        echo json_encode(['success' => false, 'message' => 'Demande introuvable.']);
        exit;
    }

    if ($req['status'] !== 'pending') {
        echo json_encode([
            'success' => false,
            'message' => 'Seules les demandes en attente peuvent être annulées.',
        ]);
        exit;
    }

    if ((int)$req['hours_since_created'] >= 24) {
        echo json_encode([
            'success' => false,
            'message' => 'Le délai de 24 heures pour annuler cette demande est dépassé.',
        ]);
        exit;
    }

    // ── Mettre à jour le statut ─────────────────────────────────────────────
    $pdo->prepare("
        UPDATE service_requests
        SET status = 'cancelled', updated_at = NOW()
        WHERE id = ? AND user_id = ?
    ")->execute([$requestId, $userId]);

    echo json_encode([
        'success' => true,
        'message' => 'Votre demande a été annulée avec succès.',
    ]);
    exit;
}

// ══════════════════════════════════════════════════════════════════════════════
// ACTION : rate
// POST action=rate & request_id=X & score=1-5 & comment=(optionnel)
// Évalue un service dont la demande est "done" et pas encore évaluée
// ══════════════════════════════════════════════════════════════════════════════
if ($action === 'rate') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
        exit;
    }

    $requestId = (int)($_POST['request_id'] ?? 0);
    $score     = (int)($_POST['score']      ?? 0);
    $comment   = trim($_POST['comment']     ?? '');

    // ── Validation ──────────────────────────────────────────────────────────
    if ($requestId <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de demande invalide.']);
        exit;
    }

    if ($score < 1 || $score > 5) {
        echo json_encode(['success' => false, 'message' => 'La note doit être entre 1 et 5 étoiles.']);
        exit;
    }

    if (mb_strlen($comment) > 1000) {
        echo json_encode(['success' => false, 'message' => 'Le commentaire ne doit pas dépasser 1000 caractères.']);
        exit;
    }

    // ── Vérifier que la demande appartient au user et est "done" ────────────
    $check = $pdo->prepare("
        SELECT sr.id, sr.service_id, sr.status,
               r.id AS existing_rating_id
        FROM service_requests sr
        LEFT JOIN service_ratings r
               ON r.service_request_id = sr.id AND r.user_id = ?
        WHERE sr.id = ? AND sr.user_id = ?
        LIMIT 1
    ");
    $check->execute([$userId, $requestId, $userId]);
    $req = $check->fetch(PDO::FETCH_ASSOC);

    if (!$req) {
        echo json_encode(['success' => false, 'message' => 'Demande introuvable.']);
        exit;
    }

    if ($req['status'] !== 'done') {
        echo json_encode([
            'success' => false,
            'message' => 'Vous ne pouvez évaluer qu\'un service clôturé.',
        ]);
        exit;
    }

    if ($req['existing_rating_id'] !== null) {
        echo json_encode([
            'success' => false,
            'message' => 'Vous avez déjà évalué cette demande.',
        ]);
        exit;
    }

    // ── Insérer l'évaluation ─────────────────────────────────────────────────
    $pdo->prepare("
        INSERT INTO service_ratings (service_request_id, service_id, user_id, score, comment, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ")->execute([
        $requestId,
        $req['service_id'],
        $userId,
        $score,
        $comment ?: null,
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Merci pour votre évaluation ! Elle aide à améliorer les services municipaux.',
    ]);
    exit;
}

// ══════════════════════════════════════════════════════════════════════════════
// ACTION : export
// GET ?action=export
// Génère un CSV de toutes les demandes du citoyen
// ══════════════════════════════════════════════════════════════════════════════
if ($action === 'export') {

    $stmt = $pdo->prepare("
        SELECT
            sr.id,
            s.name              AS service_name,
            s.category,
            s.region,
            sr.status,
            sr.message          AS request_message,
            sr.admin_message,
            sr.created_at,
            sr.updated_at
        FROM service_requests sr
        INNER JOIN services s ON s.id = sr.service_id
        WHERE sr.user_id = ?
        ORDER BY sr.created_at DESC
    ");
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Changer le Content-Type pour CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="service-requests-' . date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    // BOM UTF-8 pour Excel
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // En-tête
    fputcsv($out, ['ID', 'Reference', 'Service', 'Category', 'Region', 'Status', 'Your Message', 'Admin Reply', 'Submitted On', 'Last Updated']);

    $statusLabels = [
        'pending'     => 'Pending',
        'in_progress' => 'In Progress',
        'done'        => 'Closed',
        'overdue'     => 'Overdue',
        'cancelled'   => 'Cancelled',
    ];

    foreach ($rows as $r) {
        $ref = 'SRQ-' . date('Y', strtotime($r['created_at']))
             . '-' . str_pad($r['id'], 3, '0', STR_PAD_LEFT);
        fputcsv($out, [
            $r['id'],
            $ref,
            $r['service_name'],
            $r['category'],
            $r['region'] ?? '—',
            $statusLabels[$r['status']] ?? $r['status'],
            $r['request_message'] ?? '',
            $r['admin_message']   ?? '',
            date('Y-m-d', strtotime($r['created_at'])),
            $r['updated_at'] ? date('Y-m-d', strtotime($r['updated_at'])) : '',
        ]);
    }

    fclose($out);
    exit;
}

// ── Action inconnue ────────────────────────────────────────────────────────
echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
exit;