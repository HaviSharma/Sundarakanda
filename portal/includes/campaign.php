<?php
/**
 * Campaign totals — always computed from participation_entries (status =
 * 'approved') plus campaign_adjustments. Never a stored, manually-editable
 * total anywhere in the schema.
 */

function get_campaign(string $slug): ?array
{
    $stmt = db()->prepare('SELECT * FROM campaigns WHERE slug = ? LIMIT 1');
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function campaign_stats(int $campaignId): array
{
    $conn = db();

    $entriesTotal = 0;
    $stmt = $conn->prepare("SELECT COALESCE(SUM(count),0) t FROM participation_entries WHERE campaign_id = ? AND status = 'approved'");
    $stmt->bind_param('i', $campaignId);
    $stmt->execute();
    $entriesTotal = (int)$stmt->get_result()->fetch_assoc()['t'];
    $stmt->close();

    $historicalTotal = 0;
    $stmt = $conn->prepare('SELECT COALESCE(SUM(adjustment_count),0) t FROM campaign_adjustments WHERE campaign_id = ?');
    $stmt->bind_param('i', $campaignId);
    $stmt->execute();
    $historicalTotal = (int)$stmt->get_result()->fetch_assoc()['t'];
    $stmt->close();

    $participants = 0;
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT user_id) c FROM participation_entries WHERE campaign_id = ? AND status = 'approved'");
    $stmt->bind_param('i', $campaignId);
    $stmt->execute();
    $participants = (int)$stmt->get_result()->fetch_assoc()['c'];
    $stmt->close();

    $periods = [];
    foreach (['today' => 'CURDATE()', 'week' => 'DATE_SUB(CURDATE(), INTERVAL 7 DAY)', 'month' => 'DATE_SUB(CURDATE(), INTERVAL 30 DAY)'] as $key => $sqlDate) {
        $q = "SELECT COALESCE(SUM(count),0) t FROM participation_entries WHERE campaign_id = ? AND status = 'approved' AND participation_date >= $sqlDate";
        $stmt = $conn->prepare($q);
        $stmt->bind_param('i', $campaignId);
        $stmt->execute();
        $periods[$key] = (int)$stmt->get_result()->fetch_assoc()['t'];
        $stmt->close();
    }

    $completed = $entriesTotal + $historicalTotal;

    return [
        'entries_total' => $entriesTotal,
        'historical_total' => $historicalTotal,
        'completed' => $completed,
        'participants' => $participants,
        'today' => $periods['today'],
        'week' => $periods['week'],
        'month' => $periods['month'],
    ];
}

function recent_participation(int $campaignId, int $limit = 10): array
{
    $stmt = db()->prepare("SELECT p.count, p.participation_date, p.created_at, u.full_name
        FROM participation_entries p JOIN users u ON u.id = p.user_id
        WHERE p.campaign_id = ? AND p.status = 'approved'
        ORDER BY p.created_at DESC LIMIT ?");
    $stmt->bind_param('ii', $campaignId, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function user_campaign_summary(int $campaignId, int $userId): array
{
    $conn = db();
    $stmt = $conn->prepare("SELECT COALESCE(SUM(count),0) t, COUNT(*) n, MAX(participation_date) last_date
        FROM participation_entries WHERE campaign_id = ? AND user_id = ? AND status = 'approved'");
    $stmt->bind_param('ii', $campaignId, $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("SELECT COALESCE(SUM(count),0) t FROM participation_entries
        WHERE campaign_id = ? AND user_id = ? AND status = 'approved' AND YEAR(participation_date) = YEAR(CURDATE())");
    $stmt->bind_param('ii', $campaignId, $userId);
    $stmt->execute();
    $yearTotal = (int)$stmt->get_result()->fetch_assoc()['t'];
    $stmt->close();

    $stmt = $conn->prepare("SELECT COALESCE(SUM(count),0) t FROM participation_entries
        WHERE campaign_id = ? AND user_id = ? AND status = 'approved' AND YEAR(participation_date) = YEAR(CURDATE()) AND MONTH(participation_date) = MONTH(CURDATE())");
    $stmt->bind_param('ii', $campaignId, $userId);
    $stmt->execute();
    $monthTotal = (int)$stmt->get_result()->fetch_assoc()['t'];
    $stmt->close();

    return [
        'total' => (int)$row['t'],
        'entries' => (int)$row['n'],
        'last_date' => $row['last_date'],
        'year_total' => $yearTotal,
        'month_total' => $monthTotal,
    ];
}

function user_participation_history(int $campaignId, int $userId): array
{
    $stmt = db()->prepare('SELECT id, participation_date, count, notes, status, created_at FROM participation_entries WHERE campaign_id = ? AND user_id = ? ORDER BY participation_date DESC, id DESC');
    $stmt->bind_param('ii', $campaignId, $userId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}
