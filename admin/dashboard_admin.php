<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

// Mengambil data services dengan harga
$services_result = $conn->query("SELECT * FROM services ORDER BY name");
$services = [];
while ($srv = $services_result->fetch_assoc()) {
    $services[] = $srv;
}

// Handle update harga service
if (isset($_POST['update_service_price'])) {
    $service_id = $_POST['service_id'];
    $new_price = $_POST['new_price'];
    
    $stmt = $conn->prepare("UPDATE services SET price = ? WHERE id = ?");
    $stmt->bind_param("di", $new_price, $service_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: dashboard_admin.php");
    exit;
}

$sid = isset($_GET['service_id']) ? $_GET['service_id'] : '';
$where = '';
$params = [];
$types = '';
if ($sid && $sid !== 'all') {
    $where = "WHERE orders.service_id = ?";
    $params[] = $sid;
    $types .= 'i';
}

$sql = "SELECT orders.*, users.username, services.name AS service_name, services.price AS service_price 
        FROM orders 
        JOIN users ON orders.user_id = users.id
        JOIN services ON orders.service_id = services.id
        $where
        ORDER BY orders.order_date DESC";
$stmt = $conn->prepare($sql);
if ($where) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$orders = $stmt->get_result();

if (isset($_POST['update_status'])) {
    $stmt2 = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt2->bind_param("si", $_POST['status'], $_POST['order_id']);
    $stmt2->execute();
    $stmt2->close();
    header("Location: dashboard_admin.php" . ($sid ? "?service_id=$sid" : ""));
    exit;
}

if (isset($_POST['update_order'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $weight = $_POST['weight'];
    $service_id = $_POST['service_id'];
    
    // Ambil harga service untuk menghitung ulang total
    $price_query = $conn->prepare("SELECT price FROM services WHERE id = ?");
    $price_query->bind_param("i", $service_id);
    $price_query->execute();
    $price_result = $price_query->get_result();
    $price_data = $price_result->fetch_assoc();
    $total_price = $price_data['price'] * $weight;

    $sql2 = "UPDATE orders SET status = ?, weight = ?, service_id = ?, total_price = ? WHERE id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("sdidi", $status, $weight, $service_id, $total_price, $order_id);
    $stmt2->execute();
    $stmt2->close();

    header("Location: dashboard_admin.php" . ($sid ? "?service_id=$sid" : ""));
    exit;
}

if (isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];
    $stmt2 = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt2->bind_param("i", $order_id);
    $stmt2->execute();
    $stmt2->close();

    header("Location: dashboard_admin.php" . ($sid ? "?service_id=$sid" : ""));
    exit;
}

// Hitung statistik
$stats_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN status = 'proses' THEN 1 ELSE 0 END) as process_orders,
    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as completed_orders,
    SUM(CASE WHEN status = 'diambil' THEN 1 ELSE 0 END) as picked_orders,
    COALESCE(SUM(total_price), 0) as total_revenue
    FROM orders";
$stats = $conn->query($stats_query)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fresh by Shaa - Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<div class="admin-container">
    <header class="admin-header">
        <h1>
            <i class="fas fa-tachometer-alt"></i>
            Dashboard Admin
            <span class="brand">Fresh by Shaa</span>
        </h1>
        <nav class="admin-nav">
            <a href="#statistik" class="nav-link"><i class="fas fa-chart-line"></i> Statistik</a>
            <a href="#kelola-harga" class="nav-link"><i class="fas fa-tags"></i> Kelola Harga</a>
            <a href="#kelola-pesanan" class="nav-link"><i class="fas fa-clipboard-list"></i> Pesanan</a>
            <a href="../logout.php" class="nav-link logout" onclick="return confirm('Yakin ingin logout?')">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </header>

    <main class="main-content">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h2><i class="fas fa-user-shield"></i> Selamat datang, Admin!</h2>
            <p>"Shaa Laundry, Bersih Tanpa Drama" - Kelola bisnis laundry dengan mudah dan efisien</p>
        </div>

        <!-- Statistik Dashboard -->
        <section id="statistik">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                    <h3>Total Pesanan</h3>
                    <p class="number"><?= $stats['total_orders'] ?></p>
                </div>
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-clock"></i></div>
                    <h3>Pending</h3>
                    <p class="number"><?= $stats['pending_orders'] ?></p>
                </div>
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-spinner"></i></div>
                    <h3>Proses</h3>
                    <p class="number"><?= $stats['process_orders'] ?></p>
                </div>
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                    <h3>Selesai</h3>
                    <p class="number"><?= $stats['completed_orders'] ?></p>
                </div>
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-hand-holding"></i></div>
                    <h3>Diambil</h3>
                    <p class="number"><?= $stats['picked_orders'] ?></p>
                </div>
                <div class="stat-card revenue-card">
                    <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                    <h3>Total Pendapatan</h3>
                    <p class="number">Rp <?= number_format($stats['total_revenue'], 0, ',', '.') ?></p>
                </div>
            </div>
        </section>
        
        <!-- Manajemen Harga -->
        <section id="kelola-harga" class="content-section">
            <h3 class="section-title">
                <i class="fas fa-tags"></i>
                Kelola Harga Layanan
            </h3>
            <div class="price-grid">
                <?php foreach ($services as $service): ?>
                <div class="service-price-card">
                    <div class="service-info">
                        <h4><?= htmlspecialchars($service['name']) ?></h4>
                        <div class="current-price">Rp <?= number_format($service['price'], 0, ',', '.') ?>/kg</div>
                    </div>
                    <form method="POST" class="price-update" onsubmit="return confirm('Yakin ingin mengubah harga?')">
                        <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                        <input type="number" name="new_price" value="<?= $service['price'] ?>" step="100" min="0" required>
                        <button type="submit" name="update_service_price" class="btn-update">
                            <i class="fas fa-save"></i> Update
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        
        <!-- Kelola Pesanan -->
        <section id="kelola-pesanan" class="content-section">
            <h3 class="section-title">
                <i class="fas fa-clipboard-list"></i>
                Kelola Pesanan
            </h3>
            
            <div class="filter-section">
                <h4><i class="fas fa-filter"></i> Filter Pesanan Berdasarkan Layanan</h4>
                <form method="GET" style="margin-top: 15px;">
                    <select name="service_id" onchange="this.form.submit()">
                        <option value="all" <?= ($sid === 'all' || $sid === '') ? 'selected' : '' ?>>
                            <i class="fas fa-list"></i> Semua Layanan
                        </option>
                        <?php foreach ($services as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= ($sid == $s['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-user"></i> Username</th>
                            <th><i class="fas fa-concierge-bell"></i> Layanan</th>
                            <th><i class="fas fa-weight"></i> Berat (kg)</th>
                            <th><i class="fas fa-tag"></i> Harga/kg</th>
                            <th><i class="fas fa-calculator"></i> Total Biaya</th>
                            <th><i class="fas fa-calendar"></i> Tanggal</th>
                            <th><i class="fas fa-info-circle"></i> Status</th>
                            <th><i class="fas fa-cogs"></i> Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $orders->data_seek(0); // Reset pointer
                        $total_revenue_filtered = 0;
                        while ($row = $orders->fetch_assoc()) : 
                            $total_price = isset($row['total_price']) && $row['total_price'] > 0 
                                ? $row['total_price'] 
                                : ($row['weight'] * $row['service_price']);
                            $total_revenue_filtered += $total_price;
                        ?>
                        <tr>
                            <form method="POST" onsubmit="return confirm('Yakin ingin update/hapus pesanan ini?');">
                                <td><strong>#<?= $row['id'] ?></strong></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td>
                                    <select name="service_id" class="service-select" required onchange="updateTotal(this)">
                                        <?php foreach ($services as $service): ?>
                                            <option value="<?= $service['id'] ?>" 
                                                    data-price="<?= $service['price'] ?>"
                                                    <?= ($service['id'] == $row['service_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($service['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="weight" class="weight-input" 
                                           step="0.1" min="0.1" value="<?= $row['weight'] ?>" 
                                           required onchange="updateTotal(this)" />
                                </td>
                                <td><strong>Rp <?= number_format($row['service_price'], 0, ',', '.') ?></strong></td>
                                <td class="total-price">
                                    <span class="total-display">Rp <?= number_format($total_price, 0, ',', '.') ?></span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($row['order_date'])) ?></td>
                                <td>
                                    <select name="status" class="status-select" required>
                                        <option value="pending" <?= ($row['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                                        <option value="proses" <?= ($row['status'] == 'proses') ? 'selected' : '' ?>>Proses</option>
                                        <option value="selesai" <?= ($row['status'] == 'selesai') ? 'selected' : '' ?>>Selesai</option>
                                        <option value="diambil" <?= ($row['status'] == 'diambil') ? 'selected' : '' ?>>Diambil</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" name="order_id" value="<?= $row['id'] ?>" />
                                    <div class="action-buttons">
                                        <button type="submit" name="update_order" class="btn-primary">
                                            <i class="fas fa-save"></i> Update
                                        </button>
                                        <button type="submit" name="delete_order" class="btn-danger" 
                                                onclick="return confirm('Yakin ingin menghapus pesanan ini?');">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </div>
                                </td>
                            </form>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="5"><strong><i class="fas fa-calculator"></i> Total Pendapatan (Filter Aktif):</strong></td>
                            <td class="total-price"><strong>Rp <?= number_format($total_revenue_filtered, 0, ',', '.') ?></strong></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </main>

    <footer class="admin-footer">
        <p><i class="fas fa-heart" style="color: #ff6b6b;"></i> © <?= date('Y'); ?> Fresh by Shaa - Laundry App Admin</p>
        <p>Kelola bisnis laundry dengan mudah dan efisien - "Bersih Tanpa Drama"</p>
    </footer>
</div>

<script>
function updateTotal(element) {
    const row = element.closest('tr');
    const serviceSelect = row.querySelector('select[name="service_id"]');
    const weightInput = row.querySelector('input[name="weight"]');
    const totalDisplay = row.querySelector('.total-display');
    
    if (serviceSelect && weightInput && totalDisplay) {
        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const weight = parseFloat(weightInput.value) || 0;
        const total = price * weight;
        
        totalDisplay.textContent = `Rp ${total.toLocaleString('id-ID')}`;
    }
}

// Update semua total saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('select[name="service_id"]').forEach(updateTotal);
    
    // Smooth scrolling untuk navigasi
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add loading animation for forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                submitBtn.disabled = true;
            }
        });
    });
});

// Add some interactive effects
document.addEventListener('DOMContentLoaded', function() {
    // Animate stats on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationDelay = `${Array.from(entry.target.parentNode.children).indexOf(entry.target) * 0.1}s`;
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.stat-card').forEach(card => {
        observer.observe(card);
    });
    
    // Add hover effects for better UX
    document.querySelectorAll('.service-price-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});

// Add notification system
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
        ${message}
    `;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? 'linear-gradient(135deg, #00b894 0%, #00cec9 100%)' : 'linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)'};
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Enhanced form validation
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('input', function() {
        if (this.value < 0) {
            this.style.borderColor = '#ff6b6b';
            this.style.boxShadow = '0 0 0 3px rgba(255, 107, 107, 0.2)';
        } else {
            this.style.borderColor = '#667eea';
            this.style.boxShadow = '0 0 0 3px rgba(102, 126, 234, 0.1)';
        }
    });
});
</script>

<style>
/* Additional CSS for enhanced animations and effects */
.animate-in {
    animation: slideInUp 0.6s ease-out forwards;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.notification {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
}

/* Enhanced table responsiveness */
@media (max-width: 1200px) {
    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    table {
        min-width: 1000px;
    }
    
    th, td {
        padding: 10px;
        font-size: 0.9rem;
    }
}

@media (max-width: 768px) {
    .admin-container {
        padding: 10px;
    }
    
    .admin-header h1 {
        font-size: 1.5rem;
    }
    
    .admin-header .brand {
        display: none;
    }
    
    .nav-link {
        padding: 8px 12px;
        font-size: 0.9rem;
    }
    
    .welcome-section {
        padding: 20px;
    }
    
    .welcome-section h2 {
        font-size: 1.3rem;
    }
    
    .welcome-section p {
        font-size: 1rem;
    }
    
    .stat-card {
        padding: 20px;
    }
    
    .stat-card .number {
        font-size: 1.5rem;
    }
    
    .content-section {
        padding: 20px;
    }
    
    .section-title {
        font-size: 1.1rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-primary, .btn-danger {
        width: 100%;
        margin-bottom: 5px;
    }
}

/* Loading states */
.loading {
    opacity: 0.7;
    pointer-events: none;
}

.loading button {
    cursor: not-allowed;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

/* Print styles */
@media print {
    body {
        background: white !important;
        color: black !important;
    }
    
    .admin-header, .admin-footer, .nav-link {
        display: none !important;
    }
    
    .content-section {
        background: white !important;
        box-shadow: none !important;
        border: 1px solid #ccc !important;
    }
    
    .stat-card {
        background: white !important;
        border: 1px solid #ccc !important;
        color: black !important;
    }
}

/* Enhanced accessibility */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Focus indicators for keyboard navigation */
button:focus, input:focus, select:focus, a:focus {
    outline: 2px solid #667eea;
    outline-offset: 2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .stat-card, .content-section {
        border: 2px solid currentColor;
    }
    
    .btn-primary, .btn-danger, .btn-update {
        border: 2px solid currentColor;
    }
}
</style>
</body>
</html>