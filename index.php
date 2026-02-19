<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Rethouse - Real Estate HTML Template">
    <meta name="keywords" content="Real Estate, Property, Directory Listing, Marketing, Agency" />
    <meta name="author" content="mardianto - retenvi.com">
    <title>ASSURNEST REALITY</title>

    <!-- Facebook and Twitter integration -->
    <meta property="og:title" content="" />
    <meta property="og:image" content="" />
    <meta property="og:url" content="" />
    <meta property="og:site_name" content="" />
    <meta property="og:description" content="" />
    <meta name="twitter:title" content="" />
    <meta name="twitter:image" content="" />
    <meta name="twitter:url" content="" />
    <meta name="twitter:card" content="" />

    <link rel="manifest" href="site.webmanifest">
    <!-- favicon.ico in the root directory -->
    <link rel="apple-touch-icon" href="icon.png">
    <meta name="theme-color" content="#3454d1">
    <link href="./css/styles.css" rel="stylesheet">
    <link rel="stylesheet" href="css/homepage_addtional.css">
</head>
   
                                    

<body>
    <!-- HEADER -->
    <header class="header__style-one">
        <!-- NAVBAR -->
        <nav class="navbar navbar-hover navbar-expand-lg navbar-soft navbar-transparent ">
            <div class="container">
                <a class="navbar-brand" href="/homepage-v1.html">
                    <img src="images/logo-blue.png" alt="">
                    <img src="images/logo-blue-stiky.png" alt="">
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main_nav99">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="main_nav99">
                    <ul class="navbar-nav mx-auto ">
                        <li class="nav-item dropdown">
                            <a class="nav-link active dropdown-toggle" href="#" data-toggle="dropdown"> Home </a>
                            <ul class="dropdown-menu dropdown-menu-left animate fade-up">
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown"> Properties </a>
                            <ul class="dropdown-menu animate fade-up">
                                <li><a class="dropdown-item " href="#">Luxury Flats</a>
                                </li>
                                <li><a class="dropdown-item " href="#">Villa</a>
                            </li>
                                <li><a class="dropdown-item " href="#"> Row Houses </a>
                                                        </li>
                                <li><a class="dropdown-item " href="#"> Lands and Plots </a>
                                                                </li>
                                <li><a class="dropdown-item " href="#">Bungloows</a>
                                                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link active dropdown-toggle" href="#" data-toggle="dropdown"> Blog </a>
                            <ul class="dropdown-menu dropdown-menu-left animate fade-up">
                            </ul>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="/contact.html"> contact </a></li>
                    </ul>
                    <!-- Search bar.// -->
                    <ul class="navbar-nav ">
                        <li>
                            <a href="#" class="btn btn-primary text-capitalize">
                                <i class="fa fa-sign-in mr-1"></i> Sign up</a>
                        </li>
                    </ul>
                    <!-- Search content bar.// -->
                    <div class="top-search navigation-shadow">
                        <div class="container">
                            <div class="input-group ">
                                <form action="#">

                                    <div class="row no-gutters mt-3">
                                        <div class="col">
                                            <input class="form-control border-secondary border-right-0 rounded-0"
                                                type="search" value="" placeholder="Search " id="example-search-input4">
                                        </div>
                                        <div class="col-auto">
                                            <a class="btn btn-outline-secondary border-left-0 rounded-0 rounded-right"
                                                href="/search-result.html">
                                                <i class="fa fa-search"></i>
                                            </a>
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Search content bar.// -->
                </div> <!-- navbar-collapse.// -->
            </div>
        </nav>
        <!-- END NAVBAR -->
    </header>
    <!-- END HEADER -->
    <!-- CAROUSEL -->

    <!-- CAROUSEL -->
    <div class="slider-container" >
        <section class="hero fade-in" id="home">
            <div class="container">
                <h1 class="animate-on-enter">Find Your Dream Property</h1>
                <p class="animate-on-enter" style="--delay: 0.35s;">
                    Prime Properties offers luxury real estate with premium financial solutions.
                    Discover exclusive properties and personalized financing options tailored to your needs.
                </p>
                <a href="#properties" class="btn-hero animate-on-enter" style="--delay: 0.65s;">
                    Explore Properties
                </a>
            </div>
            <style>
    

    .stats-wrapper {
    display: flex;
    flex-direction: row;
    gap: 40px;
    width: 100%;
    max-width: 420px;
    justify-content: center;
    }

    .stat-item {
      border-radius: 12px;
      padding: 28px 32px;
      text-align: center;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .stat-item:hover {
      transform: translateY(-4px);
    }

    .stat-number {
      font-size: 3.6rem;
      font-weight: 800;
      color: #ca8f19;           /* nice blue */
      margin: 0 0 8px 0;
      line-height: 1;
      min-height: 85px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .stat-label {
      font-size: 1.25rem;
      font-weight: 500;
      color: #ffffff;
    }

    .currency {
      font-size: 2.4rem;
      vertical-align: super;
      margin-left: 6px;
      color: #ca8f19;
    }
  </style>
</head>
<body>

<div class="stats-wrapper">

  <div class="stat-item">
    <div class="stat-number" id="houses-sold">0</div>
    <div class="stat-label">Total Houses Sold</div>
  </div>

  <div class="stat-item">
    <div class="stat-number" id="properties-available">0</div>
    <div class="stat-label">Total Properties Available</div>
  </div>

  <div class="stat-item">
    <div class="stat-number" id="total-revenue">
      <span id="revenue-value">0</span><span class="currency">₹ </span>
    </div>
    <div class="stat-label">Total Revenue</div>
  </div>

</div>

<script>
// Change these values as needed
const targets = {
  housesSold: 1247,
  propertiesAvailable: 389,
  totalRevenue: 856000000           // ₹  85.6 Cr
};

function formatNumber(num) {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function animateValue(id, start, end, duration = 2400) {
  const obj = document.getElementById(id);
  let startTime = null;

  function step(timestamp) {
    if (!startTime) startTime = timestamp;
    const progress = Math.min((timestamp - startTime) / duration, 1);
    
    // smooth ease-out cubic
    const eased = 1 - Math.pow(1 - progress, 3);
    
    const current = Math.floor(start + (end - start) * eased);
    obj.textContent = formatNumber(current);

    if (progress < 1) {
      requestAnimationFrame(step);
    } else {
      obj.textContent = formatNumber(end);
    }
  }

  requestAnimationFrame(step);
}

// Start counters when page is loaded
window.addEventListener('load', () => {
  animateValue("houses-sold", 0, targets.housesSold);
  animateValue("properties-available", 0, targets.propertiesAvailable);
  animateValue("revenue-value", 0, targets.totalRevenue);
});
</script>
        </section>
    </div>
    
    <script>
        // 1. Staggered entrance animation when loaded/visible
        document.addEventListener('DOMContentLoaded', () => {
            const hero = document.querySelector('.hero');
            const animatedEls = document.querySelectorAll('.animate-on-enter');

            // Optional: IntersectionObserver for better performance (if below fold)
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animatedEls.forEach(el => el.classList.add('visible'));
                        observer.unobserve(hero);
                    }
                });
            }, { threshold: 0.15 });

            observer.observe(hero);

            // Fallback: trigger after small delay if observer not supported
            setTimeout(() => {
                animatedEls.forEach(el => el.classList.add('visible'));
            }, 400);
        });

        // 2. Interactive mouse / touch spotlight follow
        const heroSection = document.querySelector('.hero');

        function updateMousePosition(e) {
            const rect = heroSection.getBoundingClientRect();
            let x, y;

            if (e.touches && e.touches.length > 0) {
                // Touch support
                const touch = e.touches[0];
                x = touch.clientX - rect.left;
                y = touch.clientY - rect.top;
            } else {
                // Mouse
                x = e.clientX - rect.left;
                y = e.clientY - rect.top;
            }

            // Normalize to percentage (better for different screen sizes)
            const percentX = (x / rect.width) * 100;
            const percentY = (y / rect.height) * 100;

            heroSection.style.setProperty('--mouse-x', `${percentX}%`);
            heroSection.style.setProperty('--mouse-y', `${percentY}%`);
        }

        heroSection.addEventListener('mousemove', updateMousePosition);
        heroSection.addEventListener('touchmove', updateMousePosition, { passive: true });

        // Optional: center on load / when mouse leaves
        heroSection.addEventListener('mouseleave', () => {
            heroSection.style.setProperty('--mouse-x', '50%');
            heroSection.style.setProperty('--mouse-y', '50%');
        });

    </script>
    <!-- --------------------------------------------------------------------------- -->
    <div class="clearfix"></div>
    <!-- END CAROUSEL -->
    <!-- END CAROSUEL -->
    <div class="clearfix"></div>

    <div class="search__area search__area-1" id="search__area-1">
        <div class="container">
            <div class="search__area-inner">
                <div class="row">
                    <div class="col-6 col-lg-3 col-md-3">
                        <div class="form-group">
                            <select class="wide select_option">
                                <option data-display="Property Status">Property Status</option>
                                <option>For Sale</option>
                                <option disabled>For Rent</option>

                            </select>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-3">
                        <div class="form-group">
                            <select class="wide select_option">
                                <option data-display="Property Type">Property Type</option>
                                <option>Lands</option>
                                <option>Villas</option>
                                <option>Bungloows</option>
                                <option>Row Houses</option>
                                <option>Luxury Flats</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-3">
                        <div class="form-group">
                            <select class="wide select_option">
                                <option data-display="Area From">Area From </option>
                                <option>1500 sqft</option>
                                <option>1200 sqft</option>
                                <option>900 sqft</option>
                                <option>600 sqft</option>
                                <option>300 sqft</option>
                                <option>100 sqft</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-3">
                        <div class="form-group">
                            <select class="wide select_option">
                                <option data-display="State">State</option>
                                <!-- <option>United Kingdom</option>
                                <option>American Samoa</option>
                                <option>Belgium</option>
                                <option>Canada</option>
                                <option>Delaware</option>
                                <option>Indonesia</option>
                                <option>Malaysia</option>
                                <option>Japan</option> -->
                            </select>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-3">
                        <div class="form-group">
                            <select class="wide select_option">
                                <option data-display="City">City</option>
                                <option>1</option>
                                <option>2</option>
                                <option>3</option>
                                <option>4</option>
                                <option>5</option>
                                <option>6</option>
                                <option>7</option>
                                <option>8</option>
                                <option>9</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-3">
                        <div class="form-group">
                            <select class="wide select_option">
                                <option data-display="Area">City</option>
                                <option>1</option>
                                <option>2</option>
                                <option>3</option>
                                <option>4</option>


                            </select>
                        </div>
                    </div>
                    <!-- --------------------------------------------------------------------------- -->
                    <style>
                        .slidecontainer {
                            width: 100%;
                        }

                        .slider {
                            -webkit-appearance: none;
                            width: 100%;
                            height: 25px;
                            background: #d3d3d3;
                            outline: none;
                            opacity: 0.7;
                            -webkit-transition: .2s;
                            transition: opacity .2s;
                            border-radius: 12.5px;
                        }

                        .slider:hover {
                            opacity: 1;
                        }

                        .slider::-webkit-slider-thumb {
                            -webkit-appearance: none;
                            appearance: none;
                            width: 25px;
                            height: 25px;
                            background: #3454d1;
                            cursor: pointer;
                            border-radius: 12.5px;
                        }

                        .slider::-moz-range-thumb {
                            width: 25px;
                            height: 25px;
                            background: #3454d1;
                            cursor: pointer;
                            border-radius: 12.5px;

                        }
                    </style>

                    <div class="col-12 col-lg-3 col-md-3">
                        <div class="form-group">
                            <div class="slidecontainer">
                                <input type="range" min="100000" max="50000000" value="50" class="slider" id="myRange">
                                <p>Price Range: <span id="demo"></span></p>
                            </div>
                        </div>
                    </div>
                    <script>
                        var slider = document.getElementById("myRange");
                        var output = document.getElementById("demo");
                        output.innerHTML = slider.value;

                        slider.oninput = function () {
                            output.innerHTML = this.value;
                        }
                    </script>
                    <div class="col-12 col-lg-3 col-md-3">
                        <div class="form-group">
                            <button class="btn btn-primary text-uppercase btn-block"> search <i
                                    class="fa fa-search ml-1"></i></button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>


   
    <section class="featured__property bg-light mt-5" id="properties">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-lg-6 mx-auto">
                    <div class="title__head">
                        <h2 class="text-center text-capitalize">
                            Recent Property
                        </h2>
                        <p class="text-center text-capitalize">We provide full service at every step.</p>

                    </div>
                </div>
            </div>
            <div class="featured__property-carousel owl-carousel owl-theme">

                <?php
    require_once 'includes/db.php';

    $sql = "SELECT property_id, property_type, property_name, 
                   location_area, location_city, 
                   price, sqft, rooms, bathrooms, kitchens,
                   status
            FROM properties 
            WHERE status = 'available' 
            ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows) {
        foreach ($rows as $row) {
            $price_raw = (float)$row['price'];
            if ($price_raw >= 10000000) {
                $price_display = "₹ " . number_format($price_raw / 10000000, 2) . " Cr";
            } elseif ($price_raw >= 100000) {
                $price_display = "₹ " . number_format($price_raw / 100000, 2) . " Lac";
            } else {
                $price_display = "₹ " . number_format($price_raw);
            }

            $area_display = $row['sqft'] ? number_format($row['sqft'], 0) . " sq ft" : "N/A";
            $image_src    = "images/500x400.jpg"; // ← change later

            $type     = htmlspecialchars(ucfirst($row['property_type'] ?? 'Property'));
            $name     = htmlspecialchars($row['property_name'] ?? 'Unnamed Property');
            $location = htmlspecialchars(trim($row['location_area'] . ', ' . $row['location_city']));
            $baths    = $row['bathrooms'] ?? 0;
            $kitchens = $row['kitchens'] ?? 0;
            $rooms    = $row['rooms'] ?? 0;
    ?>
                <div class="item">
                    <div class="card__image card__box">
                        <div class="card__image-header h-250">
                            <div class="ribbon text-uppercase">Available</div>
                            <img src="<?php echo $image_src; ?>" alt="<?php echo $name; ?>"
                                class="img-fluid w100 img-transition">
                            <div class="info"> for sale</div>
                        </div>
                        <div class="card__image-body">
                            <span class="badge badge-primary text-capitalize mb-2"><?php echo $type; ?></span>
                            <h6 class="text-capitalize"><?php echo $name; ?></h6>
                            <p class="text-capitalize">
                                <i class="fa fa-map-marker"></i> <?php echo $location; ?>
                            </p>
                            <ul class="list-inline card__content">
                            <?php if($type=='Lands and Plots'){?>   
                            <li class="list-inline-item">
                                    <span>baths <br><i class="fa fa-bath"></i> <?php echo $baths; ?></span>
                                </li>
                                <li class="list-inline-item">
                                    <span>kitchens <br><i class="fa fa-utensils"></i> <?php echo $kitchens; ?></span>
                                </li>
                                <li class="list-inline-item">
                                    <span>rooms <br><i class="fa fa-inbox"></i> <?php echo $rooms; ?></span>
                                </li>
                                <?php }?> 
                                <li class="list-inline-item">
                                    <span>area <br><i class="fa fa-map"></i> <?php echo $area_display; ?></span>
                                </li>
                            </ul>
                        </div>
                        <div class="card__image-footer">
                            <figure>
                                <img src="images/80x80.jpg" alt="" class="img-fluid rounded-circle">
                            </figure>
                            <ul class="list-inline my-auto">
                                <li class="list-inline-item pt-2">
                                    <a href="#">ASSURNEST<br>REALITY</a>
                                </li>
                            </ul>
                            <ul class="list-inline my-auto ml-auto">
                                <li class="list-inline-item">
                                    <h6><?php echo $price_display; ?></h6>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php
        }
    } else {
        echo '<div class="item"><div class="text-center py-5">No properties available right now.</div></div>';
    }
    ?>

            </div>










            <!-- </div> -->
    </section>
    <!-- Land and plots PROPERTIES -->
    <section class="featured__property space-area" id="p1">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-lg-6 mx-auto">
                    <div class="title__head">
                        <h2 class="text-center text-capitalize">
                            Land and Plots
                        </h2>
                        <p class="text-center text-capitalize">handpicked exclusive properties by our team.</p>

                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="featured__property-carousel owl-carousel owl-theme">

                <?php
    require_once 'includes/db.php';

    $sql = "SELECT property_id, property_type, property_name, 
                   location_area, location_city, 
                   price, sqft, rooms, bathrooms, kitchens,
                   status
            FROM properties 
            WHERE status = 'available' and property_type = 'Lands and Plots'
            ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows) {
        foreach ($rows as $row) {
            $price_raw = (float)$row['price'];
            if ($price_raw >= 10000000) {
                $price_display = "₹ " . number_format($price_raw / 10000000, 2) . " Cr";
            } elseif ($price_raw >= 100000) {
                $price_display = "₹ " . number_format($price_raw / 100000, 2) . " Lac";
            } else {
                $price_display = "₹ " . number_format($price_raw);
            }

            $area_display = $row['sqft'] ? number_format($row['sqft'], 0) . " sq ft" : "N/A";
            $image_src    = "images/500x400.jpg"; // ← change later

            $type     = htmlspecialchars(ucfirst($row['property_type'] ?? 'Property'));
            $name     = htmlspecialchars($row['property_name'] ?? 'Unnamed Property');
            $location = htmlspecialchars(trim($row['location_area'] . ', ' . $row['location_city']));
            $baths    = $row['bathrooms'] ?? 0;
            $kitchens = $row['kitchens'] ?? 0;
            $rooms    = $row['rooms'] ?? 0;
    ?>
                <div class="item">
                    <div class="card__image card__box">
                        <div class="card__image-header h-250">
                            <div class="ribbon text-uppercase">Available</div>
                            <img src="<?php echo $image_src; ?>" alt="<?php echo $name; ?>"
                                class="img-fluid w100 img-transition">
                            <div class="info"> for sale</div>
                        </div>
                        <div class="card__image-body">
                            <span class="badge badge-primary text-capitalize mb-2"><?php echo $type; ?></span>
                            <h6 class="text-capitalize"><?php echo $name; ?></h6>
                            <p class="text-capitalize">
                                <i class="fa fa-map-marker"></i> <?php echo $location; ?>
                            </p>
                            <ul class="list-inline card__content">
                                <!-- <li class="list-inline-item">
                                    <span>baths <br><i class="fa fa-bath"></i> <?php echo $baths; ?></span>
                                </li>
                                <li class="list-inline-item">
                                    <span>kitchens <br><i class="fa fa-utensils"></i> <?php echo $kitchens; ?></span>
                                </li>
                                <li class="list-inline-item">
                                    <span>rooms <br><i class="fa fa-inbox"></i> <?php echo $rooms; ?></span>
                                </li> -->
                                <li class="list-inline-item">
                                    <span>area <br><i class="fa fa-map"></i> <?php echo $area_display; ?></span>
                                </li>
                            </ul>
                        </div>
                        <div class="card__image-footer">
                            <figure>
                                <img src="images/80x80.jpg" alt="" class="img-fluid rounded-circle">
                            </figure>
                            <ul class="list-inline my-auto">
                                <li class="list-inline-item pt-2">
                                    <a href="#">ASSURNEST<br>REALITY</a>
                                </li>
                            </ul>
                            <ul class="list-inline my-auto ml-auto">
                                <li class="list-inline-item">
                                    <h6><?php echo $price_display; ?></h6>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php
        }
    } else {
        echo '<div class="item"><div class="text-center py-5">No properties available right now.</div></div>';
    }
    ?>

            </div>







        </div>
    </section>
    <section class="featured__property space-area" id="p2">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-lg-6 mx-auto">
                    <div class="title__head">
                        <h2 class="text-center text-capitalize">
                            Villa and Apartments
                        </h2>
                        <p class="text-center text-capitalize">handpicked exclusive properties by our team.</p>

                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
             <div class="featured__property-carousel owl-carousel owl-theme">

    <?php
    require_once 'includes/db.php';

    $sql = "SELECT property_id, property_type, property_name, 
                   location_area, location_city, 
                   price, sqft, rooms, bathrooms, kitchens,
                   status
            FROM properties 
            WHERE status = 'available' and  property_type = 'Villas'
            ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows) {
        foreach ($rows as $row) {
            $price_raw = (float)$row['price'];
            if ($price_raw >= 10000000) {
                $price_display = "₹ " . number_format($price_raw / 10000000, 2) . " Cr";
            } elseif ($price_raw >= 100000) {
                $price_display = "₹ " . number_format($price_raw / 100000, 2) . " Lac";
            } else {
                $price_display = "₹ " . number_format($price_raw);
            }

            $area_display = $row['sqft'] ? number_format($row['sqft'], 0) . " sq ft" : "N/A";
            $image_src    = "images/500x400.jpg"; // ← change later

            $type     = htmlspecialchars(ucfirst($row['property_type'] ?? 'Property'));
            $name     = htmlspecialchars($row['property_name'] ?? 'Unnamed Property');
            $location = htmlspecialchars(trim($row['location_area'] . ', ' . $row['location_city']));
            $baths    = $row['bathrooms'] ?? 0;
            $kitchens = $row['kitchens'] ?? 0;
            $rooms    = $row['rooms'] ?? 0;
    ?>
            <div class="item">
                <div class="card__image card__box">
                    <div class="card__image-header h-250">
                        <div class="ribbon text-uppercase">Available</div>
                        <img src="<?php echo $image_src; ?>" alt="<?php echo $name; ?>" class="img-fluid w100 img-transition">
                        <div class="info"> for sale</div>
                    </div>
                    <div class="card__image-body">
                        <span class="badge badge-primary text-capitalize mb-2"><?php echo $type; ?></span>
                        <h6 class="text-capitalize"><?php echo $name; ?></h6>
                        <p class="text-capitalize">
                            <i class="fa fa-map-marker"></i> <?php echo $location; ?>
                        </p>
                        <ul class="list-inline card__content">
                            <li class="list-inline-item">
                                <span>baths <br><i class="fa fa-bath"></i> <?php echo $baths; ?></span>
                            </li>
                            <li class="list-inline-item">
                                <span>kitchens <br><i class="fa fa-utensils"></i> <?php echo $kitchens; ?></span>
                            </li>
                            <li class="list-inline-item">
                                <span>rooms <br><i class="fa fa-inbox"></i> <?php echo $rooms; ?></span>
                            </li>
                            <li class="list-inline-item">
                                <span>area <br><i class="fa fa-map"></i> <?php echo $area_display; ?></span>
                            </li>
                        </ul>
                    </div>
                    <div class="card__image-footer">
                        <figure>
                            <img src="images/80x80.jpg" alt="" class="img-fluid rounded-circle">
                        </figure>
                        <ul class="list-inline my-auto">
                            <li class="list-inline-item pt-2">
                                <a href="#">ASSURNEST<br>REALITY</a>
                            </li>
                        </ul>
                        <ul class="list-inline my-auto ml-auto">
                            <li class="list-inline-item">
                                <h6><?php echo $price_display; ?></h6>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
    <?php
        }
    } else {
        echo '<div class="item"><div class="text-center py-5">No properties available right now.</div></div>';
    }
    ?>

</div>






        </div>
    </section>
    <section class="featured__property space-area" id="p3">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-lg-6 mx-auto">
                    <div class="title__head">
                        <h2 class="text-center text-capitalize">
                            Luxury Flats
                        </h2>
                        <p class="text-center text-capitalize">handpicked exclusive properties by our team.</p>

                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
             <div class="featured__property-carousel owl-carousel owl-theme">

    <?php
    require_once 'includes/db.php';

    $sql = "SELECT property_id, property_type, property_name, 
                   location_area, location_city, 
                   price, sqft, rooms, bathrooms, kitchens,
                   status
            FROM properties 
            WHERE status = 'available' and  property_type = 'Flats'
            ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows) {
        foreach ($rows as $row) {
            $price_raw = (float)$row['price'];
            if ($price_raw >= 10000000) {
                $price_display = "₹ " . number_format($price_raw / 10000000, 2) . " Cr";
            } elseif ($price_raw >= 100000) {
                $price_display = "₹ " . number_format($price_raw / 100000, 2) . " Lac";
            } else {
                $price_display = "₹ " . number_format($price_raw);
            }

            $area_display = $row['sqft'] ? number_format($row['sqft'], 0) . " sq ft" : "N/A";
            $image_src    = "images/500x400.jpg"; // ← change later

            $type     = htmlspecialchars(ucfirst($row['property_type'] ?? 'Property'));
            $name     = htmlspecialchars($row['property_name'] ?? 'Unnamed Property');
            $location = htmlspecialchars(trim($row['location_area'] . ', ' . $row['location_city']));
            $baths    = $row['bathrooms'] ?? 0;
            $kitchens = $row['kitchens'] ?? 0;
            $rooms    = $row['rooms'] ?? 0;
    ?>
            <div class="item">
                <div class="card__image card__box">
                    <div class="card__image-header h-250">
                        <div class="ribbon text-uppercase">Available</div>
                        <img src="<?php echo $image_src; ?>" alt="<?php echo $name; ?>" class="img-fluid w100 img-transition">
                        <div class="info"> for sale</div>
                    </div>
                    <div class="card__image-body">
                        <span class="badge badge-primary text-capitalize mb-2"><?php echo $type; ?></span>
                        <h6 class="text-capitalize"><?php echo $name; ?></h6>
                        <p class="text-capitalize">
                            <i class="fa fa-map-marker"></i> <?php echo $location; ?>
                        </p>
                        <ul class="list-inline card__content">
                            <li class="list-inline-item">
                                <span>baths <br><i class="fa fa-bath"></i> <?php echo $baths; ?></span>
                            </li>
                            <li class="list-inline-item">
                                <span>kitchens <br><i class="fa fa-utensils"></i> <?php echo $kitchens; ?></span>
                            </li>
                            <li class="list-inline-item">
                                <span>rooms <br><i class="fa fa-inbox"></i> <?php echo $rooms; ?></span>
                            </li>
                            <li class="list-inline-item">
                                <span>area <br><i class="fa fa-map"></i> <?php echo $area_display; ?></span>
                            </li>
                        </ul>
                    </div>
                    <div class="card__image-footer">
                        <figure>
                            <img src="images/80x80.jpg" alt="" class="img-fluid rounded-circle">
                        </figure>
                        <ul class="list-inline my-auto">
                            <li class="list-inline-item pt-2">
                                <a href="#">ASSURNEST<br>REALITY</a>
                            </li>
                        </ul>
                        <ul class="list-inline my-auto ml-auto">
                            <li class="list-inline-item">
                                <h6><?php echo $price_display; ?></h6>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
    <?php
        }
    } else {
        echo '<div class="item"><div class="text-center py-5">No properties available right now.</div></div>';
    }
    ?>

</div>

        </div>
    </section>
    <section class="featured__property space-area" id="p4">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-lg-6 mx-auto">
                    <div class="title__head">
                        <h2 class="text-center text-capitalize">
                            Row Houses
                        </h2>
                        <p class="text-center text-capitalize">handpicked exclusive properties by our team.</p>

                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
             <div class="featured__property-carousel owl-carousel owl-theme">

    <?php
    require_once 'includes/db.php';

    $sql = "SELECT property_id, property_type, property_name, 
                   location_area, location_city, 
                   price, sqft, rooms, bathrooms, kitchens,
                   status
            FROM properties 
            WHERE status = 'available' and  property_type = 'Row Houses'
            ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows) {
        foreach ($rows as $row) {
            $price_raw = (float)$row['price'];
            if ($price_raw >= 10000000) {
                $price_display = "₹ " . number_format($price_raw / 10000000, 2) . " Cr";
            } elseif ($price_raw >= 100000) {
                $price_display = "₹ " . number_format($price_raw / 100000, 2) . " Lac";
            } else {
                $price_display = "₹ " . number_format($price_raw);
            }

            $area_display = $row['sqft'] ? number_format($row['sqft'], 0) . " sq ft" : "N/A";
            $image_src    = "images/500x400.jpg"; // ← change later

            $type     = htmlspecialchars(ucfirst($row['property_type'] ?? 'Property'));
            $name     = htmlspecialchars($row['property_name'] ?? 'Unnamed Property');
            $location = htmlspecialchars(trim($row['location_area'] . ', ' . $row['location_city']));
            $baths    = $row['bathrooms'] ?? 0;
            $kitchens = $row['kitchens'] ?? 0;
            $rooms    = $row['rooms'] ?? 0;
    ?>
            <div class="item">
                <div class="card__image card__box">
                    <div class="card__image-header h-250">
                        <div class="ribbon text-uppercase">Available</div>
                        <img src="<?php echo $image_src; ?>" alt="<?php echo $name; ?>" class="img-fluid w100 img-transition">
                        <div class="info"> for sale</div>
                    </div>
                    <div class="card__image-body">
                        <span class="badge badge-primary text-capitalize mb-2"><?php echo $type; ?></span>
                        <h6 class="text-capitalize"><?php echo $name; ?></h6>
                        <p class="text-capitalize">
                            <i class="fa fa-map-marker"></i> <?php echo $location; ?>
                        </p>
                        <ul class="list-inline card__content">
                            <li class="list-inline-item">
                                <span>baths <br><i class="fa fa-bath"></i> <?php echo $baths; ?></span>
                            </li>
                            <li class="list-inline-item">
                                <span>kitchens <br><i class="fa fa-utensils"></i> <?php echo $kitchens; ?></span>
                            </li>
                            <li class="list-inline-item">
                                <span>rooms <br><i class="fa fa-inbox"></i> <?php echo $rooms; ?></span>
                            </li>
                            <li class="list-inline-item">
                                <span>area <br><i class="fa fa-map"></i> <?php echo $area_display; ?></span>
                            </li>
                        </ul>
                    </div>
                    <div class="card__image-footer">
                        <figure>
                            <img src="images/80x80.jpg" alt="" class="img-fluid rounded-circle">
                        </figure>
                        <ul class="list-inline my-auto">
                            <li class="list-inline-item pt-2">
                                <a href="#">ASSURNEST<br>REALITY</a>
                            </li>
                        </ul>
                        <ul class="list-inline my-auto ml-auto">
                            <li class="list-inline-item">
                                <h6><?php echo $price_display; ?></h6>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
    <?php
        }
    } else {
        echo '<div class="item"><div class="text-center py-5">No properties available right now.</div></div>';
    }
    ?>

</div>



        </div>
    </section>



      <section class="featured__property space-area" id="p5">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-lg-6 mx-auto">
                    <div class="title__head">
                        <h2 class="text-center text-capitalize">
                            Bungalows
                        </h2>
                        <p class="text-center text-capitalize">handpicked exclusive properties by our team.</p>

                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
             <div class="featured__property-carousel owl-carousel owl-theme">

    <?php
    require_once 'includes/db.php';

    $sql = "SELECT property_id, property_type, property_name, 
                   location_area, location_city, 
                   price, sqft, rooms, bathrooms, kitchens,
                   status
            FROM properties 
            WHERE status = 'available' and  property_type = 'Bungalows'
            ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows) {
        foreach ($rows as $row) {
            $price_raw = (float)$row['price'];
            if ($price_raw >= 10000000) {
                $price_display = "₹ " . number_format($price_raw / 10000000, 2) . " Cr";
            } elseif ($price_raw >= 100000) {
                $price_display = "₹ " . number_format($price_raw / 100000, 2) . " Lac";
            } else {
                $price_display = "₹ " . number_format($price_raw);
            }

            $area_display = $row['sqft'] ? number_format($row['sqft'], 0) . " sq ft" : "N/A";
            $image_src    = "images/500x400.jpg"; // ← change later

            $type     = htmlspecialchars(ucfirst($row['property_type'] ?? 'Property'));
            $name     = htmlspecialchars($row['property_name'] ?? 'Unnamed Property');
            $location = htmlspecialchars(trim($row['location_area'] . ', ' . $row['location_city']));
            $baths    = $row['bathrooms'] ?? 0;
            $kitchens = $row['kitchens'] ?? 0;
            $rooms    = $row['rooms'] ?? 0;
    ?>
            <div class="item">
                <div class="card__image card__box">
                    <div class="card__image-header h-250">
                        <div class="ribbon text-uppercase">Available</div>
                        <img src="<?php echo $image_src; ?>" alt="<?php echo $name; ?>" class="img-fluid w100 img-transition">
                        <div class="info"> for sale</div>
                    </div>
                    <div class="card__image-body">
                        <span class="badge badge-primary text-capitalize mb-2"><?php echo $type; ?></span>
                        <h6 class="text-capitalize"><?php echo $name; ?></h6>
                        <p class="text-capitalize">
                            <i class="fa fa-map-marker"></i> <?php echo $location; ?>
                        </p>
                        <ul class="list-inline card__content">
                            <li class="list-inline-item">
                                <span>baths <br><i class="fa fa-bath"></i> <?php echo $baths; ?></span>
                            </li>
                            <li class="list-inline-item">
                                <span>kitchens <br><i class="fa fa-utensils"></i> <?php echo $kitchens; ?></span>
                            </li>
                            <li class="list-inline-item">
                                <span>rooms <br><i class="fa fa-inbox"></i> <?php echo $rooms; ?></span>
                            </li>
                            <li class="list-inline-item">
                                <span>area <br><i class="fa fa-map"></i> <?php echo $area_display; ?></span>
                            </li>
                        </ul>
                    </div>
                    <div class="card__image-footer">
                        <figure>
                            <img src="images/80x80.jpg" alt="" class="img-fluid rounded-circle">
                        </figure>
                        <ul class="list-inline my-auto">
                            <li class="list-inline-item pt-2">
                                <a href="#">ASSURNEST<br>REALITY</a>
                            </li>
                        </ul>
                        <ul class="list-inline my-auto ml-auto">
                            <li class="list-inline-item">
                                <h6><?php echo $price_display; ?></h6>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
    <?php
        }
    } else {
        echo '<div class="item"><div class="text-center py-5">No properties available right now.</div></div>';
    }
    ?>

</div>



        </div>
    </section>
    <!-- END FEATURED PROPERTIES -->

    <!-- RECENT PROPERTY -->

    <!-- END RECENT PROPERTY -->

    <!-- PRICITNG TABLE -->

    <!-- END PRICITNG TABLE -->

    <!-- BRAND PARTNER -->
    <section class="projects__partner bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="title__head">
                        <h2 class="text-center text-capitalize">our partners</h2>
                        <p class="text-center text-capitalize">Brand Partners Successful Projects Trusted Many Clients
                            Real Estate </p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="projects__partner-logo">
                        <ul class="list-inline mb-0">
                            <li class="list-inline-item">
                                <img src="images/partner-logo6.png" alt="" class="img-fluid">
                            </li>
                            <li class="list-inline-item">
                                <img src="images/partner-logo7.png" alt="" class="img-fluid">
                            </li>
                            <li class="list-inline-item">
                                <img src="images/partner-logo8.png" alt="" class="img-fluid">
                            </li>
                            <li class="list-inline-item">
                                <img src="images/partner-logo1.png" alt="" class="img-fluid">
                            </li>
                            <li class="list-inline-item">
                                <img src="images/partner-logo5.png" alt="" class="img-fluid">
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END BRAND PARTNER -->

    <!-- TESTIMONIAL -->
    <section id="r1">
        <div class="container" >
            <div class="row">
                <div class="col-md-8 col-lg-6 mx-auto">
                    <div class="title__head">
                        <h2 class="text-center text-capitalize">
                            what people says
                        </h2>
                        <p class="text-center text-capitalize">people says about walls property.</p>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="testimonial owl-carousel owl-theme">
                <!-- TESTIMONIAL -->
                <div class="item testimonial__block">
                    <div class="testimonial__block-card bg-reviews">
                        <p>
                            Thank you walls property help me, choice dream home We were impressed with the build
                            quality, Plus they are competitively priced.
                        </p>
                    </div>
                    <div class="testimonial__block-users">
                        <div class="testimonial__block-users-img">
                            <img src="images/80x80.jpg" alt="" class="img-fluid">
                        </div>
                        <div class="testimonial__block-users-name">
                            jhon doe <br>
                            <span>owner, digital agency</span>
                        </div>
                    </div>
                </div>
                <!-- END TESTIMONIAL -->
                <!-- TESTIMONIAL -->
                <div class="item testimonial__block">
                    <div class="testimonial__block-card bg-reviews">
                        <p>
                            Thank you walls property help me, choice dream home We were impressed with the build
                            quality, Plus they are competitively priced.
                        </p>
                    </div>
                    <div class="testimonial__block-users">
                        <div class="testimonial__block-users-img">
                            <img src="images/80x80.jpg" alt="" class="img-fluid">
                        </div>
                        <div class="testimonial__block-users-name">
                            jhon doe <br>
                            <span>owner, digital agency</span>
                        </div>
                    </div>
                </div>
                <!-- END TESTIMONIAL -->
                <!-- TESTIMONIAL -->
                <div class="item testimonial__block">
                    <div class="testimonial__block-card bg-reviews">
                        <p>
                            Thank you walls property help me, choice dream home We were impressed with the build
                            quality, Plus they are competitively priced.
                        </p>
                    </div>
                    <div class="testimonial__block-users">
                        <div class="testimonial__block-users-img">
                            <img src="images/80x80.jpg" alt="" class="img-fluid">
                        </div>
                        <div class="testimonial__block-users-name">
                            jhon doe <br>
                            <span>owner, digital agency</span>
                        </div>
                    </div>
                </div>
                <!-- END TESTIMONIAL -->
                <!-- TESTIMONIAL -->
                <div class="item testimonial__block">
                    <div class="testimonial__block-card bg-reviews">
                        <p>
                            Thank you walls property help me, choice dream home We were impressed with the build
                            quality, Plus they are competitively priced.
                        </p>
                    </div>
                    <div class="testimonial__block-users">
                        <div class="testimonial__block-users-img">
                            <img src="images/80x80.jpg" alt="" class="img-fluid">
                        </div>
                        <div class="testimonial__block-users-name">
                            jhon doe <br>
                            <span>owner, digital agency</span>
                        </div>
                    </div>
                </div>
                <!-- END TESTIMONIAL -->
                <!-- TESTIMONIAL -->
                <div class="item testimonial__block">
                    <div class="testimonial__block-card bg-reviews">
                        <p>
                            Thank you walls property help me, choice dream home We were impressed with the build
                            quality, Plus they are competitively priced.
                        </p>
                    </div>
                    <div class="testimonial__block-users">
                        <div class="testimonial__block-users-img">
                            <img src="images/80x80.jpg" alt="" class="img-fluid">
                        </div>
                        <div class="testimonial__block-users-name">
                            jhon doe <br>
                            <span>owner, digital agency</span>
                        </div>
                    </div>
                </div>
                <!-- END TESTIMONIAL -->
            </div>
        </div>
    </section>
    <!-- END TESTIMONIAL -->

    <!-- BLOG -->

    <section class="featured__property bg-light mt-5" id="properties">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-lg-6 mx-auto">
                    <div class="title__head">
                        <h2 class="text-center text-capitalize">
                            Sold Out Properties
                        </h2>
                        <p class="text-center text-capitalize">We provide full service at every step.</p>

                    </div>
                </div>
            </div>
            <div class="featured__property-carousel owl-carousel owl-theme">

                <?php
    require_once 'includes/db.php';

    $sql = "SELECT property_id, property_type, property_name, 
                   location_area, location_city, 
                   price, sqft, rooms, bathrooms, kitchens,
                   status
            FROM properties 
            WHERE status = 'Sold' 
            ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows) {
        foreach ($rows as $row) {
            $price_raw = (float)$row['price'];
            if ($price_raw >= 10000000) {
                $price_display = "₹ " . number_format($price_raw / 10000000, 2) . " Cr";
            } elseif ($price_raw >= 100000) {
                $price_display = "₹ " . number_format($price_raw / 100000, 2) . " Lac";
            } else {
                $price_display = "₹ " . number_format($price_raw);
            }

            $area_display = $row['sqft'] ? number_format($row['sqft'], 0) . " sq ft" : "N/A";
            $image_src    = "images/500x400.jpg"; // ← change later

            $type     = htmlspecialchars(ucfirst($row['property_type'] ?? 'Property'));
            $name     = htmlspecialchars($row['property_name'] ?? 'Unnamed Property');
            $location = htmlspecialchars(trim($row['location_area'] . ', ' . $row['location_city']));
            $baths    = $row['bathrooms'] ?? 0;
            $kitchens = $row['kitchens'] ?? 0;
            $rooms    = $row['rooms'] ?? 0;
    ?>
                <div class="item">
                    <div class="card__image card__box">
                        <div class="card__image-header h-250">
                           
                            <img src="<?php echo $image_src; ?>" alt="<?php echo $name; ?>"
                                class="img-fluid w100 img-transition">
                            <div class="INFO1" style="background-color:red;"> Sold Out </div>
                        </div>
                        <div class="card__image-body">
                            <span class="badge badge-primary text-capitalize mb-2"><?php echo $type; ?></span>
                            <h6 class="text-capitalize"><?php echo $name; ?></h6>
                            <p class="text-capitalize">
                                <i class="fa fa-map-marker"></i> <?php echo $location; ?>
                            </p>
                            <ul class="list-inline card__content">
                            <?php if($type=='Lands and Plots'){?>   
                            <li class="list-inline-item">
                                    <span>baths <br><i class="fa fa-bath"></i> <?php echo $baths; ?></span>
                                </li>
                                <li class="list-inline-item">
                                    <span>kitchens <br><i class="fa fa-utensils"></i> <?php echo $kitchens; ?></span>
                                </li>
                                <li class="list-inline-item">
                                    <span>rooms <br><i class="fa fa-inbox"></i> <?php echo $rooms; ?></span>
                                </li>
                                <?php }?> 
                                <li class="list-inline-item">
                                    <span>area <br><i class="fa fa-map"></i> <?php echo $area_display; ?></span>
                                </li>
                            </ul>
                        </div>
                        <div class="card__image-footer">
                            <figure>
                                <img src="images/80x80.jpg" alt="" class="img-fluid rounded-circle">
                            </figure>
                            <ul class="list-inline my-auto">
                                <li class="list-inline-item pt-2">
                                    <a href="#">ASSURNEST<br>REALITY</a>
                                </li>
                            </ul>
                            <ul class="list-inline my-auto ml-auto">
                                <li class="list-inline-item">
                                    <h6><?php echo $price_display; ?></h6>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php
        }
    } else {
        echo '<div class="item"><div class="text-center py-5">No properties available right now.</div></div>';
    }
    ?>

            </div>










            <!-- </div> -->
    </section>
   
    <!-- END BLOG -->



    <!-- CALL TO ACTION -->
    <section class="cta-v1 py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-9">
                    <h2 class="text-uppercase text-white">Looking To Sell Or Rent Your Property?</h2>
                    <p class="text-capitalize text-white">We Will Assist You In The Best And Comfortable Property
                        Services
                        For You
                    </p>

                </div>
                <div class="col-lg-3">
                    <a href="#" class="btn btn-light text-uppercase ">request a quote
                        <i class="fa fa-angle-right ml-3 arrow-btn "></i></a>
                </div>
            </div>
        </div>
    </section>
    <!-- END CALL TO ACTION -->

   

    <!-- Footer  -->
    <footer>
        <div class="wrapper__footer bg-theme-footer">
            <div class="container">
                <div class="row">
                    <!-- ADDRESS -->
                    <div class="col-md-4">
                        <div class="widget__footer">
                            <figure>
                                <img src="images/logo-blue.png" alt="" class="logo-footer">
                            </figure>
                            <p>
                                Rethouse Real Estate is a premium Property template based on Bootstrap 4. Rethouse Real
                                Estate helped thousands of clients to find the right property for their needs.

                            </p>

                            <ul class="list-unstyled mb-0 mt-3">
                                <li> <b> <i class="fa fa-map-marker"></i></b><span>214 West Arnold St. New York, NY
                                        10002</span> </li>
                                <li> <b><i class="fa fa-phone-square"></i></b><span>(123) 345-6789</span> </li>
                                <li> <b><i class="fa fa-phone-square"></i></b><span>(+100) 123 456 7890</span> </li>
                                <li> <b><i class="fa fa-headphones"></i></b><span>support@realvilla.demo</span> </li>
                                <li> <b><i class="fa fa-clock-o"></i></b><span>Mon - Sun / 9:00AM - 8:00PM</span> </li>
                            </ul>
                        </div>

                    </div>
                    <!-- END ADDRESS -->

                    <!-- QUICK LINKS -->
                    <div class="col-md-4">
                        <div class="widget__footer">
                            <h4 class="footer-title">Quick Links</h4>
                            <div class="link__category-two-column">
                                <ul class="list-unstyled ">
                                    <li class="list-inline-item">
                                        <a href="#">Commercial</a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a href="#">business</a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a href="#">House</a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a href="#">Residential</a>
                                    </li>

                                    <li class="list-inline-item">
                                        <a href="#">Residential Tower</a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a href="#">Beverly Hills</a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a href="#">Los angeles</a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a href="#">The beach</a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a href="#">Property Listing</a>
                                    </li>

                                    <li class="list-inline-item">
                                        <a href="#">Clasic</a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a href="#">Modern Home</a>
                                    </li>

                                    <li class="list-inline-item">
                                        <a href="#">Luxury</a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a href="#">Beach Pasadena</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- END QUICK LINKS -->


                    <!-- NEWSLETTERS -->
                    <div class="col-md-4">
                        <div class="widget__footer">
                            <h4 class="footer-title">follow us </h4>
                            <p class="mb-2">
                                Follow us and stay in touch to get the latest news
                            </p>
                            <p>
                                <button class="btn btn-social btn-social-o facebook mr-1">
                                    <i class="fa fa-facebook-f"></i>
                                </button>
                                <button class="btn btn-social btn-social-o twitter mr-1">
                                    <i class="fa fa-twitter"></i>
                                </button>

                                <button class="btn btn-social btn-social-o linkedin mr-1">
                                    <i class="fa fa-linkedin"></i>
                                </button>
                                <button class="btn btn-social btn-social-o instagram mr-1">
                                    <i class="fa fa-instagram"></i>
                                </button>

                                <button class="btn btn-social btn-social-o youtube mr-1">
                                    <i class="fa fa-youtube"></i>
                                </button>
                            </p>
                            <br>
                            <h4 class="footer-title"></h4>
                            <!-- Form Newsletter -->
                            <div class="widget__form-newsletter ">
                                <p>

                                    
                                </p>
                                <div class="mt-3">
                                    
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- END NEWSLETTER -->
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="bg__footer-bottom-v1">
            <div class="container">
                <div class="row flex-column-reverse flex-md-row">
                    <div class="col-md-6">
                        <span>
                            © 2020 Rethouse Real Estate - Premium real estate & theme &amp; theme by
                            <a href="#">retenvi.com</a>
                        </span>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-inline ">
                            <li class="list-inline-item">
                                <a href="#">
                                    privacy
                                </a>
                            </li>
                            <li class="list-inline-item">
                                <a href="#">
                                    contact
                                </a>
                            </li>
                            <li class="list-inline-item">
                                <a href="#">
                                    about
                                </a>
                            </li>
                            <li class="list-inline-item">
                                <a href="#">
                                    faq
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Footer  -->
    </footer>

    <!-- SCROLL TO TOP -->
    <a href="javascript:" id="return-to-top"><i class="fa fa-chevron-up"></i></a>
    <!-- END SCROLL TO TOP -->
    <script src="./js/index.bundle.js?fd365619e86ad9137a29"></script>
</body>

</html>