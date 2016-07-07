<?php
class TwitchSettings {
	private $options;
	protected $defaults;

	public function __construct() {
		$this->defaults = array(
			'twitch-username' => '',
			'twitch-banner-colour' => '#3fa4b8',
			'twitch-banner-text-colour' => '#1f1f1f',
			'twitch-banner-text-glow-colour' => '#1f1f1f',
			'twitch-banner-padding' => 10,
			'twitch-banner-opacity' => 60,
			'twitch-hide-mobile-title' => false
		);
		if(is_admin()) {
			add_action('admin_menu', array($this, 'add_plugin_page'));
			add_action('admin_init', array($this, 'page_init'));

			if(is_admin()) {
				add_action('wp_head', array($this, 'head'));
			}
		}
	}
	
	public function get() { 
		$options = get_option('twitch-banner_options', $this->defaults);

		foreach($options as $key => $val) {
			if(empty($val)) {
				$options[$key] = $this->defaults[$key];
			}
		}
		return $options;
	}

	public function head() {
		wp_enqueue_style('minicolors', TWITCHBANNER_URL . 'minicolors/jquery.minicolors.css');
		wp_enqueue_script('minicolors', TWITCHBANNER_URL . 'minicolors/jquery.minicolors.min.js'); ?>
		<style type='text/css'> 
			input.minicolors { width:135px !important; height:25px !important; } 
			.minicolors-swatch { top:3px !important; left:3px !important; height:19px !important; width:19px !important; }
		</style>
		<script type='text/javascript'><!--
			jQuery(function() {
				jQuery('input.minicolors').each(function() { 
					console.log(jQuery(this));
					jQuery(this).minicolors(); 
				})
			});
		</script> <?php
	}
	
	public function add_plugin_page() {
		add_options_page(
			'Settings Admin', 
			'Twitch Banner', 
			'manage_options', 
			'twitch-banner-setting-admin', 
			array($this, 'create_admin_page')
		);
	}

	public function create_admin_page() {
		$this->options = $this->get();
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2>Twitch Banner Settings</h2>		 
			<form method="post" action="options.php" style='display:inline'>
			<?php
				settings_fields('twitch-banner_option_group'); 
				do_settings_sections('twitch-banner-setting-admin');
				submit_button('Save Changes', 'primary', '', false, 'style="display:inline"'); 
			?>
			</form>
		</div>
		<?php
	}

	public function page_init() {
		register_setting(
			'twitch-banner_option_group',
			'twitch-banner_options'
		);
		
		add_settings_section(
			'twitch_banner',
			'Twitch Banner Settings',
			null,
			'twitch-banner-setting-admin'
		);
		
		add_settings_field(
			'twitch-username',
			'<a title="Your Twitch username">Username:</a>',
			array($this, 'standard_field'),
			'twitch-banner-setting-admin',
			'twitch_banner',
			array(
				'name' => 'twitch-username',
				'opt-group' => 'twitch-banner_options',
				'value' => $this->options['twitch-username'],
				'default' => ''
			)
		);
		
		add_settings_field(
			'twitch-banner-colour',
			'<a title="The colour of the banner">Banner Colour:</a>',
			array($this, 'standard_field'),
			'twitch-banner-setting-admin',
			'twitch_banner',
			array(
				'name' => 'twitch-banner-colour',
				'opt-group' => 'twitch-banner_options',
				'value' => $this->options['twitch-banner-colour'],
				'default' => '',
				'type' => 'color'
			)
		);
		
		add_settings_field(
			'twitch-banner-text-colour',
			'<a title="Colour for the text on the banner">Primary Text Colour:</a>',
			array($this, 'standard_field'),
			'twitch-banner-setting-admin',
			'twitch_banner',
			array(
				'name' => 'twitch-banner-text-colour',
				'opt-group' => 'twitch-banner_options',
				'value' => $this->options['twitch-banner-text-colour'],
				'default' => '',
				'type' => 'color'
			)
		);
		
		add_settings_field(
			'twitch-banner-text-glow-colour',
			'<a title="Colour for the text to glow to (set this the same as above to disable glow)">Secondary Text Colour:</a>',
			array($this, 'standard_field'),
			'twitch-banner-setting-admin',
			'twitch_banner',
			array(
				'name' => 'twitch-banner-text-glow-colour',
				'opt-group' => 'twitch-banner_options',
				'value' => $this->options['twitch-banner-text-glow-colour'],
				'default' => '',
				'type' => 'color'
			)
		);
		
		add_settings_field(
			'twitch-banner-padding',
			'<a title="Number of pixels to pad the banner.  Set to 10 if unsure.">Padding:</a>',
			array($this, 'standard_field'),
			'twitch-banner-setting-admin',
			'twitch_banner',
			array(
				'name' => 'twitch-banner-padding',
				'opt-group' => 'twitch-banner_options',
				'value' => $this->options['twitch-banner-padding'],
				'default' => '',
				'type' => 'number'
			)
		);
		
		add_settings_field(
			'twitch-banner-opacity',
			'<a title="Between 1 and 100.  The lower it is, the less visible the banner will be.">Opacity:</a>',
			array($this, 'standard_field'),
			'twitch-banner-setting-admin',
			'twitch_banner',
			array(
				'name' => 'twitch-banner-opacity',
				'opt-group' => 'twitch-banner_options',
				'value' => $this->options['twitch-banner-opacity'],
				'default' => '',
				'type' => 'number'
			)
		);
		
		add_settings_field(
			'twitch-hide-mobile-title',
			'<a title="Tick to hide the title on mobile (useful if your stream title is long)">Hide Title on Mobile:</a>',
			array($this, 'checkbox_field'),
			'twitch-banner-setting-admin',
			'twitch_banner',
			array(
				'name' => 'twitch-hide-mobile-title',
				'opt-group' => 'twitch-banner_options',
				'checked' => $this->options['twitch-hide-mobile-title'] == 1 ? "checked" : ""
			)
		);
	}

	public function standard_field($args) {
		$class = '';
		$type = isset($args['type']) ? $args['type'] : 'text';
		if($args['type'] == 'color') { $args['type'] = 'text'; $class = 'minicolors'; }
		printf(
			'<input type="%s" id="%s" name="%s[%s]" value="%s" class="%s %s" />',
			$args['type'],
			$args['name'],
			$args['opt-group'],
			$args['name'],
			esc_attr($this->options[$args['name']]),
			$class,
			$args['class']
		);
	}

	public function checkbox_field($args) {
		$checked = $this->options[$args['name']] ? 'checked' : '';
		printf(
			'<input type="checkbox" id="%s" name="%s[%s]" value="1" %s />',
			$args['name'],
			$args['opt-group'],
			$args['name'],
			$checked
		);		
	}
}
