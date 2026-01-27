<?php get_header(); ?>
<main>
    <h1><?php single_term_title(); ?></h1>
    <?php if (have_posts()) : ?>
        <div class="inmuebles-listado">
            <?php while (have_posts()) : the_post(); ?>
                <article <?php post_class('inmueble'); ?>>
                    <a class="inmueble__link" href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) {
                            the_post_thumbnail('inmueble-thumb', ['class' => 'inmueble__img']);
                        } ?>
                        <h2 class="inmueble__title"><?php the_title(); ?></h2>
                        <div class="inmueble__meta inmueble__precio">
                            <?php echo get_post_meta(get_the_ID(), '_precio', true); ?> MXN
                        </div>
                        <div class="inmueble__meta">
                            <?php echo get_post_meta(get_the_ID(), '_metros', true); ?> m²
                        </div>
                        <div class="inmueble__meta">
                            <?php echo get_post_meta(get_the_ID(), '_recamaras', true); ?> recámaras, <?php echo get_post_meta(get_the_ID(), '_banos', true); ?> baños
                        </div>
                    </a>
                </article>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(); ?>
    <?php else : ?>
        <p>No hay inmuebles en esta categoría.</p>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
