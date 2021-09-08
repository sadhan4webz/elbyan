<?php
defined( 'ABSPATH' ) || die();
class WCCB_Frontend_Myaccount_View {

	public static function render_my_account_availability_content() {
		$availability = get_user_meta(get_current_user_id() , 'availability' , true );
		?>
		<form class="woocommerce-EditAccountForm edit-account" action="" method="post" >
			<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide availability_container">
				
				<label><b><?php esc_html_e( 'Availability Settings', PLUGIN_TEXT_DOMAIN ); ?></b>&nbsp;<span class="required">*</span></label>
				<table>
					<thead>
						<tr>
							<th>
								<?php echo __('Day' , PLUGIN_TEXT_DOMAIN);?>
							</th>
							<th>
								<?php echo __('Start Time' , PLUGIN_TEXT_DOMAIN);?>
							</th>
							<th>
								<?php echo __('End Time' , PLUGIN_TEXT_DOMAIN);?>
							</th>
							<th>
								<?php __('Is Unavailable?' , PLUGIN_TEXT_DOMAIN);?>
							</th>
						</tr>
					</thead>
					
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

	public static function render_my_account_bookings_content() {
		global $wpdb;
		$table_name = $wpdb->prefix.'booking_history';
		?>
		<div class="my_booking_main_wrapper">
			<form id="my_booking_form" method="post">
				<?php
				if(array_key_exists('administrator' , get_user_meta(get_current_user_id() , 'wp_capabilities' , true ) )) {
					$args   = array(
						'role__in' => array('wccb_tutor')
					);
					$student = get_users( $args );
					?>
					<div class="field-group">
						<label><?php echo __('Select Tutor', PLUGIN_TEXT_DOMAIN);?></label>
						<select class="select" name="tutor_id" onchange="this.form.submit();">
							<option value="">Select</option>
							<?php
				        	foreach ($student as $row) {
				        		?>
				        		<option value="<?php echo $row->ID;?>" <?php if($_POST['tutor_id'] == $row->ID){ $tutor_id = $_POST['tutor_id'];?> selected="selected" <?php }?>><?php echo $row->display_name;?></option>
				        		<?php
				        	}
				        	?>
						</select>
					</div>
					<?php
					
				}
				else {
					$tutor_id = get_current_user_id();
				}

				echo WCCB_Frontend_Myaccount_View::get_my_booking_list( $tutor_id );
				?>
			</form>
		</div>
		<?php
	}

	public static function get_my_booking_list( $tutor_id ) {
		ob_start();
		global $wpdb;
		$table_name = $wpdb->prefix.'booking_history';
		?>
		<div class="booking_list_wrapper">
			<?php
			if (empty($tutor_id)) {
				?>
				<h3><?php echo __('Select tutor to view his/her bookings' , PLUGIN_TEXT_DOMAIN); ?></h3>
				<?php
			}
			else {
				$tutor = get_userdata($tutor_id);
				?>
				<div id="tabs">
				  <ul>
				    <li><a href="#tabs-1"><?php echo __('Upcoming Clases' , PLUGIN_TEXT_DOMAIN);?></a></li>
				    <li><a href="#tabs-2"><?php echo __('Past Classes' , PLUGIN_TEXT_DOMAIN);?></a></li>
				  </ul>
				  <div id="tabs-1">
				    <h3><?php echo __('List of Upcoming Classes of '.$tutor->display_name , PLUGIN_TEXT_DOMAIN);?></h3>
				
					<table class="table table-bordered" width="100%" border="1">
						<thead>
							<tr>
								<th>
									<?php echo __('SI. NO.' , PLUGIN_TEXT_DOMAIN);?>
								</th>
								<th>
									<?php echo __('Class' , PLUGIN_TEXT_DOMAIN);?>
								</th>
								<th>
									<?php echo __('Slot Time' , PLUGIN_TEXT_DOMAIN);?>
								</th>
								<th>
									<?php echo __('Student Name' , PLUGIN_TEXT_DOMAIN);?>
								</th>
								<th>
									<?php echo __('Actions' , PLUGIN_TEXT_DOMAIN);?>
								</th>
							</tr>
						</thead>
						
						<?php
						$query         = "SELECT * FROM $table_name WHERE tutor_id='".$tutor_id."' and class_date >= '".date('Y-m-d')."' and status != 'Cancelled'";
						$results       = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.
						if (count($results)>0) {
							foreach ($results as $key => $value) {
								$user = get_userdata( $value['user_id'] ); 
								?>
								<tr>
									<td>
										<?php echo $key+1;?>
									</td>
									<td>
										<?php echo get_the_title( $value['product_id'] );;?>
									</td>
									<td>
										<?php echo WCCB_Helper::display_date($value['class_date']).', '.$value['class_time'];?>
										
									</td>
									<td>
										<?php echo $user->display_name;?>
									</td>
									<td>
										<a href="#" class="cancel_booking" data-booking_id="<?php echo $value['ID'];?>" data-cancel_booking_url_nonce="<?php echo wp_create_nonce('cancel_booking_url_nonce');?>"><?php echo __('Cancel', PLUGIN_TEXT_DOMAIN);?></a>
									</td>
								</tr>
								<?php
							}
						}
						else {
							?>
							<tr>
								<td colspan="6">
									<?php echo __('No class found.',PLUGIN_TEXT_DOMAIN);?>
								</td>
							</tr>
							<?php
						}
						?>
					</table>
				
				  </div>
				  <div id="tabs-2">
				  	<h3><?php echo __('List of Past Classes' , PLUGIN_TEXT_DOMAIN) ;?></h3>
				  	<table class="table table-bordered" width="100%" border="1">
				  		<thead>
				  			<tr>
								<th>
									<?php echo __('SI. NO.' , PLUGIN_TEXT_DOMAIN);?>
								</th>
								<th>
									<?php echo __('Class' , PLUGIN_TEXT_DOMAIN);?>
								</th>
								<th>
									<?php echo __('Slot Time' , PLUGIN_TEXT_DOMAIN);?>
								</th>
								<th>
									<?php echo __('Student Name' , PLUGIN_TEXT_DOMAIN);?>
								</th>
								
							</tr>
				  		</thead>
						
						<?php
						$query         = "SELECT * FROM $table_name WHERE tutor_id='".$tutor_id."' and class_date < '".date('Y-m-d')."'";
						$results       = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.
						if (count($results)>0) {
							foreach ($results as $key => $value) {
								$user = get_userdata( $value['user_id'] ); 
								?>
								<tr>
									<td>
										<?php echo $key+1;?>
									</td>
									<td>
										<?php echo get_the_title( $value['product_id'] );;?>
									</td>
									<td>
										<?php echo WCCB_Helper::display_date($value['class_date']).', '.$value['class_time'];?>
										
									</td>
									<td>
										<?php echo $user->display_name;?>
									</td>
									
								</tr>
								<?php
							}
						}
						else {
							?>
							<tr>
								<td colspan="6">
									<?php echo __('No class found.',PLUGIN_TEXT_DOMAIN);?>
								</td>
							</tr>
							<?php
						}
						?>
					</table>
				  </div>
				  
				</div>
				<?php
			}
			?>
		</div>
		<?php

		return ob_get_clean();
	}

	public static function render_my_account_classes_content() {
		global $wpdb;
		$table_name    = $wpdb->prefix.'booking_history';
		$show_table    = true;

		if ($_REQUEST['reschedule'] == 'yes' && !empty($_REQUEST['booking_id'])) {
			if (wp_verify_nonce( $_REQUEST['reschedule_booking_url_nonce'], 'reschedule_booking_url_nonce' )) {
				$show_table = false;
				$query      = "select * from $table_name where ID='".$_REQUEST['booking_id']."'";
				$booking    = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok

				if (count($booking) >0 ) {
					if ($booking[0]['user_id'] == get_current_user_id() || array_key_exists('administrator' , get_user_meta(get_current_user_id() , 'wp_capabilities' , true ) )) {
						?>
						<div class="my_classes_main_wrapper">
							<div class="title_wrapper">
								<h2><?php echo __('Reschedule Class' , PLUGIN_TEXT_DOMAIN );?></h2>
							</div>
							
							<form id="my_classes_form" class="wccb_form" method="post">
								<input type="hidden" name="booking_id" value="<?php echo $booking[0]['ID'];?>">
								<input type="hidden" name="tutor_id" value="<?php echo $booking[0]['tutor_id'];?>">
								<input type="hidden" name="action_do" value="reschedule">
								<?php wp_nonce_field( 'save_reschedule', 'save_reschedule_nonce_field' ); ?>
								<div class="field-group">
									<label><?php echo __('Class Name' , PLUGIN_TEXT_DOMAIN);?></label>
									<span><?php echo get_the_title($booking[0]['product_id']);?></span>
								</div>
								<div class="field-group">
									<label><?php echo __('Class Date & Time' , PLUGIN_TEXT_DOMAIN);?></label>
									<span><?php echo WCCB_Helper::display_date( $booking[0]['class_date']).', '.$booking[0]['class_time'];?></span>
								</div>
								<div class="slot_selected_container"></div>
								<div class="tutor_availability_main_wrapper">
									<?php 
									echo WCCB_Frontend_View::get_tutor_availability_calendar( $booking[0]['tutor_id'] , date('Y-m-d') , NUM_DAYS_CALENDAR , WCCB_Frontend::get_tutor_future_booking( $booking[0]['tutor_id'] ) );
									?>
								</div>
								<div class="field-group">
									<button type="submit" name="save_reschedule" class="woocommerce-Button button">Save Changes</button>
								</div>
							</form>
						</div>
						<?php
					}
					else {
						echo __( '<p style="color:red;">Unauthorize access</p>' , PLUGIN_TEXT_DOMAIN );
					}
					
				}
				else {
					echo __( '<p style="color:red;">Booking ID not exist</p>' , PLUGIN_TEXT_DOMAIN );
				}
				
			}
			else {
				echo __( '<p style="color:red;">Unauthorize access</p>' , PLUGIN_TEXT_DOMAIN );
			}
		}
		
		if($show_table) {
			?>
			<div class="my_classes_main_wrapper">
				<form id="my_classes_form" method="post">
					<?php
					if(array_key_exists('administrator' , get_user_meta(get_current_user_id() , 'wp_capabilities' , true ) )) {
						$args   = array(
							'role__in' => array('wccb_student')
						);
						$student = get_users( $args );
						?>
						<div class="field-group">
							<label><?php echo __('Select Student', PLUGIN_TEXT_DOMAIN);?></label>
							<select class="select" name="user_id" onchange="this.form.submit();">
								<option value="">Select</option>
								<?php
					        	foreach ($student as $row) {
					        		?>
					        		<option value="<?php echo $row->ID;?>" <?php if($_POST['user_id'] == $row->ID){ $user_id = $_POST['user_id'];?> selected="selected" <?php }?>><?php echo $row->display_name;?></option>
					        		<?php
					        	}
					        	?>
							</select>
						</div>
						<?php
						
					}
					else {
						$user_id = get_current_user_id();
					}

					echo WCCB_Frontend_Myaccount_View::get_my_class_list( $user_id );
					?>
					
				</form>
			</div>
			<?php
		}
	}

	public static function get_my_class_list( $user_id ) {
		ob_start();
		global $wpdb;
		$table_name = $wpdb->prefix.'booking_history';
		?>
		<div class="class_list_wrapper">
			<?php
			if (empty($user_id)) {
				?>
				<h3><?php echo __('Select student to view his/her class list' , PLUGIN_TEXT_DOMAIN);?></h3>
				<?php
			}
			else {
				$user = get_userdata( $user_id);
				?>
				<div id="tabs">
				  <ul>
				    <li><a href="#tabs-1"><?php echo __('Upcoming Clases' , PLUGIN_TEXT_DOMAIN);?></a></li>
				    <li><a href="#tabs-2"><?php echo __('Past Classes',PLUGIN_TEXT_DOMAIN);?></a></li>
				  </ul>
				  <div id="tabs-1">
				    <h3><?php echo __('List of Upcoming Classes of '.$user->display_name , PLUGIN_TEXT_DOMAIN);?></h3>
				
					<table class="table table-bordered" width="100%" border="1">
						<thead>
							<tr>
								<th>
									<?php echo __('SI. NO.' , PLUGIN_TEXT_DOMAIN);?>
								</th>
								<th>
									<?php echo __('Class' , PLUGIN_TEXT_DOMAIN);?>
								</th>
								<th>
									<?php echo __('Slot Time' , PLUGIN_TEXT_DOMAIN);?>
								</th>
								<th>
									<?php echo __('Tutor Name' , PLUGIN_TEXT_DOMAIN);?>
								</th>
								<th>
									<?php echo __('Actions', PLUGIN_TEXT_DOMAIN);?>
								</th>
								
							</tr>
						</thead>
						
						<?php
						$query         = "SELECT * FROM $table_name WHERE user_id='".$user_id."' and class_date >= '".date('Y-m-d')."' and status != 'Cancelled' ";
						$results       = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.
						if (count($results)>0) {
							foreach ($results as $key => $value) {
								$tutor = get_userdata( $value['tutor_id'] ); 
								?>
								<tr>
									<td>
										<?php echo $key+1;?>
									</td>
									<td>
										<a href="<?php echo get_permalink($value['product_id']);?>">
											<?php echo get_the_title( $value['product_id'] );?>
										</a>
										
									</td>
									<td>
										<?php echo wp_date('D M j, Y' , strtotime($value['class_date'])).', '.$value['class_time'];?>
										
									</td>
									<td>
										<?php echo $tutor->display_name;?>
									</td>
									
									<td>
										<a href="?reschedule=yes&booking_id=<?php echo $value['ID'];?>&reschedule_booking_url_nonce=<?php echo wp_create_nonce('reschedule_booking_url_nonce');?>">Reshedule</a> |
										<a href="#" class="cancel_booking" data-booking_id="<?php echo $value['ID'];?>" data-cancel_booking_url_nonce="<?php echo wp_create_nonce('cancel_booking_url_nonce');?>">Cancel</a>
									</td>
								</tr>
								<?php
							}
						}
						else {
							?>
							<tr>
								<td colspan="6">
									<?php echo __('No class found.',PLUGIN_TEXT_DOMAIN);?>
								</td>
							</tr>
							<?php
						}
						?>
					</table>
				
				  </div>
				  <div id="tabs-2">
				  	<h3><?php echo __('List of Past Classes of '.$user->display_name , PLUGIN_TEXT_DOMAIN);?></h3>
				  	<table class="table table-bordered" width="100%" border="1">
				  		<thead>
				  			<tr>
								<th>
									<?php echo __('SI. NO.' , PLUGIN_TEXT_DOMAIN);?>
								</th>
								<th>
									<?php echo __('Class' , PLUGIN_TEXT_DOMAIN);?>
								</th>
								<th>
									<?php echo __('Slot Time' , PLUGIN_TEXT_DOMAIN);?>
								</th>
								<th>
									<?php echo __('Tutor Name' , PLUGIN_TEXT_DOMAIN);?>
								</th>
								
								
							</tr>
				  		</thead>
						
						<?php
						$query         = "SELECT * FROM $table_name WHERE user_id='".$user_id."' and class_date < '".date('Y-m-d')."' and status != 'Cancelled' ";
						$results       = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.
						if (count($results)>0) {
							foreach ($results as $key => $value) {
								$tutor = get_userdata( $value['tutor_id'] ); 
								?>
								<tr>
									<td>
										<?php echo $key+1;?>
									</td>
									<td>
										<a href="<?php echo get_permalink($value['product_id']);?>">
											<?php echo get_the_title( $value['product_id'] );?>
										</a>
											
										
									</td>
									<td>
										<?php echo wp_date('D M j, Y' , strtotime($value['class_date'])).', '.$value['class_time'];?>
										
									</td>
									<td>
										<?php echo $tutor->display_name;?>
									</td>
									
									
								</tr>
								<?php
							}
						}
						else {
							?>
							<tr>
								<td colspan="6">
									<?php echo __('No class found.',PLUGIN_TEXT_DOMAIN);?>
								</td>
							</tr>
							<?php
						}
						?>
					</table>
				  </div>
				  
				</div>
				<?php
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_my_account_hours_content() {
		global $wpdb;
		$table_name    = $wpdb->prefix.'hour_history';
		?>
		<div class="my_hours_main_wrapper">
			<form id="my_hours_form" method="post">

				<?php
				if(array_key_exists('administrator' , get_user_meta(get_current_user_id() , 'wp_capabilities' , true ) )) {
					$args   = array(
						'role__in' => array('wccb_student')
					);
					$student = get_users( $args );
					?>
					<div class="field-group">
						<label><?php echo __('Select Student', PLUGIN_TEXT_DOMAIN);?></label>
						<select class="select" name="user_id" onchange="this.form.submit();">
							<option value="">Select</option>
							<?php
				        	foreach ($student as $row) {
				        		?>
				        		<option value="<?php echo $row->ID;?>" <?php if($_POST['user_id'] == $row->ID){ $user_id = $_POST['user_id'];?> selected="selected" <?php }?>><?php echo $row->display_name;?></option>
				        		<?php
				        	}
				        	?>
						</select>
					</div>
					<?php
					
				}
				else {
					$user_id = get_current_user_id();
				}

				echo WCCB_Frontend_Myaccount_View::get_my_hour_list( $user_id );
				?>
			</form>
		</div>
		<?php
	}

	public static function get_my_hour_list( $user_id ) {
		ob_start();
		global $wpdb;
		$table_name = $wpdb->prefix.'hour_history';

		if (empty($user_id)) {
			?>
			<h3><?php echo __('Select student to view his/her hour list', PLUGIN_TEXT_DOMAIN);?></h3>
			<?php
		}
		else {
			$user = get_userdata($user_id);
			?>
			<h3><?php echo __('List of Purchased Hours of '.$user->display_name , PLUGIN_TEXT_DOMAIN);?></h3>
		  	<table class="table table-bordered" width="100%" border="1">
		  		<thead>
		  			<tr>
						<th>
							<?php echo __('SI. NO.' , PLUGIN_TEXT_DOMAIN);?>
						</th>
						<th>
							<?php echo __('Date Purchased' , PLUGIN_TEXT_DOMAIN);?>
						</th>
						<th>
							<?php echo __('Purchased Hours' , PLUGIN_TEXT_DOMAIN);?>
						</th>
						<th>
							<?php echo __('Used Hours' , PLUGIN_TEXT_DOMAIN);?>
						</th>
						<th>
							<?php echo __('Expired Hours', PLUGIN_TEXT_DOMAIN);?>
						</th>
						<th>
							<?php echo __('Expiry Date', PLUGIN_TEXT_DOMAIN);?>
						</th>
						
					</tr>
		  		</thead>
				
				<?php
				$query         = "SELECT * FROM $table_name WHERE user_id='".$user_id."'";
				$results       = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.
				if (count($results)>0) {
					foreach ($results as $key => $value) {
						$expiry_date    = '';
						$remaining_hour = $value['purchased_hours'] - $value['used_hours'];
						if ($remaining_hour > 0) {
							$expiry_date = wp_date('d-m-Y', strtotime('+'.HOUR_EXPIRE_DAYS.' days' , strtotime($value['date_purchased'])));
						}
						?>
						<tr>
							<td>
								<?php echo $key+1;?>
							</td>
							<td>
								<?php echo wp_date('d-m-Y', strtotime($value['date_purchased']));?>
							</td>
							<td>
								<?php echo (int)$value['purchased_hours'];?>
							</td>
							<td>
								<?php echo (int)$value['used_hours'];?>
							</td>
							<td>
								<?php echo (int)$value['expired_hours'];?>
							</td>
							<td>
								<?php echo !empty($expiry_date) ? $expiry_date : '';?>
							</td>
						</tr>
						<?php
					}
				}
				else {
					?>
					<tr>
						<td colspan="6">
							<?php echo __('No hours found.' , PLUGIN_TEXT_DOMAIN);?>
						</td>
					</tr>
					<?php
				}
				?>
			</table>
			<?php
		}

		return ob_get_clean();
	}

}