<?php 	/*
	Plugin Name: Coming Soon Page / Splashpage Max
	Plugin URI: https://newburynew.media/wordpress-plugin-coming-soon-page/
	Description: Your very own Coming soon page...
	Version: 0.5
	Requires at least: 4.9.4
	Requires PHP: 7.0
	Author: Gordon Abbotts
	Author URI: http://newburynew.media
	Text Domain: nnmcs
	Domain Path: /languages
	License: GPL2v2
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*
|	Admin Setup
*/
class NNM_Coming_Soon_Page {

	function __construct() {
		define('NNMPATH', plugin_dir_path( __FILE__ ) );
		if( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'coming_soon_admin_enqueues' ) );
			add_action( 'admin_menu', array( $this, 'add_coming_soon_menu' ) );
			add_action( 'admin_init', array( $this,'coming_soon_options_init' ) );	
			add_action( 'plugins_loaded', array( $this,'nnmcs_load_textdomain' ) );		
		} else {
			add_filter( 'template_include', array( $this, 'coming_soon_redirect' ), 500 );
		}
	}

	function coming_soon_redirect($t){
		if( $this->coming_soon_active() && $this->coming_soon_user() ) :
			add_action( 'wp_enqueue_scripts', array($this,'coming_soon_stylesheet'), 5);
			$t = NNMPATH . '/coming-soon-page-template.php';
		endif;
		return $t;
	}

	private function nnmcs_options_setup(){
		$nnm_opts = array(
			'nnm-coming-soon-settings' => array(
				'active'	=> array(
					'name'	=> __( 'Coming Soon Page Active', 'nnmcs' ),
					'func'	=> array( $this,'chk_options' ),
				),
				'main_content'	=> array(
					'name'	=> __( 'Choose Content Page', 'nnmcs' ),  
					'func'	=> array( $this, 'admin_page_dropdown_options' ),
					'size'	=> 120,
				),
				'enable_social'	=> array(
					'name'	=> __( 'Enable Social Icons', 'nnmcs' ),
					'func'	=> array( $this,'chk_options' ),
				),				
			),
			'nnm-coming-soon-colour-settings' => array(
				'background_colour' => array(
					'name'	=> __( 'Pick a Background colour', 'nnmcs' ),
				),
				'text_colour'		=> array(
					'name'	=>  __( 'Pick a Text colour', 'nnmcs' ),
				),

				'popup_colour'		=> array(
					'name'	=>  __( 'Pick a Box colour', 'nnmcs' ),
				),
				'box_opacity' 		=> array(
					'name'	=> __( 'Box opacity:', 'nnmcs' ),
					'func'	=> array( $this, 'number_to_one' ),
				),
			),
			'nnm-coming-soon-layout-settings' => array(
				'bordersh' 			=> array(
					'name'	=>   __( 'Do you border shadow?' , 'nnmcs' ) . '(<em>auto colour</em>)',
					'func'	=> array( $this, 'chk_options' ),
				),		
				'launch' 			=> array(
					'name'	=>   __( 'Count Down to' , 'nnmcs' ) . '<p class="small">Leave blank for no counter</p>',
					'func'	=> array( $this,'date_chooser' ),
				),							
				'featured_logo'		=> array(
					'name'	=>   __( 'Choose the logo', 'nnmcs' ),
					'opts'	=> array(
						'none' 		=> __( 'None', 'nnmcs' ),
						'image'		=> __( 'Selected page image', 'nnmcs' ),
						'logo'		=> __( 'Site Logo', 'nnmcs' ),	
					),						
				),					
				'page_title'		=> array(
					'name'	=>   __( 'Choose the title', 'nnmcs' ),
					'opts'	=> array(
						'none'	=> __( 'None', 'nnmcs' ),
						'site'	=> __( 'Site title', 'nnmcs' ),
						'page'	=> __( 'Selected page title', 'nnmcs' ),
					),					
				),					
				'style_choice' 		=> array(
					'name'	=>   __( 'Choose the content', 'nnmcs' ),
					'opts'	=> array(			
						'coming-soon' 			=> __( 'None', 'nnmcs' ),
						'coming-soon-content'	=> __( 'Selected page content', 'nnmcs' ),
					),					
				),					
				'background_style' 	=> array(
					'name'	=>   __( 'Choose the background', 'nnmcs' ),
					'opts' 	=> array(
						'none' 		=> __( 'None', 'nnmcs' ),
						'image'		=> __( 'Selected page image', 'nnmcs' ),
						'logo'		=> __( 'Site logo', 'nnmcs' ),
					),					
				),					
				'text_alignment'	=> array(
					'name'	=>   __( 'Text alignment', 'nnmcs' ),
					'opts'	=> array(
						'left'		=> __( 'Left', 'nnmcs' ),
						'center'	=> __( 'Centre', 'nnmcs' ),
						'right'		=> __( 'Right', 'nnmcs' ),
					),					
				),
				'custom_css'		=> array(
					'name'	=> __( 'Custom CSS', 'nnmcs' ) . '<ul class="label-list"><li>Classes</li><li>.nnmcs-coming-soon-page</li><li>.nnmcs-frame</li><li>.nnmcs-title</li><li>.nnmcs-content-title</li><li>.nnmcs-logo</li><li>.nnmcs-social-wrap</li></ul>',
					'func'	=> array( $this,'style_textarea' ),
				),				
			),
			'nnm-coming-soon-social-settings' => array(
				'social_facebook' 	=> array(
					'name'	=> __( 'Enter your Facebook url', 'nnmcs' ),
				),	
				'social_google'	=> array(
					'name'	=> __( 'Enter your Google+ url', 'nnmcs' ),
				),	
				'social_twitter'	=> array(
					'name'	=> __( 'Enter your Twitter url', 'nnmcs' ),
				),	
				'social_linkedin'	=> array(
					'name'	=> __( 'Enter your LinkedIn url', 'nnmcs' ),
				),
			),	
		);
		return $nnm_opts;
	}

	function coming_soon_active(){
		$active = $this->get_v( 'active' );
		if( 1 == $active ) {
			return true;
		} else {
			return false;
		}
	}

	function coming_soon_user() {
		$result = true;
		if( is_user_logged_in() ) { 
			$result = false;
		}
		return $result;
	}

	function coming_soon_admin_enqueues($hook) {
		if( is_admin() && $hook == 'settings_page_nnmcs-coming-soon' ) {
			
			wp_enqueue_style( 'wp-color-picker' ); 
			//wp_enqueue_style( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'nnmcs-admin-style', plugins_url( 'css/admin.css', __FILE__ ), false, '1.0.0' );
			
			$settings = wp_enqueue_code_editor( array( 'type' => 'text/css' ) );
			wp_add_inline_script(
			    'code-editor',
			    sprintf(
			        'jQuery( function() { wp.codeEditor.initialize( "custom_css", %s ); } );',
			        wp_json_encode( $settings )
			    )
			);	
			wp_enqueue_script( 'nnmcs-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery', 'wp-color-picker', 'jquery-ui-core', 'jquery-ui-datepicker' ), false ); 
		}
	}

	function add_coming_soon_menu() {
		add_options_page( 'NNM Coming Soon', 'Coming Soon Setup', 'manage_options', 'nnmcs-coming-soon', array( $this,'coming_soon_options' ) );
	}

	function coming_soon_options() { 
		?>
		<div class="wrap">		
			<div class="icon32" id="icon-options-general"><br></div>
			<h2>NNM Coming Soon Page</h2>
			<form action="options.php" method="post">
				<?php 
				settings_fields( 'nnm_coming_soon' );
				do_settings_sections( 'nnm-coming-soon-settings' );
				do_settings_sections( 'nnm-coming-soon-colour-settings' );
				?>
				<div id="social-section">
					<?php do_settings_sections( 'nnm-coming-soon-social-settings' ); ?>
				</div>
				<?php do_settings_sections( 'nnm-coming-soon-layout-settings' ); ?>
				<p class="submit">
					<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes','nnmcs'); ?>" />
				</p>
			</form>
			<p class="footer-info">Coming Soon Page by <a href="https://newburynew.media" rel="author noreferrer noopener" target="_blank">NewburyNewMedia</a></p>
		</div>
	<?php 
	}	
	

	function basic_options_group(){
		?>
		<div id="nnmcs-create-page" class="nnmcs-intro">
			<p><?php _e( 'Create a page and add content, including a featured image.', 'nnmcs' ); ?> 
				<a href="<?php echo admin_url('post-new.php?post_type=page'); ?>" target="_blank" class="button-primary"><?php _e( 'Create New Page' , 'nnmcs' ); ?></a>
			</p>
			<p style="clear:both;"></p>
		</div>
		<p><?php _e( 'Use the layout options to choose how you want the content and imagery to be displayed.', 'nnmcs' ); ?></p>
		<?php 
	}	

	function coming_soon_options_init(){
		register_setting( 'nnm_coming_soon', 'nnmcs_opts', array( 'sanitize_callback' => array( $this, 'nnmcs_opts_validation') ) );
		$nnmcs_settings = $this->nnmcs_options_setup();
		foreach( $nnmcs_settings as $section => $fields ) {
			$func = null;
			$subfunc = array( $this, 'text_input' );
			$section_id = str_replace( '-', '_', $section );
			if( 'nnm-coming-soon-settings'  == $section ) {
				$n = __( 'Coming Soon Page Setup', 'nnmcs' );
				$func = array( $this, 'basic_options_group' );
			} elseif( 'nnm-coming-soon-layout-settings'  == $section ){
				$n = __( 'Layout', 'nnmcs' );
				$subfunc = array( $this, 'all_my_selects' );
			} elseif( 'nnm-coming-soon-colour-settings'  == $section ){
				$n = __( 'Colours', 'nnmcs' );
				$subfunc = array( $this, 'colour_chooser' );
			} else {
				$n = __( 'Social', 'nnmcs' );
			}

			add_settings_section( $section_id, $n, $func,  $section );

			foreach ( $fields as $id => $data ) {
				if( !isset( $data['func'] ) ) {
					$f = $subfunc;
				} else {
					$f = $data['func'];
				}
				if( strpos($id, 'social_') !== false ) {
					$data['size'] = 80;
				}	
				$s = array(
					'name' => 'nnmcs_opts[' . $id . ']',
					'id' => $id, 					
				);
				$o = array('size','opts');
				for ($i=0; $i < count($o); $i++) { 
					if( isset( $data[ $o[ $i ] ] ) ) {
						$s[ $o[ $i ] ] = $data[ $o[ $i ] ];
					}		
				}
				add_settings_field( $id, $data['name'], $f, $section, $section_id, $s );
			}
		}
	}

	function nnmcs_opts_validation( $elem ){
		$plugin_opts = $this->nnmcs_options_setup();
		foreach ( $plugin_opts as $section => $fields) {
			foreach ($fields as $id => $data) {
				if( isset( $elem[ $id ] ) ) {
					if( strpos( $id, '_colour') !== false ) {
						if( strpos( $elem[ $id ], '#') !== false ) {
							$elem[ $id ] = sanitize_text_field(substr( $elem[ $id ], 0,7) );
						} else {
							unset( $elem[ $id ] );
						}
					} elseif ('custom_css' == $id ) {
						 $elem[ $id ] = wp_strip_all_tags( $elem[ $id ] );
					} elseif('box_opacity' == $id) {
						 $elem[ $id ] = floatval( $elem[ $id ] );
					} elseif( 'active' == $id || 'enable_social' == $id || 'main_content' == $id || 'bordersh' == $id ) {
						 $elem[ $id ] = intval( $elem[ $id ] );
					} elseif( strpos( $id, 'social_') !== false ) {
						if( substr( $elem[ $id ], 0, 3 ) == 'http' ) {
							$elem[ $id ] = sanitize_text_field( $elem[ $id ] );
						} else {
							unset( $elem[ $id ] );
						}
					} else {
						 $elem[$id] = sanitize_text_field( $elem[ $id ] );
					}			
				}
			}
		}
		return $elem;
	}

	function all_my_selects($args){
		$data = $args['opts'];
		$v = $this->get_v( $args['id'] );
		if( isset( $data ) ) {  
			?>
			<select id="<?php echo $args['id']; ?>" name="<?php echo $args['name']; ?>" class="nnmcs-select" width="50">
				<?php 
				foreach( $data as $key => $val) { 
					?>
					<option value="<?php echo $key; ?>" <?php if( $v == $key ) { echo 'selected'; } ?>><?php echo $val; ?></option>
					<?php 
				} 
				?>
			</select>
			<?php					
		} else {
			echo '<p>' . __( 'No arguments available', 'nnmcs' ) . '</p>';
		}
	}

	function date_chooser($args){
		$v = $this->get_v( $args['id'] );
		echo '<input type="datetime-local" id="' . $args['id'] . '" name="' . $args['name'] . '" class="choose-a-date" value="'. $v .'" />';
	}

	function colour_chooser($args){
		$v = $this->get_v( $args['id'] );
		echo '<input type="text" id="' . $args['id'] . '" name="' . $args['name'] . '" class="choose-a-colour" value="'. $v .'" />';
	}

	function chk_options($args){
		$value = $this->get_v( $args['id'] );	
		if(1 == $value) { 
			$value='checked'; 
		}
		echo '<input type="checkbox" id="' . $args['id'] . '" name="' . $args['name'] . '" value="1" '.$value.' />';
	}
	function number_to_one($args) {
		$v = $this->get_v( $args['id'] );
		echo '<input type="number" id="' . $args['id'] . '" name="' . $args['name'] . '" min="0" max="1" step="0.1" value="'. $v .'" />';	
	}
	
	function text_input($args){
		$v = $this->get_v( $args['id'] );	
		echo '<input type="text" id="' . $args['id'] . '" name="' . $args['name'] . '" size="'. $args['size'] .'" value="'. $v .'" />';
	}

	function admin_page_dropdown_options($args){
		$page_args = array(
			'status'			=> 'private, publish',
			'posts_per_page'	=> -1,
		);
		$pages = get_pages($page_args); 
		if( $pages ) { 		
			$value = $this->get_v( $args['id'] );
			?>
			<select id="<?php echo $args['id']; ?>" class="page-selector" name="<?php echo $args['name']; ?>">
				<option value=""> <?php esc_html_e( 'No Coming Soon Page', 'nnmcs' ); ?></option>
				<?php 
				foreach($pages as $p) { 
					$s = '';
					if($p->ID == $value) { 
						$s = 'selected'; 
						$pagelink = '<a href="' . admin_url('post.php?post=' . $p->ID . '&action=edit') . '" class="button-secondary" target="_blank" rel="noopener noreferrer">Edit Page</a>';
					} 
					echo '<option value="' .  $p->ID . '" ' . $s . '>' . $p->post_title . '</option>';
				} 
				?>
			</select>
			<?php 
			if( isset( $pagelink ) ) { 
				echo $pagelink; 
			}
		} else { 
		?>
			<p><em> <?php _e( 'no pages! :(' ,'nnmcs' ); ?></em></p>
		<?php
		}
	}

	function style_textarea($args){
		$v = $this->get_v( $args['id'] );
		$a = array(
			'wpautop'			=> false,
			'media_buttons'		=> false,
			'textarea_name' 	=> $args['name'],
			'tinymce'			=> false,
			'quicktags'			=> false,
		);
		
		wp_editor( esc_html($v), $args['id'], $a );
	}
	
	function nnmcs_load_textdomain() {
		load_plugin_textdomain('nnmcs', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	function get_v($v){
		$opts = get_option('nnmcs_opts');
		if( isset( $opts[ $v ] ) ) {
			return esc_attr( $opts[ $v ] );
		} else {
			return '';
		}
	}

	function coming_soon_stylesheet() { 
		$opts = get_option('nnmcs_opts');
		
		$style = array(
			'social'		=> 'enable_social',
			'bg' 			=> 'background_colour',
			'frame-border' 	=> 'bordersh',
			'txt'			=> 'text_colour',
			'frame'			=> 'popup_colour',
			'bg-img'		=> 'background_style',
			'custom'		=> 'custom_css',
			'launch'		=> 'launch',
		);
		$css = array();
		foreach($style as $k => $v) {
			$val = $this->get_v( $v );
			if( !empty( $val ) ){
				switch( $k ){
					case 'social' :
						wp_enqueue_style( 'fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css', false, '1.0.0' );
						$css[ $k ] = '.nnmcs-social-wrap { border-top: 1px solid ' . $this->get_v( 'text_colour' ) . '; }';
						break;
					case 'bg' :
						$css[ $k ] = '.nnmcs-coming-soon-page { background:' . $val . '; }';
						break; 											
					case 'frame-border' : 
						if( array_key_exists( 'bg', $css ) ) {
							$css[ $k ] = '.nnmcs-border-shadow { box-shadow: 0 0 48px '. $this->colour_inverse( $this->get_v( 'background_colour' ) ) .'; }' ;
						} else {
							$css[ $k ] = '.nnmcs-border-shadow { box-shadow: 0 0 48px '. $this->get_v( 'text_colour' ) .'; }' ;
						}
						break;
					case 'txt' :
						$css[ $k ] = '.nnmcs-coming-soon-page .nnmcs-content, .nnmcs-social, .nnmcs-title { color:' . $val . '; }';
						$css[ $k . '-hover' ] = '.nnmcs-social:hover { color:' . $this->colour_inverse( $val ) . '; }';
						break;
					case 'frame' :
						if( !empty( $this->get_v( 'box_opacity' ) ) ) {
							$css[ $k ] = '.nnmcs-frame-cover { display:block; background:' . $val . '; opacity: '. $this->get_v( 'box_opacity' ) .'; }';
						} else {
							$css[ $k ] = '.nnmcs-frame { background:' . $val . '; }';
						}						
						break;
					case 'bg-img' :
							if('image' == $val && !empty( $this->get_v( 'main_content' ) ) && has_post_thumbnail( $this->get_v( 'main_content' ) ) ) {
								$thumbnail = get_the_post_thumbnail_url( $this->get_v( 'main_content' ), 'full-size' );
								$end = ' center top no-repeat; background-size: cover';
							} elseif( 'logo' == $val && has_header_image() ) {
								$thumbnail = get_header_image();
								$end = 'center center no-repeat';
							}
							if( isset( $thumbnail ) ) {
								if( array_key_exists( 'bg', $css ) ){
									$css['bg'] = '.nnmcs-coming-soon-page { background:' . $this->get_v( 'background_colour' ) . ' url(' . $thumbnail . ') '. $end . ';  }' ;
								} else {
									$css['bg'] = '.nnmcs-coming-soon-page { background: url('. $thumbnail .') '. $end . '; }' ;
								}
							}
						break;
					default :
						$css[ $k ] = $val;
				}
			}
		}
		
		wp_enqueue_style( 'coming-soon-default', plugins_url( 'css/basic.css', __FILE__ ) );

		if( !empty($css) ) {
			wp_add_inline_style( 'coming-soon-default', implode( "\n", $css ) );
		}	
		wp_enqueue_script( 'nnmcs-scripts', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ), '1.0.3' ); 	
	}

	function colour_inverse( $colour ){
	    $colour = str_replace( '#', '', $colour );
	    if ( strlen($colour) != 6 ){ 
	    	$clr='';
	    	for ( $i=0; $i < 3; $i++ ) {
	    		$clr .= substr( $colour, $i, $i+1 ) . substr( $colour, $i, $i+1 );
	    	}
	    	$colour = $clr;
	    }
	    $rgb = '';
	    for ( $x=0; $x<3; $x++ ){
	        $c = 255 - hexdec(substr($colour,(2*$x),2));
	        $c = ($c < 0) ? 0 : dechex($c);
	        $rgb .= (strlen($c) < 2) ? '0'.$c : $c;
	    }
	    return '#'.$rgb;
	}	
}

$comingsoon = new NNM_Coming_Soon_Page;

/*
|	Layout 
*/
class NNM_Coming_Soon_Layout {
	function __construct(){
		
	}

	function get_content_layout_template(){
		$opts = array( 
			'bordersh',
			'enable_social',
			'style_choice',
			'page_title',
			'featured_logo',
			'text_alignment',
			'launch',
		);
		$values = array();
		for ($i=0; $i < count($opts) ; $i++) { 
			$values[ $opts[ $i ] ] = $this->get_v( $opts[ $i ] );
		}
		$classes = array(
			'nnmcs-' . $values['style_choice'],
			'nnmcs-text-' . $values['text_alignment'],
		);
		if( 1 == $values['bordersh'] ) {
			$classes[] = 'nnmcs-border-shadow';
		}
		if( 1 == $values['enable_social'] ) {
			$classes[] = 'nnmcs-social-included';
		}
		?>

		<div id="nnmcs-frame" class="nnmcs-frame <?php echo implode( " ", $classes ); ?>">	
			<div id="nnmcs-content" class="nnmcs-content">
				<?php 
					if ( 'none' != $values['featured_logo'] ) { 
						$this->logo_src();
					}
					if ( 'none' != $values['page_title'] ) { 
						$this->the_title(); 
					}
					if ( 'coming-soon-content' == $values['style_choice'] ) { 
						$this->get_content();
					}	

					if ( !empty( $values['launch'] ) ) { 						
						$this->get_countdown();
					}	

					if ( 1 == $values['enable_social'] ) { 
						$this->social_links();
					}	
				?>
			</div>
			<div class="nnmcs-frame-cover"></div>
		</div>

		<?php
	}

	function the_title(){
		$v = $this->get_v( 'page_title' );
		$mc = $this->get_v( 'main_content' ); 
		if( 'site' == $v ) {
			echo '<h1 class="nnmcs-title">' . get_bloginfo('name') . '</h1>';	
		} elseif( 'page' == $v && !empty( $mc ) ) {
			$page = get_post( $mc );
			echo '<h1 class="nnmcs-title"> ' . $page->post_title . ' </h1>';
		}
	}

	function logo_src() {
		$v = $this->get_v( 'featured_logo' );
		$mc = $this->get_v( 'main_content' ); 
		echo $v;
		if( 'logo' === $v && has_header_image() ) {
			$logo = get_header_image();
		} elseif( 'image' === $v && !empty( $mc ) ) {
			$logo = get_the_post_thumbnail_url( $mc, 'full-size' );
		}

		if( isset($logo) ) {
			?>
			<div class="nnmcs-logo">
				<img src="<?php echo $logo; ?>" alt="<?php bloginfo('title'); ?>" />
			</div>
			<?php
		} 		
	}

	function get_content(){
		$v = $this->get_v( 'main_content' );
		$p = $this->get_v( 'page_title' );
		if( !empty( $v ) ) {
			$post = get_post( $v );
			if( $post->post_title != '' && 'page' != $p ) { 
				?>
					<h2 class="nnmcs-content-title"><?php echo $post->post_title; ?></h2>
				<?php 
			}

			if( $post->post_content != '' ) { 
				?>
					<div class="entry"><?php echo do_shortcode( wpautop( $post->post_content ) ); ?></div>
				<?php 
			}  
		} 
	}

	function get_countdown(){
		$future = new DateTime( $this->get_v( 'launch' ) );
		$now = new DateTime();
		$interval = $future->diff($now); 
		?>

		<div id="countdown" data-cdate="<?php echo esc_attr(  strtotime( $this->get_v( 'launch' ) )  ); ?>">
			<div class="nnmcs-digits">
				<?php $dates = array(
					'y' => 'year',
					'm' => 'month',
					'd' => 'day',
					'h' => 'hour',
					'i' => 'minute',
					's' => 'second',
				); 
				$last = 0;
				foreach( $dates as $key => $val ) { 
					$d = $interval->$key;
					if( $last !== 0 && 0 === $d || 0 !== $d) {
						$text = sprintf( _n( '%s', '%ss',  $d, 'nnmcs' ), $val );
						echo '<p class="'. $key .'"><span class="digits">'. $d .'</span> <span class="text">'. $text .'</span></p>';
					}
					$last = $d;
				} ?>
			</div>
		</div>
		<?php
	}

	function social_links() {
		$opts = get_option('nnmcs_opts');
		$display = array();
		foreach($opts as $k => $v) {
			if( strpos( $k, 'social_' ) !== false && !empty( $v ) ) {
				$display[ str_replace( 'social_', '', $k ) ] = $v;
			}
		}

		if(isset($display) && !empty($display)) { 
			?>
			<div id="nnmcs-social-links" class="nnmcs-social-wrap">
				<ul class="nnmcs-social-grid">
					<?php foreach($display as $k => $v) { ?>
						<li>
							<a href="<?php echo esc_url( $v ); ?>" target="_blank" rel="noreferrer noopener" class="nnmcs-social" title="view my <?php echo esc_attr( $k ); ?> profile">
								<i class="fa fa-<?php echo esc_attr( $k ); ?>"></i>
							</a>
						</li>
					<?php } ?>
				</ul>
			</div>
			<?php
		}
	}

	function get_v($v){
		$opts = get_option('nnmcs_opts');
		if( isset( $opts[ $v ] ) ) {
			return esc_attr( $opts[ $v ] );
		} else {
			return '';
		}
	}	
}