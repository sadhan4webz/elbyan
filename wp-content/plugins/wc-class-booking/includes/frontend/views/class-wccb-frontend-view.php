<?php
defined( 'ABSPATH' ) || die();
class WCCB_Frontend_View {

	public static function render_register_form_fields( $user_id = 0 ) {
		?>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="reg_username"><?php esc_html_e( 'First Name', PLUGIN_TEXT_DOMAIN ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="first_name" id="first_name" autocomplete="first_name" value="<?php echo ( ! empty( $_POST['first_name'] ) ) ? esc_attr( wp_unslash( $_POST['first_name'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="reg_username"><?php esc_html_e( 'Last Name', PLUGIN_TEXT_DOMAIN ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="last_name" id="last_name" autocomplete="last_name" value="<?php echo ( ! empty( $_POST['last_name'] ) ) ? esc_attr( wp_unslash( $_POST['last_name'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="reg_username"><?php esc_html_e( 'Gender', PLUGIN_TEXT_DOMAIN ); ?>&nbsp;<span class="required">*</span></label>
			<select name="gender" id="gender" class="select">
				<option value="">Select</option>
				<option value="Male" <?php selected($_POST['gender'],'Male');?>>Male</option>
				<option value="Female" <?php selected($_POST['gender'],'Female');?>>Female</option>
			</select>
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="reg_username"><?php esc_html_e( 'Registration Type', PLUGIN_TEXT_DOMAIN ); ?>&nbsp;<span class="required">*</span></label>
			<select name="user_role" id="user_role" class="select">
				<option value="">Select</option>
				<option value="Student" <?php selected($_POST['user_role'],'Student');?>>Student</option>
				<option value="Tutor" <?php selected($_POST['user_role'],'Tutor');?>>Tutor</option>
			</select>
		</p>

		<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide availability_container" style="display: <?php echo $_POST['user_role'] == 'Tutor' ? 'block' : 'none';?>;">
			
			<label for="reg_password"><b><?php esc_html_e( 'Your Availability', PLUGIN_TEXT_DOMAIN ); ?></b>&nbsp;<span class="required">*</span></label>
			<table>
				<tr>
					<th>
						Day
					</th>
					<th>
						Start Time
					</th>
					<th>
						End Time
					</th>
					<th>
						Is Unavailable?
					</th>
				</tr>
				<?php
				foreach (WCCB_Helper::get_weekdays_array() as $key => $value) {
					$lower_key = strtolower($key);
					?>
					<tr>
						<td>
							<?php echo $key;?>
						</td>
						<td>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="<?php echo $lower_key;?>_start_time" id="<?php echo $lower_key;?>_start_time"  value="<?php echo ( ! empty( $_POST[$lower_key.'_start_time'] ) ) ? esc_attr( wp_unslash( $_POST[$lower_key.'_start_time'] ) ) : ''; ?>" />
						</td>
						<td>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="<?php echo $lower_key;?>_end_time" id="<?php echo $lower_key;?>_end_time"  value="<?php echo ( ! empty( $_POST[$lower_key.'_end_time'] ) ) ? esc_attr( wp_unslash( $_POST[$lower_key.'_end_time'] ) ) : ''; ?>" />
						</td>
						<td>
							<input type="checkbox" class="woocommerce-Input woocommerce-Input--cehckbox input-checkbox" name="<?php echo $lower_key;?>_is_unavailable" id="<?php echo $lower_key;?>_is_unavailable"  value="Yes" <?php echo ! empty( $_POST[$lower_key.'_is_unavailable'] ) ? 'checked="checked"' : ''; ?> />
						</td>
					</tr>
					<?php
				}
				?>

			</table>
			
		</div>
		<?php

	}
}