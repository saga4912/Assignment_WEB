<?php
namespace HtMegaBlocks;
use Exception;
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage Block CSS
 */
class Manage_Styles {

    /**
     * [$_instance]
     * @var null
     */
    private static $_instance = null;

    /**
     * [instance] Initializes a singleton instance
     * @return [Manage_Styles]
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if( ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) ){
			$this->manage_block_css();
		}
	}

	/**
	 * Resgister API routes
	 */
	public function register_routes( $namespace ){

		register_rest_route( $namespace, 'get_post',
			[
				[
					'methods'  => 'POST',
					'callback' => [ $this, 'get_post_data' ],
					'permission_callback' => [ $this, 'permission_check' ],
					'args' => []
				]
			]
		);

		register_rest_route( $namespace, 'save_css',
			[
				[
					'methods'  => 'POST', 
					'callback' => [ $this, 'save_block_css' ],
					'permission_callback' => [ $this, 'permission_check' ],
					'args' => []
				]
			]
		);

		register_rest_route( $namespace, 'appened_css',
			[
				[
					'methods'  => 'POST',
					'callback' => [ $this, 'appened_css' ],
					'permission_callback' => [ $this, 'permission_check' ],
					'args' => []
				]
			]
		);

	}

	/**
     * Api permission check
     */
    public function permission_check() {

		if( ! current_user_can( 'edit_posts' ) ){
            return false;
        }
        // Additional security check: Only allow users who can edit published posts or are administrators
        // This prevents contributors from accessing CSS operations on posts they don't own
        if ( ! current_user_can( 'edit_published_posts' ) && ! current_user_can( 'manage_options' ) ) {
            return false;
        }
		return true;
    }

	/**
	 * Get Post data From API request
	 */
	public function get_post_data( $request ) {
		$params = $request->get_params();
		if ( isset( $params['post_id'] ) ) {
			$post = get_post( $params['post_id'] );
			if ( ! $post ) {
				return [
					'success' => false,
					'message' => __('Post not found.', 'htmega-addons' )
				];
			}

			// Check if user has permission to access this post
			if ( ! current_user_can( 'manage_options' ) && get_current_user_id() !== (int) $post->post_author ) {
				return [
					'success' => false,
					'message' => __('You do not have permission to access this content.', 'htmega-addons' )
				];
			}

			return [
				'success' => true, 
				'data' 	  => $post->post_content, 
				'message' => __('Post Data found.', 'htmega-addons' )
			];
		} else {
			return [
				'success' => false, 
				'message' => __('Post Data not found.', 'htmega-addons' )
			];
		}
	}

	/**
	 * Save Block CSS
	 */
	public function save_block_css( $request ){
		try{
			$params 	= $request->get_params();
			$post_id 	= sanitize_text_field( $params['post_id'] );

			// For regular posts, check if user is admin or post author
			$post = get_post( $post_id );
			if ( ! $post || 
				( ! current_user_can( 'manage_options' ) && 
				get_current_user_id() !== (int) $post->post_author )
			) {
				throw new Exception( __('You do not have permission to manage CSS for this post.', 'htmega-addons' ) );
			}


			global $wp_filesystem;
			if ( ! $wp_filesystem ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

			$block_css = isset($params['block_css']) ? $this->sanitize_css_content($params['block_css']) : '';

			if ( $post_id == 'htmega-widget' && $params['has_block'] ) {
				update_option( $post_id, sanitize_text_field( $block_css ) );

				return [
					'success' => true, 
					'message' => __('Widget CSS Saved.', 'htmega-addons')
				];
			}
			
			$filename 		= sanitize_file_name("htmega-css-{$post_id}.css");
			$upload_dir_url = wp_upload_dir();
			$dirname 		= trailingslashit( $upload_dir_url['basedir'] ) . 'htmega-addons/';

			if ( $params['has_block'] ) {
				update_post_meta( $post_id, '_htmega_active', 'yes' );
				//$all_block_css = $params['block_css'];
				$all_block_css = $block_css;

				WP_Filesystem( false, $upload_dir_url['basedir'], true );
				if( ! $wp_filesystem->is_dir( $dirname ) ) {
					$wp_filesystem->mkdir( $dirname );
				}

				update_post_meta( $post_id, '_htmega_css', sanitize_text_field( $all_block_css ) );
				if ( ! $wp_filesystem->put_contents( $dirname . $filename, sanitize_text_field( $all_block_css ) ) ) {
					throw new Exception( __('You are not permitted to save CSS.', 'htmega-addons' ) ); 
				}
				return [
					'success' => true,
					'message' =>__('HtMega Blocks css file update.', 'htmega-addons' )
				];
			} else {
				delete_post_meta( $post_id, '_htmega_active' );
				$safe_filename = sanitize_file_name($filename);
			if ( file_exists( $dirname.$safe_filename ) ) {
					wp_delete_file( $dirname.$safe_filename );
				}
				delete_post_meta( $post_id, '_htmega_css' );
				return [
					'success' => true,
					'message' => __('HtMega Blocks CSS Delete.', 'htmega-addons' )
				];
			}
		} catch( Exception $e ){
			return [
				'success' => false,
				'message' => $e->getMessage()
			];
        }
	}


	/**
	 * Save Inner Block CSS
	 */
	public function appened_css( $request ) {
		try {
			$params  = $request->get_params();
			$post_id = (int) sanitize_text_field( $params['post_id'] );

			// For regular posts, check if user is admin or post author
			$post = get_post( $post_id );
			if ( ! $post || 
				( ! current_user_can( 'manage_options' ) && 
				get_current_user_id() !== (int) $post->post_author )
			) {
				throw new Exception( __('You do not have permission to manage CSS for this post.', 'htmega-addons' ) );
			}

			global $wp_filesystem;
			if ( ! $wp_filesystem ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

			$css = isset($params['inner_css']) ? $this->sanitize_css_content($params['inner_css']) : '';

			$filename = sanitize_file_name("htmega-css-{$post_id}.css");
			$dirname  = trailingslashit( wp_upload_dir()['basedir'] ).'htmega-addons/';
			
			WP_Filesystem( false, $upload_dir_url['basedir'], true );
			if( ! $wp_filesystem->is_dir( $dirname ) ) {
				$wp_filesystem->mkdir( $dirname );
			}
			
			update_post_meta( $post_id, '_htmega_css', sanitize_text_field( $css ) );
			update_post_meta( $post_id, '_htmega_active', 'yes' );
			
			if ( ! $wp_filesystem->put_contents( $dirname . $filename, sanitize_text_field( $css ) ) ) {
				throw new Exception( esc_html__('You are not permitted to save CSS.', 'htmega-addons' ) );
			}

			wp_send_json_success(
				[
					'success' => true, 
					'message' => __('Data fetch', 'htmega-addons' )
				]
			);

		} catch( Exception $e ){
			return [
				'success' => false,
				'message' => $e->getMessage()
			];
		}
	}

    /**
	 * Manage Block CSS
	 * @return void
	 */
	public function manage_block_css(){
		$css_adding_system = htmegaBlocks_get_option( 'css_add_via', 'htmega_gutenberg_tabs', 'internal' );
		if ( $css_adding_system === 'internal' ) {
			add_action( 'wp_head', [ $this, 'block_inline_css' ], 100 );
		} else {
			add_action( 'wp_enqueue_scripts', [ $this, 'block_css_file' ] );
		}
	}

    /**
     * Inline CSS Manage
     *
     * @return void
     */
    public function block_inline_css(){
		$this->generate_inline_css( htmegaBlocks_get_ID() );
    }

	/**
     * CSS File Manage
     *
     * @return void
     */
	public function block_css_file(){
		$this->enqueue_block_css( htmegaBlocks_get_ID() );
	}

	/**
	 * Generate Inline CSS
	 *
	 * @param [type] $post_id
	 * @return void
	 */
	public function generate_inline_css( $post_id ){
		if( $post_id ){
			$upload_dir_url 	= wp_get_upload_dir();
            $upload_css_dir_url = trailingslashit( $upload_dir_url['basedir'] );
			$css_file_path 		= $upload_css_dir_url."htmega-addons/htmega-css-{$post_id}.css";

			// Reusable Block CSS
			$reusable_block_css = '';
			$reusable_id = htmegaBlocks_reusable_id( $post_id );
			foreach ( $reusable_id as $id ) {
				$safe_reusable_filename = sanitize_file_name("htmega-css-{$id}.css");
				$reusable_dir_path = $upload_css_dir_url."htmega-addons/".$safe_reusable_filename;
				if (file_exists( $reusable_dir_path )) {
					$reusable_block_css .= htmega_get_local_file_data( $reusable_dir_path );
				}else{
					$reusable_block_css .= get_post_meta($id, '_htmega_css', true);
				}
			}

			if ( file_exists( $css_file_path ) ) {
				echo '<style type="text/css">'.htmega_get_local_file_data( $css_file_path ).$reusable_block_css.'</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				$css = get_post_meta( $post_id, '_htmega_css', true );
				if( $css ) {
					echo '<style type="text/css">'.$css.$reusable_block_css.'</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		}
	}

	/**
	 * enqueue block CSS
	 *
	 * @param [type] $post_id
	 * @return void
	 */
	public function enqueue_block_css( $post_id ){
		if( $post_id ){
			$upload_dir_url 	= wp_get_upload_dir();
            $upload_css_dir_url = trailingslashit( $upload_dir_url['basedir'] );
			$css_file_path 		= $upload_css_dir_url."htmega-addons/htmega-css-{$post_id}.css";

            $css_dir_url = trailingslashit( $upload_dir_url['baseurl'] );
            if ( is_ssl() ) {
                $css_dir_url = str_replace('http://', 'https://', $css_dir_url);
            }

            // Reusable Block CSS
			$reusable_id = htmegaBlocks_reusable_id( $post_id );
			foreach ( $reusable_id as $id ) {
				$safe_reusable_filename = sanitize_file_name("htmega-css-{$id}.css");
				$reusable_dir_path = $upload_css_dir_url."htmega-addons/".$safe_reusable_filename;
				if (file_exists( $reusable_dir_path )) {
                    $safe_css_filename = sanitize_file_name("htmega-css-{$id}.css");
                    $css_file_url = $css_dir_url . "htmega-addons/".$safe_css_filename;
				    wp_enqueue_style( "htmega-post-{$id}", $css_file_url, false, HTMEGA_VERSION, 'all' );
				}else{
					$css = get_post_meta( $id, '_htmega_css', true );
                    if( $css ) {
                        wp_enqueue_style( "htmega-post-{$id}", $css, false, HTMEGA_VERSION );
                    }
				}
            }

			if ( file_exists( $css_file_path ) ) {
				$safe_css_filename = sanitize_file_name("htmega-css-{$post_id}.css");
				$css_file_url = $css_dir_url . "htmega-addons/".$safe_css_filename;
				wp_enqueue_style( "htmega-post-{$post_id}", $css_file_url, false, HTMEGA_VERSION, 'all' );
			} else {
				$css = get_post_meta( $post_id, '_htmega_css', true );
				if( $css ) {
					wp_enqueue_style( "htmega-post-{$post_id}", $css, false, HTMEGA_VERSION );
				}
			}
		}
	}

	/**
	 * Sanitize CSS content to prevent XSS and dangerous patterns
	 */
	private function sanitize_css_content( $css ) {
		// Remove script tags
		$css = preg_replace( '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $css );
		// Remove dangerous CSS patterns
		$dangerous_patterns = [
			'/expression\s*\(/i',
			'/url\s*\(\s*[\'\"]?\s*javascript:/i',
			'/behavior\s*:/i',
			'/binding\s*:/i',
			'/@import\s+url\s*\(\s*[\'\"]?\s*javascript:/i',
			'/moz-binding\s*:/i',
			'/filter\s*:\s*progid\s*:/i',
			'/<iframe\b[^>]*>/i',
			'/<object\b[^>]*>/i',
			'/<embed\b[^>]*>/i'
		];
		foreach ( $dangerous_patterns as $pattern ) {
			$css = preg_replace( $pattern, '', $css );
		}
		// Strip HTML tags but preserve CSS
		$css = wp_strip_all_tags( $css );
		return $css;
	}

}