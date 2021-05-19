<?php
/** 
 * Template Name: Single Article for TFR
 * Description: Template for displaying individual articles. Requires the Article CPT Plugin, v2.0 or higher and the Multiple Featured Images plugin.
 * Author: Mike W. Leavitt
 */

get_header();

the_post();

$id = $post->ID;
$meta = maybe_unserialize( get_post_meta( $id, 'article-meta', true) );

if( empty( $meta ) ) {
    $plugin_path = parse_url( plugins_url() );
    require_once $plugin_path . 'common-article-2/includes/cah-article-editor.php';

    CAH_ArticleEditor::update_meta_schema( $id );

    $meta = maybe_unserialize( get_post_meta( $id, 'article-meta', true ) );
}

$other_auth_str = '';
if( !empty( $meta['other-authors'] ) ) {
    $other_arr = explode( ',', $other_authors );
    
    if( count( $other_arr ) > 1 ) {
        foreach( $other_arr as $i => $author ) {

            if( $i + 1 == count( $other_arr ) ) {
                $other_auth_str .= ', and';
            }
            else {
                $other_auth_str .= ', ';
            }

            $other_auth_str .= trim( $author );
        }
    }
    else {
        $other_auth_str .= " and {$other_arr[0]}";
    }
}

$authors = ( !empty( $meta['author1-first'] ) ? "{$meta['author1-first']} " : '' ) . $meta['author1-last'] . $other_auth_str;

$genres = array(
    'fiction',
    'nonfiction',
    'poetry',
    'graphic-narrative',
    'digital-stories',
    'interview',
    'book-review',
    'visual-art'
);

$categories = get_the_category( $id );

$pub_cat = '';
foreach( $categories as $cat ) {
    if( in_array( $cat->slug, $genres ) ) {
        $pub_cat = $cat->name;
    }
}
?>

<div id="primary" class="content-area border-top">
    <main id="main" class="site-main" role="main">
    <?php if( !empty( $pub_cat ) ) : ?>
        <p style="margin: 0; font-size: 14px;"><em>&raquo; <?= $pub_cat ?></em></p>
    <?php endif; ?>
        <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
    <?php
        if( in_category( 'florida-review' ) ) {
            $vol = $meta['volume'];
            $issue = str_replace(' &amp; ', ' & ', $meta['issue']);

            $args = array(
                'post_type' => 'issue',
                'post_status' => 'publish',
                'posts_per-page' => 1,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'issue-volume',
                        'value' => $vol,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'issue-issue',
                        'value' => $issue,
                        'compare' => '='
                    )
                ),
                'fields' => 'ids'
            );

            $query = new WP_Query( $args );

            $a_begin = '';
            $a_end = '';

            if( $query->have_posts() ) {
                $issue_link = get_the_permalink( $query->posts[0] );
                $a_begin = "<a href=\"$issue_link\">";
                $a_end = "</a>";
            }
        }

        the_content();
    ?>

    <?php if( in_category( 'florida-review' ) ) : ?>
        <br />
        <p style="margin: -20px 0 20px 0;">This work originally appeared in <em>The Florida Review</em>, <?= $a_begin ?>Vol. <?= $vol ?>.<?= $issue ?><?= $a_end ?>.</p>
    <?php endif; ?>

    <?php if( !empty( $authors ) ) : ?>
        <div class="author">

        <?php if( kdmfi_has_featured_image( 'author-image', $id ) ) : ?>
            <div class="author-image">
                <div style="background-image: url(<?= kdmfi_get_featured_image_src( 'author-image', 'large', $id ) ?>);"></div>
            </div>
        <?php endif; ?>
            
            <div class="author-info">
                <h3><?= $authors ?></h3>

            <?php if( !empty( $meta['auth-url'] ) ) : ?>
                <a href="<?= $meta['auth-url'] ?>"><?= preg_replace( "/^https?:\/\//", '', $meta['auth-url'] ) ?></a>
            <?php endif; ?>
                <?= wpautop( $meta['auth-info'], true ) ?>
            </div>
        </div>
    <?php endif; ?>

    </main> <!-- /#main -->
</div> <!-- /#primary -->

<?php

get_footer();