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
				<?php
				foreach (WCCB_Frontend::get_editable_roles() as $key => $value) {
					if ($key == 'wccb_tutor' || $key == 'wccb_student' ) {
						?>
						<option value="<?php echo $key;?>" <?php selected($_POST['user_role'],$key);?>><?php echo $value['name'];?></option>
						<?php
					}
				}
				?>
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
							<select class="select" name="<?php echo $lower_key;?>_start_time" id="<?php echo $lower_key;?>_start_time">
								<?php
								for ($i=0; $i < 24 ; $i++) { 
									?>
									<option value="<?= $i; ?>" <?php selected($i , $_POST[$lower_key.'_start_time']);?>>
										<?= $i % 12 ? $i % 12 : 12 ?>:00 <?= $i >= 12 ? 'pm' : 'am' ?>
									</option>
									<?php
								}
								?>					
							</select>
							
						</td>
						<td>
							<select class="select" name="<?php echo $lower_key;?>_end_time" id="<?php echo $lower_key;?>_end_time">
								<?php
								for ($i=0; $i < 24 ; $i++) { 
									?>
									<option value="<?= $i; ?>" <?php selected($i , $_POST[$lower_key.'_end_time']);?>>
										<?= $i % 12 ? $i % 12 : 12 ?>:00 <?= $i >= 12 ? 'pm' : 'am' ?>
									</option>
									<?php
								}
								?>				
							</select>
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

	public static function render_my_account_availability_content() {
		$availability = get_user_meta(get_current_user_id() , 'availability' , true );
		?>
		<form class="woocommerce-EditAccountForm edit-account" action="" method="post" >
			<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide availability_container">
				
				<label><b><?php esc_html_e( 'Availability Settings', PLUGIN_TEXT_DOMAIN ); ?></b>&nbsp;<span class="required">*</span></label>
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
								<select class="select" name="<?php echo $lower_key;?>_start_time" id="<?php echo $lower_key;?>_start_time">
									<?php
									for ($i=0; $i < 24 ; $i++) { 
										?>
										<option value="<?= $i; ?>" <?php selected($i , $availability[$lower_key]['start_time']);?>>
											<?= $i % 12 ? $i % 12 : 12 ?>:00 <?= $i >= 12 ? 'pm' : 'am' ?>
										</option>
										<?php
									}
									?>					
								</select>
								
							</td>
							<td>
								<select class="select" name="<?php echo $lower_key;?>_end_time" id="<?php echo $lower_key;?>_end_time">
									<?php
									for ($i=0; $i < 24 ; $i++) { 
										?>
										<option value="<?= $i; ?>" <?php selected($i , $availability[$lower_key]['end_time']);?>>
											<?= $i % 12 ? $i % 12 : 12 ?>:00 <?= $i >= 12 ? 'pm' : 'am' ?>
										</option>
										<?php
									}
									?>				
								</select>
							</td>
							<td>
								<input type="checkbox" class="woocommerce-Input woocommerce-Input--cehckbox input-checkbox" name="<?php echo $lower_key;?>_is_unavailable" id="<?php echo $lower_key;?>_is_unavailable"  value="Yes" <?php echo ! empty( $availability[$lower_key]['is_unavailable'] ) ? 'checked="checked"' : ''; ?> />
							</td>
						</tr>
						<?php
					}
					?>

				</table>
				
			</div>

			<p>
				<?php wp_nonce_field( 'save_tutor_availability', 'save-tutor-availability-nonce' ); ?>
				<button type="submit" class="woocommerce-Button button" name="save_availability" value="<?php esc_attr_e( 'Save changes', PLUGIN_TEXT_DOMAIN ); ?>"><?php esc_html_e( 'Save changes', PLUGIN_TEXT_DOMAIN ); ?></button>
				<input type="hidden" name="action" value="save_availability" />
			</p>

		</form>
		<?php
	}

	public static function product_detail_page_form_start() {
		global $product;
		?>
		<form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
		<?php
	}

	public static function show_product_description() {
		global $product;

		if ($product->get_type() == 'wccb_package' ) {
			?>
			<div class="woocommerce-product-details__short-description">
				<?php echo $product->get_description();?>
			</div>
			<?php
		}
	}

	public static function show_product_price() {
		global $product;
		?>
		<p class="<?php echo esc_attr( apply_filters( 'woocommerce_product_price_class', 'price' ) ); ?>">
			Hourly Price : <?php echo $product->get_price_html(); ?>
		</p>
		<?php
	}

	public static function wccb_package_add_to_cart () {
	    global $product;

	    // Make sure it's our custom product type
	    if ( 'wccb_package' == $product->get_type() ) {
	        ?>
				<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

				<?php
				do_action( 'woocommerce_before_add_to_cart_quantity' );
				?>
				<div class="quantity_wrapper">
					<label>Hours</label>
					<?php
					woocommerce_quantity_input(
						array(
							'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
							'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
							'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
						)
					);
					?>
				</div>
				<?php

				do_action( 'woocommerce_after_add_to_cart_quantity' );
				?>

				<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>

				<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
			
			<?php
	    }
	}

	public static function show_tutor_profile() {
	    if (!empty($_REQUEST['gender'])) {
	    	$args = array(
	        	'meta_query'    => array(
		            'relation'  => 'AND',
		            array( 
		                'key'     => 'user_role',
		                'value'   => 'Tutor',
		            ),
		            array( 
		                'key'     => 'gender',
		                'value'   => $_REQUEST['gender'],
		            )
	        	)
	    	);
	    }
	    else {
	    	$args = array(
		        'meta_query'    => array(
		            array( 
		                'key'     => 'user_role',
		                'value'   => 'Tutor',
		            )
		        )
		    );
	    }

		$user_query = new WP_User_Query( $args);
		?>
		<div class="tutor_profile_main_wrapper">
			<div class="gender_seletion_wrapper">
				<label><?php echo __('Select Gender' ,PLUGIN_TEXT_DOMAIN);?></label>
				<select name="gender" id="gender" class="gender_select">
					<option value="">All</option>
					<option value="Male" <?php selected($_REQUEST['gender'] , 'Male');?>>Male</option>
					<option value="Female" <?php selected($_REQUEST['gender'] , 'Female');?>>Female</option>
				</select>
			</div>
			<h2><?php echo __('Our expert '.strtolower($_REQUEST['gender']).' tutors for this course' , PLUGIN_TEXT_DOMAIN);?></h2>
			<p><?php echo __('Select any one tuotor from your choice and proceed' , PLUGIN_TEXT_DOMAIN);?></p>
			<?php
			if ( ! empty( $user_query->get_results() ) ) {
				foreach ( $user_query->get_results() as $user ) {
					?>
					<div class="tutor_profile_wrapper">
						<div class="profile_image_wrapper">
							<?php
							$attachment_id = get_user_meta( $user->ID , 'profile_image' , true );
							if ($attachment_id) {
								echo wp_get_attachment_image($attachment_id , 'thumbnail' , array('class' => 'ct-image oxel_reviewbox__image_wrapper__image') );
							}
							else {
								echo get_avatar( $user->ID, 96 , '' , $user->display_name , array('class' => 'ct-image oxel_reviewbox__image_wrapper__image'));
							}
							?>
						</div>
						<div class="tutor_meta_wrapper">
							<input type="radio" name="tutor_id_radio" value="<?php echo $user->ID;?>" class="get_tutor_availability" data-action="get_tutor_availability" data-date="<?php echo date('Y-m-d');?>" data-response_container=".tutor_availability_main_wrapper" data-num_days="5">
							<?php echo $user->display_name;?>
						</div>
					</div>
					<?php
				}
			} 
			else {
				echo __('No tutor found.' , PLUGIN_TEXT_DOMAIN);
			}
			?>
		</div>
		<?php
	}

	public static function get_tutor_availability( $tutor_id , $date , $num_days = 2 ) {
		if (empty($tutor_id) || empty($date)) {
			return;
		}

		$tutor_info   = get_userdata($tutor_id);
		$availability = get_user_meta($tutor_id , 'availability' , true );

		$next_end_date     = date('Y-m-d' , strtotime($date.' +'.$num_days.' days'));
		$pervious_end_date = date('Y-m-d' , strtotime($date.' -'.$num_days.' days'));
		?>
		<h2>Choose slots from availability of <?php echo $tutor_info->display_name;?></h2>
		<table class="table table-bordered" border="1">
			<tr>
				<th>
					<span class="get_tutor_availability previous_date" data-action="get_tutor_availability" data-date="<?php echo $pervious_end_date;?>" data-response_container=".tutor_availability_main_wrapper"><<</span>
				</th>
				<?php
				for ($i=0; $i < $num_days; $i++) {
					?>
					<th><?php echo date('D M j, Y' , strtotime($date.' +'.$i.' days'));?></th>
					<?php

					//Prepare slot table for each date
					$lower_key  = strtolower(date('l', strtotime($date.' +'.$i.' days')));
					if (empty($availability[$lower_key.'_is_unavailable'])) {

						$start_time = explode(' ', $availability[$lower_key]['start_time']);
						$end_time   = explode(' ' , $availability[$lower_key]['end_time']);
						$slot_table = '<table>';
						for ($j=(int)$start_time[0]; $j <(int)$end_time[0] ; $j++) {
							$from  = $j % 12 ? $j % 12 : '12:00';
							$from .= $j >= 12 ? ' pm' : ' am';

							$to  = ($j+1) % 12 ? ($j+1) % 12 : '12:00';
							$to .= ($j+1) >= 12 ? ' pm' : ' am';

							$am_pm = $from.' - '.$to;
							$slot_table .= '<tr>
												<td><input type="checkbox" name="slot[]" value="'.$am_pm.'"></td>
												<td>'.$am_pm.'</td>
											</tr>';
						}
						$slot_table .= '</table>';

						$slot_table_array[] = $slot_table;
					}
				}
				?>
				
				<th>
					<span class="get_tutor_availability next_date" data-action="get_tutor_availability" data-date="<?php echo $next_end_date;?>" data-response_container=".tutor_availability_main_wrapper" data-num_days="5">>></span>
				</th>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<?php
				for ($i=0; $i < $num_days; $i++) {
					?>
					<th>Slot</th>
					<?php
				}
				?>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					&nbsp;
				</td>
				<?php
				for ($i=0; $i < $num_days; $i++) {
					?>
					<td>
						<?php echo $slot_table_array[$i];?>
					</td>
					<?php
				}
				?>
				<td>
					&nbsp;
				</td>
			</tr>
		</table>
		<?php

		return ob_get_clean();
	}
}
?>