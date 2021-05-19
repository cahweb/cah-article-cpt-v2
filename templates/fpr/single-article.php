<?php
/**
 * Template Name: Single Article for FPR
 * Description: Template for displaying individual articles. Requires the Article CPT Plugin, v2.0 or higher.
 * Author: Mike W. Leavitt
 */

get_header();

the_post();

$meta = maybe_unserialize( get_post_meta( $post->ID, 'article-meta', true ) );

if( empty( $meta ) ) {

    $plugins_path = ABSPATH . "wp-content/plugins/";

    require_once $plugins_path . 'common-article-2/includes/cah-article-editor.php';

    CAH_ArticleEditor::update_meta_schema( $post->ID );

    $meta = maybe_unserialize( get_post_meta( $post->ID , 'article-meta', true ) );
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

?>
<div class="container byline">
    <p><em>By <?= $authors ?></em></p>
</div>
<div class="container mb-5 mt-3 mt-lg-5" style="min-height: 250px;">
    <article class="<?= $post->post_status ?> post-list-item mb-5">
    <?php
        if( has_post_thumbnail() ) {
            the_post_thumbnail( 'medium', array( 'class' => 'rounded float-left mr-3' ) );
        }
    ?>
        <p class="mb-4">
            <button type="button" class="btn btn-default btn-sm article-pdf">
                <?= do_shortcode( '[pdf_attachment file="1" name="Download Article PDF"]' ); ?>
            </button>
        </p>

    <?php
        the_content();
    ?>
    </article>
<?php if( !empty( $authors ) ) : ?>
    <div class="author">
        <div class="author-info">
            <h3><?= $authors ?></h3>

        <?php if( !empty( $meta['auth-url'] ) ) : ?>
            <a href="<?= $meta['auth-url'] ?>"><?= preg_replace("/^https?:\/\//", '', $meta['auth-url'] ) ?></a>
        <?php endif; ?>
            <?= wpautop( $meta['auth-info'], true ); ?>
        </div> <!-- /.author-info -->
    </div> <!-- /.author -->
<?php endif; ?>
</div> <!-- /.container -->

<script>
    document.body.querySelector('div.container > div.mb-4.mb-md-5').remove();
</script>

<?php
get_footer();
?>