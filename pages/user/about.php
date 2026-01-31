<?php
session_start();
include '../../includes/db.php';

// Optional: require login (remove these 4 lines if public page)
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch all properties with images
$stmt = $pdo->query("
    SELECT id, type, location, price, image1, image2, image3, image4 
    FROM properties 
    WHERE status = 'available' 
    ORDER BY id DESC
");
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Gallery & About | Assurnest Realty</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        :root {
            --primary: #2a5bd7;
            --primary-dark: #1e4bb9;
            --light: #f8f9fa;
            --gray: #6c757d;
            --dark: #2c3e50;
        }

        body {
                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;

            background: var(--light);
            color: var(--dark);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        header {
            text-align: center;
            padding: 4rem 1rem 2rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            margin-bottom: 3rem;
            border-radius: 0 0 30px 30px;
        }

        h1 {
            font-size: 2.8rem;
            margin: 0 0 1rem;
        }

        .about-text {
            max-width: 800px;
            margin: 0 auto;
            font-size: 1.15rem;
        }

        .gallery-section {
            margin: 3rem 0;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
            transition: all 0.35s ease;
            aspect-ratio: 4 / 3;
            cursor: pointer;
        }

        .gallery-item:hover {
            transform: scale(1.04);
            box-shadow: 0 15px 40px rgba(0,0,0,0.18);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.12);
        }

        .image-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.45);
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
            font-weight: 600;
            text-shadow: 0 2px 6px rgba(0,0,0,0.7);
        }

        .gallery-item:hover .image-overlay {
            opacity: 1;
        }

        .lightbox {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.95);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .lightbox img {
            max-width: 90%;
            max-height: 90vh;
            border-radius: 12px;
            box-shadow: 0 0 40px rgba(0,0,0,0.6);
        }

        .close-lightbox {
            position: absolute;
            top: 30px;
            right: 30px;
            color: white;
            font-size: 3rem;
            cursor: pointer;
            text-shadow: 0 2px 10px black;
        }

        @media (max-width: 768px) {
            h1 { font-size: 2.2rem; }
            .gallery-grid { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
        }
    </style>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>

<div class="container">

    <header>
        <h1>Assurnest Realty</h1>
        <p class="about-text">
            Welcome to Assurnest Realty – your trusted partner in real estate. 
            We specialize in premium residential and commercial properties across Maharashtra. 
            Explore our latest listings, find your dream home or investment opportunity, 
            and experience excellence in property services.
        </p>
    </header>

    <section class="gallery-section">
        <h2 style="text-align:center; margin-bottom:2rem; color:var(--dark);">
            Our Latest Properties
        </h2>

        <?php if (empty($properties)): ?>
            <p style="text-align:center; color:var(--gray); font-size:1.3rem;">
                No properties available at the moment.
            </p>
        <?php else: ?>
            <div class="gallery-grid">
                <?php foreach ($properties as $prop): ?>
                    <?php
                    $images = array_filter([
                        $prop['image1'],
                        $prop['image2'],
                        $prop['image3'],
                        $prop['image4']
                    ], function($img) {
                        return !empty($img) && filter_var($img, FILTER_VALIDATE_URL);
                    });
                    foreach ($images as $img): ?>
                        <div class="gallery-item" onclick="openLightbox('<?= htmlspecialchars($img) ?>')">
                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($prop['type'] ?? 'Property') ?>" loading="lazy">
                            <div class="image-overlay">
                                <?= htmlspecialchars($prop['type'] ?? 'Property') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

</div>

<!-- Lightbox -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <span class="close-lightbox" onclick="closeLightbox()">×</span>
    <img id="lightbox-img" src="" alt="Property Image">
</div>

<script>
function openLightbox(src) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox').style.display = 'flex';
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
}
</script>

</body>
</html>