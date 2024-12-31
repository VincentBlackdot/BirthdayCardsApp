<?php
require_once 'config/database.php';

// Get email tracking data
$db = new Database();
$conn = $db->connect();

$stmt = $conn->prepare("
    SELECT 
        el.*,
        t.name as template_name,
        CASE 
            WHEN el.opened = 1 THEN 'Opened'
            ELSE 'Not Opened'
        END as status,
        TIMESTAMPDIFF(MINUTE, el.created_at, COALESCE(el.opened_at, NOW())) as minutes_to_open
    FROM email_logs el
    LEFT JOIN templates t ON el.template_id = t.id
    ORDER BY el.created_at DESC
");
$stmt->execute();
$emails = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate statistics
$totalEmails = count($emails);
$openedEmails = array_filter($emails, function($email) {
    return $email['opened'] == 1;
});
$openRate = $totalEmails > 0 ? (count($openedEmails) / $totalEmails) * 100 : 0;

// Calculate average time to open (only for opened emails)
$totalMinutesToOpen = 0;
$openedCount = 0;
foreach ($openedEmails as $email) {
    $totalMinutesToOpen += $email['minutes_to_open'];
    $openedCount++;
}
$avgTimeToOpen = $openedCount > 0 ? $totalMinutesToOpen / $openedCount : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Tracking Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stats-card {
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,.075);
        }
        .badge-opened {
            background-color: #28a745;
        }
        .badge-not-opened {
            background-color: #dc3545;
        }
        .navbar {
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Email Tracking Dashboard</h1>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stats-card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Emails Sent</h5>
                        <h2 class="card-text"><?php echo $totalEmails; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Open Rate</h5>
                        <h2 class="card-text"><?php echo number_format($openRate, 1); ?>%</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Avg. Time to Open</h5>
                        <h2 class="card-text"><?php echo number_format($avgTimeToOpen, 1); ?> min</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email List -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($emails)): ?>
                <div class="alert alert-info">
                    No emails have been sent yet. Once you send emails, their tracking information will appear here.
                </div>
                <?php else: ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Recipient</th>
                            <th>Template</th>
                            <th>Status</th>
                            <th>Sent At</th>
                            <th>Opened At</th>
                            <th>Time to Open</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($emails as $email): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($email['recipient_name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($email['recipient_email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($email['template_name']); ?></td>
                            <td>
                                <span class="badge <?php echo $email['opened'] ? 'badge-opened' : 'badge-not-opened'; ?>">
                                    <?php echo $email['status']; ?>
                                </span>
                            </td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($email['created_at'])); ?></td>
                            <td><?php echo $email['opened_at'] ? date('Y-m-d H:i:s', strtotime($email['opened_at'])) : '-'; ?></td>
                            <td>
                                <?php if ($email['opened']): ?>
                                    <?php echo number_format($email['minutes_to_open'], 1); ?> min
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
