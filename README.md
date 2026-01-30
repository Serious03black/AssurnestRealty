<!-- <?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Get admin user info
$admin_stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$admin_stmt->execute([$_SESSION['user_id']]);
$admin = $admin_stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['type'];
    $location = $_POST['location'];
    $address = $_POST['address'];
    $price = $_POST['price'];
    $commission = $_POST['commission'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("INSERT INTO properties (type, location, address, price, commission, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$type, $location, $address, $price, $commission, $status]);
    header('Location: admin_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Property | Assurnest Realty Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/navbar.php'; ?>
    <!-- Main Content -->
    <div class="main-content">
        <h1 class="main-title">Add New Property</h1>
        <p class="subtitle">Fill in the property details to add it to your portfolio. Let's create new opportunities!</p>
        
        <div class="card">
            <div class="card-header">
                <i class="fas fa-home"></i>
                Property Information
            </div>
            <div class="card-body">
                <form method="POST" id="propertyForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="type">
                                <i class="fas fa-tag"></i> Property Type
                            </label>
                            <input type="text" class="form-control" id="type" name="type" required 
                                   placeholder="e.g., Villa, Apartment, Condo">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="location">
                                <i class="fas fa-map-marker-alt"></i> Location
                            </label>
                            <input type="text" class="form-control" id="location" name="location" required 
                                   placeholder="e.g., Downtown, Suburb, Beachfront">
                        </div>
                        
                        <div class="form-group">
                            <h2 class="form-label" for="price">
                               ₹ Price
                            </h2>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required 
                                   placeholder="0.00" oninput="updatePriceDisplay()">
                            <div class="price-display" id="priceDisplay">₹ 0.00</div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="commission">
                                <i class="fas fa-percentage"></i> Commission (%)
                            </label>
                            <input type="number" class="form-control" id="commission" name="commission" step="0.01" required 
                                   placeholder="0.00" oninput="updateCommissionDisplay()">
                            <div class="commission-display" id="commissionDisplay">0% (₹ 0.00)</div>
                        </div>
                        
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label" for="address">
                                <i class="fas fa-address-card"></i> Full Address
                            </label>
                            <textarea class="form-control" id="address" name="address" required 
                                      placeholder="Enter the complete property address..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-signal"></i> Property Status
                            </label>
                            <div class="status-badges">
                                <label class="status-badge available">
                                    <input type="radio" name="status" value="available" class="hidden-radio" checked> 
                                    Available
                                </label>
                                <label class="status-badge sold">
                                    <input type="radio" name="status" value="sold" class="hidden-radio">
                                    Sold
                                </label>
                                <label class="status-badge maintenance">
                                    <input type="radio" name="status" value="maintenance" class="hidden-radio">
                                    Maintenance
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="admin_dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Add Property
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="inspiration-box">
            <div class="inspiration-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="inspiration-text">
                <h3>Real Estate Opportunity Awaits!</h3>
                <p>Every property added is a new opportunity for growth. You're not just adding a listing—you're connecting dreams to reality and building futures. Premium properties attract premium clients.</p>
            </div>
        </div>
    </div>

    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('mobile-open');
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                (!mobileMenuBtn || !mobileMenuBtn.contains(event.target))) {
                sidebar.classList.remove('mobile-open');
            }
        });

        // Update price display with formatting
        function updatePriceDisplay() {
            const priceInput = document.getElementById('price');
            const priceDisplay = document.getElementById('priceDisplay');
            const price = parseFloat(priceInput.value) || 0;
            
            // Format with commas and 2 decimal places
            priceDisplay.textContent = '₹ ' + price.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            updateCommissionDisplay();
        }
        
        // Update commission display
        function updateCommissionDisplay() {
            const priceInput = document.getElementById('price');
            const commissionInput = document.getElementById('commission');
            const commissionDisplay = document.getElementById('commissionDisplay');
            
            const price = parseFloat(priceInput.value) || 0;
            const commission = parseFloat(commissionInput.value) || 0;
            const commissionAmount = (price * commission) / 100;
            
            commissionDisplay.textContent = `${commission}% (₹ ${commissionAmount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })})`;
        }
        
        // Initialize displays
        document.addEventListener('DOMContentLoaded', function() {
            updatePriceDisplay();
            
            // Add active class to selected status badge
            const statusBadges = document.querySelectorAll('.status-badge');
            statusBadges.forEach(badge => {
                const radio = badge.querySelector('.hidden-radio');
                if (radio.checked) {
                    badge.classList.add('active');
                }
                
                badge.addEventListener('click', function() {
                    statusBadges.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Form validation
            const form = document.getElementById('propertyForm');
            form.addEventListener('submit', function(e) {
                let valid = true;
                const inputs = form.querySelectorAll('input[required], textarea[required]');
                
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        valid = false;
                        input.style.borderColor = '#dc3545';
                    } else {
                        input.style.borderColor = '';
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                }
            });

            // User dropdown toggle
            document.getElementById('userDropdownBtn')?.addEventListener('click', function() {
                alert('Profile menu - Add user dropdown functionality here');
            });

            // Search functionality
            document.querySelector('.search-input')?.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    alert('Searching for: ' + this.value);
                    // Implement actual search here
                }
            });
        });
    </script>   
</body>
</html> -->
