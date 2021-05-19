<?php
if( !class_exists( 'CAH_ArticleSetup' ) ) {
    class CAH_ArticleSetup
    {
    // Private members
        private static $pages = array(
            'edit.php',
            'post-new.php',
            'post.php',
        );

    // Public methods
        public static function setup() {
            add_action( 'init', array( __CLASS__, 'register_article' ), 10, 0 );
            add_action( 'add_meta_boxes', array( 'CAH_ArticleEditor' , 'load_meta_boxes' ), 10, 0 );
            add_action( 'save_post_article', array( 'CAH_ArticleEditor', 'save' ), 10, 0 );
            add_filter( 'kdmfi_featured_images', array( 'CAH_ArticleEditor', 'author_image' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'load_scripts' ), 10, 0 );
			add_filter( 'manage_article_posts_columns', array( __CLASS__, 'columns' ) );
			add_filter( 'manage_edit-article_sortable_columns', array( __CLASS__, 'sortable_columns' ) );
			add_action( 'manage_article_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
        }


        public static function register_article() {
            register_post_type( 'article', self::_args() );
        }


        public static function load_scripts() {
            global $pagenow;

            if( in_array( $pagenow, self::$pages ) ) {
                if( isset( $_GET['post_type'] ) && $_GET['post_type'] != 'article' ) return;

                else if( 'post.php' == $pagenow && isset( $_GET['action'] ) && 'edit' == $_GET['action'] ) {
                    global $post;

                    if( 'article' != $post->post_type ) return;
                }

                // Enqueue our scripts and styles...
                wp_enqueue_style( CAH_ARTICLE__PLUGIN_NAME . "_editor_style", CAH_ARTICLE__PLUGIN_DIR_URL . 'dist/css/article-2-style.css', CAH_ARTICLE__PLUGIN_VERSION, 'all' );
                wp_enqueue_script( CAH_ARTICLE__PLUGIN_NAME . "_editor_script", CAH_ARTICLE__PLUGIN_DIR_URL . 'dist/js/article-2.min.js', array( 'jquery' ), CAH_ARTICLE__PLUGIN_VERSION, true );
            }
		}
		

		public static function columns( $columns ) {
			//$columns['volume'] = 'Volume';
			//$columns['issue'] = 'Issue';

			$new_columns = array();

			foreach( $columns as $key => $value ) {
				$new_columns[$key] = $value;

				if( 'tags' == $key ) {
					$new_columns['volume'] = __( 'Volume', 'cah-article-2' );
					$new_columns['issue'] = __( 'Issue', 'cah-article-2' );
				}
			}

			return $new_columns;
		}


		public static function sortable_columns( $columns ) {
			$columns['volume'] = 'volume';

			return $columns;
		}


		public static function column_content( $column, $post_id ) {

			$meta = maybe_unserialize( get_post_meta( $post_id, 'article-meta', true ) );
			$vol = '';
			$issue = '';

			if( empty( $meta ) ) {
				$vol_iss = get_post_meta( $post_id, 'issue', true );
				$matches = array();

				preg_match( "/(\d+)\.(\d+)/", $vol_iss, $matches );

				if( !empty( $matches ) ) {
					$vol = $matches[1];
					$issue = $matches[2];
				}
			}
			else {
				$vol = $meta['volume'];
				$issue = $meta['issue'];

				if( empty( $vol ) || empty( $issue ) ) {
					$vol = get_post_meta( $post_id, 'article-volume', true );
					$issue = get_post_meta( $post_id, 'article-issue', true );
				}
			}

			switch( $column ) {
				case 'volume':
					if( !empty( $vol ) ) echo $vol;
					else echo "&ndash;";
					break;

				case 'issue':
					if( !empty( $issue ) ) echo $issue;
					else echo "&ndash;";
					break;
			}
		}

        
    // Private methods
        private static function _args() : array {
            $args = array(
				'label'                 => __( 'Article', 'cah-article' ),
				'description'           => __( 'A post type that contains extra meta information for authors, publication details, etc.', 'cah-article' ),
				'labels'                => self::_labels(),
				'supports'              => array( 
                                            'title',
                                            'editor',
                                            'excerpt',
                                            'thumbnail',
                                            'revisions',
                                            'custom-fields'
                ),
				'taxonomies'            => self::_taxonomies(),
				'hierarchical'          => false,
				'public'                => true,
				'show_ui'               => true,
				'show_in_menu'          => true,
				'menu_position'         => 5,
				'menu_icon'             => 'dashicons-media-document',
				'show_in_admin_bar'     => true,
				'show_in_nav_menus'     => true,
				'can_export'            => true,
				'has_archive'           => true,		
				'exclude_from_search'   => false,
				'publicly_queryable'    => true,
				'capability_type'       => 'post',
			);

			$args = apply_filters( 'cah_article_post_type_args', $args );

			return $args;
        }


        private static function _labels() : array {
            return array(
				'name'                  => _x( 'Articles', 'Post Type General Name', 'cah-article' ),
				'singular_name'         => _x( 'Article', 'Post Type Singular Name', 'cah-article' ),
				'menu_name'             => __( 'Articles', 'cah-article' ),
				'name_admin_bar'        => __( 'Article', 'cah-article' ),
				'archives'              => __( 'Article Archives', 'cah-article' ),
				'parent_item_colon'     => __( 'Parent Article:', 'cah-article' ),
				'all_items'             => __( 'All Articles', 'cah-article' ),
				'add_new_item'          => __( 'Add New Article', 'cah-article' ),
				'add_new'               => __( 'Add New', 'cah-article' ),
				'new_item'              => __( 'New Article', 'cah-article' ),
				'edit_item'             => __( 'Edit Article', 'cah-article' ),
				'update_item'           => __( 'Update Article', 'cah-article' ),
				'view_item'             => __( 'View Article', 'cah-article' ),
				'search_items'          => __( 'Search Articles', 'cah-article' ),
				'not_found'             => __( 'Not found', 'cah-article' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'cah-article' ),
				'featured_image'        => __( 'Featured Image', 'cah-article' ),
				'set_featured_image'    => __( 'Set featured image', 'cah-article' ),
				'remove_featured_image' => __( 'Remove featured image', 'cah-article' ),
				'use_featured_image'    => __( 'Use as featured image', 'cah-article' ),
				'insert_into_item'      => __( 'Insert into article', 'cah-article' ),
				'uploaded_to_this_item' => __( 'Uploaded to this article', 'cah-article' ),
				'items_list'            => __( 'Articles list', 'cah-article' ),
				'items_list_navigation' => __( 'Articles list navigation', 'cah-article' ),
				'filter_items_list'     => __( 'Filter article list', 'cah-article' ),
			);
        }


        private static function _taxonomies() {
            $retval = array(
                'category',
                'post_tag',
			);

			$retval = apply_filters( 'cah_article_taxonomies', $retval );

			foreach( $retval as $taxonomy ) {
				if ( !taxonomy_exists( $taxonomy ) ) {
					unset( $retval[$taxonomy] );
				}
			}

			return $retval;
        }
    }
}
?>