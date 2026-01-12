<?php
require_once '../includes/config.php';

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_program']) || isset($_POST['edit_program'])) {
        // Get form data
        $title = isset($_POST['title']) ? sanitize_admin_input($_POST['title']) : '';
        $slug = isset($_POST['slug']) ? sanitize_admin_input($_POST['slug']) : '';
        $short_description = isset($_POST['short_description']) ? sanitize_admin_input($_POST['short_description']) : '';
        $description = isset($_POST['description']) ? $_POST['description'] : ''; // Allow HTML
        $age_range = isset($_POST['age_range']) ? sanitize_admin_input($_POST['age_range']) : '';
        $duration = isset($_POST['duration']) ? sanitize_admin_input($_POST['duration']) : '';
        $schedule = isset($_POST['schedule']) ? sanitize_admin_input($_POST['schedule']) : '';
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : null;
        $max_participants = isset($_POST['max_participants']) ? (int)$_POST['max_participants'] : 0;
        $location = isset($_POST['location']) ? sanitize_admin_input($_POST['location']) : '';
        $is_online = isset($_POST['is_online']) ? 1 : 0;
        $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
        $active = isset($_POST['active']) ? 1 : 0;
        
        // Generate slug if empty
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $title), '-'));
        }
        
        // Validate form data
        if (empty($title)) {
            set_admin_alert('Program title is required.', 'danger');
        } elseif (empty($short_description)) {
            set_admin_alert('Short description is required.', 'danger');
        } elseif (empty($description)) {
            set_admin_alert('Description is required.', 'danger');
        } elseif (empty($age_range)) {
            set_admin_alert('Age range is required.', 'danger');
        } elseif (empty($duration)) {
            set_admin_alert('Duration is required.', 'danger');
        } else {
            // Handle image upload
            $image = '';
            if (isset($_POST['current_image'])) {
                $image = $_POST['current_image'];
            }
            
            if (!empty($_FILES['image']['name'])) {
                $upload_dir = ROOT_PATH . 'assets/images/kids/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['image']['name']);
                $target_file = $upload_dir . $file_name;
                $relative_path = 'assets/images/kids/' . $file_name;
                
                $upload_ok = true;
                $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                
                // Check if image file is an actual image
                $check = getimagesize($_FILES['image']['tmp_name']);
                if ($check === false) {
                    set_admin_alert('File is not an image.', 'danger');
                    $upload_ok = false;
                }
                
                // Check file size (limit to 5MB)
                if ($_FILES['image']['size'] > 5000000) {
                    set_admin_alert('Image file is too large. Maximum size is 5MB.', 'danger');
                    $upload_ok = false;
                }
                
                // Allow certain file formats
                if (!in_array($image_file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                    set_admin_alert('Only JPG, JPEG, PNG & GIF files are allowed.', 'danger');
                    $upload_ok = false;
                }
                
                // Upload file
                if ($upload_ok) {
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image = $relative_path;
                    } else {
                        set_admin_alert('There was an error uploading your file.', 'danger');
                    }
                }
            }
            
            try {
                if (isset($_POST['add_program'])) {
                    // Add new program
                    $stmt = $db->prepare("
                        INSERT INTO kids_programs (
                            title, slug, short_description, description, age_range, 
                            duration, schedule, price, image, start_date, end_date, 
                            max_participants, location, is_online, display_order, active
                        )
                        VALUES (
                            :title, :slug, :short_description, :description, :age_range, 
                            :duration, :schedule, :price, :image, :start_date, :end_date, 
                            :max_participants, :location, :is_online, :display_order, :active
                        )
                    ");
                    $stmt->execute([
                        'title' => $title,
                        'slug' => $slug,
                        'short_description' => $short_description,
                        'description' => $description,
                        'age_range' => $age_range,
                        'duration' => $duration,
                        'schedule' => $schedule,
                        'price' => $price,
                        'image' => $image,
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'max_participants' => $max_participants,
                        'location' => $location,
                        'is_online' => $is_online,
                        'display_order' => $display_order,
                        'active' => $active
                    ]);
                    
                    set_admin_alert('Program added successfully.', 'success');
                    header('Location: ' . ADMIN_URL . '/pages/kids-programs.php');
                    exit;
                } elseif (isset($_POST['edit_program'])) {
                    // Edit existing program
                    $program_id = isset($_POST['program_id']) ? (int)$_POST['program_id'] : 0;
                    
                    if ($program_id > 0) {
                        // Update program
                        $stmt = $db->prepare("
                            UPDATE kids_programs
                            SET title = :title, slug = :slug, short_description = :short_description, 
                                description = :description, age_range = :age_range, duration = :duration, 
                                schedule = :schedule, price = :price, image = :image, 
                                start_date = :start_date, end_date = :end_date, 
                                max_participants = :max_participants, location = :location, 
                                is_online = :is_online, display_order = :display_order, active = :active
                            WHERE id = :id
                        ");
                        $stmt->execute([
                            'title' => $title,
                            'slug' => $slug,
                            'short_description' => $short_description,
                            'description' => $description,
                            'age_range' => $age_range,
                            'duration' => $duration,
                            'schedule' => $schedule,
                            'price' => $price,
                            'image' => $image,
                            'start_date' => $start_date,
                            'end_date' => $end_date,
                            'max_participants' => $max_participants,
                            'location' => $location,
                            'is_online' => $is_online,
                            'display_order' => $display_order,
                            'active' => $active,
                            'id' => $program_id
                        ]);
                        
                        set_admin_alert('Program updated successfully.', 'success');
                        header('Location: ' . ADMIN_URL . '/pages/kids-programs.php');
                        exit;
                    }
                }
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
    } elseif (isset($_POST['delete_program'])) {
        // Delete program
        $program_id = isset($_POST['program_id']) ? (int)$_POST['program_id'] : 0;
        
        if ($program_id > 0) {
            try {
                // Get program image
                $stmt = $db->prepare("SELECT image FROM kids_programs WHERE id = :id");
                $stmt->execute(['id' => $program_id]);
                $program = $stmt->fetch();
                
                // Delete program from database
                $stmt = $db->prepare("DELETE FROM kids_programs WHERE id = :id");
                $stmt->execute(['id' => $program_id]);
                
                // Delete program image if exists
                if ($program && !empty($program['image'])) {
                    $image_path = ROOT_PATH . $program['image'];
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
                
                set_admin_alert('Program deleted successfully.', 'success');
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: ' . ADMIN_URL . '/pages/kids-programs.php');
        exit;
    }
}

// Get program data for editing
$program = null;
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM kids_programs WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $program = $stmt->fetch();
        
        if (!$program) {
            set_admin_alert('Program not found.', 'danger');
            header('Location: ' . ADMIN_URL . '/pages/kids-programs.php');
            exit;
        }
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        header('Location: ' . ADMIN_URL . '/pages/kids-programs.php');
        exit;
    }
}

// Get all programs for listing
$programs = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("SELECT * FROM kids_programs ORDER BY display_order ASC, created_at DESC");
        $stmt->execute();
        $programs = $stmt->fetchAll();
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
    }
}

include '../includes/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- Add/Edit Program Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><?php echo $action === 'add' ? 'Add New Program' : 'Edit Program'; ?></h5>
        </div>
        <div class="card-body">
            <form action="" method="post" enctype="multipart/form-data">
                <?php if ($action === 'edit' && $program): ?>
                    <input type="hidden" name="program_id" value="<?php echo $program['id']; ?>">
                    <?php if (!empty($program['image'])): ?>
                        <input type="hidden" name="current_image" value="<?php echo $program['image']; ?>">
                    <?php endif; ?>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Program Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo $program ? $program['title'] : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" value="<?php echo $program ? $program['slug'] : ''; ?>">
                            <small class="form-text text-muted">Leave blank to auto-generate from title. Use only lowercase letters, numbers, and hyphens.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="short_description" class="form-label">Short Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="short_description" name="short_description" rows="2" maxlength="255" required><?php echo $program ? $program['short_description'] : ''; ?></textarea>
                            <small class="form-text text-muted">Brief summary of the program (max 255 characters)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Full Description <span class="text-danger">*</span></label>
                            <textarea class="form-control summernote" id="description" name="description" rows="10"><?php echo $program ? $program['description'] : ''; ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="age_range" class="form-label">Age Range <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="age_range" name="age_range" value="<?php echo $program ? $program['age_range'] : ''; ?>" required>
                                    <small class="form-text text-muted">e.g., "7-12 years"</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duration" class="form-label">Duration <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="duration" name="duration" value="<?php echo $program ? $program['duration'] : ''; ?>" required>
                                    <small class="form-text text-muted">e.g., "8 weeks", "3 months"</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="schedule" class="form-label">Schedule</label>
                            <input type="text" class="form-control" id="schedule" name="schedule" value="<?php echo $program ? $program['schedule'] : ''; ?>">
                            <small class="form-text text-muted">e.g., "Saturdays, 10:00 AM - 12:00 PM"</small>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo $program ? $program['price'] : ''; ?>">
                            </div>
                            <small class="form-text text-muted">Leave blank or set to 0 for "Contact for Pricing"</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $program ? $program['start_date'] : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $program ? $program['end_date'] : ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="max_participants" class="form-label">Maximum Participants</label>
                            <input type="number" class="form-control" id="max_participants" name="max_participants" min="0" value="<?php echo $program ? $program['max_participants'] : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo $program ? $program['location'] : ''; ?>">
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_online" name="is_online" <?php echo ($program && $program['is_online']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_online">Online Program</label>
                        </div>
                        
                        <div class="mb-3">
                            <label for="display_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo $program ? $program['display_order'] : '0'; ?>" min="0">
                            <small class="form-text text-muted">Lower numbers will be displayed first</small>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="active" name="active" <?php echo (!$program || $program['active']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Program Image</label>
                            <?php if ($program && !empty($program['image'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo SITE_URL . '/' . $program['image']; ?>" alt="<?php echo $program['title']; ?>" class="img-thumbnail" style="max-height: 150px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="form-text text-muted">Recommended size: 800x600 pixels, max 5MB</small>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" name="<?php echo $action === 'add' ? 'add_program' : 'edit_program'; ?>" class="btn btn-primary">
                        <?php echo $action === 'add' ? 'Add Program' : 'Update Program'; ?>
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/pages/kids-programs.php" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-generate slug from title
        const titleInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');
        
        titleInput.addEventListener('blur', function() {
            if (slugInput.value === '') {
                slugInput.value = this.value
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        });
    });
    </script>
<?php else: ?>
    <!-- Programs List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Kids Programs</h5>
            <a href="<?php echo ADMIN_URL; ?>/pages/kids-programs.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Add New Program
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($programs)): ?>
                <div class="alert alert-info">
                    No programs found. Click the "Add New Program" button to create one.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Age Range</th>
                                <th>Duration</th>
                                <th>Price</th>
                                <th>Dates</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($programs as $program): ?>
                                <tr>
                                    <td><?php echo $program['id']; ?></td>
                                    <td>
                                        <?php if (!empty($program['image'])): ?>
                                            <img src="<?php echo SITE_URL . '/' . $program['image']; ?>" alt="<?php echo $program['title']; ?>" class="img-thumbnail" style="max-height: 50px;">
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-image"></i> No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $program['title']; ?></td>
                                    <td><?php echo $program['age_range']; ?></td>
                                    <td><?php echo $program['duration']; ?></td>
                                    <td>
                                        <?php if ($program['price'] > 0): ?>
                                            $<?php echo number_format($program['price'], 2); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Contact for pricing</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($program['start_date'] && $program['end_date']): ?>
                                            <?php echo date('M j, Y', strtotime($program['start_date'])); ?> - 
                                            <?php echo date('M j, Y', strtotime($program['end_date'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not specified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($program['active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/pages/kids-programs.php?action=edit&id=<?php echo $program['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo ADMIN_URL; ?>/pages/program-gallery.php?program_id=<?php echo $program['id']; ?>" class="btn btn-sm btn-info" title="Manage Gallery">
                                            <i class="fas fa-images"></i>
                                        </a>
                                        <form action="" method="post" class="d-inline">
                                            <input type="hidden" name="program_id" value="<?php echo $program['id']; ?>">
                                            <button type="submit" name="delete_program" class="btn btn-sm btn-danger confirm-delete" data-bs-toggle="tooltip" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>