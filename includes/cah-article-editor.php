<?php

require_once 'cah-article-field.php';
use CAH_ArticleMetaField as Field;

if( !class_exists( 'CAH_ArticleEditor' ) ) {
    class CAH_ArticleEditor
    {
    // Private members
        private static $meta_values;
        private static $settings = array(
            'sm' => array( 'textarea_rows' => 3 ),
            'md' => array( 'textarea_rows' => 6 ),
        );

        private static $post;


    // Public methods
        public static function author_image( $feat_imgs ) {
            $feat_imgs[] = array(
                'id' => 'author-image',
                'desc' => 'A picture of the author',
                'label_name' => 'Author Image',
                'label_set' => 'Set Author Image',
                'label_remove' => 'Remove Author Image',
                'label_use' => 'Set Author Image',
                'post_type' => array( 'article' ),
            );

            return $feat_imgs;
        }


        public static function save() {
            $post = self::_get_post();

            if( !is_object( $post ) ) return;

            $meta = self::_get_meta();

            $vol = get_post_meta( $post->ID, 'article-volume', true );
            $issue = get_post_meta( $post->ID, 'article-issue', true );
            $last = get_post_meta( $post->ID, 'author1-last', true );
            $first = get_post_meta( $post->ID, 'author1-first', true );

            if( empty( $meta ) ) {
                $meta = array(
                    'author1-last' => null,
                    'author1-first' => null,
                    'other-authors' => null,
                    'volume' => null,
                    'issue' => null,
                    'start' => null,
                    'end' => null,
                    'pur-url' => null,
                    'doi' => null,
                    'abstract' => null,
                    'auth-info' => null,
                    'auth-url' => null,
                    'rev-info' => array(),
                    'bibliography' => null,
                );
            }

            if( is_array( $meta['author1-last'] ) ) {
                $meta = self::_clean_meta( $meta );
            }

            $rev_info = array();
            if( isset( $_POST['auth-rev'] ) ) {
                foreach( $_POST['auth-rev'] as $i => $entry ) {
                    $arr = array(
                        'auth-rev' => $entry,
                        'title-rev' => $_POST['title-rev'][$i],
                        'url-rev' => $_POST['url-rev'][$i]
                    );

                    $rev_info[] = $arr;
                }
            }

            foreach( $meta as $key => $value ) {
                if( 'rev-info' == $key ) {
                    $meta[$key] = $rev_info;
                }
                else if( isset( $_POST[$key] ) ) {
                    $meta[$key] = $_POST[$key];
                }
            }

            // Less efficient to have these in separate meta fields, but this will allow
            // us to use these terms for queries in other places, which will ultimately
            // speed things up.
            if (isset( $_POST['volume'] ) && $_POST['volume'] != $vol ) {
                update_post_meta( $post->ID, 'article-volume', $_POST['volume'] );
                $meta['volume'] = $_POST['volume'];
            }
            if( isset( $_POST['issue'] ) && $_POST['issue'] != $issue ) {
                update_post_meta( $post->ID, 'article-issue', $_POST['issue'] );
                $meta['issue'] = $_POST['issue'];
            }

            // Inefficient, but we need to do this for now to avoid breaking some of the
            // legacy code, most notably the CAH AJAX Query plugin that drives the article
            // indices and archives on the Florida Review site.
            if( isset( $meta['author1-last'] ) && $last != $meta['author1-last'] ) {
                update_post_meta( $post->ID, 'author1-last', $meta['author1-last'] );
            }
            if( isset( $meta['author1-first'] ) && $first != $meta['author1-first'] ) {
                update_post_meta( $post->ID, 'author1-first', $meta['author1-first'] );
            }
            if( isset( $_POST['start'] ) ) {
                
                $start = $_POST['start'];

                if( !isset( $meta['start'] ) ) {
                    $meta['start'] = $start;
                }
                    
                update_post_meta( $post->ID, 'start', $_POST['start'] );
            }

            if( !isset( $meta['bibliography'] ) ) {
                $meta['bibliography'] = $_POST['bibliography'];
            }

            update_post_meta( $post->ID, 'article-meta', $meta );
        }



        public static function load_meta_boxes() {
            add_meta_box( 
                'article-info-meta', 
                'Basic Information', 
                array( __CLASS__, 'basic_info' ), 
                'article', 
                'normal', 
                'high' 
            );

            add_meta_box( 
                'article-abstract-meta', 
                'Abstract', 
                array( __CLASS__, 'abstract_info' ), 
                'article', 
                'normal', 
                'high' 
            );

            add_meta_box(
                'article-bibliography-meta',
                'Bibliography/Works Cited',
                array( __CLASS__, 'bibliography' ),
                'article',
                'normal',
                'high'
            );

            add_meta_box( 
                'article-author-meta', 
                'Author Information', 
                array( __CLASS__, 'author_info' ), 
                'article', 
                'normal', 
                'high' 
            );

            add_meta_box( 
                'article-review-meta', 
                'For Reviews Only', 
                array( __CLASS__, 'review_info' ), 
                'article', 
                'normal', 
                'high' 
            );
        }


        public static function basic_info() {
            $meta = self::_get_meta();
            $vol = array(
                'volume' => get_post_meta( self::_get_post()->ID, 'article-volume', true )
            );
            $issue = array(
                'issue' => get_post_meta( self::_get_post()->ID, 'article-issue', true )
            );

            $fields = array(
                new Field( $meta, 'author1-last', 'First Author Last Name' ),
                new Field( $meta, 'author1-first', 'First Author First Name', 'Include middle names/initials here.' ),
                new Field( $meta, 'other-authors', 'Additional Authors(s)', 'Separate each name by comma.' ),
                new Field( $vol, 'volume', 'Volume Number' ),
                new Field( $issue, 'issue', 'Issue Number' ),
                new Field( $meta, 'start', 'Start Page' ),
                new Field( $meta, 'end', 'End Page' ),
                new Field( $meta, 'pur-url', 'Purchase URL', '', 'url' ),
                new Field( $meta, 'doi', 'DOI' ),
            );

            ob_start();
            ?>
            <div class="inner-meta">
                <table>
                    <?php foreach( $fields as $field ) : ?>
                    <?= $field ?>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php
            echo ob_get_clean();
            unset( $fields );
        }


        public static function abstract_info() {
            $meta = self::_get_meta();

            wp_editor( isset( $meta['abstract'] ) ? $meta['abstract'] : '', 'abstract', self::$settings['md'] );
        }


        public static function bibliography() {
            $meta = self::_get_meta();

            wp_editor( isset( $meta['bibliography'] ) ? $meta['bibliography'] : '', 'bibliography', self::$settings['md'] );
        }


        public static function author_info() {
            $meta = self::_get_meta();

            wp_editor( isset( $meta['auth-info'] ) ? $meta['auth-info'] : '', 'auth-info', self::$settings['md'] );

            $fields = array(
                new Field( $meta, 'auth-url', 'Author URL', '', 'url' ),
            );

            ob_start();
            ?>
            <div class="inner-meta">
                <table>
                    <?php foreach( $fields as $field ) : ?>
                    <?= $field ?>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php
            echo ob_get_clean();
            unset( $fields );
        }


        public static function review_info() {
            $meta = self::_get_meta();

            $fields = array(
                new Field( $meta, 'auth-rev', 'Author of Reviewed Work' ),
                new Field( $meta, 'title-rev', 'Title of Reviewed Work' ),
                new Field( $meta, 'url-rev', 'URL for Reviewed Work', '', 'url' ),
            );

            ob_start();
            ?>
            <div class="inner-meta">
                <?php foreach( $meta['rev-info'] as $i => $info ) : ?>
                <div class="review-entry">
                    <button type="button" 
                        id="delete-rev-book-<?= $i ?>" 
                        class="button button-danger rev-delete"
                    >
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                    <table>
                        <?php foreach( $fields as $field ) : ?>
                        <tr>
                            <td>
                                <label for="<?= $field->get_name() . "-$i" ?>">
                                    <?= $field->get_label() ?>:
                                </label>
                            </td>
                            <td>
                                <input type="<?= $field->get_type() ?>"
                                    name="<?= $field->get_name() ?>[]"
                                    value="<?= isset( $info[ $field->get_name() ] ) 
                                        ? $info[ $field->get_name() ] 
                                        : '' ?>"
                                    id="<?= $field->get_name() . "-$i" ?>"
                                    size="50"
                                >
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endforeach; ?>
                <button type="button" id="add-rev-book" class="button button-default">Add Reviewed Work</button>
            </div>
            <?php
            echo ob_get_clean();
            unset( $fields );
        }


        public static function update_meta_schema( $post_id, $return = false ) {
            $meta_fields = array(
                'other-authors',
                'start',
                'end',
                'pur-url',
                'doi',
                'auth-url',
                'abstract',
                'auth-info',
                'bibliography',
            );

            $meta = self::_clean_meta( get_post_meta( $post_id ) );

            $new_meta = array();

            foreach( $meta_fields as $key ) {
                if( isset( $meta[$key] ) ) {
                    $new_meta[$key] = is_array( $meta[$key] ) ? $meta[$key][0] : $meta[$key];
                    delete_post_meta( $post_id, $key );
                }
            }

            $vol = null;
            $issue = null;
            if( isset( $meta['issue'] ) && !empty( $meta['issue'] ) ) {
                $matches = array();
                $patt = "/(\d+)\.(\d+)/";

                $iss = is_array( $meta['issue'] ) ? $meta['issue'][0] : $meta['issue'];

                preg_match( $patt, $iss, $matches );

                if( !empty( $matches ) ) {
                    $vol = $matches[1];
                    $issue = $matches[2];
                }
                else {
                    $issue = $iss;
                }
            }
            delete_post_meta( $post_id, 'issue' );

            // Inefficient, but we need to do this for now to avoid breaking some of the
            // legacy code, most notably the CAH AJAX Query plugin that drives the article
            // indices and archives on the Florida Review site.
            $first = null;
            $last = null;
            if( isset( $meta['author1-last'] ) ) {
                $last = is_array( $meta['author1-last'] ) ? $meta['author1-last'][0] : $meta['author1-last'];
                $new_meta['author1-last'] = $last;
            }
            if( isset( $meta['author1-first'] ) ) {
                $first = is_array( $meta['author1-first'] ) ? $meta['author1-first'][0] : $meta['author1-first'];
                $new_meta['author1-first'] = $first;
            }

            $new_meta['rev-info'] = array();

            if( isset( $meta['auth-rev'] ) && !empty( $meta['auth-rev'] ) ) {

                $authors = maybe_unserialize( $meta['auth-rev'][0] );
                $titles = maybe_unserialize( $meta['title-rev'][0] );
                $urls = maybe_unserialize( $meta['url-rev'][0] );

                if( !is_array( $authors ) ) {
                    $new_meta['rev-info'][] = array(
                        'auth-rev' => $authors,
                        'title-rev' => $titles,
                        'url-rev' => $urls
                    );
                }
                else {
                    foreach( $authors as $i => $author ) {
                        $new_meta['rev-info'][] = array(
                            'auth-rev' => $author,
                            'title-rev' => $titles[$i],
                            'url-rev' => $urls[$i]
                        );
                    }
                }

                delete_post_meta( $post_id, 'auth-rev' );
                delete_post_meta( $post_id, 'title-rev' );
                delete_post_meta( $post_id, 'url-rev' );
            }
            else if( isset( $meta['rev-info'] ) ) {
                $new_meta['rev-info'] = maybe_unserialize( $meta['rev-info'][0] );
                delete_post_meta( $post_id, 'rev-info' );
            }
            else {
                $new_meta['rev-info'][] = array(
                    'auth-rev' => '',
                    'title-rev' => '',
                    'url-rev' => ''
                );
            }

            update_post_meta( $post_id, 'article-meta', $new_meta );

            // Less efficient to have these in separate meta fields, but this will allow
            // us to use these terms for queries in other places, which will ultimately
            // speed things up.
            update_post_meta( $post_id, 'article-volume', $vol );
            update_post_meta( $post_id, 'article-issue', $issue );
            update_post_meta( $post_id, 'author1-last', $last );
            update_post_meta( $post_id, 'author1-first', $first );
            update_post_meta( $post_id, 'article-page-start', $new_meta['start'] );

            if( $return ) {
                return $new_meta;
            }
        }


    // Private methods
        private static function _get_post() {
            if( !isset( self::$post ) ) {
                global $post;
                self::$post = $post;
            }
            return self::$post;
        }


        private static function _get_meta() {
            $post = self::_get_post();

            if( !isset( self::$meta_values ) && get_post_meta( $post->ID, 'issue', true ) ) {
                self::update_meta_schema( $post->ID );
            }

            self::$meta_values = maybe_unserialize( get_post_meta( $post->ID , 'article-meta', true ) );

            if( !is_array( self::$meta_values ) ) {
                self::$meta_values = array();
            }

            if( isset( self::$meta_values['other-authors'] ) && is_array( self::$meta_values['other-authors'] ) ) {
                self::$meta_values = self::_clean_meta( self::$meta_values );
            }

            return self::$meta_values;
        }


        private static function _clean_meta( $meta ) {
            $meta_values = array(
                'author1-last',
                'author1-first',
                'other-authors',
                'issue',
                'start',
                'end',
                'pur-url',
                'doi',
                'abstract',
                'auth-info',
                'auth-url',
                'bibliography',
            );

            foreach( $meta_values as $key ) {
                if( isset( $meta[$key] ) && is_array( $meta[$key] ) ) {
                    $arr = maybe_unserialize( $meta[$key][0] );
                    if( is_array( $arr ) ) {
                        $meta[$key] = $arr[0];
                    }
                    else {
                        $meta[$key] = $arr;
                    }
                }
            }

            return $meta;
        }
    }
}
?>