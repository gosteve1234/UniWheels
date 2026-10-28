<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniWheels Motors - Certified Pre-Owned & Trade-Ins</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    
    <!-- Google Fonts: Inter and Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Main Dealership Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Clean, Friendly Navigation Bar for Everyday Drivers & Families -->
<header class="site-header">
  <div class="container">
    <div class="nav-wrapper">
      
      <!-- Official Brand Logo -->
      <a href="index.php" class="brand-link" title="UniWheels Motors - RIDE YOUR DREAMS">
        <img src="assets/images/logo.png" alt="UniWheels Motors Logo" class="brand-emblem">
        <div class="brand-title-group">
          <div class="brand-logo-text">
            <span class="brand-uni">UNI</span><span class="brand-wheels">WHEELS</span> <span class="brand-motors">MOTORS</span>
          </div>
          <span class="brand-subtitle">RIDE YOUR DREAMS</span>
        </div>
      </a>

      <!-- Friendly, Highly Readable Navigation Links -->
      <nav class="site-nav" aria-label="Main Navigation">
        <?php $home_link = $home_link ?? ''; // set to index.php on pages other than the homepage ?>
        <a href="<?= $home_link ?>#inventoryCatalogSection" class="nav-link">Our Cars</a>
        <a href="<?= $home_link ?>#tradeInSection" class="nav-link">Trade-In Your Car</a>
        <a href="<?= $home_link ?>#inquirySection" class="nav-link">Visit & Contact</a>
        <a href="<?= $home_link ?>#about" class="nav-link">Our Guarantee</a>
      </nav>

    </div>
  </div>
</header>
