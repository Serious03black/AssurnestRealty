<?php
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary: #2a5bd7;
            --primary-dark: #1e4bb9;
            --secondary: #ff7e5f;
            --light: #f8f9fa;
            --dark: #343a40;
            --success: #28a745;
            --warning: #ffc107;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --navbar-height: 70px;
            --sidebar-width: 250px;
            --card-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            min-height: 100vh;
            color: var(--dark);
            margin-left:1%;
            padding: 0;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            padding-top: var(--navbar-height);
            padding: calc(var(--navbar-height) + 20px) 30px 30px;
            transition: all 0.3s;
            min-height: 100vh;
        }

        .main-title {
            font-size: 28px;
            margin-bottom: 10px;
            color: var(--dark);
            position: relative;
            padding-bottom: 10px;
        }

        .main-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--secondary);
            border-radius: 2px;
        }

        .subtitle {
            color: var(--gray);
            margin-bottom: 30px;
            font-size: 16px;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header {
            background: linear-gradient(to right, var(--primary), var(--primary-dark));
            color: white;
            padding: 20px 30px;
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-header i {
            font-size: 24px;
        }

        .card-body {
            padding: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 15px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            background-color: #fdfdfd;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(42, 91, 215, 0.1);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .status-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .status-badge {
            padding: 8px 15px;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .status-badge.available {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }

        .status-badge.sold {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .status-badge.maintenance {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning);
        }

        .status-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        }

        .status-badge.active {
            border-color: currentColor;
        }

        .hidden-radio {
            display: none;
        }

        .price-display {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary);
            margin-top: 5px;
        }

        .commission-display {
            font-size: 16px;
            color: var(--secondary);
            font-weight: 600;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--light-gray);
        }

        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(to right, var(--primary), var(--primary-dark));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(42, 91, 215, 0.3);
        }

        .btn-secondary {
            background: var(--light);
            color: var(--gray);
            border: 1px solid var(--light-gray);
        }

        .btn-secondary:hover {
            background: var(--light-gray);
        }

        .inspiration-box {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin-top: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .inspiration-icon {
            font-size: 40px;
            color: var(--secondary);
        }

        .inspiration-text h3 {
            font-size: 20px;
            margin-bottom: 8px;
        }

        .inspiration-text p {
            opacity: 0.9;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .navbar {
                left: 0;
                padding: 0 20px;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .navbar-search {
                display: none;
            }
            
            .user-info {
                display: none;
            }
            
            .main-content {
                margin-left: 0;
                padding: calc(var(--navbar-height) + 20px) 15px 15px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .inspiration-box {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
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
</html>