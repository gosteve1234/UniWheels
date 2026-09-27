<?php
/**
 * UniWheels Theme Page Template
 *
 * @package UniWheels
 */

get_header();
?>

<main id="primary" class="site-main">
  <?php while ( have_posts() ) : the_post(); ?>
    
    <!-- Hero Banner -->
    <section class="page-hero-banner">
      <div class="container">
        <div class="page-pill-tag">
          <span style="color:#2ed573;">●</span> UniWheels Dealership Portal
        </div>
        <h1 class="page-hero-title"><?php the_title(); ?></h1>
        <div class="breadcrumbs-nav">
          <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
          <span>/</span>
          <span style="color: #fff; font-weight: 600;"><?php the_title(); ?></span>
        </div>
      </div>
    </section>

    <!-- Main Page Content Body -->
    <div class="container" style="max-width: 1080px;">
      <article id="post-<?php the_ID(); ?>" <?php post_class( 'luxury-page-card' ); ?>>
        <div class="entry-content">
          <?php the_content(); ?>
        </div>
      </article>
    </div>

  <?php endwhile; ?>
</main>

<?php
get_footer();
