<?php
$page_title = "Announcements";
require_once 'includes/header.php';
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            if ($_POST['action'] === 'add') {
                $stmt = $conn->prepare("INSERT INTO announcements (title, content, category, date_posted, event_date, posted_by, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$_POST['title'], $_POST['content'], $_POST['category'], $_POST['date_posted'], $_POST['event_date'] ?: null, $_SESSION['user_id'], $_POST['status']]);
                logActivity($conn, $_SESSION['user_id'], 'Posted announcement: ' . $_POST['title'], 'Announcements');
                $success = 'Announcement posted successfully!';
            } elseif ($_POST['action'] === 'edit') {
                $stmt = $conn->prepare("UPDATE announcements SET title=?, content=?, category=?, event_date=?, status=? WHERE announcement_id=?");
                $stmt->execute([$_POST['title'], $_POST['content'], $_POST['category'], $_POST['event_date'] ?: null, $_POST['status'], $_POST['announcement_id']]);
                logActivity($conn, $_SESSION['user_id'], 'Updated announcement ID: ' . $_POST['announcement_id'], 'Announcements');
                $success = 'Announcement updated successfully!';
            } elseif ($_POST['action'] === 'delete') {
                $stmt = $conn->prepare("DELETE FROM announcements WHERE announcement_id=?");
                $stmt->execute([$_POST['announcement_id']]);
                logActivity($conn, $_SESSION['user_id'], 'Deleted announcement ID: ' . $_POST['announcement_id'], 'Announcements');
                $success = 'Announcement deleted successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

try {
    $stmt = $conn->query("SELECT a.*, u.fullname as posted_by_name FROM announcements a JOIN users u ON a.posted_by = u.user_id ORDER BY a.date_posted DESC");
    $announcements = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching announcements: ' . $e->getMessage();
}
?>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-bullhorn me-2"></i>Announcements</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
            <i class="fas fa-plus me-2"></i>Post Announcement
        </button>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <?php foreach ($announcements as $announcement): ?>
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="badge bg-<?php 
                            echo $announcement['category'] == 'Event' ? 'primary' : 
                                ($announcement['category'] == 'Emergency' ? 'danger' : 
                                ($announcement['category'] == 'Notice' ? 'warning' : 'info')); 
                        ?>"><?php echo $announcement['category']; ?></span>
                        <small class="text-muted"><?php echo formatDate($announcement['date_posted']); ?></small>
                    </div>
                    <div class="card-body">
                        <h5><?php echo htmlspecialchars($announcement['title']); ?></h5>
                        <p><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                        <?php if ($announcement['event_date']): ?>
                            <p class="mb-0"><i class="fas fa-calendar me-2"></i><strong>Event Date:</strong> <?php echo formatDate($announcement['event_date']); ?></p>
                        <?php endif; ?>
                        <small class="text-muted">Posted by: <?php echo $announcement['posted_by_name']; ?></small>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-sm btn-warning" onclick='editAnnouncement(<?php echo json_encode($announcement); ?>)'>
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <form method="POST" style="display:inline;" onsubmit="return confirmDelete()">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="announcement_id" value="<?php echo $announcement['announcement_id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addAnnouncementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-bullhorn me-2"></i>Post Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content *</label>
                        <textarea class="form-control" name="content" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category *</label>
                        <select class="form-select" name="category" required>
                            <option value="General">General</option>
                            <option value="Event">Event</option>
                            <option value="Notice">Notice</option>
                            <option value="Emergency">Emergency</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Event Date (if applicable)</label>
                        <input type="date" class="form-control" name="event_date">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date Posted *</label>
                        <input type="date" class="form-control" name="date_posted" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select class="form-select" name="status" required>
                            <option value="Active">Active</option>
                            <option value="Archived">Archived</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Post</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editAnnouncementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="announcement_id" id="edit_announcement_id">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" id="edit_title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content *</label>
                        <textarea class="form-control" name="content" id="edit_content" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category *</label>
                        <select class="form-select" name="category" id="edit_category" required>
                            <option value="General">General</option>
                            <option value="Event">Event</option>
                            <option value="Notice">Notice</option>
                            <option value="Emergency">Emergency</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Event Date</label>
                        <input type="date" class="form-control" name="event_date" id="edit_event_date">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select class="form-select" name="status" id="edit_status" required>
                            <option value="Active">Active</option>
                            <option value="Archived">Archived</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editAnnouncement(announcement) {
    document.getElementById('edit_announcement_id').value = announcement.announcement_id;
    document.getElementById('edit_title').value = announcement.title;
    document.getElementById('edit_content').value = announcement.content;
    document.getElementById('edit_category').value = announcement.category;
    document.getElementById('edit_event_date').value = announcement.event_date || '';
    document.getElementById('edit_status').value = announcement.status;
    new bootstrap.Modal(document.getElementById('editAnnouncementModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>
