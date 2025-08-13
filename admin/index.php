<?php
$page_title = 'Dashboard';
require_once __DIR__ . '/auth.php'; // Check login
require_once __DIR__ . '/header.php'; // Include header

$pdo = get_db_connection();

// 1. Get total published videos
$total_videos_stmt = $pdo->query("SELECT COUNT(*) FROM published_videos");
$total_videos = $total_videos_stmt->fetchColumn();

// 2. Get total views
$total_views_stmt = $pdo->query("SELECT SUM(views) FROM video_metrics");
$total_views = $total_views_stmt->fetchColumn() ?: 0;

// 3. Get average engagement metrics
$engagement_stmt = $pdo->query("
    SELECT
        AVG(likes) as avg_likes,
        AVG(comments) as avg_comments,
        AVG(shares) as avg_shares
    FROM video_metrics
");
$engagement = $engagement_stmt->fetch();

$avg_likes = $engagement['avg_likes'] ? number_format($engagement['avg_likes'], 1) : 0;
$avg_comments = $engagement['avg_comments'] ? number_format($engagement['avg_comments'], 1) : 0;
$avg_shares = $engagement['avg_shares'] ? number_format($engagement['avg_shares'], 1) : 0;

?>

<div class="card-container">
    <div class="card">
        <h3>Total Videos Published</h3>
        <p class="metric"><?php echo $total_videos; ?></p>
    </div>
    <div class="card">
        <h3>Total Views</h3>
        <p class="metric"><?php echo number_format($total_views); ?></p>
    </div>
    <div class="card">
        <h3>Avg. Likes per Video</h3>
        <p class="metric"><?php echo $avg_likes; ?></p>
    </div>
    <div class="card">
        <h3>Avg. Comments per Video</h3>
        <p class="metric"><?php echo $avg_comments; ?></p>
    </div>
    <div class="card">
        <h3>Avg. Shares per Video</h3>
        <p class="metric"><?php echo $avg_shares; ?></p>
    </div>
</div>

<div class="form-container" style="margin-top: 40px;">
    <h2>Quick Stats</h2>
    <p>This dashboard provides a high-level overview of your content performance.</p>
    <p>Use the navigation on the left to manage content categories or view individual video posts.</p>
</div>


<?php
require_once __DIR__ . '/footer.php'; // Include footer
?>
