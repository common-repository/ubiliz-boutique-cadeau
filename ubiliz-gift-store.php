<?php

/**
 * @package Ubiliz
 */

/*
Plugin Name: Ubiliz - Boutique cadeau
Description: Ce plugin E-commerce vous permet d'afficher la liste de vos bons cadeaux sur votre site Internet Wordpress. Vendez et gérez vos bons cadeaux en ligne simplement et en quelques clics avec le plugin Ubiliz.
Version: 1.4
Author: Ubiliz
License: GPLv2 or later
Text Domain: ubiliz
*/

final class UbilizGiftStore {

	private const VERSION = '1.0';
	private const NAMESPACE = 'ubiliz_gs';
	private const OPTION_NAME = 'ubiliz_gift_store_plugin_options';

	private const CACHE_KEY_COLOR = 'ubiliz_gs_color';
	private const CACHE_KEY_PRODUCT = 'ubiliz_gs_data';
	private const CACHE_MAX_AGE = 60 * 60 * 6; // 6 hours

	private static $instance;

	private $errors = [];

	public static function get_instance(): UbilizGiftStore {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		register_activation_hook(__FILE__, [__CLASS__, 'ubiliz_activate']);
		register_deactivation_hook( __FILE__, [__CLASS__, 'ubiliz_uninstall']);
		add_filter('plugin_action_links_ubiliz-gift-store/ubiliz-gift-store.php', [$this, 'ubiliz_links'], 10, 1 );
		add_action( 'after_setup_theme', [ $this, 'theme_setup' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_assets' ] );
		add_action( 'admin_menu', [ $this, 'admin_pages' ] );
		add_shortcode( 'UBILIZ', [ $this, 'ubiliz_shortcode' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'ubiliz_assets' ] );
        add_action( 'wp_footer', [ $this, 'add_integration_code'] );
    }

	/**
	 * On plugin activation
	 */
	public static function ubiliz_activate(): void {
		register_uninstall_hook( __FILE__, [__CLASS__, 'ubiliz_uninstall']);
	}

	/**
	 * On plugin deactivation
	 */
	public static function ubiliz_uninstall(): void {
		delete_option(static::OPTION_NAME);
		delete_transient(static::CACHE_KEY_PRODUCT);
		delete_transient(static::CACHE_KEY_COLOR);
	}

	public function ubiliz_links($links): array {
		$links['settings'] = '<a href="' . admin_url( 'admin.php?page=ubiliz-gs-settings' ) . '">' . __( 'Settings') . '</a>';
		return $links;
	}

	/**
	 * Init editor button
	 * @return void
	 */
	public function theme_setup(): void {
		add_action( 'init', [ $this, 'ubiliz_editor_button' ] );
	}

	/**
	 * Define editor buttons
	 * @return void
	 */
	public function ubiliz_editor_button(): void {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		if ( get_user_option( 'rich_editing' ) !== 'true' ) {
			return;
		}
		add_filter( 'mce_external_plugins', [ $this, 'ubiliz_editor_add_button' ] );
		add_filter( 'mce_buttons', [ $this, 'ubiliz_editor_register_button' ] );
	}

	/**
	 * Add editor button
	 */
	public function ubiliz_editor_add_button( array $plugin_array ): array {
		$plugin_url                    = plugin_dir_url( __FILE__ );
		$plugin_array['ubiliz_gifts'] = $plugin_url . 'tinymce/buttons.js';

		return $plugin_array;
	}

	/**
	 * Register editor button
	 */
	public function ubiliz_editor_register_button( array $buttons ): array {
		array_push( $buttons, 'ubiliz_gifts' );
		return $buttons;
	}

	/**
	 * Admin assets
	 */
	public function admin_assets(): void {
		$plugin_url = plugin_dir_url( __FILE__ );
		wp_enqueue_style( static::NAMESPACE . '_admin_style', $plugin_url . 'css/admin.css', [], static::VERSION );
		wp_enqueue_script( static::NAMESPACE . '_admin_script', $plugin_url . 'js/admin.js', ['jquery'], static::VERSION, true );
	}

	/**
	 * Ubiliz Assets
	 */
	public function ubiliz_assets(): void {
		$plugin_url = plugin_dir_url( __FILE__ );
		$place_color = $this->get_place_color();

		$version = static::VERSION . $place_color;
		wp_enqueue_style( static::NAMESPACE . '_style', $plugin_url . 'css/ubiliz.css', [], $version );

		if ($place_color) {
			$extra_css = "
                .ubiliz-product-box:hover .ubiliz-product-push {
                    background-color: #{$place_color} !important;
                }
                
                .ubiliz-product-name:hover a {
                    color: #{$place_color} !important;
                }
                ";
			wp_add_inline_style( static::NAMESPACE . '_style', $extra_css );
		}

		wp_enqueue_script( static::NAMESPACE . '_script', $plugin_url . 'js/ubiliz.js', ['jquery'], static::VERSION, true );
	}

	/**
	 * Admin pages
	 */
	public function admin_pages(): void {
		$logo = plugin_dir_url( __FILE__ ) . 'assets/logo-ubiliz-white.svg';
		add_menu_page(
			'Produits | Ubiliz - Boutique Cadeaux',
			'Boutique Cadeaux - Ubiliz',
			'manage_options',
			'ubiliz-gs-products',
			[ $this, 'admin_page_products' ],
			$logo,
			100
		);

		add_submenu_page(
			'ubiliz-gs-products',
			'Produits de votre boutique cadeaux Ubiliz',
			'Produits',
			'manage_options',
			'ubiliz-gs-products',
			[ $this, 'admin_page_products' ]
		);

		add_submenu_page(
			'ubiliz-gs-products',
			'Configuration boutique cadeaux Ubiliz',
			'Configuration',
			'manage_options',
			'ubiliz-gs-settings',
			[ $this, 'admin_page_settings' ]
		);
	}

	/**
	 * Products page callback
	 */
	public function admin_page_products(): void {
		$title = 'Produits de votre boutique cadeaux Ubiliz';
		$products = $this->get_all_products();

		echo $this->render( 'admin--products.tpl.php', [
			'title'    => $title,
			'is_token_set' => $this->is_token_set(),
			'is_token_valid' => $this->is_token_valid(),
			'products' => $products,
		] );
	}

	/**
	 * Settings page callback
	 */
	public function admin_page_settings(): void {
		$this->handle_settings_submit();
		$title = 'Configuration boutique cadeaux Ubiliz';

		echo $this->render( 'admin--settings.tpl.php', [
			'title'          => $title,
			'is_token_set'   => $this->is_token_set(),
			'is_token_valid' => $this->is_token_valid(),
			'token'          => $this->get_token(),
			'places'         => $this->get_places(),
			'place_data'     => $this->get_place_data($this->get_place()),
			'errors'         => $this->errors
		] );
	}

	/**
	 * Handle form settings submit
	 */
	private function handle_settings_submit(): void {
		$form_values = [];

		// sanitize post inputs
		foreach ($_POST as $key => $value) {
			$form_values[$key] = sanitize_text_field($value);
		}

		if (empty($form_values['_ubiliz_nonce']) || !wp_verify_nonce($form_values['_ubiliz_nonce'], 'ubiliz_settings_submit')) {
			return;
		} elseif ( !empty( $form_values['submit'] ) ) {
			$token = $form_values['ubiliz_token'] ?? '';
			$place = $form_values['ubiliz_place'] ?? '';

			// Save options (token)
			$this->set_options($token);

			if (!empty($place) && $place !== 'all' && !is_numeric($place)) {
				$this->errors[] = "L'établissement est invalide";
				return;
			}

			if (empty($place)) { // set first place of account (fallback)
				$placeData = $this->get_first_place();
				if (!empty($placeData)) {
					$place = $placeData['id'];
				} else {
					$place = 'all';
				}
			}

			// Save options (token + place)
			$this->set_options($token, $place);
		} elseif ( !empty( $form_values['reset_token'] ) ) {
			// Reset options
			$this->set_options();
		}
	}

	/**
	 * Check if token is set
	 */
	private function is_token_set(): bool {
		$token = $this->get_token();

		return !empty( $token );
	}

	/**
	 * Check if token is valid
	 */
	private function is_token_valid(): bool {
		$body = $this->api_call('https://api.ubiliz.com/me');

		if (empty($body['email'])) {
			return false;
		}

		return true;
	}

	/**
	 * Set ubiliz options
	 */
	private function set_options(string $token = '', string $place = ''): void {
		update_option( self::OPTION_NAME, [
			'token' => trim($token),
			'place' => trim($place)
		] );
		delete_transient( self::CACHE_KEY_COLOR );
		delete_transient( self::CACHE_KEY_PRODUCT );
	}

	/**
	 * Get Ubiliz token
	 */
	private function get_token(): string {
		$options = get_option( self::OPTION_NAME, [] );
		return $options['token'] ?? '';
	}

	/**
	 * Get place option
	 */
	private function get_place(): string {
		$options = get_option( self::OPTION_NAME, [] );
		return $options['place'] ?? '';
	}

	/**
	 * Get place color option
	 */
	private function get_place_color(): string {
		// Return data from cache if exists
		if ( $color = get_transient( self::CACHE_KEY_COLOR ) ) {
			return $color;
		}

		$color = $this->get_color_from_place($this->get_place());

		// Store data in cache
		set_transient( self::CACHE_KEY_COLOR, $color, self::CACHE_MAX_AGE );

		return $color;
	}

	/**
	 * Get place data from API
	 */
	private function get_place_data(string $place = null): ?array {
		if (empty($place)) {
			return [];
		}

		return $this->api_call('https://api.ubiliz.com/places/'.$place);
	}

	/**
	 * Get places from API
	 */
	private function get_places(): array {
		$body = $this->api_call('https://api.ubiliz.com/places');

		if ( empty( $body['hydra:member'] ) ) {
			return [];
		}

		return $body['hydra:member'];
	}

	/**
	 * Get first place from API
	 */
	private function get_first_place(): array {
		$body = $this->api_call('https://api.ubiliz.com/places');

		if ( empty( $body['hydra:member'] ) ) {
			return [];
		}

		return reset($body['hydra:member']);
	}

	/**
	 * Get color from place
	 */
	private function get_color_from_place(string $place = null): string {
		if (empty($place)) {
			return '';
		}

		$body = $this->api_call('https://api.ubiliz.com/places/'.$place);
		return $body['color'] ?? '';
	}

	/**
	 * Get all products from API
	 */
	private function get_all_products(): array {
		$url = 'https://api.ubiliz.com/voucher_configurations?enabled=true';
		$place = $this->get_place();
		if (!empty($place) && $place !== 'all') {
			$url .= '&mainPlace=' . $place;
		}

		$body = $this->api_call($url);

		if (empty($body['hydra:member'])) {
			return [];
		}

		return $this->sortProducts($body['hydra:member']);
	}

	/**
	 * Get enabled products from API
	 */
	private function get_products(): array {
		// Return cached data if exists
		if ( $data = get_transient( self::CACHE_KEY_PRODUCT ) ) {
			return $data;
		}

		$url = 'https://api.ubiliz.com/voucher_configurations?enabled=true';
		$place = $this->get_place();
		if (!empty($place) && $place !== 'all') {
			$url .= '&mainPlace=' . $place;
		}

		$body = $this->api_call($url);

		if ( empty( $body['hydra:member'] ) ) {
			return [];
		}

		// Sort products
		$products = $this->sortProducts($body['hydra:member']);

		// Cache products
		set_transient( self::CACHE_KEY_PRODUCT, $products, self::CACHE_MAX_AGE );

		return $products;
	}

	/**
	 * Sort products based on position field
	 */
	private function sortProducts(array $products): array {
		usort($products, function ($a, $b) {
			$aPosition = $a['position'] ?? 0;
			$bPosition = $b['position'] ?? 0;
			return $aPosition <=> $bPosition;
		});

		return $products;
	}

	/**
	 * Ubiliz API Call
	 */
	private function api_call(string $endpoint): array {
		if (!$token = $this->get_token()) {
			return [];
		}

		// Set arguments
		$args = [
			'headers' => [
				'X-Api-Key' => $token,
			],
		];

		try {
			$response = wp_remote_get( $endpoint, $args );
		} catch ( Exception $e ) {
			// Log error
			error_log( 'Ubiliz API error : ' . $e->getMessage() );
			return [];
		}

		if ($response instanceof WP_Error || empty( $response['body'] ) ) {
			return [];
		}

		return json_decode( $response['body'], true );
	}

	/**
	 * Render template
	 */
	private function render( $template, array $variables = [] ): ?string {
		ob_start();
		$dir = plugin_dir_path( __FILE__ );
		extract( $variables );
		include( $dir . '/templates/' . $template );
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * Render Ubiliz shortcode
	 */
	public function ubiliz_shortcode(): ?string {
        if($place_id = $this->get_place()) {
            return $this->render('app--products-iframe.tpl.php', ['place_id' => $place_id]);
        } else {
            return '';
        }
	}

    function add_integration_code() {
        if($place = $this->get_place()) {
            if($place !== 'all') {
                echo '<script>window.ubilizSettings={integrationUrl:"https://app.ubiliz.com/widget/integration/'.esc_html($place).'"};</script><script src="https://app.ubiliz.com/widget/integration.js"></script>';
            }
        }
    }

	public static function format_price(int $price) {
		return $price / 100;
	}
}

$ubilizGiftStore = UbilizGiftStore::get_instance();
