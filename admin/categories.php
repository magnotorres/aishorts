<?php
$page_title = 'Category Management';
require_once __DIR__ . '/auth.php';

$pdo = get_db_connection();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$error = null;
$success = null;

// Handle POST requests for Create, Update, Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_action = $_POST['action'] ?? '';

    if ($form_action === 'create') {
        $name = $_POST['name'] ?? '';
        if (!empty($name)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
                $stmt->execute([$name]);
                $success = "Category '{$name}' created successfully.";
            } catch (PDOException $e) {
                $error = "Failed to create category. It might already exist.";
            }
        } else {
            $error = "Category name cannot be empty.";
        }
    }

    if ($form_action === 'update' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $name = $_POST['name'] ?? '';
        $priority_score = $_POST['priority_score'] ?? 1.0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (!empty($name)) {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, priority_score = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $priority_score, $is_active, $id]);
            $success = "Category updated successfully.";
            $action = 'list'; // Return to list view
        } else {
            $error = "Category name cannot be empty.";
            $action = 'edit'; // Stay on edit view
        }
    }

    if ($form_action === 'delete' && isset($_POST['id'])) {
        try {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Category deleted successfully.";
        } catch (PDOException $e) {
            $error = "Failed to delete category. It may be in use by published videos.";
        }
        $action = 'list';
    }
}

require_once __DIR__ . '/header.php';

if ($error) echo "<div class='alert alert-danger'>{$error}</div>";
if ($success) echo "<div class='alert alert-success'>{$success}</div>";

// Main view logic
switch ($action) {
    case 'edit':
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $category = $stmt->fetch();
        if ($category) {
            // Show edit form
            ?>
            <div class="form-container">
                <h2>Edit Category</h2>
                <form action="categories.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                    <div class="form-group">
                        <label for="name">Category Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="priority_score">Priority Score (Manual Override)</label>
                        <input type="number" step="0.1" id="priority_score" name="priority_score" value="<?php echo htmlspecialchars($category['priority_score']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" value="1" <?php echo $category['is_active'] ? 'checked' : ''; ?>>
                            Active (AI can select this category)
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                    <a href="categories.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
            <?php
        } else {
            echo "<p>Category not found.</p>";
        }
        break;

    case 'delete':
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $category = $stmt->fetch();
        if ($category) {
             ?>
            <div class="form-container">
                <h2>Confirm Deletion</h2>
                <p>Are you sure you want to delete the category "<strong><?php echo htmlspecialchars($category['name']); ?></strong>"?</p>
                <p>This action cannot be undone. If the category is linked to published videos, deletion might fail.</p>
                <form action="categories.php" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    <a href="categories.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
            <?php
        } else {
            echo "<p>Category not found.</p>";
        }
        break;

    case 'list':
    default:
        $categories = $pdo->query("SELECT * FROM categories ORDER BY priority_score DESC")->fetchAll();
        ?>
        <div class="form-container">
            <h2>Add New Category</h2>
            <form action="categories.php" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label for="name">New Category Name</label>
                    <input type="text" id="name" name="name" required placeholder="e.g., Personal Budgeting">
                </div>
                <button type="submit" class="btn btn-primary">Add Category</button>
            </form>
        </div>

        <h2 style="margin-top: 40px;">Existing Categories</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Priority Score</th>
                    <th>Status</th>
                    <th>Last Used</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                        <td><?php echo htmlspecialchars($category['priority_score']); ?></td>
                        <td><?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?></td>
                        <td><?php echo $category['last_used_at'] ?? 'Never'; ?></td>
                        <td class="action-links">
                            <a href="?action=edit&id=<?php echo $category['id']; ?>">Edit</a>
                            <a href="?action=delete&id=<?php echo $category['id']; ?>" style="color: #dc3545;">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        break;
}

// Add some CSS for alerts
echo '<style>
.alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
.alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
.alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
</style>';

require_once __DIR__ . '/footer.php';
?>
