<?php
require_once '../includes/config.php';

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product']) || isset($_POST['edit_product'])) {
        // Get form data
        $title = isset($_POST['title']) ? sanitize_admin_input($_POST['title']) : '';
        $slug = isset($_POST['slug']) ? sanitize_admin_input($_POST['slug']) : '';
        $short_description = isset($_POST['short_description']) ? sanitize_admin_input($_POST['short_description']) : '';
        $description = isset($_POST['description']) ? $_POST['description'] : ''; // Allow HTML
        $age_range = isset($_POST['age_range']) ? sanitize_admin_input($_POST['age_range']) : '';
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        $sale_price = isset($_POST['sale_price']) ? (float)$_POST['sale_price'] : 0;
        $category = isset($_POST['category']) ? sanitize_admin_input($_POST['category']) : '';
        $features = isset($_POST['features']) ? sanitize_admin_input($_POST['features']) : '';
        $specifications = isset($_POST['specifications']) ? sanitize_admin_input($_POST['specifications']) : '';
        $stock_status = isset($_POST['stock_status']) ? sanitize_admin_input($_POST['stock_status']) : 'in_stock';
        $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
        $active = isset($_POST['active']) ? 1 : 0;
        
        // Generate slug if empty
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $title), '-'));
        }
        
        // Validate form data
        if (empty($title)) {
            set_admin_alert('Product title is required.', 'danger');
        } elseif (empty($short_description)) {
            set_admin_alert('Short description is required.', 'danger');
        } elseif (empty($description)) {
            set_admin_alert('Description is required.', 'danger');
        } elseif (empty($age_range)) {
            set_admin_alert('Age range is required.', 'danger');
        } elseif (empty($category)) {
            set_admin_alert('Category is required.', 'danger');
        } else {
            // Handle image upload
            $image = '';
            if (isset($_POST['current_image'])) {
                $image = $_POST['current_image'];
            }
            
            if (!empty($_FILES['image']['name'])) {
                $upload_dir = ROOT_PATH . 'assets/images/kids/products/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['image']['name']);
                $target_file = $upload_dir . $file_name;
                $relative_path = 'assets/images/kids/products/' . $file_name;
                
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
                if (isset($_POST['add_product'])) {
                    // Add new product
                    $stmt = $db->prepare("
                        INSERT INTO kids_products (
                            title, slug, short_description, description, age_range, 
                            price, sale_price, image, category, features, 
                            specifications, stock_status, display_order, active
                        )
                        VALUES (
                            :title, :slug, :short_description, :description, :age_range, 
                            :price, :sale_price, :image, :category, :features, 
                            :specifications, :stock_status, :display_order, :active
                        )
                    ");
                    $stmt->execute([
                        'title' => $title,
                        'slug' => $slug,
                        'short_description' => $short_description,
                        'description' => $description,
                        'age_range' => $age_range,
                        'price' => $price,
                        'sale_price' => $sale_price,
                        'image' => $image,
                        'category' => $category,
                        'features' => $features,
                        'specifications' => $specifications,
                        'stock_status' => $stock_status,
                        'display_order' => $display_order,
                        'active' => $active
                    ]);
                    
                    set_admin_alert('Product added successfully.', 'success');
                    header('Location: ' . ADMIN_URL . '/pages/kids-products.php');
                    exit;
                } elseif (isset($_POST['edit_product'])) {
                    // Edit existing product
                    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
                    
                    if ($product_id > 0) {
                        // Update product
                        $stmt = $db->prepare("
                            UPDATE kids_products
                            SET title = :title, slug = :slug, short_description = :short_description, 
                                description = :description, age_range = :age_range, price = :price, 
                                sale_price = :sale_price, image = :image, category = :category, 
                                features = :features, specifications = :specifications, 
                                stock_status = :stock_status, display_order = :display_order, active = :active
                            WHERE id = :id
                        ");
                        $stmt->execute([
                            'title' => $title,
                            'slug' => $slug,
                            'short_description' => $short_description,
                            'description' => $description,
                            'age_range' => $age_range,
                            'price' => $price,
                            'sale_price' => $sale_price,
                            'image' => $image,
                            'category' => $category,
                            'features' => $features,
                            'specifications' => $specifications,
                            'stock_status' => $stock_status,
                            'display_order' => $display_order,
                            'active' => $active,
                            'id' => $product_id
                        ]);
                        
                        set_admin_alert('Product updated successfully.', 'success');
                        header('Location: ' . ADMIN_URL . '/pages/kids-products.php');
                        exit;
                    }
                }
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
    } elseif (isset($_POST['delete_product'])) {
        // Delete product
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        
        if ($product_id > 0) {
            try {
                // Get product image
                $stmt = $db->prepare("SELECT image FROM kids_products WHERE id = :id");
                $stmt->execute(['id' => $product_id]);
                $product = $stmt->fetch();
                
                // Delete product from database
                $stmt = $db->prepare("DELETE FROM kids_products WHERE id = :id");
                $stmt->execute(['id' => $product_id]);
                
                // Delete product image if exists
                if ($product && !empty($product['image'])) {
                    $image_path = ROOT_PATH . $product['image'];
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
                
                set_admin_alert('Product deleted successfully.', 'success');
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: ' . ADMIN_URL . '/pages/kids-products.php');
        exit;
    }
}

// Get product data for editing
$product = null;
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM kids_products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            set_admin_alert('Product not found.', 'danger');
            header('Location: ' . ADMIN_URL . '/pages/kids-products.php');
            exit;
        }
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        header('Location: ' . ADMIN_URL . '/pages/kids-products.php');
        exit;
    }
}

// Get all products for listing
$products = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("SELECT * FROM kids_products ORDER BY display_order ASC, created_at DESC");
        $stmt->execute();
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
    }
}

// Get product categories
$categories = [];
try {
    $stmt = $db->prepare("SELECT DISTINCT category FROM kids_products ORDER BY category");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Ignore error
}

// Default categories if none exist
if (empty($categories)) {
    $categories = ['Robotics', 'Coding Games', 'Electronics', 'STEM Kits', 'Educational Toys'];
}

include '../includes/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- Add/Edit Product Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><?php echo $action === 'add' ? 'Add New Kids Product' : 'Edit Kids Product'; ?></h5>
        </div>
        <div class="card-body">
            <form action="" method="post" enctype="multipart/form-data">
                <?php if ($action === 'edit' && $product): ?>
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <?php if (!empty($product['image'])): ?>
                        <input type="hidden" name="current_image" value="<?php echo $product['image']; ?>">
                    <?php endif; ?>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Product Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo $product ? $product['title'] : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" value="<?php echo $product ? $product['slug'] : ''; ?>">
                            <small class="form-text text-muted">Leave blank to auto-generate from title. Use only lowercase letters, numbers, and hyphens.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="short_description" class="form-label">Short Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="short_description" name="short_description" rows="2" maxlength="255" required><?php echo $product ? $product['short_description'] : ''; ?></textarea>
                            <small class="form-text text-muted">Brief summary of the product (max 255 characters)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Full Description <span class="text-danger">*</span></label>
                            <textarea class="form-control summernote" id="description" name="description" rows="10"><?php echo $product ? $product['description'] : ''; ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="features" class="form-label">Features</label>
                                    <textarea class="form-control" id="features" name="features" rows="6"><?php echo $product ? $product['features'] : ''; ?></textarea>
                                    <small class="form-text text-muted">Enter each feature on a new line, starting with a dash (-)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="specifications" class="form-label">Specifications</label>
                                    <textarea class="form-control" id="specifications" name="specifications" rows="6"><?php echo $product ? $product['specifications'] : ''; ?></textarea>
                                    <small class="form-text text-muted">Enter each specification on a new line in "Name: Value" format</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>" <?php echo ($product && $product['category'] === $cat) ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                                <?php endforeach; ?>
                                <option value="other">Other (New Category)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="new_category_container" style="display: none;">
                            <label for="new_category" class="form-label">New Category Name</label>
                            <input type="text" class="form-control" id="new_category" name="new_category">
                        </div>
                        
                        <div class="mb-3">
                            <label for="age_range" class="form-label">Age Range <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="age_range" name="age_range" value="<?php echo $product ? $product['age_range'] : ''; ?>" required>
                            <small class="form-text text-muted">e.g., "7-12 years"</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Regular Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo $product ? $product['price'] : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sale_price" class="form-label">Sale Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="sale_price" name="sale_price" step="0.01" min="0" value="<?php echo $product ? $product['sale_price'] : ''; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="stock_status" class="form-label">Stock Status</label>
                            <select class="form-select" id="stock_status" name="stock_status">
                                <option value="in_stock" <?php echo ($product && $product['stock_status'] === 'in_stock') ? 'selected' : ''; ?>>In Stock</option>
                                <option value="out_of_stock" <?php echo ($product && $product['stock_status'] === 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock</option>
                                <option value="backorder" <?php echo ($product && $product['stock_status'] === 'backorder') ? 'selected' : ''; ?>>On Backorder</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="display_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo $product ? $product['display_order'] : '0'; ?>" min="0">
                            <small class="form-text text-muted">Lower numbers will be displayed first</small>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="active" name="active" <?php echo (!$product || $product['active']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Product Image</label>
                            <?php if ($product && !empty($product['image'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo SITE_URL . '/' . $product['image']; ?>" alt="<?php echo $product['title']; ?>" class="img-thumbnail" style="max-height: 150px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="form-text text-muted">Recommended size: 800x600 pixels, max 5MB</small>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" name="<?php echo $action === 'add' ? 'add_product' : 'edit_product'; ?>" class="btn btn-primary">
                        <?php echo $action === 'add' ? 'Add Product' : 'Update Product'; ?>
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/pages/kids-products.php" class="btn btn-secondary ms-2">Cancel</a>
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
        
        // Show/hide new category input
        const categorySelect = document.getElementById('category');
        const newCategoryContainer = document.getElementById('new_category_container');
        const newCategoryInput = document.getElementById('new_category');
        
        categorySelect.addEventListener('change', function() {
            if (this.value === 'other') {
                newCategoryContainer.style.display = 'block';
                newCategoryInput.setAttribute('required', 'required');
            } else {
                newCategoryContainer.style.display = 'none';
                newCategoryInput.removeAttribute('required');
            }
        });
    });
    </script>
<?php else: ?>
    <!-- Products List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Kids Products</h5>
            <a href="<?php echo ADMIN_URL; ?>/pages/kids-products.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Add New Product
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($products)): ?>
                <div class="alert alert-info">
                    No products found. Click the "Add New Product" button to create one.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Age Range</th>
                                <th>Price</th>
                                <th>Stock Status</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td>
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="<?php echo SITE_URL . '/' . $product['image']; ?>" alt="<?php echo $product['title']; ?>" class="img-thumbnail" style="max-height: 50px;">
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-image"></i> No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $product['title']; ?></td>
                                    <td><?php echo $product['category']; ?></td>
                                    <td><?php echo $product['age_range']; ?></td>
                                    <td>
                                        <?php if ($product['sale_price'] > 0): ?>
                                            <span class="text-decoration-line-through text-muted">$<?php echo number_format($product['price'], 2); ?></span>
                                            <span class="text-danger">$<?php echo number_format($product['sale_price'], 2); ?></span>
                                        <?php elseif ($product['price'] > 0): ?>
                                            $<?php echo number_format($product['price'], 2); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Contact for pricing</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($product['stock_status'] === 'in_stock'): ?>
                                            <span class="badge bg-success">In Stock</span>
                                        <?php elseif ($product['stock_status'] === 'out_of_stock'): ?>
                                            <span class="badge bg-danger">Out of Stock</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">On Backorder</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($product['active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/pages/kids-products.php?action=edit&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="" method="post" class="d-inline">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" name="delete_product" class="btn btn-sm btn-danger confirm-delete" data-bs-toggle="tooltip" title="Delete">
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