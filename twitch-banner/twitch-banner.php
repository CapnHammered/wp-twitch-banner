<?php
/*
	Plugin Name: Twitch Banner
	Plugin URI: http://danny.gg
	Description: If online, adds a banner to the bottom of your Wordpress site with a link to your Twitch profile.
	Version: 1.0
	Author: Danny Battison
	Author URI: http://danny.gg
	License: GPL2
*/


define('TWITCHBANNER_URL', plugin_dir_url(__FILE__));
define('TWITCHBANNER_DIR', plugin_dir_path(__FILE__));

require_once TWITCHBANNER_DIR . 'twitch-settings.php';

class TwitchBanner {
	protected $settings, $options;
	private $data;

	public function __construct() {
		$this->settings = new TwitchSettings();
		$this->options = $this->settings->get();

		$this->data = json_decode(file_get_contents("https://api.twitch.tv/kraken/streams/{$this->options['twitch-username']}"));

		add_action('wp_head', array($this, 'script'));

		add_action('wp_ajax_nopriv_twitch-banner', array($this, 'ajax'));
		add_action('wp_ajax_twitch-banner', array($this, 'ajax'));
	}

	function isOnline() {
		return !is_null($this->data->stream); 
	}

	function getUrl() {
		return $this->isOnline() ? $this->data->stream->channel->url : '';
	}

	function getGame() {
		return $this->isOnline() ? $this->data->stream->channel->game : '';
	}

	function getTitle() {
		return $this->isOnline() ? $this->data->stream->channel->status : '';
	}

	function script() { 
		$colour = $this->hex2rgb($this->options['twitch-banner-colour']);
		$text_colour = $this->options['twitch-banner-text-colour'];
		$text_glow = $this->options['twitch-banner-text-glow-colour'];
		$padding = $this->options['twitch-banner-padding'];
		$opacity = $this->options['twitch-banner-opacity'] / 100;
		$border = $opacity >= 0.8 ? 1 : ($opacity+0.2); ?>
		<script type='text/javascript'><!--
			var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
			jQuery(document).ready(function() {
				jQuery('body').append('<div id="twitch-banner-wrapper"></div>');
				checkTwitchStatus();
			});

			function checkTwitchStatus() {
				jQuery.get(ajaxurl, {'action':'twitch-banner'}, function(response) {
					console.log(response);
					jQuery('#twitch-banner-wrapper').html(response);
				});

				setTimeout(function() {
					checkTwitchStatus();
				}, 30000);
			}
		--></script> 
		<style type='text/css'>
			#twitch-banner { 
				position: fixed; 
				bottom: 0; 
				font-size: 14pt; 
				line-height: 22pt;
				text-align: center;
				z-index: 99;
				background: rgba(<?php echo $colour; ?>,<?php echo $opacity; ?>);
				border-top: 1px solid rgba(<?php echo $colour; ?>,<?php echo $border; ?>);
				width: 100%;
				font-weight: bold;
				display: block;
				padding: <?php echo $padding; ?>px; 
				margin: 0;
				animation:glow 5s infinite;
				-webkit-animation: glow 5s infinite;
			}
			#twitch-banner {
				color: <?php echo $text_colour; ?> !important;
			} 
			span.twitch-title {
				color: inherit !important;
			}
			<?php
			if($this->options['twitch-hide-mobile-title']) { ?>
				@media only screen and (max-width: 480px) {
					span.twitch-title { display: none !important; }
				} <?php
			} ?>

			@keyframes glow {
			    0% {color: <?php echo $text_colour; ?>;}
			    50% {color: <?php echo $text_glow; ?>;}
			    100% {color: <?php echo $text_colour; ?>;}
			}
			@-webkit-keyframes glow {
			    0% {color: <?php echo $text_colour; ?>;}
			    50% {color: <?php echo $text_glow; ?>;}
			    100% {color: <?php echo $text_colour; ?>;}
			}
		</style> <?php
	}

	function ajax() {
		if($this->isOnline()) { ?>
			<a href='<?php echo $this->getUrl(); ?>' id='twitch-banner'>Currently streaming<span class='twitch-title'>: <?php echo $this->getGame() . " | " . $this->getTitle(); ?></span></a> <?php
		}
		wp_die();
	}

	function hex2rgb($hex) {
		$hex = str_replace("#", "", $hex);

		if(strlen($hex) == 3) {
		  $r = hexdec(substr($hex,0,1).substr($hex,0,1));
		  $g = hexdec(substr($hex,1,1).substr($hex,1,1));
		  $b = hexdec(substr($hex,2,1).substr($hex,2,1));
		} else {
		  $r = hexdec(substr($hex,0,2));
		  $g = hexdec(substr($hex,2,2));
		  $b = hexdec(substr($hex,4,2));
		}
		$rgb = array('r' => $r, 'g' => $g, 'b' => $b);
		return implode(',', $rgb);
	}
}

$TwitchBanner = new TwitchBanner();