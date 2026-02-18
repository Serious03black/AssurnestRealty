
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prime Properties | Luxury Real Estate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --finance-blue: #4a90e2;
            --real-estate-green: #2ecc71;
            --logo-gold: #997C37;
            --logo-black: #121212;
            --bg-primary: #0a0a0a;
            --bg-secondary: #1a1a1a;
            --bg-card: #222222;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --border-color: #333333;
            --shadow-color: rgba(0, 0, 0, 0.5);
            --hover-gold: #ffd700;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            transition: background-color 0.3s, color 0.3s;
        }
        
     
        /* Header & Navigation */
        header {
            background-color: var(--bg-secondary);
            box-shadow: 0 2px 15px var(--shadow-color);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--border-color);
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;

            /* padding: 15px 0; */
        }

        
        .logo {
            display: flex;
            gap: 10px;
            align-items: center;
            text-decoration: none;
        }
        
        .logo-icon {
            color: var(--logo-gold);
            font-size: 32px;
            margin-right: 10px;
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .logo-text span {
            color: var(--logo-gold);
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-left: 30px;
        }
        
        .nav-links a {
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: var(--logo-gold);
        }
        
     /* From Uiverse.io by joe-watson-sbf */ 
.btn {
  font-size: 17px;
  background: transparent;
  border: none;
  padding: 0em 1em;
  color: #ffedd3;
  text-transform: uppercase;
  position: relative;
  transition: 0.5s ease;
  cursor: pointer;
}

.btn::before {
  content: "";
  position: absolute;
  left: 0;
  bottom: 0;
  height: 2px;
  width: 0;
  background-color: #997C37;
  transition: 0.5s ease;
}

.btn:hover {
  color: #1e1e2b;
  transition-delay: 0.5s;
}

.btn:hover::before {
  width: 100%;
}

.btn::after {
  content: "";
  position: absolute;
  left: 0;
  bottom: 0;
  height: 0;
  width: 100%;
  background-color: #997C37;
  transition: 0.4s ease;
  z-index: -1;
}

.btn:hover::after {
  height: 100%;
  transition-delay: 0.4s;
  color: aliceblue;
}

 
  
     
        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(10, 10, 10, 0.9), rgba(10, 10, 10, 0.7)), url('https://images.unsplash.com/photo-1512917774080-9991f1c4c750?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80');
            background-size: cover;
            background-position: center;
            text-align: center;
            padding: 120px 20px;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at center, transparent 0%, rgba(0,0,0,0.8) 100%);
            z-index: 1;
        }
        
        .hero > .container {
            position: relative;
            z-index: 2;
        }
        
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            color: var(--text-primary);
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }
        
        .hero p {
            font-size: 20px;
            max-width: 800px;
            margin: 0 auto 30px;
            color: var(--text-secondary);
        }
        
        .btn-hero {
            background-color: var(--logo-gold);
            color: var(--logo-black);
            padding: 15px 40px;
            font-size: 18px;
            border-radius: 30px;
            font-weight: 700;
        }
        
        /* Properties Section */
        .section-title {
            text-align: center;
            margin: 60px 0 40px;
            font-size: 36px;
            color: var(--text-primary);
        }
        
        .section-title span {
            color: var(--logo-gold);
        }
        
        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }
        
        .property-card {
            background-color: var(--bg-card);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px var(--shadow-color);
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid var(--border-color);
        }
        
        .property-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(241, 196, 15, 0.1);
            border-color: var(--logo-gold);
        }
        
        .property-img {
            height: 240px;
            width: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .property-card:hover .property-img {
            transform: scale(1.05);
        }
        
        .property-info {
            padding: 25px;
        }
        
        .property-title {
            font-size: 22px;
            margin-bottom: 10px;
            color: var(--text-primary);
        }
        
        .property-location {
            color: var(--text-secondary);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .property-location i {
            margin-right: 8px;
            color: var(--logo-gold);
        }
        
        .property-price {
            font-size: 24px;
            font-weight: 700;
            color: var(--real-estate-green);
            margin-bottom: 15px;
        }
        
        .property-description {
            color: var(--text-secondary);
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .property-features {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        
        .feature {
            text-align: center;
        }
        
        .feature i {
            color: var(--logo-gold);
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .feature div {
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        /* About & Contact Sections */
        .about-section, .contact-section {
            padding: 80px 0;
        }
        
        .about-section {
            background-color: var(--bg-secondary);
        }
        
        .about-content {
            display: flex;
            align-items: center;
            gap: 50px;
        }
        
        .about-text {
            flex: 1;
        }
        
        .about-text p {
            color: var(--text-secondary);
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .about-img {
            flex: 1;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px var(--shadow-color);
            border: 1px solid var(--border-color);
        }
        
        .about-img img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.5s;
        }
        
        .about-img:hover img {
            transform: scale(1.05);
        }
        
        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .contact-item {
            background-color: var(--bg-card);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px var(--shadow-color);
            text-align: center;
            transition: transform 0.3s;
            border: 1px solid var(--border-color);
        }
        
        .contact-item:hover {
            transform: translateY(-5px);
            border-color: var(--logo-gold);
        }
        
        .contact-item i {
            font-size: 36px;
            color: var(--logo-gold);
            margin-bottom: 20px;
        }
        
        .contact-item h3 {
            color: var(--text-primary);
            margin-bottom: 10px;
        }
        
        .contact-item p {
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        /* Contact Form */
        .contact-form-container {
            max-width: 800px;
            margin: 50px auto 0;
        }
        
        .contact-form {
            background-color: var(--bg-card);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 25px var(--shadow-color);
            border: 1px solid var(--border-color);
        }
        
        .contact-form h3 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--text-primary);
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            flex: 1;
            margin-bottom: 20px;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
            color: var(--text-primary);
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--logo-gold);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        /* Footer */
        footer {
            background-color: var(--logo-black);
            padding: 60px 0 30px;
            border-top: 1px solid var(--border-color);
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }
        
        .footer-column {
            flex: 1;
            min-width: 250px;
            margin-bottom: 30px;
            padding-right: 20px;
        }
        
        .footer-column h3 {
            font-size: 20px;
            margin-bottom: 20px;
            color: var(--logo-gold);
        }
        
        .footer-column p {
            color: var(--text-secondary);
            margin-bottom: 20px;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: var(--logo-gold);
        }
        
        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: var(--text-primary);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .social-icons a:hover {
            background-color: var(--logo-gold);
            color: var(--logo-black);
            transform: translateY(-3px);
        }
        
        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-secondary);
            font-size: 14px;
        }
        /* Mobile Menu Button */
.mobile-menu-btn {
    display: none;
    background: none;
    border: none;
    font-size: 24px;
    color: var(--text-primary);
    cursor: pointer;
    padding: 5px;
    margin-left: 15px;
    z-index: 1002;
}

/* Mobile Menu Overlay */
.mobile-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    z-index: 999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.mobile-overlay.active {
    opacity: 1;
    visibility: visible;
}

/* Mobile Menu Sidebar */
.mobile-menu {
    position: fixed;
    top: 0;
    right: -100%;
    width: 300px;
    height: 100vh;
    background-color: var(--bg-card);
    box-shadow: -5px 0 15px var(--shadow-color);
    transition: right 0.3s ease;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    border-left: 1px solid var(--border-color);
}

.mobile-menu.active {
    right: 0;
}

.mobile-menu-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
}

.mobile-menu-header .logo {
    font-size: 18px;
}

.close-menu {
    background: none;
    border: none;
    font-size: 24px;
    color: var(--text-primary);
    cursor: pointer;
    padding: 5px;
}

.mobile-menu-links {
    list-style: none;
    padding: 20px;
    flex-grow: 1;
}

.mobile-menu-links li {
    margin-bottom: 20px;
}

.mobile-menu-link {
    text-decoration: none;
    color: var(--text-primary);
    font-size: 18px;
    font-weight: 500;
    display: block;
    padding: 10px 0;
    border-bottom: 1px solid var(--border-color);
    transition: color 0.3s;
}

.mobile-menu-link:hover {
    color: var(--logo-gold);
}

.mobile-login-buttons {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
    border-top: 1px solid var(--border-color);
}

/* Responsive Design */
@media (max-width: 992px) {
    .nav-links {
        display: none;
    }
    
    .mobile-menu-btn {
        display: block;
    }
    
    .login-buttons {
        display: none;
    }
}

@media (max-width: 768px) {
    .hero h1 {
        font-size: 36px;
    }
    
    .hero p {/* Mobile Menu Button */
.mobile-menu-btn {
    display: none;
    background: none;
    border: none;
    font-size: 24px;
    color: var(--text-primary);
    cursor: pointer;
    padding: 5px;
    margin-left: 15px;
    z-index: 1002;
}

/* Mobile Menu Overlay */
.mobile-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    z-index: 999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.mobile-overlay.active {
    opacity: 1;
    visibility: visible;
}

/* Mobile Menu Sidebar */
.mobile-menu {
    position: fixed;
    top: 0;
    right: -100%;
    width: 300px;
    height: 100vh;
    background-color: var(--bg-card);
    box-shadow: -5px 0 15px var(--shadow-color);
    transition: right 0.3s ease;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    border-left: 1px solid var(--border-color);
}

.mobile-menu.active {
    right: 0;
}

.mobile-menu-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
}

.mobile-menu-header .logo {
    font-size: 18px;
}

.close-menu {
    background: none;
    border: none;
    font-size: 24px;
    color: var(--text-primary);
    cursor: pointer;
    padding: 5px;
}

.mobile-menu-links {
    list-style: none;
    padding: 20px;
    flex-grow: 1;
}

.mobile-menu-links li {
    margin-bottom: 20px;
}

.mobile-menu-link {
    text-decoration: none;
    color: var(--text-primary);
    font-size: 18px;
    font-weight: 500;
    display: block;
    padding: 10px 0;
    border-bottom: 1px solid var(--border-color);
    transition: color 0.3s;
}

.mobile-menu-link:hover {
    color: var(--logo-gold);
}

.mobile-login-buttons {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
    border-top: 1px solid var(--border-color);
}

/* Responsive Design */
@media (max-width: 992px) {
    .nav-links {
        display: none;
    }
    
    .mobile-menu-btn {
        display: block;
    }
    
    .login-buttons {
        display: none;
    }
}

@media (max-width: 768px) {
    .hero h1 {
        font-size: 36px;
    }
    
    .hero p {
        font-size: 18px;
    }
    
    .properties-grid {
        grid-template-columns: 1fr;
    }
    
    .mobile-menu {
        width: 280px;
    }
}
        font-size: 18px;
    }
    
    .properties-grid {
        grid-template-columns: 1fr;
    }
    
    .mobile-menu {
        width: 280px;
    }
}
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .about-content {
                flex-direction: column;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .nav-links {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
                background: none;
                border: none;
                font-size: 24px;
                color: var(--text-primary);
                cursor: pointer;
            }
         /* Mobile Menu Button */
.mobile-menu-btn {
    display: none;
    background: none;
    border: none;
    font-size: 24px;
    color: var(--text-primary);
    cursor: pointer;
    padding: 5px;
    margin-left: 15px;
    z-index: 1002;
}


            
            .mobile-menu.active {
                right: 0;
            }
            
            .mobile-menu-links {
                list-style: none;
            }
            
            .mobile-menu-links li {
                margin-bottom: 20px;
            }
            
            .mobile-menu-links a {
                text-decoration: none;
                color: var(--text-primary);
                font-size: 18px;
                font-weight: 500;
            }
            
            .close-menu {
                position: absolute;
                top: 25px;
                right: 25px;
                background: none;
                border: none;
                font-size: 24px;
                color: var(--text-primary);
                cursor: pointer;
            }
            
            .overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7);
                z-index: 1000;
                display: none;
            }
            
            .overlay.active {
                display: block;
            }
        }
        
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 36px;
            }
            
            .hero p {
                font-size: 18px;
            }
            
            .login-buttons {
                flex-direction: column;
                width: 100%;
            }
            
            .btn {
                text-align: center;
            }
            
            .properties-grid {
                grid-template-columns: 1fr;
            }
            
        
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--bg-secondary);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--logo-gold);
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--hover-gold);
        }
    </style>
</head>
<body>
    
    <!-- Header & Navigation -->
    <header style="background-color:#0E2432">
        <div class="container">
            <div class="nav-container">
                <div class="logo">
                    <img src="./assets/images/logo.png" alt="" height="60px" width="60px">
                    <a href="index.php" class="logo" >
                        <div class="logo-text">Assurenest<span>Reality</span></div>
                    </a>
                </div>
                <div>
                    <ul class="nav-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#properties">Properties</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                 <div>
              <div class="btn">
    <a href="./pages/login.php" class="auth-btn">
     <button class="btn">SIgn up</button>
    </a>
</div>

                </div>
                <button class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>
    
    <!-- Mobile Menu Button -->
<button class="mobile-menu-btn" id="mobileMenuBtn">
    <i class="fas fa-bars"></i>
</button>

<!-- Mobile Menu Overlay -->
<div class="mobile-overlay" id="mobileOverlay"></div>

<!-- Mobile Menu Sidebar -->
<div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu-header">
        <div class="logo">
            <i class="fas fa-building logo-icon"></i>
            <div class="logo-text">Prime<span>Properties</span></div>
        </div>
        <button class="close-menu" id="closeMenu">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <ul class="mobile-menu-links">
        <li><a href="#home" class="mobile-menu-link">Home</a></li>
        <li><a href="#properties" class="mobile-menu-link">Properties</a></li>
        <li><a href="#about" class="mobile-menu-link">About Us</a></li>
        <li><a href="#contact" class="mobile-menu-link">Contact</a></li>
    </ul>
    
    <div class="mobile-login-buttons">
        <a href="pages/login.php?type=real_estate" class="btn btn-login-realestate">Real Estate Login</a>
        <a href="pages/login.php?type=finance" class="btn btn-login-finance">Finance Login</a>
    </div>
</div>
    
    <!-- Hero Section -->
    <section class="hero fade-in" id="home">
        <div class="container">
            <h1>Find Your Dream Property</h1>
            <p>Prime Properties offers luxury real estate with premium financial solutions. Discover exclusive properties and personalized financing options tailored to your needs.</p>
            <a href="#properties" class="btn btn-hero">Explore Properties</a>
        </div>
    </section>
    
    <!-- Recent Properties Section -->
<!-- Recent Properties Section -->
<section id="properties">
    <div class="container">
        <h2 class="section-title">Recently <span>Added</span> Properties</h2>
        
        <div class="properties-grid">
            <?php
            try {
                // Include the database configuration
                require_once 'includes/db.php';
                
                // Fetch properties from database using PDO
                $query = "SELECT * FROM properties ORDER BY property_id DESC LIMIT 3";
                $stmt = $pdo->query($query);
                $properties = $stmt->fetchAll();
                
                if (count($properties) > 0) {
                    foreach ($properties as $property) {
            ?>
            ?>
            <div class="property-card fade-in">
                <img src="includes/view_image.php?id=<?php echo $property['property_id']; ?>&num=1" alt="<?php echo htmlspecialchars($property['property_name']); ?>" class="property-img">
                <div class="property-info">
                    <h3 class="property-title"><?php echo htmlspecialchars($property['property_name']); ?></h3>
                    <div class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo htmlspecialchars($property['location_city'] . ', ' . $property['location_state']); ?></span>
                    </div>
                    <div class="property-price">₹<?php echo number_format($property['price']); ?></div>
                    <p class="property-description"><?php echo htmlspecialchars(substr($property['description'], 0, 100)) . '...'; ?></p>
                    <div class="property-features">
                       <div class="feature">
                            <i class="fas fa-tag"></i>
                            <div><?php echo htmlspecialchars($property['property_type']); ?></div>
                       </div>
                       <div class="feature">
                            <i class="fas fa-map"></i>
                            <div><?php echo htmlspecialchars($property['location_area']); ?></div>
                       </div>
                    </div>
                </div>
            </div>
            <?php
                    }
                } else {
                    throw new Exception("No properties found");
                }
                
            } catch (Exception $e) {
                // If there's an error with the database, show default properties
                $default_properties = [
                    [
                        'image_url' => 'https://images.unsplash.com/photo-1613977257363-707ba9348227?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80',
                        'title' => 'Modern Villa with Ocean View',
                        'location' => 'Malibu, California',
                        'price' => '3850000',
                        'description' => 'A stunning contemporary villa featuring panoramic ocean views, infinity pool, and smart home technology.',
                        'bedrooms' => 5,
                        'bathrooms' => 6,
                        'square_feet' => 6500
                    ],
                    [
                        'image_url' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1171&q=80',
                        'title' => 'Luxury Manhattan Penthouse',
                        'location' => 'New York, NY',
                        'price' => '8200000',
                        'description' => 'An exclusive penthouse in the heart of Manhattan with private elevator, rooftop terrace, and premium finishes.',
                        'bedrooms' => 4,
                        'bathrooms' => 5,
                        'square_feet' => 4200
                    ],
                    [
                        'image_url' => 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80',
                        'title' => 'Alpine Mountain Retreat',
                        'location' => 'Aspen, Colorado',
                        'price' => '2950000',
                        'description' => 'A luxurious mountain retreat with ski-in/ski-out access, heated floors, and breathtaking mountain views.',
                        'bedrooms' => 6,
                        'bathrooms' => 7,
                        'square_feet' => 8000
                    ]
                ];
                
                foreach ($default_properties as $property) {
            ?>
            <div class="property-card fade-in">
                <img src="<?php echo $property['image_url']; ?>" alt="<?php echo $property['title']; ?>" class="property-img">
                <div class="property-info">
                    <h3 class="property-title"><?php echo $property['title']; ?></h3>
                    <div class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo $property['location']; ?></span>
                    </div>
                    <div class="property-price">$<?php echo number_format($property['price']); ?></div>
                    <p class="property-description"><?php echo $property['description']; ?></p>
                    <div class="property-features">
                        <div class="feature">
                            <i class="fas fa-bed"></i>
                            <div><?php echo $property['bedrooms']; ?> Beds</div>
                        </div>
                        <div class="feature">
                            <i class="fas fa-bath"></i>
                            <div><?php echo $property['bathrooms']; ?> Baths</div>
                        </div>
                        <div class="feature">
                            <i class="fas fa-ruler-combined"></i>
                            <div><?php echo number_format($property['square_feet']); ?> sqft</div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                }
            }
            ?>
        </div>
    </div>
</section>
    <!-- About Us Section -->
    <section class="about-section fade-in" id="about">
        <div class="container">
            <h2 class="section-title">About <span>Prime Properties</span></h2>
            <div class="about-content">
                <div class="about-text">
                    <p>Founded in 2005, Prime Properties has established itself as a premier real estate firm specializing in luxury properties and comprehensive financial solutions. Our unique approach combines real estate expertise with financial advisory services to provide our clients with a seamless experience.</p>
                    <p>We understand that purchasing property is one of the most significant financial decisions you'll make. That's why we offer integrated real estate and financing solutions, ensuring you find not only the perfect property but also the optimal financial strategy to acquire it.</p>
                    <p>Our team of experienced professionals has helped over 2,500 clients find their dream homes and secure financing that aligns with their long-term financial goals.</p>
                    <br>
                    <a href="#contact" class="btn btn-login-realestate">Get In Touch</a>
                </div>
                <div class="about-img">
                    <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1073&q=80" alt="Prime Properties Office">
                </div>
            </div>
        </div>
    </section>
    
    <!-- Contact Us Section -->
    <section class="contact-section fade-in" id="contact">
        <div class="container">
            <h2 class="section-title">Contact <span>Us</span></h2>
            <p style="text-align: center; max-width: 800px; margin: 0 auto 40px; color: var(--text-secondary);">Our team is ready to assist you with all your real estate and financing needs. Reach out to us through any of the following channels.</p>
            
            <div class="contact-info">
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Visit Our Office</h3>
                    <p>123 Luxury Avenue<br>Beverly Hills, CA 90210</p>
                </div>
                
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <h3>Call Us</h3>
                    <p>+1 (310) 555-1234 (Real Estate)<br>+1 (310) 555-5678 (Finance)</p>
                </div>
                
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <h3>Email Us</h3>
                    <p>info@primeproperties.com<br>finance@primeproperties.com</p>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="contact-form-container">
                <form action="includes/contact_form.php" method="POST" class="contact-form">
                    <h3>Send us a Message</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" id="name" name="name" placeholder="Enter your name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Your Email</label>
                            <input type="email" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" placeholder="Enter subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Your Message</label>
                        <textarea id="message" name="message" placeholder="Enter your message" required></textarea>
                    </div>
                    <div class="form-group" style="text-align: center;">
                        <button type="submit" class="btn btn-login-finance">Send Message</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <div class="logo">
                        <i class="fas fa-building logo-icon"></i>
                        <div class="logo-text">Prime<span>Properties</span></div>
                    </div>
                    <p>Luxury real estate with integrated financial solutions for discerning clients.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#properties">Properties</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Our Services</h3>
                    <ul class="footer-links">
                        <li>Luxury Property Sales</li>
                        <li>Real Estate Investment</li>
                        <li>Property Financing</li>
                        <li>Mortgage Solutions</li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> Prime Properties. All rights reserved.
            </div>
        </div>
    </footer>
    
    <script>
        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const closeMenuBtn = document.querySelector('.close-menu');
            const mobileMenu = document.querySelector('.mobile-menu');
            const overlay = document.querySelector('.overlay');
            
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', function() {
                    mobileMenu.classList.add('active');
                    overlay.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });
            }
            
            if (closeMenuBtn) {
                closeMenuBtn.addEventListener('click', function() {
                    mobileMenu.classList.remove('active');
                    overlay.classList.remove('active');
                    document.body.style.overflow = 'auto';
                });
            }
            
            if (overlay) {
                overlay.addEventListener('click', function() {
                    mobileMenu.classList.remove('active');
                    this.classList.remove('active');
                    document.body.style.overflow = 'auto';
                });
            }
            
            // Theme toggle functionality
            const themeToggle = document.getElementById('themeToggle');
            const body = document.body;
            const themeIcon = themeToggle.querySelector('i');
            const themeText = themeToggle.querySelector('span');
            
            // Check for saved theme preference
            const savedTheme = localStorage.getItem('theme') || 'dark';
            if (savedTheme === 'light') {
                enableLightMode();
            }
            
            themeToggle.addEventListener('click', function() {
                if (body.getAttribute('data-theme') === 'dark') {
                    enableLightMode();
                    localStorage.setItem('theme', 'light');
                } else {
                    enableDarkMode();
                    localStorage.setItem('theme', 'dark');
                }
            });
            
            function enableLightMode() {
                body.setAttribute('data-theme', 'light');
                body.style.backgroundColor = '#f8f9fa';
                body.style.color = '#333';
                themeIcon.className = 'fas fa-moon';
                themeText.textContent = 'Dark Mode';
                
                // Update CSS variables for light mode
                updateThemeVariables({
                    '--bg-primary': '#f8f9fa',
                    '--bg-secondary': '#ffffff',
                    '--bg-card': '#ffffff',
                    '--text-primary': '#333333',
                    '--text-secondary': '#666666',
                    '--border-color': '#e0e0e0',
                    '--shadow-color': 'rgba(0, 0, 0, 0.1)',
                    '--logo-black': '#1a1a1a'
                });
            }
            
            function enableDarkMode() {
                body.setAttribute('data-theme', 'dark');
                body.style.backgroundColor = '#0a0a0a';
                body.style.color = '#ffffff';
                themeIcon.className = 'fas fa-sun';
                themeText.textContent = 'Light Mode';
                
                // Update CSS variables for dark mode
                updateThemeVariables({
                    '--bg-primary': '#0a0a0a',
                    '--bg-secondary': '#1a1a1a',
                    '--bg-card': '#222222',
                    '--text-primary': '#ffffff',
                    '--text-secondary': '#b0b0b0',
                    '--border-color': '#333333',
                    '--shadow-color': 'rgba(0, 0, 0, 0.5)',
                    '--logo-black': '#121212'
                });
            }
            
            function updateThemeVariables(variables) {
                const root = document.documentElement;
                for (const [key, value] of Object.entries(variables)) {
                    root.style.setProperty(key, value);
                }
            }
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        e.preventDefault();
                        
                        window.scrollTo({
                            top: targetElement.offsetTop - 80,
                            behavior: 'smooth'
                        });
                        
                        // Close mobile menu if open
                        if (mobileMenu) {
                            mobileMenu.classList.remove('active');
                        }
                        if (overlay) {
                            overlay.classList.remove('active');
                            document.body.style.overflow = 'auto';
                        }
                    }
                });
            });
            
            // Add fade-in animation to elements on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in');
                    }
                });
            }, observerOptions);
            
            // Observe elements for animation
            document.querySelectorAll('.property-card, .contact-item, .about-content').forEach(el => {
                observer.observe(el);
            });
        });
    </script>
</body>
</html>