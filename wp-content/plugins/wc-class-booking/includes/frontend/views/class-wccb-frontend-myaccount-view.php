<?php
defined( 'ABSPATH' ) || die();
class WCCB_Frontend_Myaccount_View {

	public static function show_welcome_text() {
		$user = get_userdata( get_current_user_id() );
		$role_key = $user->roles[0];

		switch ($role_key) {
			case 'wccb_tutor':
				$role_name = 'Tutor';
				break;

			case 'wccb_student':
				$role_name = 'Student';
				break;

			case 'administrator':
				$role_name = 'Administrator';
				break;
		}
		$welcome_text = __('Hello' , WC_CLASS_BOOKING_TEXT_DOMAIN ).' '.wccb_user_get_display_name( $user ).' ('.$role_name.')';
		?>
		<div class="welcome_text_wrapper">
			<?php echo __($welcome_text , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
		</div>  
		<?php
	}

	public static function show_dashboard_content() {
		$user = get_userdata( get_current_user_id() );

		if (in_array('administrator', $user->roles)) {
			$total_users = count_users();
			$num_tutor   = $total_users['avail_roles']['wccb_tutor'];
			$num_student = $total_users['avail_roles']['wccb_student'];
			?>
			<div class="user_count_wrapper">
				<h3><?php echo __('User Statistics' , WC_CLASS_BOOKING_TEXT_DOMAIN);?> </h3>
				<div class="tutor_count_wrapper">
					<label><?php echo __('Total Tutor' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></label>
					<span><?php echo $num_tutor;?></span>
				</div>
				<div class="student_count_wrapper">
					<label><?php echo __('Total Student' , WC_CLASS_BOOKING_TEXT_DOMAIN );?></label>
					<span><?php echo $num_student;?></span>
				</div>
			</div>
			<?php
		}
	}

	public static function show_edit_profile_content() {
	    $user_id       = get_current_user_id();
	    $attachment_id = get_user_meta( $user_id , 'profile_image', true );
	    $mobile_no     = get_user_meta( $user_id , 'mobile_no' , true);

	    ?>
	    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="mobile_no"><?php esc_html_e( 'Mobile no.', WC_CLASS_BOOKING_TEXT_DOMAIN ); ?></label>
			<input type="number" name="mobile_no" class="woocommerce-Input woocommerce-Input--phone input-text" placeholder="Enter mobile no." value="<?php echo $mobile_no;?>">
		</p>
	    <?php
	    if ( $attachment_id ) {
	    	?>
	    	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
	    		<?php echo wp_get_attachment_image( $attachment_id, 'thumbnail');?>
	        </p>
	        <div class="clear"></div>
	        <?php
	    }
		?>		
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="profile_image"><?php esc_html_e( 'Profile Image (JPEG / PNG)', WC_CLASS_BOOKING_TEXT_DOMAIN ); ?></label>
			<input type="file" name="profile_image" accept="image/x-png,image/gif,image/jpeg">
		</p>
		<?php
	}

	public static function render_my_account_availability_content() {
		$availability = get_user_meta(get_current_user_id() , 'availability' , true );
		?>
		<form class="woocommerce-EditAccountForm edit-account" action="" method="post" >
			<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide availability_container">
				
				<label><b><?php esc_html_e( 'Availability Settings', WC_CLASS_BOOKING_TEXT_DOMAIN ); ?></b>&nbsp;<span class="required">*</span></label>
				<?php echo WCCB_Frontend_View::get_tutor_availability_time_fields($availability);?>
				
				
			</div>

			<p>
				<?php wp_nonce_field( 'save_tutor_availability', 'save-tutor-availability-nonce' ); ?>
				<button type="submit" class="woocommerce-Button button" name="save_availability" value="<?php esc_attr_e( 'Save changes', WC_CLASS_BOOKING_TEXT_DOMAIN ); ?>"><?php esc_html_e( 'Save changes', WC_CLASS_BOOKING_TEXT_DOMAIN ); ?></button>
				<input type="hidden" name="action" value="save_availability" />
			</p>

		</form>
		<?php
	}

	public static function render_my_account_bookings_content() {
		global $wpdb;
		$table_name      = $wpdb->prefix.'booking_history';
		$table_name_meta = $wpdb->prefix.'booking_history_meta';
		$show_table      = true;
		if ($_REQUEST['show_notes'] == 'yes' && !empty($_REQUEST['booking_id'])) {
			if (wp_verify_nonce( $_REQUEST['notes_url_nonce'], 'notes_url_nonce' )) {
				$show_table = false;
				$query      = "select * from $table_name where ID='".$_REQUEST['booking_id']."'";
				$booking    = $wpdb->get_results( $query ); // db call ok. no cache ok

				$query      = "select * from $table_name_meta where booking_id='".$_REQUEST['booking_id']."' and meta_key='notes'";
				$notes      = $wpdb->get_results( $query ); // db call ok. no cache ok

				if (count($booking) >0 ) {
					$user            = get_userdata( get_current_user_id() );
					$role_key        = $user->roles[0];
					if ($role_key    != 'wccb_student') {

						$student = get_userdata($booking[0]->user_id);
						$tutor   = get_userdata($booking[0]->tutor_id);
						?>
						<div class="my_classes_main_wrapper">
							<div class="title_wrapper">
								<h2><?php echo __('Notes for Class' , WC_CLASS_BOOKING_TEXT_DOMAIN );?></h2>
							</div>
							
							<form id="my_notes_form" class="woocommerce-EditAccountForm my_notes_form" method="post">
								<input type="hidden" name="booking_id" value="<?php echo $booking[0]->ID;?>">
								<input type="hidden" name="ID" value="<?php echo $notes[0]->ID;?>">
								<input type="hidden" name="action_do" value="save_notes">
								<?php wp_nonce_field( 'save_notes', 'save_notes_nonce_field' ); ?>
								<div class="field-group">
									<label><?php echo __('Class Name' , WC_CLASS_BOOKING_TEXT_DOMAIN);?> :</label>
									<span>
										<a href="<?php echo get_permalink($booking[0]->product_id);?>">
											<?php echo get_the_title($booking[0]->product_id);?>
												
										</a>
									</span>
								</div>
								<div class="field-group">
									<label><?php echo __('Class Date & Time' , WC_CLASS_BOOKING_TEXT_DOMAIN);?> :</label>
									<span><?php echo WCCB_Helper::display_date( $booking[0]->class_date).', '.$booking[0]->class_time;?></span>
								</div>
								<div class="field-group">
									<label><?php echo __('Student Name' , WC_CLASS_BOOKING_TEXT_DOMAIN);?> :</label>
									<span><?php echo wccb_user_get_display_name($student);?></span>
								</div>
								<div class="field-group">
									<label><?php echo __('Tutor Name' , WC_CLASS_BOOKING_TEXT_DOMAIN);?> :</label>
									<span><?php echo wccb_user_get_display_name($tutor);?></span>
								</div>
								<div class="slot_selected_container"></div>
								<div class="tutor_availability_main_wrapper">
									<label><?php echo __('Notes' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></label>
									<textarea name="notes" rows="5" cols="20"><?php echo $notes[0]->meta_value;?></textarea>
								</div>
								<div class="field-group">
									<button type="submit" name="save_notes" class="woocommerce-Button button">Save Changes</button>
								</div>
							</form>
						</div>
						<?php
					}
					else {
						 wc_add_notice( __( 'Unauthorize access' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error');
					}
					
				}
				else {
					wc_add_notice(__( 'Booking ID not exist' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error');
				}
				
			}
			else {
				wc_add_notice(__( 'Unauthorize access' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error');
			}
		}
		if ($show_table) {
			?>
			<div class="my_booking_main_wrapper">
				<form id="my_booking_form" class="woocommerce-EditAccountForm my_booking_form" method="get">
					<?php
					if(array_key_exists('administrator' , get_user_meta(get_current_user_id() , 'wp_capabilities' , true ) )) {
						$args   = array(
							'role__in' => array('wccb_tutor')
						);
						$tutor = get_users( $args );
						?>
						<div class="title_wrapper">
							<h2><?php echo __('Select tutor to view his/her bookings' , WC_CLASS_BOOKING_TEXT_DOMAIN); ?></h2>
						</div>
						<div class="field-group">
							<label><?php echo __('Select Tutor', WC_CLASS_BOOKING_TEXT_DOMAIN);?></label>
							<select class="select" name="tutor_id" onchange="this.form.submit();">
								<option value="">Select</option>
								<?php
					        	foreach ($tutor as $row) {
					        		?>
					        		<option value="<?php echo $row->ID;?>" <?php if($_REQUEST['tutor_id'] == $row->ID){ $tutor_id = $_REQUEST['tutor_id'];?> selected="selected" <?php }?>><?php echo wccb_user_get_display_name($row);?></option>
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
	}

	public static function get_my_booking_list( $tutor_id ) {
		if (empty($tutor_id)) {
			return;
		}

		ob_start();
		global $wpdb;
		$table_name      = $wpdb->prefix.'booking_history';
		$table_name_meta = $wpdb->prefix.'booking_history_meta';
		$tutor = get_userdata($tutor_id);

		wp_enqueue_script( 'thickbox' );
 
		?>
		<div class="booking_list_wrapper">
			<div id="tabs">
			  <ul>
			    <li><a href="#tabs-1"><?php echo __('Past Classes' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></a></li>
			    <li><a href="#tabs-2"><?php echo __('Upcoming Classes' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></a></li>
			  </ul>
			  <div id="tabs-1">

			  	<div class="date_filter_wrapper">
			  		<div class="from_date_wrapper">
			  			<label><?php echo __('From Date', WC_CLASS_BOOKING_TEXT_DOMAIN);?> :</label>
			  			<input type="text" name="start_date" id="start_date" readonly="readonly" class="date_picker" value="<?php echo !empty($_REQUEST['start_date']) ? $_REQUEST['start_date'] : '';?>">
			  		</div>
			  		<div class="from_date_wrapper">
			  			<label><?php echo __('To Date' , WC_CLASS_BOOKING_TEXT_DOMAIN);?> :</label>
			  			<input type="text" name="end_date" id="end_date" readonly="readonly" class="date_picker" value="<?php echo !empty($_REQUEST['end_date']) ? $_REQUEST['end_date'] : '';?>">
			  		</div>
			  		<button type="submit" name="search_booking"><?php echo __('Search' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></button>
			  		<button type="button" id="reset_search"><?php echo __('Reset', WC_CLASS_BOOKING_TEXT_DOMAIN ) ;?></button>
			  	</div>

			    <h3><?php echo __('List of Past Classes of '.wccb_user_get_display_name($tutor), WC_CLASS_BOOKING_TEXT_DOMAIN);?></h3>


				<table class="table table-bordered" width="100%" border="1">
			  		<thead>
			  			<tr>
							<th>
								<?php echo __('SI. NO.' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</th>
							<th>
								<?php echo __('Class' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</th>
							<th>
								<?php echo __('Slot Time' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
								<?php echo WCCB_Helper::help_tip(WC_CLASS_BOOKING_TIMEZONE_MSG);?>
							</th>
							<th>
								<?php echo __('Student Name' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</th>
							<th>
								<?php echo __('Delivery Status' , WC_CLASS_BOOKING_TEXT_DOMAIN );?>
							</th>
							<th>
								<?php echo __('Actions' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</th>
							
						</tr>
			  		</thead>
					
					<?php
					if (!empty($_REQUEST['start_date']) && !empty($_REQUEST['end_date'])) {
						$query         = "SELECT * FROM $table_name WHERE tutor_id='".$tutor_id."' and class_date between '".wp_date('Y-m-d', strtotime($_REQUEST['start_date']))."' and '".wp_date('Y-m-d', strtotime($_REQUEST['end_date']))."' and status!='Cancelled' order by class_date desc";
					}
					else {
						$query         = "SELECT * FROM $table_name WHERE tutor_id='".$tutor_id."' and class_date <= '".wp_date('Y-m-d')."' and status='Completed' order by class_date desc";
					}
					
					$results       = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.
					if (count($results)>0) {
						foreach ($results as $key => $value) {
							$user = get_userdata( $value['user_id'] );

							$query2     = "select * from $table_name_meta where booking_id='".$value['ID']."' and meta_key='notes'";
							$notes      = $wpdb->get_results( $query2 ); // db call ok. no cache ok 
							?>
							<tr>
								<td>
									<?php echo $key+1;?>
								</td>
								<td>
									<a href="<?php echo get_permalink($value['product_id']);?>">
										<?php echo get_the_title( $value['product_id'] );?>
									</a>

									<?php
									if (!empty($notes[0]->meta_value)) {
										echo WCCB_Helper::help_tip($notes[0]->meta_value);
									}
									else {
										echo WCCB_Helper::help_tip('No notes found.');
									}
									?>
								</td>
								<td>
									<?php echo WCCB_Helper::display_date($value['class_date']).', '.$value['class_time'];?>
									
								</td>
								<td>
									<?php echo wccb_user_get_display_name($user);?>
								</td>
								<td id="delivery_status_column_<?php echo $value['ID'];?>">
									<?php echo $value['delivery_status'];?>
								</td>
								<td>
									<?php
									if (current_user_can( 'manage_options' )) {
										?>
										<a href="#TB_inline?&width=550&height=350&inlineId=change_class_status_thikbox_<?php echo $value['ID'];?>" title="<?php echo __( 'Change Class Status', WC_CLASS_BOOKING_TEXT_DOMAIN ); ?>" class="change_class_status_link thickbox"    >
									 	<?php echo __('Change Status' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
									 	</a> |

									 	<!-- Thinkbox HTML -->
										<div class="change_class_status_thikbox" id="change_class_status_thikbox_<?php echo $value['ID'];?>" style="display:none">
											<div class="change_class_status_wrapper">

												<div id="message_container_<?php echo $value['ID'];?>">
													
												</div>
												
												<div class="field-group">
													<label><?php echo __('Class Name', WC_CLASS_BOOKING_TEXT_DOMAIN);?></label> :
													<span class="class_name"><?php echo get_the_title( $value['product_id'] );?></span>
												</div>
												<div class="field-group">
													<label><?php echo __('Stuent Name', WC_CLASS_BOOKING_TEXT_DOMAIN);?></label> :
													<span class="student_name"><?php echo wccb_user_get_display_name($user);?></span>
												</div>
												<div class="field-group">
													<label><?php echo __('Class Date & Time', WC_CLASS_BOOKING_TEXT_DOMAIN);?></label> :
													<span class="class_date_time"><?php echo WCCB_Helper::display_date($value['class_date']).', '.$value['class_time'];?></span>
												</div>
												<div class="field-group">
													<label><?php echo __('Class Status', WC_CLASS_BOOKING_TEXT_DOMAIN);?></label> :
													<select class="select" name="delivery_status_<?php echo $value['ID'];?>">
														<option value="Pending" <?php selected('Pending',$value['delivery_status']);?>><?php echo __('Pending' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></option>
														<option value="Student did not attend" <?php selected('Student did not attend',$value['delivery_status']);?>><?php echo __('Stuent did not attend' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></option>
														<option value="Delivered" <?php selected('Delivered',$value['delivery_status']);?>><?php echo __('Delivered' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></option>
													</select>
												</div>

												<div class="field-group button_wrapper">
													<button type="submit" name="change_class_status_btn" class="woocommerce-Button button change_class_status_btn" data-booking_id="<?php echo $value['ID'];?>">
														<?php echo __('Submit' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
													</button>
												</div>
												
											</div>
										</div>
										<!-- Thikbox HTML end -->
									 	<?php
									}
									?>
									<a href="?show_notes=yes&booking_id=<?php echo $value['ID'];?>&notes_url_nonce=<?php echo wp_create_nonce('notes_url_nonce');?>"><?php echo __('Notes' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></a>
									 
								</td>
								
							</tr>
							<?php
						}

						?>
						<tr>
							<th colspan="3" align="right">
								<?php echo __('Total Class Completed' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</th>
							<td>
								<?php echo count($results);?>
							</td>
						</tr>
						<?php
					}
					else {
						?>
						<tr>
							<td colspan="6">
								<?php echo __('No class found.',WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</td>
						</tr>
						<?php
					}
					?>
				</table>
				
			
			  </div>
			  <div id="tabs-2">

			  	<h3><?php echo __('List of Upcoming Classes of '.wccb_user_get_display_name($tutor) , WC_CLASS_BOOKING_TEXT_DOMAIN) ;?></h3>

			  	<table class="table table-bordered" width="100%" border="1">
					<thead>
						<tr>
							<th>
								<?php echo __('SI. NO.' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</th>
							<th>
								<?php echo __('Class' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</th>
							<th>
								<?php echo __('Slot Time' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
								<?php echo WCCB_Helper::help_tip(WC_CLASS_BOOKING_TIMEZONE_MSG);?>
							</th>
							<th>
								<?php echo __('Student Name' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</th>
							<th>
								<?php echo __('Actions' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</th>
						</tr>
					</thead>
					
					<?php
					$query         = "SELECT * FROM $table_name WHERE tutor_id='".$tutor_id."' and class_date >= '".wp_date('Y-m-d')."' and status = 'Upcoming' order by ID desc";
					$results       = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.
					if (count($results)>0) {
						foreach ($results as $key => $value) {
							$user = get_userdata( $value['user_id'] );
							$query2     = "select * from $table_name_meta where booking_id='".$value['ID']."' and meta_key='notes'";

							$notes      = $wpdb->get_results( $query2 ); // db call ok. no cache ok 
							?>
							<tr>
								<td>
									<?php echo $key+1;?>
								</td>
								<td>
									<a href="<?php echo get_permalink($value['product_id']);?>">
										<?php echo get_the_title( $value['product_id'] );;?>
									</a>
									<?php
									if (!empty($notes[0]->meta_value)) {
										echo WCCB_Helper::help_tip($notes[0]->meta_value);
									}
									else {
										echo WCCB_Helper::help_tip('No notes found');
									}
									?>
								</td>
								<td>
									<?php echo WCCB_Helper::display_date($value['class_date']).', '.$value['class_time'];?>
									
								</td>
								<td>
									<?php echo wccb_user_get_display_name($user);?>
								</td>
								<td>
									 <a href="?show_notes=yes&booking_id=<?php echo $value['ID'];?>&notes_url_nonce=<?php echo wp_create_nonce('notes_url_nonce');?>"><?php echo __('Notes' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></a>
									<a href="#" class="cancel_booking" data-booking_id="<?php echo $value['ID'];?>" data-cancel_booking_url_nonce="<?php echo wp_create_nonce('cancel_booking_url_nonce');?>"><?php echo __('Cancel', WC_CLASS_BOOKING_TEXT_DOMAIN);?></a>
								</td>
							</tr>
							<?php
						}
					}
					else {
						?>
						<tr>
							<td colspan="6">
								<?php echo __('No class found.',WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</td>
						</tr>
						<?php
					}
					?>
				</table>
			  </div>
			  
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	public static function render_my_account_classes_content() {
		global $wpdb;
		$table_name    = $wpdb->prefix.'booking_history';
		$hour_table    = $wpdb->prefix.'hour_history';
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

							<?php
							$class_time_exp  = explode('-' , $booking[0]['class_time']);
							$class_date_time = $booking[0]['class_date'].' '.$class_time_exp[0];
							$validation_flag = WCCB_Frontend_Myaccount::validate_class_for_reschedule_and_cancel('reschedule', $class_date_time);

							if (!$validation_flag) {
								echo __('<div class="woocommerce-notices-wrapper"><div class="woocommerce-message">Your class is not eligible to reschedule now. You have to reschedule class before '.WC_CLASS_BOOKING_RESCHEDULE_CLASS_BEFORE_HOURS.' hours from starting the class.</div></div>' , WC_CLASS_BOOKING_TEXT_DOMAIN );
							}
							?>
							
							<form id="my_reschedule_form" class="woocommerce-EditAccountForm wccb_form my_reschedule_form" method="post">
								<input type="hidden" name="booking_id" value="<?php echo $booking[0]['ID'];?>">
								<input type="hidden" name="tutor_id" value="<?php echo $booking[0]['tutor_id'];?>">
								<input type="hidden" name="action_do" value="reschedule">
								<div class="back_link_wrapper">
									<a href="?user_id=<?php echo $booking[0]['user_id'];?>">
										<?php echo __('Back to List' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
									</a>
								</div>

								<div class="title_wrapper">
									<h2><?php echo __('Reschedule Class' , WC_CLASS_BOOKING_TEXT_DOMAIN );?></h2>
								</div>
								<?php wp_nonce_field( 'save_reschedule', 'save_reschedule_nonce_field' ); ?>
								<div class="field-group">
									<label><?php echo __('Class Name' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></label>
									<span><?php echo get_the_title($booking[0]['product_id']);?></span>
								</div>
								<div class="field-group">
									<label><?php echo __('Class Date & Time' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></label>
									<span><?php echo WCCB_Helper::display_date( $booking[0]['class_date']).', '.$booking[0]['class_time'];?></span>
								</div>
								<?php

								if ($validation_flag) {
									?>
									<div class="slot_selected_container"></div>
									<div class="tutor_availability_main_wrapper">
										<?php 
										echo WCCB_Frontend_View::get_tutor_availability_calendar( $booking[0]['product_id'] , $booking[0]['tutor_id'] , wp_date('Y-m-d') , WC_CLASS_BOOKING_NUM_DAYS_CALENDAR );
										?>
									</div>
									<div class="field-group">
										<button type="submit" name="save_reschedule" class="woocommerce-Button button"><?php echo __('Save Changes' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></button>
									</div>
									<?php
								}
								
								?>
								
							</form>
						</div>
						<?php
					}
					else {
						echo  __( '<div class="error inline">Unauthorize access</div>' , WC_CLASS_BOOKING_TEXT_DOMAIN );
					}
					
				}
				else {
					echo __( '<div class="error inline">Booking ID not exist</div>' , WC_CLASS_BOOKING_TEXT_DOMAIN );
				}
				
			}
			else {
				echo __( '<div class="error inline">Unauthorize access</div>' , WC_CLASS_BOOKING_TEXT_DOMAIN );
			}
		}

		if ($_REQUEST['new_booking'] == 'yes' && !empty($_REQUEST['user_id'])) {
			$show_table = false;
			$query      = "select * from $hour_table where user_id='".$_REQUEST['user_id']."'";
			$hours      = $wpdb->get_results( $query ); // db call ok. no cache ok
			$no_hour_flag = 1;
			?>
			<div class="my_classes_main_wrapper">
				
				<form id="new_class_form" class="woocommerce-EditAccountForm wccb_form new_class_form " method="post">
					<input type="hidden" name="new_booking" id="new_booking" value="<?php echo $_REQUEST['new_booking'];?>">
					<input type="hidden" name="action_do" value="save_booking">
					<input type="hidden" name="user_id" value="<?php echo $_REQUEST['user_id'];?>">
					<input type="hidden" name="hour_id" value="<?php echo $_REQUEST['hour_id'];?>">
					<input type="hidden" name="hour_expire_date" value="<?php echo $_REQUEST['hour_expire_date'];?>">
					<input type="hidden" name="display_expire_date" value="<?php echo $_REQUEST['display_expire_date'];?>">
					<?php wp_nonce_field( 'save_booking', 'save_booking_nonce_field' ); ?>
					<div class="back_link_wrapper">
						<a href="?user_id=<?php echo $_REQUEST['user_id'];?>">
							<?php echo __('Back to List' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
						</a>
					</div>
					<div class="title_wrapper">
						<h2><?php echo __('Book New Class' , WC_CLASS_BOOKING_TEXT_DOMAIN );?></h2>
					</div>

					<?php
					if (count($hours)>0 ) {
						?>
						<div class="field-group product_container">
							
							<label><?php echo __('Class Name' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></label>
							<select class="select get_tutor_profile" name="product_id" id="product_id">
								<option value=""><?php echo __('Select' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></option>
								<?php
								foreach ($hours as $hour) {
									$product        = wc_get_product($hour->product_id);
									$days           = WCCB_Helper::get_date_difference( $hour->date_purchased, date('Y-m-d') );
									$available_hour = 0;
									if ($days < WC_CLASS_BOOKING_HOUR_EXPIRE_DAYS ) {
										$available_hour = WCCB_Frontend_Myaccount::get_student_total_available_hours($hour->user_id , $hour->ID);
									}
									if ($available_hour > 0 ) {
										$no_hour_flag = 0;
										$expire_date  = WCCB_Helper::get_particular_date($hour->date_purchased , WC_CLASS_BOOKING_HOUR_EXPIRE_DAYS);
										?>
										<option value="<?php echo $product->get_id();?>" data-hour_id="<?php echo $hour->ID;?>" data-expire_date="<?php echo $expire_date;?>" data-display_expire_date="<?php echo wp_date('F j, Y, g:i a',strtotime($expire_date));?>" <?php selected($_REQUEST['hour_id'] , $hour->ID)?>><?php echo $product->get_name().' - (Available hours : '.$available_hour.')';?></option>
										<?php
									}
								}
								?>
							</select>

							<span class="expire_date_container">
								<?php 
								echo !empty($_REQUEST['display_expire_date']) ? $_REQUEST['display_expire_date'] : '';
								?>
							</span>
						</div>
						<?php
					}
					if ( $no_hour_flag ) {
						?>
						<div class="no_hour_msg_wrapper">
							<p>
								<?php 
								echo sprintf( __('You don\'t have available hour to book new class. You can purchase hours from <a href="%s">here</a>' , WC_CLASS_BOOKING_TEXT_DOMAIN) , WCCB_Frontend::get_price_page_link());
								?>
							</p>
						</div>
						<?php
					}
					?>
					<div class="tutor_container">
						<?php
						if (!empty($_REQUEST['product_id'])) {
							echo WCCB_Frontend_View::show_tutor_profile($_REQUEST['product_id']);
						}
						?>
					</div>
					<div class="calendar_container">
						<?php
						echo WCCB_Frontend_View::render_tutor_availability_container();
						?>
					</div>
					<div class="field-group button_wrapper" style="display: <?php echo !empty($_REQUEST['product_id']) ? 'block' : 'none';?>;">
						<button type="submit" name="save_booking" class="woocommerce-Button button">
							<?php echo __('Save Booking' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
						</button>
						
					</div>
				</form>
			</div>
			<?php
		}
		
		if($show_table) {
			?>
			<div class="my_classes_main_wrapper">
				<form id="my_classes_form" method="get" class="woocommerce-EditAccountForm my_classes_form">
					<?php				
					if( array_key_exists('administrator' , get_user_meta(get_current_user_id() , 'wp_capabilities' , true ))) {
						$args   = array(
							'role__in' => array('wccb_student')
						);
						$student = get_users( $args );
						?>
						<div class="title_wrapper">
							<h2>
								<?php 
								echo __('Select student to view his/her class list' , WC_CLASS_BOOKING_TEXT_DOMAIN);
								?>
							</h2>
						</div>
						
						<div class="field-group">
							<label><?php echo __('Select Student', WC_CLASS_BOOKING_TEXT_DOMAIN);?></label>
							<select class="select" name="user_id" onchange="this.form.submit();">
								<option value=""><?php echo __('Select' , WC_CLASS_BOOKING_TEXT_DOMAIN );?></option>
								<?php
					        	foreach ($student as $row) {
					        		?>
					        		<option value="<?php echo $row->ID;?>" <?php if($_REQUEST['user_id'] == $row->ID){ $user_id = $_REQUEST['user_id'];?> selected="selected" <?php }?>><?php echo $row->display_name;?></option>
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
					if (!empty($user_id)) {
						?>
						<a href="?new_booking=yes&user_id=<?php echo $user_id;?>" class="woocommerce-Button button"><?php echo __('Book New Class' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></a>
						<?php
					}
					echo WCCB_Frontend_Myaccount_View::get_my_class_list( $user_id );
					?>
					
				</form>
			</div>
			<?php
		}
	}

	public static function get_my_class_list( $user_id ) {
		if (empty($user_id)) {
			return;
		}

		ob_start();
		global $wpdb;
		$table_name      = $wpdb->prefix.'booking_history';
		$table_name_meta = $wpdb->prefix.'booking_history_meta';
		$user = get_userdata( $user_id);
		?>
		<div class="class_list_wrapper">
			<div id="tabs">
			  <ul>
			    <li><a href="#tabs-1"><?php echo __('Past Classes' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></a></li>
			    <li><a href="#tabs-2"><?php echo __('Upcoming Classes',WC_CLASS_BOOKING_TEXT_DOMAIN);?></a></li>
			  </ul>
			  <div id="tabs-1">
			    <h3><?php echo __('List of Past Classes of '.wccb_user_get_display_name($user) , WC_CLASS_BOOKING_TEXT_DOMAIN);?></h3>
			
				<table class="table table-bordered" width="100%" border="1">
					<thead>
						<tr>
							<th>
								<?php echo __('SI. NO.' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</th>
							<th>
								<?php echo __('Class' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</th>
							<th>
								<?php echo __('Slot Time' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>

								<?php echo WCCB_Helper::help_tip(WC_CLASS_BOOKING_TIMEZONE_MSG);?>
							</th>
							<th>
								<?php echo __('Tutor Name' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</th>
							
							
						</tr>
					</thead>
					
					<?php
					$query         = "SELECT * FROM $table_name WHERE user_id='".$user_id."' and class_date <= '".wp_date('Y-m-d')."' and status = 'Completed' order by class_date asc ";
					$results       = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.
					if (count($results)>0) {
						foreach ($results as $key => $value) {
							$tutor = get_userdata( $value['tutor_id'] );

							$query2     = "select * from $table_name_meta where booking_id='".$value['ID']."' and meta_key='notes'";
							$notes      = $wpdb->get_results( $query2 ); // db call ok. no cache ok
							?>
							<tr>
								<td>
									<?php echo $key+1;?>
								</td>
								<td>
									<a href="<?php echo get_permalink($value['product_id']);?>">
										<?php echo get_the_title( $value['product_id'] );?>
									</a>

									<?php
									if (!empty($notes[0]->meta_value)) {
										echo WCCB_Helper::help_tip($notes[0]->meta_value);
									}
									else {

										echo WCCB_Helper::help_tip('No notes found.');
									}
									?>
								</td>
								<td>
									<?php echo wp_date('D M j, Y' , strtotime($value['class_date'])).', '.$value['class_time'];?>
									
								</td>
								<td>
									<?php echo wccb_user_get_display_name($tutor);?>
								</td>
								
								
							</tr>
							<?php
						}
					}
					else {
						?>
						<tr>
							<td colspan="6">
								<?php echo __('No class found.',WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</td>
						</tr>
						<?php
					}
					?>
				</table>
			
			  </div>
			  <div id="tabs-2">
			  	<h3><?php echo __('List of Upcoming Classes of '.wccb_user_get_display_name($user) , WC_CLASS_BOOKING_TEXT_DOMAIN);?></h3>
			  	<table class="table table-bordered" width="100%" border="1">
			  		<thead>
			  			<tr>
							<th>
								<?php echo __('SI. NO.' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</th>
							<th>
								<?php echo __('Class' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</th>
							<th>
								<?php echo __('Slot Time' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
								<?php echo WCCB_Helper::help_tip(WC_CLASS_BOOKING_TIMEZONE_MSG);?>
							</th>
							<th>
								<?php echo __('Tutor Name' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</th>
							<th>
								<?php echo __('Actions', WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</th>
							
						</tr>
			  		</thead>
					
					<?php
					$query         = "SELECT * FROM $table_name WHERE user_id='".$user_id."' and class_date >= '".wp_date('Y-m-d')."' and status = 'Upcoming' order by ID desc";
					$results       = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.
					if (count($results)>0) {
						foreach ($results as $key => $value) {
							$tutor = get_userdata( $value['tutor_id'] );

							$query2     = "select * from $table_name_meta where booking_id='".$value['ID']."' and meta_key='notes'";
							$notes      = $wpdb->get_results( $query2 ); // db call ok. no cache ok
							?>
							<tr>
								<td>
									<?php echo $key+1;?>
								</td>
								<td>
									<a href="<?php echo get_permalink($value['product_id']);?>">
										<?php echo get_the_title( $value['product_id'] );?>
									</a>
									<?php
									if (!empty($notes[0]->meta_value)) {
										echo WCCB_Helper::help_tip($notes[0]->meta_value);
									}
									else {
										echo WCCB_Helper::help_tip('No notes found.');
									}
									?>	
									
								</td>
								<td>
									<?php echo wp_date('D M j, Y' , strtotime($value['class_date'])).', '.$value['class_time'];?>
									
								</td>
								<td>
									<?php echo wccb_user_get_display_name($tutor);?>
								</td>
								<td>
									<a href="?reschedule=yes&booking_id=<?php echo $value['ID'];?>&reschedule_booking_url_nonce=<?php echo wp_create_nonce('reschedule_booking_url_nonce');?>">Reschedule</a> |
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
								<?php echo __('No class found.',WC_CLASS_BOOKING_TEXT_DOMAIN);?>
							</td>
						</tr>
						<?php
					}
					?>
				</table>
			  </div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_my_account_hours_content() {
		global $wpdb;
		$table_name    = $wpdb->prefix.'hour_history';
		?>
		<div class="my_hours_main_wrapper">
			<form id="my_hours_form" class="woocommerce-EditAccountForm my_hours_form" method="get">

				<?php
				if(array_key_exists('administrator' , get_user_meta(get_current_user_id() , 'wp_capabilities' , true ) )) {
					$args   = array(
						'role__in' => array('wccb_student')
					);
					$student = get_users( $args );
					?>
					<div class="title_wrapper">
						<h2><?php echo __('Select student to view his/her hour list', WC_CLASS_BOOKING_TEXT_DOMAIN);?></h2>
					</div>
					<div class="field-group">
						<label><?php echo __('Select Student', WC_CLASS_BOOKING_TEXT_DOMAIN);?></label>
						<select class="select" name="user_id" onchange="this.form.submit();">
							<option value=""><?php echo __('Select' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></option>
							<?php
				        	foreach ($student as $row) {
				        		?>
				        		<option value="<?php echo $row->ID;?>" <?php if($_REQUEST['user_id'] == $row->ID){ $user_id = $_REQUEST['user_id'];?> selected="selected" <?php }?>><?php echo wccb_user_get_display_name($row);?></option>
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
		if (empty($user_id)) {
			return;
		}

		ob_start();
		global $wpdb;
		$table_name = $wpdb->prefix.'hour_history';

		$user = get_userdata($user_id);
		?>
		<div class="hour_list_wrapper">
			<div class="total_hour_wrapper" style="float: right;">
				<h3>
					<?php echo __('Total Available Hours' , WC_CLASS_BOOKING_TEXT_DOMAIN);?> : <?php echo WCCB_Frontend_Myaccount::get_student_total_available_hours( $user_id );?>
				</h3>
			</div>
			
			<h3><?php echo __('List of Purchased Hours of '.wccb_user_get_display_name($user) , WC_CLASS_BOOKING_TEXT_DOMAIN);?></h3>
		  	<table class="table table-bordered" width="100%" border="1">
		  		<thead>
		  			<tr>
						<th>
							<?php echo __('SI. NO.' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
						</th>
						<th>
							<?php echo __('Product' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
						</th>
						<th>
							<?php echo __('Purchased Hours' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
						</th>
						<th>
							<?php echo __('Used Hours' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
						</th>
						<th>
							<?php echo __('Deducted Hours' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
						</th>
						<th>
							<?php echo __('Expired Hours', WC_CLASS_BOOKING_TEXT_DOMAIN);?>
						</th>
						<th>
							<?php echo __('Date Purchased' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
						</th>
						<th>
							<?php echo __('Expiry Date', WC_CLASS_BOOKING_TEXT_DOMAIN);?>
						</th>
						
					</tr>
		  		</thead>
				
				<?php
				$query         = "SELECT * FROM $table_name WHERE user_id='".$user_id."' order by ID desc";
				$results       = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.
				if (count($results)>0) {
					foreach ($results as $key => $value) {
						$expiry_date     = '';
						$remaining_hour  = $value['purchased_hours'] - $value['used_hours'];
						if ($remaining_hour > 0) {
							$expiry_date = WCCB_Helper::get_particular_date($value['date_purchased'] , WC_CLASS_BOOKING_HOUR_EXPIRE_DAYS);
						}
						?>
						<tr>
							<td>
								<?php echo $key+1;?>
							</td>
							<td>
								<a href="<?php echo get_permalink($value['product_id']);?>" target="_blank">
									<?php echo get_the_title($value['product_id']);?>
								</a>
							</td>
							<td>
								<?php echo (float)$value['purchased_hours'];?>
							</td>
							<td>
								<?php echo (float)$value['used_hours'];?>
							</td>
							<td>
								<?php echo (float)$value['deducted_hours'];?>
							</td>
							<td>
								<?php echo (float)$value['expired_hours'];?>
							</td>
							<td>
								<?php echo wp_date('d-m-Y h:i:s', strtotime($value['date_purchased']));?>
							</td>
							<td>
								<?php 
								echo !empty($expiry_date) ? wp_date('d-m-Y H:i:s', strtotime($expiry_date)) : 'N/A '.WCCB_Helper::help_tip('No hour left to expire');
								?>
							</td>
						</tr>
						<?php
					}
				}
				else {
					?>
					<tr>
						<td colspan="7">
							<?php echo __('No hours found.' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
						</td>
					</tr>
					<?php
				}
				?>
			</table>
		</div>
		<?php

		return ob_get_clean();
	}

	public static function render_my_account_add_hour_content() {
		$args   = array(
					'role__in' => array('wccb_student')
				);
		$student = get_users( $args );
		?>
		<div class="add_hour_main_wrapper">

			<div class="response_container">
				
			</div>

			<form class="woocommerce-EditAccountForm add_hour_form" method="post">
				<?php wp_nonce_field( 'add_hour_nonce', 'add_hour_nonce_field' ); ?>
				
				<div class="title_wrapper">
					<h2><?php echo __('Add Hours for Student', WC_CLASS_BOOKING_TEXT_DOMAIN);?></h2>
				</div>
				<div class="field-group">
					<label><?php echo __('Select Student', WC_CLASS_BOOKING_TEXT_DOMAIN);?></label>
					<select class="select" name="user_id">
						<option value=""><?php echo __('Select' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></option>
						<?php
			        	foreach ($student as $row) {
			        		?>
			        		<option value="<?php echo $row->ID;?>" <?php if($_REQUEST['user_id'] == $row->ID){ $user_id = $_REQUEST['user_id'];?> selected="selected" <?php }?>><?php echo wccb_user_get_display_name($row);?></option>
			        		<?php
			        	}
			        	?>
					</select>
				</div>
				<div class="field-group product_container">
							
					<label><?php echo __('Class Name' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></label>
					<select class="select" name="product_id" id="product_id">
						<option value=""><?php echo __('Select' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></option>
						<?php
						$args = array(
						  'post_type' => 'product',
						  'numberposts' => -1
						);
						 
						$products = get_posts( $args );
						foreach ($products as $temp_product) {
							$product        = wc_get_product($temp_product->ID);
							?>
							<option value="<?php echo $product->get_id();?>"><?php echo $product->get_name().' - '.$product->get_price_html().'/ Hour';?></option>
							<?php
						}
						?>
					</select>
				</div>
				<div class="field-group">
					<label><?php echo __('Hours', WC_CLASS_BOOKING_TEXT_DOMAIN);?></label>
					<input type="text" name="hour" id="hour" >
				</div>
				<p>&nbsp;</p>

				<div class="field-group button_wrapper">
					<button type="submit" name="save_hour" class="woocommerce-Button button save_hour">
						<?php echo __('Submit' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
					</button>
				</div>
			</form>
		</div>
		<?php
	}

	public static function render_my_account_deduct_hour_content() {
		$args   = array(
					'role__in' => array('wccb_student')
				);
		$student = get_users( $args );
		?>
		<div class="add_hour_main_wrapper">

			<div class="response_container">
				
			</div>

			<form class="woocommerce-EditAccountForm deduct_hour_form" method="post">
				<?php wp_nonce_field( 'deduct_hour_nonce', 'deduct_hour_nonce_field' ); ?>
				
				<div class="title_wrapper">
					<h2><?php echo __('Deduct Hours for Student', WC_CLASS_BOOKING_TEXT_DOMAIN);?></h2>
				</div>
				<div class="field-group">
					<label><?php echo __('Select Student', WC_CLASS_BOOKING_TEXT_DOMAIN);?></label>
					<select class="select get_available_hour_product" name="user_id">
						<option value=""><?php echo __('Select' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></option>
						<?php
			        	foreach ($student as $row) {
			        		?>
			        		<option value="<?php echo $row->ID;?>" <?php if($_REQUEST['user_id'] == $row->ID){ $user_id = $_REQUEST['user_id'];?> selected="selected" <?php }?>><?php echo wccb_user_get_display_name($row);?></option>
			        		<?php
			        	}
			        	?>
					</select>
				</div>
				<div class="field-group product_container">
							
					<label><?php echo __('Class Name' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></label>
					<select class="select" name="hour_id" id="hour_id">
						<option value=""><?php echo __('Select' , WC_CLASS_BOOKING_TEXT_DOMAIN);?></option>
					</select>

					<span class="expire_date_container">
						
					</span>
				</div>
				<div class="field-group">
					<label><?php echo __('Hours to deduct', WC_CLASS_BOOKING_TEXT_DOMAIN);?></label>
					<input type="text" name="hour" id="hour" >
				</div>
				<p>&nbsp;</p>

				<div class="field-group button_wrapper">
					<button type="submit" name="deduct_hour" class="woocommerce-Button button deduct_hour">
						<?php echo __('Submit' , WC_CLASS_BOOKING_TEXT_DOMAIN);?>
					</button>
				</div>
			</form>
		</div>
		<?php
	}

}