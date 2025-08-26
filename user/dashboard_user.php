<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Ambil daftar layanan dengan harga untuk dropdown (untuk form order)
$services = $conn->query("SELECT id, name, price FROM services");

// Handle buat pesanan
$order_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_id'], $_POST['weight'])) {
    $service_id = $_POST['service_id'];
    $weight = $_POST['weight'];

    // Ambil harga layanan untuk menghitung total
    $service_query = $conn->prepare("SELECT price FROM services WHERE id = ?");
    $service_query->bind_param("i", $service_id);
    $service_query->execute();
    $service_result = $service_query->get_result();
    $service_data = $service_result->fetch_assoc();
    $total_price = $service_data['price'] * $weight;

    $stmt = $conn->prepare("INSERT INTO orders (user_id, service_id, order_date, weight, total_price, status) VALUES (?, ?, NOW(), ?, ?, 'proses')");
    $stmt->bind_param("iidd", $user_id, $service_id, $weight, $total_price);

    if ($stmt->execute()) {
        $order_message = "<div class='alert alert-success'>✅ Pesanan berhasil dibuat! Total biaya: Rp " . number_format($total_price, 0, ',', '.') . "</div>";
    } else {
        $order_message = "<div class='alert alert-error'>❌ Terjadi kesalahan: " . $conn->error . "</div>";
    }
}

// Hitung statistik user
$stats_query = $conn->prepare("SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'proses' THEN 1 ELSE 0 END) as proses_orders,
    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai_orders,
    SUM(CASE WHEN status = 'diambil' THEN 1 ELSE 0 END) as diambil_orders,
    SUM(total_price) as total_spent
    FROM orders WHERE user_id = ?");
$stats_query->bind_param("i", $user_id);
$stats_query->execute();
$stats = $stats_query->get_result()->fetch_assoc();

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Shaa Laundry</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/user.css">
</head>
<body>
    <header class="header">
        <div class="brand">
            <div class="brand-icon">
                <i class="fas fa-tshirt"></i>
            </div>
            <div class="brand-text">
                <h1>Dashboard User</h1>
                <p>Shaa Laundry - Bersih Tanpa Drama</p>
            </div>
        </div>
        <nav class="nav-menu">
            <a href="?page=dashboard" class="<?= $page === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-home"></i> Beranda
            </a>
            <a href="?page=price_list" class="<?= $page === 'price_list' ? 'active' : '' ?>">
                <i class="fas fa-tags"></i> Daftar Harga
            </a>
            <a href="?page=order" class="<?= $page === 'order' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i> Buat Pesanan
            </a>
            <a href="?page=my_orders" class="<?= $page === 'my_orders' ? 'active' : '' ?>">
                <i class="fas fa-list"></i> Pesanan Saya
            </a>
        </nav>
        <div style="display: flex; align-items: center; gap: 15px;">
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span>Selamat datang, <strong><?php echo htmlspecialchars($username); ?></strong>!</span>
            </div>
            <a href="../logout.php" class="logout-btn" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </div>
    </header>

    <main class="main-content">
        <?php if ($page === 'dashboard') { ?>
            <div class="welcome-card">
                <h2><i class="fas fa-hand-wave" style="color: #667eea;"></i> Selamat datang, <?php echo htmlspecialchars($username); ?>!</h2>
                <p>Nikmati layanan laundry terbaik dengan kemudahan dan kenyamanan maksimal</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                    <div class="stat-number"><?= $stats['total_orders'] ?></div>
                    <div class="stat-label">Total Pesanan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-number"><?= $stats['proses_orders'] ?></div>
                    <div class="stat-label">Sedang Proses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-number"><?= $stats['selesai_orders'] ?></div>
                    <div class="stat-label">Selesai</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-handshake"></i></div>
                    <div class="stat-number"><?= $stats['diambil_orders'] ?></div>
                    <div class="stat-label">Sudah Diambil</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="stat-number">Rp <?= number_format($stats['total_spent'] ?: 0, 0, ',', '.') ?></div>
                    <div class="stat-label">Total Pengeluaran</div>
                </div>
            </div>

            <div class="content-card">
                <h2><i class="fas fa-tags"></i> Layanan & Harga Kami</h2>
                <div class="price-list">
                    <?php
                    $services->data_seek(0);
                    while ($row = $services->fetch_assoc()) { ?>
                        <div class="price-item">
                            <div>
                                <div class="service-name"><?= htmlspecialchars($row['name']) ?></div>
                                <small>Per kilogram</small>
                            </div>
                            <div class="service-price">Rp <?= number_format($row['price'], 0, ',', '.') ?></div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            
        <?php } elseif ($page === 'price_list') { ?>
            <div class="content-card">
                <h2><i class="fas fa-list-alt"></i> Daftar Harga Layanan Laundry</h2>
                <div class="price-list">
                    <?php
                    $services->data_seek(0);
                    while ($row = $services->fetch_assoc()) { ?>
                        <div class="price-item">
                            <div>
                                <div class="service-name"><?= htmlspecialchars($row['name']) ?></div>
                                <small>Berkualitas tinggi dengan perawatan terbaik</small>
                            </div>
                            <div class="service-price">Rp <?= number_format($row['price'], 0, ',', '.') ?>/kg</div>
                        </div>
                    <?php } ?>
                </div>
                <p style="margin-top: 20px; text-align: center; color: #666; font-style: italic;">
                    <i class="fas fa-info-circle"></i> Harga dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.
                </p>
            </div>
            
        <?php } elseif ($page === 'order') { ?>
            <div class="content-card">
                <h2><i class="fas fa-plus-circle"></i> Buat Pesanan Baru</h2>
                <?php if ($order_message) echo $order_message; ?>
                
                <form method="POST" id="orderForm">
                    <div class="form-group">
                        <label><i class="fas fa-tshirt"></i> Pilih Layanan:</label>
                        <select name="service_id" id="service_id" class="form-control" required onchange="calculateTotal()">
                            <option value="">-- Pilih Layanan Laundry --</option>
                            <?php
                            $services->data_seek(0);
                            while ($row = $services->fetch_assoc()) { ?>
                                <option value="<?= $row['id'] ?>" data-price="<?= $row['price'] ?>">
                                    <?= htmlspecialchars($row['name']) ?> - Rp <?= number_format($row['price'], 0, ',', '.') ?>/kg
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-weight"></i> Berat Pakaian (kg):</label>
                        <input type="number" step="0.01" name="weight" id="weight" class="form-control" 
                               placeholder="Contoh: 2.5" required onchange="calculateTotal()" oninput="calculateTotal()">
                    </div>
                    
                    <div class="calculator">
                        <h4><i class="fas fa-calculator"></i> Kalkulator Biaya</h4>
                        <div id="calculation-details"></div>
                        <div class="total-display" id="total-display">
                            <i class="fas fa-money-bill-wave"></i> Total: Rp 0
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Pesan Sekarang
                    </button>
                </form>
            </div>
            
        <?php } elseif ($page === 'my_orders') { ?>
            <div class="content-card">
                <h2><i class="fas fa-history"></i> Riwayat Pesanan Saya</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Layanan</th>
                                <th>Tanggal Pesan</th>
                                <th>Berat (kg)</th>
                                <th>Harga/kg</th>
                                <th>Total Biaya</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $orders = $conn->prepare("SELECT o.*, s.name as service_name, s.price as service_price FROM orders o JOIN services s ON o.service_id = s.id WHERE o.user_id = ? ORDER BY o.order_date DESC");
                            $orders->bind_param("i", $user_id);
                            $orders->execute();
                            $result = $orders->get_result();
                            while ($row = $result->fetch_assoc()) { 
                                $total_price = isset($row['total_price']) ? $row['total_price'] : ($row['weight'] * $row['service_price']);
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['service_name']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($row['order_date'])) ?></td>
                                    <td><?= htmlspecialchars($row['weight']) ?> kg</td>
                                    <td>Rp <?= number_format($row['service_price'], 0, ',', '.') ?></td>
                                    <td>Rp <?= number_format($total_price, 0, ',', '.') ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $row['status'] ?>">
                                            <?php
                                            $status_icons = [
                                                'proses' => 'fas fa-spinner',
                                                'selesai' => 'fas fa-check',
                                                'diambil' => 'fas fa-handshake'
                                            ];
                                            echo '<i class="' . ($status_icons[$row['status']] ?? 'fas fa-question') . '"></i> ';
                                            echo ucfirst(htmlspecialchars($row['status']));
                                            ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php }
                            if ($result->num_rows === 0) {
                                echo '<tr><td colspan="7" style="text-align: center; color: #666; padding: 40px;">
                                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                                        Belum ada pesanan. <a href="?page=order">Buat pesanan pertama Anda!</a>
                                      </td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } ?>
    </main>

    <script>
        function calculateTotal() {
            const serviceSelect = document.getElementById('service_id');
            const weightInput = document.getElementById('weight');
            const calculationDetails = document.getElementById('calculation-details');
            const totalDisplay = document.getElementById('total-display');
            
            if (serviceSelect && weightInput && calculationDetails && totalDisplay) {
                const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
                const price = selectedOption.getAttribute('data-price');
                const weight = parseFloat(weightInput.value) || 0;
                
                if (price && weight > 0) {
                    const priceNum = parseFloat(price);
                    const total = priceNum * weight;
                    
                    calculationDetails.innerHTML = `
                        <div style="margin-bottom: 10px;">
                            <strong>Layanan:</strong> ${selectedOption.text.split(' - ')[0]}
                        </div>
                        <div style="margin-bottom: 10px;">
                            <strong>Berat:</strong> ${weight} kg
                        </div>
                        <div style="margin-bottom: 10px;">
                            <strong>Perhitungan:</strong> Rp ${priceNum.toLocaleString('id-ID')} × ${weight} kg
                        </div>
                    `;
                    totalDisplay.innerHTML = `<i class="fas fa-money-bill-wave"></i> Total: Rp ${total.toLocaleString('id-ID')}`;
                } else {
                    calculationDetails.innerHTML = '<p style="color: #666; text-align: center;">Silakan pilih layanan dan masukkan berat untuk melihat perhitungan</p>';
                    totalDisplay.innerHTML = '<i class="fas fa-money-bill-wave"></i> Total: Rp 0';
                }
            }
        }

        // Add active navigation highlighting
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = new URLSearchParams(window.location.search).get('page') || 'dashboard';
            const navLinks = document.querySelectorAll('.nav-menu a');
            
            navLinks.forEach(link => {
                if (link.href.includes('page=' + currentPage) || 
                    (currentPage === 'dashboard' && !link.href.includes('page='))) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>