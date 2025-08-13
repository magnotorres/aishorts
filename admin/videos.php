<?php
$page_title = 'Published Videos';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/header.php';

$pdo = get_db_connection();

// Fetch all published videos with their related info
// This query uses subqueries to get the latest metric for each video,
// which is efficient for a small to medium number of videos.
// For very large datasets, a single JOIN against a pre-aggregated metrics table might be faster.
$sql = "
    SELECT
        pv.id,
        pv.post_url,
        pv.prompt_used,
        pv.published_at,
        c.name as category_name,
        sa.platform,
        sa.account_name,
        (SELECT vm.views FROM video_metrics vm WHERE vm.video_id = pv.id ORDER BY vm.fetched_at DESC LIMIT 1) as latest_views,
        (SELECT vm.likes FROM video_metrics vm WHERE vm.video_id = pv.id ORDER BY vm.fetched_at DESC LIMIT 1) as latest_likes,
        (SELECT vm.comments FROM video_metrics vm WHERE vm.video_id = pv.id ORDER BY vm.fetched_at DESC LIMIT 1) as latest_comments
    FROM published_videos pv
    JOIN categories c ON pv.category_id = c.id
    JOIN social_accounts sa ON pv.social_account_id = sa.id
    ORDER BY pv.published_at DESC
";
$stmt = $pdo->query($sql);
$videos = $stmt->fetchAll();

?>

<table>
    <thead>
        <tr>
            <th>Published</th>
            <th>Platform</th>
            <th>Account</th>
            <th>Category</th>
            <th>Post URL</th>
            <th>Views</th>
            <th>Likes</th>
            <th>Comments</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($videos)): ?>
            <tr>
                <td colspan="8" style="text-align: center;">No videos have been published yet.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($videos as $video): ?>
                <tr>
                    <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($video['published_at']))); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($video['platform'])); ?></td>
                    <td><?php echo htmlspecialchars($video['account_name']); ?></td>
                    <td><?php echo htmlspecialchars($video['category_name']); ?></td>
                    <td><a href="<?php echo htmlspecialchars($video['post_url']); ?>" target="_blank" rel="noopener noreferrer">View Post</a></td>
                    <td><?php echo number_format($video['latest_views'] ?? 0); ?></td>
                    <td><?php echo number_format($video['latest_likes'] ?? 0); ?></td>
                    <td><?php echo number_format($video['latest_comments'] ?? 0); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php
require_once __DIR__ . '/footer.php';
?>
