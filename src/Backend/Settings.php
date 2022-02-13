<?php

namespace Infonique\Newt\Backend;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

if (!defined('ABSPATH')) {
	die('');
}

class Settings
{
	public static $USER_META_TOKEN = 'newt_token';

	/**
	 * __constructor
	 */
	public function __construct()
	{
	}

	/**
	 * Add the admin-menu
	 *
	 * @return void
	 */
	public function admin_menu()
	{
		add_action('admin_init', [$this, 'admin_init']);
		add_action('admin_menu', function () {
			add_submenu_page(
				'options-general.php',
				'Newt Options',
				'Newt Options',
				'manage_options',
				'Newt',
				[$this, 'newt_options_page_html']
			);
		});
	}

	/**
	 * Admin init
	 * This will register the settings for Newt
	 *
	 * @return void
	 */
	public function admin_init()
	{
		register_setting('newt', 'newt_options');

		add_settings_section(
			'newt_section_mashup',
			__('Newt WP-Plugin', 'newt'),
			function () {
				echo "Place the Newt settings here";
			},
			'newt'
		);

		add_settings_field(
			'newt_validation_key',
			'Validation-Key',
			[$this, 'field_input'],
			'newt',
			'newt_section_mashup',
			[
				'label_for' => 'newt_validation_key'
			],
		);

		add_settings_field(
			'newt_mashup_name',
			'Mashup name',
			[$this, 'field_input'],
			'newt',
			'newt_section_mashup',
			[
				'label_for' => 'newt_mashup_name'
			],
		);

		add_settings_field(
			'newt_app_key',
			'App-Key',
			[$this, 'field_input'],
			'newt',
			'newt_section_mashup',
			[
				'label_for' => 'newt_app_key'
			],
		);
	}

	/**
	 * Returns the QR-code for the server
	 *
	 * @param boolean $base64
	 * @return string
	 */
	public function get_qrcode_image($base64 = false): string
	{
		$content = json_encode((object)$this->get_data_for_qr_code());
		$options = new QROptions([
			'imageTransparent' => false,
			'imageBase64' => $base64
		]);
		$qrcode = new QRCode($options);

		return $qrcode->render($content);
	}

	/**
	 * Returns the data for the qr-code
	 *
	 * @return array
	 */
	public function get_data_for_qr_code(): array
	{
		$current_user = wp_get_current_user();

		$data = [];
		$data["name"] = 'Wordpress';
		$data["user"] = $current_user->user_login;
		$data["url"] = site_url() . '/newt/v1/endpoint';

		$current_user_id = $current_user->ID;
		$current_token = get_user_meta($current_user_id, $this::$USER_META_TOKEN, true);

		if (!$current_token) {
			$current_token = $this->get_new_token();
			update_user_meta($current_user_id, $this::$USER_META_TOKEN, $current_token);
		}
		$data["token"] = $current_token;

		return $data;
	}

	/**
	 * Creates a new token
	 *
	 * @return string
	 */
	public function get_new_token(): string
	{
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
	}

	/**
	 * Add the options-page
	 *
	 * @return void
	 */
	public function newt_options_page_html()
	{
		// check user capabilities
		if (!current_user_can('manage_options')) {
			return;
		}
?>
		<div class="wrap">
			<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
			<img src="<?php echo $this->get_qrcode_image(true) ?>">
			<form action="options.php" method="post">
				<?php
				settings_fields('newt');
				do_settings_sections('newt');
				submit_button(__('Save Settings', 'textdomain'));
				?>
			</form>
		</div>
<?php
	}

	/**
	 * Returns a input-field
	 *
	 * @param array $args
	 * @return void
	 */
	function field_input($args)
	{
		$fieldName = $args['label_for'];
		$placeholder = $args['placeholder'];
		$value = Settings::get_value_for_option($fieldName);
		echo "<input id=\"{$fieldName}\" name=\"newt_options[{$fieldName}]\" size=\"40\" type=\"text\" value=\"{$value}\" placeholder=\"{$placeholder}\" />";
	}

	/**
	 * Returns the value for a setting
	 *
	 * @param string $fieldName
	 * @return string|null
	 */
	public static function get_value_for_option(string $fieldName): ?string
	{
		$options = get_option('newt_options');
		if (isset($options[$fieldName])) {
			return $options[$fieldName];
		}
		return null;
	}
}
