<?php
require_once 'config.php';

// Only allow admin users
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = 'You must be an administrator to access that page.';
    redirect('login.php');
}

// Ensure a CSRF token exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf_token'];

$pageTitle = 'Admin Dashboard - RentGo';
$homeLink = 'index.php';
include 'includes/header.php';
include 'includes/messages.php';
include 'includes/modal_styles.php';

// Fetch cars
$cars = [];
$res = mysqli_query($conn, "SELECT * FROM cars ORDER BY created_at DESC");
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) $cars[] = $r;
}
?>
<main class="container" style="padding: 6rem 0 3rem;">
    <h1>Admin Dashboard</h1>
    <p>Welcome, <?php echo e($_SESSION['username'] ?? 'Admin'); ?>.</p>

    <div style="margin:12px 0 18px 0; display:flex; gap:12px; flex-wrap:wrap;">
        <a href="admin_bookings.php" class="auth-btn" style="text-decoration:none;">Bookings & Reviews</a>
        <a href="dashboard.php" class="auth-btn" style="background:#888;text-decoration:none;">Refresh</a>
    </div>

    <section style="margin-top: 30px;">
        <h2>Add New Car</h2>
        <!-- Normal form POST to process_car.php (not AJAX) -->
        <form id="add-car-form" action="process_car.php?action=add" method="post" enctype="multipart/form-data" style="max-width:700px;">
            <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">
            <div class="input-group">
                <label for="name">Car Name</label>
                <input id="name" type="text" name="name" required>
            </div>
            <div class="input-group">
                <label for="type">Type</label>
                <input id="type" type="text" name="type">
            </div>
            <div class="input-group">
                <label for="gear">Gear</label>
                <select id="gear" name="gear" required>
                    <option value="Automatic">Automatic</option>
                    <option value="Manual">Manual</option>
                </select>
            </div>
            <div class="input-group">
                <label for="engine">Engine</label>
                <select id="engine" name="engine" required>
                    <option value="Gasoline">Gasoline</option>
                    <option value="Diesel">Diesel</option>
                    <option value="Hybrid">Hybrid</option>
                    <option value="Electric">Electric</option>
                </select>
            </div>
            <div class="input-group">
                <label for="price">Price</label>
                <input id="price" type="number" name="price" min="0" required>
            </div>
            <div class="input-group">
                <label for="image">Image (JPG/PNG, max 2MB)</label>
                <input id="image" type="file" name="image" accept="image/jpeg,image/png" required>
            </div>

            <div class="input-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3" placeholder="Short description"></textarea>
                <div style="margin-top:8px;">
                    <button type="button" class="generate-desc-btn auth-btn" data-form="add-car-form">Generate</button>
                </div>
            </div>

            <div style="margin-top:10px;">
                <button type="submit" class="auth-btn">Add Car</button>
            </div>
        </form>
    </section>

    <section style="margin-top: 40px;">
        <h2>Available Cars</h2>
        <div style="overflow:auto">
        <table border="1" cellpadding="8" cellspacing="0" width="100%" style="border-collapse:collapse;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Gear</th>
                    <th>Engine</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cars)): ?>
                    <tr><td colspan="10">No cars found.</td></tr>
                <?php else: ?>
                    <?php foreach ($cars as $c): ?>
                        <tr>
                            <td><?php echo e((string)$c['id']); ?></td>
                            <td style="width:120px;">
                                <?php if (!empty($c['image'])): ?>
                                    <img src="<?php echo e($c['image']); ?>" alt="<?php echo e($c['name']); ?>" style="max-width:110px;height:auto;display:block;">
                                <?php else: ?>
                                    <div style="width:110px;height:70px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#888;">No Image</div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($c['name']); ?></td>
                            <td><?php echo e($c['type'] ?? ''); ?></td>
                            <td><?php echo e($c['gear'] ?? ''); ?></td>
                            <td><?php echo e($c['engine'] ?? ''); ?></td>
                            <td>$<?php echo e((string)$c['price']); ?></td>
                            <td style="max-width:220px;white-space:normal;"><?php echo e($c['description'] ?? ''); ?></td>
                            <td><?php echo e($c['created_at'] ?? ''); ?></td>
                            <td>
                                <!-- Toggle edit row -->
                                <a href="#" class="edit-toggle" data-id="<?php echo e((string)$c['id']); ?>">Edit</a>

                                <!-- Delete uses GET and includes csrf_token (process_car expects csrf_token in REQUEST) -->
                                <a href="process_car.php?action=delete&id=<?php echo e((string)$c['id']); ?>&csrf_token=<?php echo e($csrf); ?>" onclick="return confirm('Delete this car?');" style="color:#c00;margin-left:8px;">Delete</a>
                            </td>
                        </tr>
                        <tr class="edit-row" id="edit-row-<?php echo e((string)$c['id']); ?>" style="display:none;">
                            <td colspan="10">
                                <!-- Normal form posts to process_car.php?action=update -->
                                <form class="edit-car-form" action="process_car.php?action=update" method="post" enctype="multipart/form-data" style="max-width:700px;">
                                    <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">
                                    <input type="hidden" name="id" value="<?php echo e((string)$c['id']); ?>">
                                    <div class="input-group">
                                        <label>Name</label>
                                        <input type="text" name="name" value="<?php echo e($c['name']); ?>" required>
                                    </div>
                                    <div class="input-group">
                                        <label>Type</label>
                                        <input type="text" name="type" value="<?php echo e($c['type']); ?>">
                                    </div>
                                    <div class="input-group">
                                        <label>Gear</label>
                                        <select name="gear" required>
                                            <option value="Automatic" <?php echo (isset($c['gear']) && $c['gear']==='Automatic') ? 'selected' : ''; ?>>Automatic</option>
                                            <option value="Manual" <?php echo (isset($c['gear']) && $c['gear']==='Manual') ? 'selected' : ''; ?>>Manual</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <label>Engine</label>
                                        <select name="engine" required>
                                            <option value="Gasoline" <?php echo (isset($c['engine']) && $c['engine']==='Gasoline') ? 'selected' : ''; ?>>Gasoline</option>
                                            <option value="Diesel" <?php echo (isset($c['engine']) && $c['engine']==='Diesel') ? 'selected' : ''; ?>>Diesel</option>
                                            <option value="Hybrid" <?php echo (isset($c['engine']) && $c['engine']==='Hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                                            <option value="Electric" <?php echo (isset($c['engine']) && $c['engine']==='Electric') ? 'selected' : ''; ?>>Electric</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <label>Price</label>
                                        <input type="number" name="price" value="<?php echo e((string)$c['price']); ?>" min="0" required>
                                    </div>
                                    <div class="input-group">
                                        <label>Replace Image (optional)</label>
                                        <input type="file" name="image" accept="image/jpeg,image/png">
                                    </div>

                                    <div class="input-group">
                                        <label>Description</label>
                                        <textarea name="description" rows="3"><?php echo e($c['description'] ?? ''); ?></textarea>
                                        <div style="margin-top:8px;">
                                            <button type="button" class="generate-desc-btn auth-btn" data-car-id="<?php echo e((string)$c['id']); ?>">Generate</button>
                                        </div>
                                    </div>

                                    <div style="margin-top:10px;">
                                        <button type="submit" class="auth-btn">Save</button>
                                        <button type="button" class="cancel-edit" data-id="<?php echo e((string)$c['id']); ?>">Cancel</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </section>
</main>

<script>
// Handle generate description button clicks (delegated)
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.generate-desc-btn');
    if (!btn) return;

    e.preventDefault();
    const originalText = btn.innerText;
    btn.disabled = true;
    btn.innerText = 'Generating...';

    // Find the containing form (for edit) or the add form
    let form;
    if (btn.dataset.form) {
        form = document.getElementById(btn.dataset.form);
    } else {
        form = btn.closest('form');
    }
    if (!form) {
        alert('Form not found');
        btn.disabled = false;
        btn.innerText = originalText;
        return;
    }

    // Determine name (from input[name="name"])
    const nameInput = form.querySelector('input[name="name"]');
    const descriptionEl = form.querySelector('textarea[name="description"]');
    const carId = btn.dataset.carId || form.querySelector('input[name="id"]')?.value || '';
    const name = nameInput ? nameInput.value.trim() : '';

    if (!name) {
        alert('Please enter the vehicle name first.');
        btn.disabled = false;
        btn.innerText = originalText;
        return;
    }

    const payload = {
        name: name,
        car_id: carId,
        csrf_token: form.querySelector('input[name="csrf_token"]') ? form.querySelector('input[name="csrf_token"]').value : ''
    };

    fetch('api/generate_description.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(json => {
        if (!json || !json.success) {
            alert('Failed to generate description: ' + (json?.message || 'Unknown error'));
            return;
        }
        if (descriptionEl) {
            descriptionEl.value = json.description;
        } else {
            alert('Generated: ' + json.description);
        }
    })
    .catch(err => {
        alert('Network error: ' + err.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerText = originalText;
    });
});

// Toggle edit rows (existing)
document.addEventListener('click', function (e) {
    const toggle = e.target.closest('.edit-toggle');
    if (toggle) {
        e.preventDefault();
        const id = toggle.getAttribute('data-id');
        const row = document.getElementById('edit-row-' + id);
        if (row) row.style.display = row.style.display === 'none' ? '' : 'none';
    }
    const cancel = e.target.closest('.cancel-edit');
    if (cancel) {
        const id = cancel.getAttribute('data-id');
        const row = document.getElementById('edit-row-' + id);
        if (row) row.style.display = 'none';
    }
});
</script>

<?php
include 'includes/modal.php';
include 'includes/footer.php';
?>