<?php
require_once '../includes/config.php';

requireAdmin();

$pageTitle = 'Manage Reviews';

$pdo = getDB();

// ======================
// Handle review deletion
// ======================
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $review_id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$review_id]);
        $_SESSION['success'] = 'Review deleted successfully.';
        redirect('view-reviews.php');
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to delete review: ' . $e->getMessage();
    }
}

// ======================
// Review stats
// ======================
$stats = [
    'total'        => 0,
    'avg_rating'   => 0,
    'latest_date'  => null,
    'good_percent' => 0,
    'by_star'      => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
];

// Overall stats (total, avg, latest date)
$stmt = $pdo->query("
    SELECT 
        COUNT(*)          AS total,
        AVG(rating)       AS avg_rating,
        MAX(created_at)   AS latest_date
    FROM reviews
");
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row && $row['total'] > 0) {
    $stats['total']       = (int)$row['total'];
    $stats['avg_rating']  = round((float)$row['avg_rating'], 1);
    $stats['latest_date'] = $row['latest_date'];
}

// Rating distribution (1–5)
$stmt = $pdo->query("
    SELECT rating, COUNT(*) AS cnt
    FROM reviews
    GROUP BY rating
");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $rating = (int)$r['rating'];
    $count  = (int)$r['cnt'];
    if ($rating >= 1 && $rating <= 5) {
        $stats['by_star'][$rating] = $count;
    }
}

// % of good reviews (4★ and 5★)
if ($stats['total'] > 0) {
    $goodCount = $stats['by_star'][4] + $stats['by_star'][5];
    $stats['good_percent'] = round(($goodCount / $stats['total']) * 100);
}

// ======================
// Get all reviews
// ======================
$stmt = $pdo->query("
    SELECT 
        r.*, 
        p.name AS product_name, 
        u.username, 
        u.full_name 
    FROM reviews r
    INNER JOIN products p ON r.product_id = p.id
    INNER JOIN users   u ON r.user_id = u.id
    ORDER BY r.created_at DESC
");
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">Manage Reviews</h2>
        <small class="text-muted">Monitor product feedback and moderate user reviews</small>
    </div>
    <?php if ($stats['total'] > 0): ?>
        <span class="badge bg-success">
            <?php echo $stats['total']; ?> reviews total
        </span>
    <?php endif; ?>
</div>

<?php if ($stats['total'] > 0): ?>
<!-- Top review insights -->
<div class="row g-3 mb-4">
    <!-- Average Rating -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-2">Average Rating</h6>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <h2 class="mb-0"><?php echo number_format($stats['avg_rating'], 1); ?></h2>
                        <small class="text-muted"><?php echo $stats['total']; ?> reviews</small>
                    </div>
                    <div>
                        <?php
                        $rounded = (int)round($stats['avg_rating']);
                        for ($i = 1; $i <= 5; $i++):
                            $class = $i <= $rounded ? 'text-warning' : 'text-muted';
                        ?>
                            <span class="<?php echo $class; ?>">★</span>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Good Reviews -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-2">Positive Feedback</h6>
                <h3 class="mb-1 text-success">
                    <?php echo $stats['good_percent']; ?>%
                </h3>
                <small class="text-muted d-block mb-1">
                    4★ and 5★ reviews
                </small>
                <div class="progress" style="height: 6px;">
                    <div 
                        class="progress-bar bg-success" 
                        role="progressbar" 
                        style="width: <?php echo $stats['good_percent']; ?>%;"
                        aria-valuenow="<?php echo $stats['good_percent']; ?>" 
                        aria-valuemin="0" 
                        aria-valuemax="100">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Review -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-2">Latest Review</h6>
                <?php if ($stats['latest_date']): ?>
                    <p class="mb-1">
                        <strong><?php echo date('M d, Y', strtotime($stats['latest_date'])); ?></strong>
                    </p>
                    <small class="text-muted">
                        Last review submitted
                    </small>
                <?php else: ?>
                    <p class="text-muted mb-0">No reviews yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Rating distribution -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <h5 class="mb-0">Rating Breakdown</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php for ($star = 5; $star >= 1; $star--): 
                        $count = $stats['by_star'][$star];
                        $percent = $stats['total'] > 0 ? round(($count / $stats['total']) * 100) : 0;
                    ?>
                        <li class="list-group-item d-flex align-items-center justify-content-between">
                            <div>
                                <strong><?php echo $star; ?> ★</strong>
                                <small class="text-muted ms-2"><?php echo $percent; ?>%</small>
                            </div>
                            <div class="flex-grow-1 mx-3">
                                <div class="progress" style="height: 5px;">
                                    <div 
                                        class="progress-bar bg-warning" 
                                        role="progressbar"
                                        style="width: <?php echo $percent; ?>%;"
                                        aria-valuenow="<?php echo $percent; ?>" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark">
                                <?php echo $count; ?>
                            </span>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Reviews Table -->
<?php if (empty($reviews)): ?>
    <div class="alert alert-info">
        <p>No reviews found.</p>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">All Reviews</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>User</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Date</th>
                            <th style="width: 90px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td><?php echo $review['id']; ?></td>
                            <td>
                                <a href="../product-details.php?id=<?php echo $review['product_id']; ?>" target="_blank">
                                    <?php echo h($review['product_name']); ?>
                                </a>
                            </td>
                            <td><?php echo h($review['full_name'] ?: $review['username']); ?></td>
                            <td>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="<?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>">★</span>
                                <?php endfor; ?>
                                <small class="text-muted ms-1">
                                    (<?php echo $review['rating']; ?>/5)
                                </small>
                            </td>
                            <td>
                                <?php 
                                $comment = $review['comment'] ?? '';
                                $short = mb_substr($comment, 0, 100);
                                echo h($short);
                                if (mb_strlen($comment) > 100) {
                                    echo '...';
                                }
                                ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                            <td>
                                <a href="?delete=<?php echo $review['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this review?')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="mt-3">
    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>
