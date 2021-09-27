<?php
defined( 'ABSPATH' ) || die();
class WCCB_Frontend_View {

	public static function render_loader_html(){
		?>
		<style>
		.wccb-ajax-spinner{display:block;align-items:center;justify-content:center;margin-top:1.5em;}@keyframes loaderRotate{from{transform:rotate(0);}to{transform:rotate(360deg);}}.loader{width:80px;height:80px;}.loader circle{fill:none;stroke-width:5px;transform-origin:center;}.loader circle:nth-of-type(1){stroke:#f4c150;opacity:0.5;stroke-dasharray:251.3274122872px;stroke-dashoffset:50.2654824574px;animation:loaderRotate 3s linear infinite both;}.loader circle:nth-of-type(2){stroke:#f4c150;opacity:0.3;stroke-dasharray:188.4955592154px;stroke-dashoffset:75.3982236862px;animation:loaderRotate 1.5s linear infinite both;}.loader circle:nth-of-type(3){stroke:#f4c150;stroke-dasharray:125.6637061436px;stroke-dashoffset:75.3982236862px;animation:loaderRotate 1s linear infinite both;}
		</style>
		<div id="wccb-global">
			<div class="wccb-ajax-spinner ajax-load" style="display: none;">
				<svg class="loader">
					<circle cx="40px" cy="40px" r="37.5px" />
					<circle cx="40px" cy="40px" r="27.5px" />
					<circle cx="40px" cy="40px" r="17.5px" />
				</svg>
			</div>
		</div>
		<?php
	}

	public static function show_header_button( $atts , $content = null ) {
		ob_start();

		$atts = shortcode_atts(
    		array(
				
    		), $atts
		);
		$login_link_label = !get_current_user_id() ? 'Login' : 'My Account';
		?>
		<a href="<?php echo WCCB_Frontend::get_price_page_link();?>" class="header-btn">Quran Classes</a>
		<a href="<?php echo WCCB_Frontend::get_myaccount_page_link();?>" class="header-btn"><?php echo $login_link_label;?></a>
		<?php
		return ob_get_clean();
	}

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

		

		<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide availability_container" style="display: <?php echo $_POST['user_role'] == 'wccb_tutor' ? 'block' : 'none';?>;">
			
			<label for="reg_password"><b><?php esc_html_e( 'Your Availability', PLUGIN_TEXT_DOMAIN ); ?></b>&nbsp;<span class="required">*</span></label>
			<?php
			$availability_times = WCCB_Frontend::get_avilability_times_from_post($_POST);
			echo WCCB_Frontend_View::get_tutor_availability_time_fields($availability_times);
			?>
			
		</div>
		<?php

	}

	public static function get_tutor_availability_time_fields( $field_array = '' ) {
		ob_start();
		?>
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
				<th>&nbsp;</th>
			</tr>
			<?php
			foreach (WCCB_Helper::get_weekdays_array() as $key => $value) {
				$lower_key = strtolower($key);

				if (!empty($field_array[$lower_key]['available_time'])) {

					foreach ($field_array[$lower_key]['available_time'] as $key2 => $value2 ) {
						$more = $key2 == 0 ? '' : 'yes';

						echo WCCB_Frontend_View::get_availability_time_row_html( $more , $key , $value2['start_time'] , $value2['end_time'], $field_array[$lower_key]['is_unavailable'] );
					}
				}
				else {
					echo WCCB_Frontend_View::get_availability_time_row_html( '' , $key );
				}
			}
			?>
		</table>
		<?php
		return ob_get_clean();
	}

	public static function get_availability_time_row_html( $more = '' , $key = '' , $start_value = '' , $end_value = '' , $is_unavailable = '') {
		ob_start();
		
		if (empty($key)) {
			$start_field_name = '{lower_key}_start_time[]';
			$end_field_name   = '{lower_key}_end_time[]';
		}
		else {
			$lower_key        = strtolower($key);
			$start_field_name = $lower_key.'_start_time[]';
			$end_field_name   = $lower_key.'_end_time[]';
			$row_id           = $lower_key.'_row';
		}
		?>
		<tr class="time_row">
			<td>
				<?php echo $more == 'yes' ? '&nbsp;' : $key;?>
			</td>
			<td>
				<select class="select" name="<?php echo $start_field_name;?>">
					<?php
					for ($i=0; $i < 24 ; $i++) { 
						?>
						<option value="<?= $i; ?>" <?php selected($i , $start_value);?>>
							<?= $i % 12 ? $i % 12 : 12 ?>:00 <?= $i >= 12 ? 'pm' : 'am' ?>
						</option>
						<?php
					}
					?>					
				</select>
				
			</td>
			<td>
				<select class="select" name="<?php echo $end_field_name;?>">
					<?php
					for ($i=1; $i < 24 ; $i++) { 
						?>
						<option value="<?= $i; ?>" <?php selected($i , $end_value);?>>
							<?= $i % 12 ? $i % 12 : 12 ?>:00 <?= $i >= 12 ? 'pm' : 'am' ?>
						</option>
						<?php
					}
					?>				
				</select>
			</td>
			<?php
			if (empty($more)) {
				?>
				<td>
					<input type="checkbox" class="woocommerce-Input woocommerce-Input--cehckbox input-checkbox" name="<?php echo $lower_key;?>_is_unavailable" id="<?php echo $lower_key;?>_is_unavailable"  value="Yes" <?php echo ! empty( $is_unavailable ) ? 'checked="checked"' : ''; ?> />
				</td>
				<td>
					<a href="#" class="add_time_row" data-lower_key="<?php echo $lower_key;?>">Add Row</a>
					
				</td>
				<?php
			}
			else {
				?>
				<td>
					&nbsp;
				</td>
				<td>
					<div class="link_group">
						<a href="#" class="add_time_row" data-lower_key="{lower_key}">Add Row</a>
						<a href="#" class="delete_time_row">Delete</a>
					</div>
				</td>
				<?php
			}
			?>
		</tr>
		<?php
		return ob_get_clean();
	}

	public static function product_detail_page_form_start() {
		global $product;
		?>
		<form class="cart wccb_form" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
		<?php
	}

	public static function show_product_description() {
		global $product;

		if ($product->get_type() == 'wccb_course' ) {
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

	public static function wccb_course_add_to_cart () {
	    global $product;

	    // Make sure it's our custom product type
	    if ( 'wccb_course' == $product->get_type() ) {
	        ?>
				<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

				<?php
				do_action( 'woocommerce_before_add_to_cart_quantity' );
				?>
				<div class="quantity_wrapper">
					<label>Hours</label>
					<?php
					$course_type = get_post_meta( $product->get_id() , 'course_type' , true );
					if ($course_type == 'fixed') {
						$course_quantity = get_post_meta( $product->get_id() , 'course_quantity' , true );
						?>
						<input type="hidden" name="quantity" id="quantity" value="<?php echo $course_quantity;?>">
						<div class="quantity_label"><?php echo $course_quantity;?></div>
						<?php
					}
					else {
						woocommerce_quantity_input(
							array(
								'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
								'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
								'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
							)
						);
					}
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

	public static function render_tutor_availability_container() {
		?>
		<div class="slot_selected_container">
			<?php
			if (!empty($_REQUEST['slot'])) {
				$unique_random_id = $_REQUEST['unique_random_id'];
				$find             = array('{slot_picked_row_id}' , '{slot_date_time_hidden}' , '{slot_date}' , '{slot_time}' , '{slot_span_id}' , '{unique_random_id}' );

				foreach ($_REQUEST['slot'] as $key => $value) {
					$date_time    = explode('|', $value );
					$html         = WCCB_Frontend_View::get_slot_picked_row_html();
					$replace      = array('slot_picked_row_'.$unique_random_id[$key] , $value , wp_date('D M j, Y', strtotime($date_time[0])) , $date_time[1] , 'slot_span_id_'.$unique_random_id[$key] , $unique_random_id[$key] );
					$html         = str_replace( $find , $replace , $html );
					echo $html;
				}
			}
			?>
		</div>
		<div class="tutor_availability_main_wrapper">
			<?php
			if (!empty($_REQUEST['tutor_id'])) {
				echo WCCB_Frontend_View::get_tutor_availability_calendar( $_REQUEST['tutor_id'] , date('Y-m-d') , NUM_DAYS_CALENDAR , $_POST['slot'] );
			}
			else {
				echo __('Tutor availability will show here' , PLUGIN_TEXT_DOMAIN );
			}
			?>
		</div>
		<?php
	}



	public static function show_tutor_profile( $product_id = '' ) {
		global $product;
		if (empty($product_id)) {
			$product_id = $product->get_id();
		}

		$tutor_ids = get_post_meta( $product_id , 'tutor_ids' , true );
		$args = array(
		        'role' => 'wccb_tutor'
		);

		if (!empty($tutor_ids)) {
    		$args['include'] = $tutor_ids;
    	}

	    if (!empty($_REQUEST['gender'])) {
	    	$args['meta_query'] = array(
	            array( 
	                'key'     => 'gender',
	                'value'   => $_REQUEST['gender'],
	            )
	        );
	    }

		$user_query = new WP_User_Query( $args);

		ob_start();
		?>
		<div class="tutor_profile_main_wrapper">
			<h2 class="wccb_title"><?php echo __('Our expert '.strtolower($_REQUEST['gender']).' tutors for this course' , PLUGIN_TEXT_DOMAIN);?></h2>
			<div class="filter_wrapper">
				<p class="filter_heading"><?php echo __('Filter tutor by' , PLUGIN_TEXT_DOMAIN);?></p>
				<div class="gender_selection_wrapper">
					<label><?php echo __('Gender' ,PLUGIN_TEXT_DOMAIN);?></label>
					<select name="gender" id="gender" class="gender_select">
						<option value="">All</option>
						<option value="Male" <?php selected($_REQUEST['gender'] , 'Male');?>>Male</option>
						<option value="Female" <?php selected($_REQUEST['gender'] , 'Female');?>>Female</option>
					</select>
				</div>
			</div>
			<p><?php echo __('Select any one tuotor from your choice and proceed' , PLUGIN_TEXT_DOMAIN);?></p>
			<?php
			if ( ! empty( $user_query->get_results() ) ) {
				?>
				<div class="tutor_collection_wrapper">
					<?php
					foreach ( $user_query->get_results() as $user ) {
						?>
						<label for="tutor_<?php echo $user->ID;?>">
							<div class="tutor_profile_wrapper <?php echo $_REQUEST['tutor_id'] == $user->ID ? 'tutor_selected' : '';?>">
								
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
									<input type="radio" name="tutor_id" id="tutor_<?php echo $user->ID;?>" value="<?php echo $user->ID;?>" class="get_tutor_availability_calendar radio_btn" data-action="get_tutor_availability_calendar" data-tutor_id="<?php echo $user->ID;?>" data-date="<?php echo date('Y-m-d');?>" data-response_container=".tutor_availability_main_wrapper" data-reset_picked="yes" <?php if($_REQUEST['tutor_id'] == $user->ID){?> checked="checked" <?php }?>>
									<?php echo $user->display_name;?>
								</div>
							</div>
						</label>
						<?php
					}
					?>
				</div>
				<?php
				
			} 
			else {
				echo __('No tutor found.' , PLUGIN_TEXT_DOMAIN);
			}
			?>
		</div>
		<?php

		return ob_get_clean();
	}

	public static function get_tutor_availability_calendar( $tutor_id , $date , $num_days , $slot_picked_array = array()  ) {
		if (empty($tutor_id) || empty($date)) {
			return;
		}
		
		$calendar_stop_date = wp_date( 'Y-m-d' , strtotime('+'.HOUR_EXPIRE_DAYS.' days'));
		$tutor_info         = get_userdata($tutor_id);
		$availability       = get_user_meta($tutor_id , 'availability' , true );

		$next_end_date     = wp_date('Y-m-d' , strtotime($date.' +'.$num_days.' days'));
		$pervious_end_date = wp_date('Y-m-d' , strtotime($date.' -'.$num_days.' days'));

		ob_start();
		?>
		<h2 class="wccb_title"><?php echo __('Choose slots from availability of '.$tutor_info->display_name , PLUGIN_TEXT_DOMAIN);?></h2>
		<div style="overflow-x:auto;">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>
							<?php
							if (strtotime($date.' +0 days') > time()) {
								?>
								<span class="get_tutor_availability_calendar previous_date" data-action="get_tutor_availability_calendar" data-tutor_id="<?php echo $tutor_id;?>" data-date="<?php echo $pervious_end_date;?>" data-response_container=".tutor_availability_main_wrapper"></span>
								<?php
							}
							?>
						</th>
						<?php
						for ($i=0; $i < $num_days; $i++) {
							$value_date   = wp_date('Y-m-d' , strtotime($date.' +'.$i.' days'));
							$display_date = wp_date('D M j, Y' , strtotime($date.' +'.$i.' days'));
							if (strtotime($calendar_stop_date)<strtotime($value_date)) {
								continue;
							}
							?>
							<th><?php echo $display_date;?></th>
							<?php

							//Prepare slot table for each date
							$lower_key  = strtolower(date('l', strtotime($date.' +'.$i.' days')));
							if (empty($availability[$lower_key]['is_unavailable'])) {

								
								$slot_table = '<table>';

								foreach ( $availability[$lower_key]['available_time'] as $key => $value) {
									$start_time = $value['start_time'];
									$end_time   = $value['end_time'];

									for ($j=(int)$start_time; $j <(int)$end_time ; $j++) {
										$from  = $j % 12 ? $j % 12 : '12:00';
										$from .= $j >= 12 ? ' pm' : ' am';

										$to  = ($j+1) % 12 ? ($j+1) % 12 : '12:00';
										$to .= ($j+1) >= 12 ? ' pm' : ' am';

										$am_pm_date  = $value_date;
										$am_pm       = $from.' - '.$to;
										$am_pm_value = $am_pm_date.'|'.$am_pm;
										$rand_id     = str_replace(' ', '', $am_pm_date.$am_pm);
										if (WCCB_Frontend::date_wise_slot_availability_validation($tutor_id , $am_pm_date , $am_pm )) {
											$slot_picked = in_array($am_pm_value, $slot_picked_array) ? 'slot_picked' : '';
											$slot_table .= '<tr>
																<td>
																	<span class="slot '.$slot_picked.'" data-slot_date="'.$display_date.'" data-slot_time="'.$am_pm.'" data-slot_date_time="'.$am_pm_value.'" data-slot_picked_row_id="slot_picked_row_'.$rand_id.'" id="slot_span_id_'.$rand_id.'" data-reschedule="'.$_REQUEST['reschedule'].'">'.$am_pm.'</span>
																</td>
															</tr>';
										}
										else {
											$slot_table .= '<tr>
																<td class="slot_booked">'.$am_pm.' (Booked) </td>
															</tr>';
										}
										
									}
								}

								$slot_table .= '</table>';

								$slot_table_array[] = $slot_table;
							}
						}
						?>
						
						<th>
							<?php
							if (strtotime($calendar_stop_date) > strtotime($value_date)) {
								?>
								<span class="get_tutor_availability_calendar next_date" data-action="get_tutor_availability_calendar" data-tutor_id="<?php echo $tutor_id;?>" data-date="<?php echo $next_end_date;?>" data-response_container=".tutor_availability_main_wrapper"></span>
								<?php
							}
							?>
							
						</th>
					</tr>
				</thead>
				
				<tr>
					<td>&nbsp;</td>
					<?php
					for ($i=0; $i < $num_days; $i++) {
						$value_date = wp_date('Y-m-d' , strtotime($date.' +'.$i.' days'));
						if (strtotime($calendar_stop_date)<strtotime($value_date)) {
							continue;
						}
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
						$value_date = wp_date('Y-m-d' , strtotime($date.' +'.$i.' days'));
						if (strtotime($calendar_stop_date)<strtotime($value_date)) {
							continue;
						}
						?>
						<td class="inner_table">
							<?php
							echo $slot_table_array[$i];
							?>
						</td>
						<?php
					}
					?>
					<td>
						&nbsp;
					</td>
				</tr>
			</table>
		</div>
		<?php

		return ob_get_clean();
	}

	public static function get_slot_picked_row_html() {
		ob_start();
		?>
		<div class="slot_picked_row" id="{slot_picked_row_id}">
			<input type="hidden" name="slot[]" value="{slot_date_time_hidden}">
			<input type="hidden" name="unique_random_id[]" value="{unique_random_id}">
			<span class="slot_picked_date_time">
				{slot_date} , {slot_time}
			</span>
			<a href="#" class="delete_slot" data-slot_span_id="{slot_span_id}">Delete</a>
		</div>
		<?php

		return ob_get_clean();
	}

	public static function shop_page_product_title() {
		?>
		<h2 class="price-heading">
			<?php echo get_the_title();?>
		</h2>
		<?php
	}

	public static function shop_page_product_description() {
		?>
		<div class="price-content">
			<?php echo get_the_content();?>
		</div>
		<?php
	}

	public static function shop_page_product_price() {
		$product = wc_get_product(get_the_ID());
		?>
		<div class="price-hour">
			<?php echo $product->get_price_html(); ?> / <?php echo __('Hour' , PLUGIN_TEXT_DOMAIN);?>
		</div>
		<?php
	}

	public static function shop_page_add_to_cart_button( $button , $product , $args ) {

		if ( $product->product_type == "wccb_course" ) {
		    $simpleURL   = get_permalink();
		    $simpleLabel = 'Book Your Package';
		} else {
		    $simpleURL =  $product->add_to_cart_url();  
		    $simpleLabel = $product->add_to_cart_text();
		};

		$button = sprintf(
			'<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
			esc_url( $simpleURL ),
			esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
			esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
			isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
			esc_html( $simpleLabel )
		);

		return $button;
	}
}
?>