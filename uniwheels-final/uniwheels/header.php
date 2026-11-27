<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniWheels Motors - Certified Pre-Owned Cars</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css?v=<?= @filemtime(__DIR__ . '/assets/css/style.css') ?: '1' ?>">
</head>
<body>

<header class="site-header">
  <div class="container">
    <div class="nav-wrapper">
      <a href="index.php" class="brand-link" title="UniWheels Motors - RIDE YOUR DREAMS">
        <img src="assets/images/logo.png" alt="UniWheels Motors Logo" class="brand-emblem">
        <div class="brand-title-group">
          <div class="brand-logo-text">
            <span class="brand-uni">UNI</span><span class="brand-wheels">WHEELS</span> <span class="brand-motors">MOTORS</span>
          </div>
          <span class="brand-subtitle">RIDE YOUR DREAMS</span>
        </div>
      </a>

      <nav class="site-nav" aria-label="Main Navigation">
        <?php $home_link = $home_link ?? ''; ?>
        <a href="<?= $home_link ?>#inventoryCatalogSection" class="nav-link">Our Cars</a>
        <a href="<?= $home_link ?>#tradeInSection" class="nav-link">Trade-In</a>
        <a href="<?= $home_link ?>#inquirySection" class="nav-link">Visit &amp; Contact</a>
        <a href="<?= $home_link ?>#about" class="nav-link">Our Guarantee</a>
      </nav>
    </div>
  </div>
</header>
