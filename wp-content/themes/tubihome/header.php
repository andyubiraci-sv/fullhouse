<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header id="masthead" class="site-header">
    <div class="container header-container">
        
        <div class="site-branding">
            <?php
            if ( has_custom_logo() ) {
                the_custom_logo();
            } else {
                echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="site-title">' . get_bloginfo( 'name' ) . '</a>';
            }
            ?>
        </div>

        <nav id="site-navigation" class="main-navigation">
            <?php
            wp_nav_menu( array(
                'theme_location' => 'menu-principal', // Asegúrate de registrar este nombre en functions.php
                'menu_id'        => 'menu-principal',
                'container'      => false,
                'fallback_cb'    => false,
            ) );
            ?>
        </nav>

        <div class="mobile-menu-toggle">
            <button id="menu-opener" aria-label="Abrir menú">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>

    </div>
</header>

<div id="content" class="site-content">