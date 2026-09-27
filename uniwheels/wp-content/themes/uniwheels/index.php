<?php
/**
 * The main template file
 *
 * @package UniWheels
 */

get_header();
?>

<main>
    <div class="container" style="padding: 5rem 1.5rem;">
        <?php
        if ( have_posts() ) :
            while ( have_posts() ) : the_post();
                the_content();
            endwhile;
        else :
            // Fallback to front-page content if no custom posts
            get_template_part( 'front-page' );
        endif;
        ?>
    </div>
</main>

<?php
get_footer();
