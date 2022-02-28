<?php
defined( 'ABSPATH' ) || die();

class WCCB_Email_Content {

	public static function get_class_booking_content_once( $type , $booking_ids , $student , $tutor ) {
		global $wpdb;
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title><?php echo get_option( 'blogname' );?></title>
		</head>
		<body>
			<table>
				<?php
				if ($type == 'student') {
					$welcome_text = __( 'Dear '.$student->display_name.', you have successfully booked the class. Below are the class details' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				if ($type == 'tutor') {
					$welcome_text = __( 'Dear '.$tutor->display_name.', one student have successfully booked the class with you. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				if ($type == 'admin') {
					$welcome_text = __( 'Dear Admin, one student successfully booked the class. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				?>
				<tr>
					<td colspan="2">
						<?php echo $welcome_text;?>
					</td>
				</tr>
				<?php
				foreach ($booking_ids as $key => $value) {
					$table_name = $wpdb->prefix.'booking_history';
					$query = "SELECT * FROM $table_name WHERE ID='".$value."'";
					$booking = $wpdb->get_row($query);
					?>
					<tr>
						<td colspan="2">
							<h2>
								<?php echo __('Class Detail', WC_CLASS_BOOKING_TEXT_DOMAIN);?> <?php echo ($key + 1);?>
							</h2>
						</td>
					</tr>
					<tr>
						<th>
							<?php echo __('Class Name' , WC_CLASS_BOOKING_TEXT_DOMAIN);?> :
						</th>
						<td>
							<a href="<?php echo get_permalink($booking->product_id);?>"><?php echo get_the_title($booking->product_id);?></a>
							
						</td>
					</tr>
					<?php
					if ($type == 'student' || $type == 'admin') {
						?>
						<tr>
							<th>
								<?php echo __('Tutor Name', WC_CLASS_BOOKING_TEXT_DOMAIN);?> :
							</th>
							<td>
								<?php echo $tutor->display_name;?>
							</td>
						</tr>
						<?php
					}
					if ($type == 'tutor' || $type == 'admin') {
						?>
						<tr>
							<th>
								<?php echo __('Student Name', WC_CLASS_BOOKING_TEXT_DOMAIN);?> :
							</th>
							<td>
								<?php echo $student->display_name;?>
							</td>
						</tr>
						<?php
					}
					?>
					<tr>
						<th>
							<?php echo __('Class Date', WC_CLASS_BOOKING_TEXT_DOMAIN);?> :
						</th>
						<td>
							<?php echo WCCB_Helper::display_date($booking->class_date);?>
						</td>
					</tr>
					<tr>
						<th>
							<?php echo __('Class Time', WC_CLASS_BOOKING_TEXT_DOMAIN);?>
						</th>
						<td>
							<?php echo $booking->class_time;?>
						</td>
					</tr>
					<?php
				}
				?>
				
				<tr>
					<td colspan="2">
						&nbsp;
					</td>
				</tr>

				<tr>
					<td colspan="2">
						<?php echo __('Regards',WC_CLASS_BOOKING_TEXT_DOMAIN);?>, <br><br>

						<?php echo __('Team Elbyan', WC_CLASS_BOOKING_TEXT_DOMAIN);?>
					</td>
				</tr>
			</table>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	public static function get_class_booking_content( $type , $booking , $student , $tutor ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title><?php echo get_option( 'blogname' );?></title>
		</head>
		<body>
			<table>
				<?php
				if ($type == 'student') {
					$welcome_text = __( 'Dear '.$student->display_name.', you have successfully booked the class. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				if ($type == 'tutor') {
					$welcome_text = __( 'Dear '.$tutor->display_name.', one student have successfully booked the class with you. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				if ($type == 'admin') {
					$welcome_text = __( 'Dear Admin, one student successfully booked the class. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				?>
				<tr>
					<td colspan="2">
						<?php echo $welcome_text;?>
					</td>
				</tr>
				<tr>
					<th>
						Class Name :
					</th>
					<td>
						<a href="<?php echo get_permalink($booking->product_id);?>"><?php echo get_the_title($booking->product_id);?></a>
						
					</td>
				</tr>
				<?php
				if ($type == 'student' || $type == 'admin') {
					?>
					<tr>
						<th>
							Tutor Name :
						</th>
						<td>
							<?php echo $tutor->display_name;?>
						</td>
					</tr>
					<?php
				}
				if ($type == 'tutor' || $type == 'admin') {
					?>
					<tr>
						<th>
							Student Name :
						</th>
						<td>
							<?php echo $student->display_name;?>
						</td>
					</tr>
					<?php
				}
				?>
				<tr>
					<th>
						Class Date :
					</th>
					<td>
						<?php echo WCCB_Helper::display_date($booking->class_date);?>
					</td>
				</tr>
				<tr>
					<th>
						Class Time
					</th>
					<td>
						<?php echo $booking->class_time;?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						&nbsp;
					</td>
				</tr>

				<tr>
					<td colspan="2">
						Regards, <br><br>

						Team Elbyan
					</td>
				</tr>
			</table>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	public static function get_class_reschedule_content( $type , $booking , $student , $tutor ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title><?php echo get_option( 'blogname' );?></title>
		</head>
		<body>
			<table>
				<?php
				if ($type == 'student') {
					$welcome_text = __( 'Dear '.$student->display_name.', you have successfully rescheduled the class. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				if ($type == 'tutor') {
					$welcome_text = __( 'Dear '.$tutor->display_name.', one student have successfully rescheduled the class with you. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				if ($type == 'admin') {
					$welcome_text = __( 'Dear Admin, one student successfully rescheduled the class. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				?>
				<tr>
					<td colspan="2">
						<?php echo $welcome_text;?>
					</td>
				</tr>
				<tr>
					<th>
						Class Name :
					</th>
					<td>
						<a href="<?php echo get_permalink($booking->product_id);?>"><?php echo get_the_title($booking->product_id);?></a>
						
					</td>
				</tr>
				<?php
				if ($type == 'student' || $type == 'admin') {
					?>
					<tr>
						<th>
							Tutor Name :
						</th>
						<td>
							<?php echo $tutor->display_name;?>
						</td>
					</tr>
					<?php
				}
				if ($type == 'tutor' || $type == 'admin') {
					?>
					<tr>
						<th>
							Student Name :
						</th>
						<td>
							<?php echo $student->display_name;?>
						</td>
					</tr>
					<?php
				}
				?>
				<tr>
					<th>
						Class Date :
					</th>
					<td>
						<?php echo WCCB_Helper::display_date($booking->class_date);?>
					</td>
				</tr>
				<tr>
					<th>
						Class Time
					</th>
					<td>
						<?php echo $booking->class_time;?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						&nbsp;
					</td>
				</tr>

				<tr>
					<td colspan="2">
						Regards, <br><br>

						Team Elbyan
					</td>
				</tr>
			</table>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	public static function get_class_cancelled_content( $type , $booking , $student , $tutor ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title><?php echo get_option( 'blogname' );?></title>
		</head>
		<body>
			<table>
				<?php
				if ($type == 'student') {
					$welcome_text = __( 'Dear '.$student->display_name.', you have cancelled the class. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				if ($type == 'tutor') {
					$welcome_text = __( 'Dear '.$tutor->display_name.', one student have cancelled the class associated with you. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				if ($type == 'admin') {
					$welcome_text = __( 'Dear Admin, one student cancelled the class. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				?>
				<tr>
					<td colspan="2">
						<?php echo $welcome_text;?>
					</td>
				</tr>
				<tr>
					<th>
						Class Name :
					</th>
					<td>
						<a href="<?php echo get_permalink($booking->product_id);?>"><?php echo get_the_title($booking->product_id);?></a>
						
					</td>
				</tr>
				<?php
				if ($type == 'student' || $type == 'admin') {
					?>
					<tr>
						<th>
							Tutor Name :
						</th>
						<td>
							<?php echo $tutor->display_name;?>
						</td>
					</tr>
					<?php
				}
				if ($type == 'tutor' || $type == 'admin') {
					?>
					<tr>
						<th>
							Student Name :
						</th>
						<td>
							<?php echo $student->display_name;?>
						</td>
					</tr>
					<?php
				}
				?>
				<tr>
					<th>
						Class Date :
					</th>
					<td>
						<?php echo WCCB_Helper::display_date($booking->class_date);?>
					</td>
				</tr>
				<tr>
					<th>
						Class Time
					</th>
					<td>
						<?php echo $booking->class_time;?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						&nbsp;
					</td>
				</tr>

				<tr>
					<td colspan="2">
						Regards, <br><br>

						Team Elbyan
					</td>
				</tr>
			</table>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	public static function get_class_notification_content( $type , $booking , $student , $tutor ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title><?php echo get_option( 'blogname' );?></title>
		</head>
		<body>
			<table>

				<?php
				if ($type == 'student') {
					$welcome_text = __( 'Dear '.$student->display_name.', you have one class scheduled today. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				if ($type == 'tutor') {
					$welcome_text = __( 'Dear '.$tutor->display_name.', you have one class scheduled today. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				if ($type == 'admin') {
					$welcome_text = __( 'Dear Admin, one student has scheduled class today. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				?>
				<tr>
					<td colspan="2">
						<?php echo $welcome_text;?>
					</td>
				</tr>
				<tr>
					<th>
						Class Name :
					</th>
					<td>
						<a href="<?php echo get_permalink($booking->product_id);?>"><?php echo get_the_title($booking->product_id);?></a>
						
					</td>
				</tr>
				<?php
				if ($type == 'student' || $type == 'admin') {
					?>
					<tr>
						<th>
							Tutor Name :
						</th>
						<td>
							<?php echo $tutor->display_name;?>
						</td>
					</tr>
					<?php
				}
				if ($type == 'tutor' || $type == 'admin') {
					?>
					<tr>
						<th>
							Student Name :
						</th>
						<td>
							<?php echo $student->display_name;?>
						</td>
					</tr>
					<?php
				}
				?>
				<tr>
					<th>
						Class Date :
					</th>
					<td>
						<?php echo WCCB_Helper::display_date($booking->class_date);?>
					</td>
				</tr>
				<tr>
					<th>
						Class Time
					</th>
					<td>
						<?php echo $booking->class_time;?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						&nbsp;
					</td>
				</tr>

				<tr>
					<td colspan="2">
						Regards, <br><br>

						Team Elbyan
					</td>
				</tr>
			</table>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	public static function get_class_reminder_content( $type , $booking , $student , $tutor ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title><?php echo get_option( 'blogname' );?></title>
		</head>
		<body>
			<table>

				<?php
				if ($type == 'student') {
					$welcome_text = __( 'Dear '.$student->display_name.', you have one class scheduled today. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				if ($type == 'tutor') {
					$welcome_text = __( 'Dear '.$tutor->display_name.', you have one class scheduled today. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				if ($type == 'admin') {
					$welcome_text = __( 'Dear Admin, one student has scheduled class today. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				?>
				<tr>
					<td colspan="2">
						<?php echo $welcome_text;?>
					</td>
				</tr>
				<tr>
					<th>
						Class Name :
					</th>
					<td>
						<a href="<?php echo get_permalink($booking->product_id);?>"><?php echo get_the_title($booking->product_id);?></a>
						
					</td>
				</tr>
				<?php
				if ($type == 'student' || $type == 'admin') {
					?>
					<tr>
						<th>
							Tutor Name :
						</th>
						<td>
							<?php echo $tutor->display_name;?>
						</td>
					</tr>
					<?php
				}
				if ($type == 'tutor' || $type == 'admin') {
					?>
					<tr>
						<th>
							Student Name :
						</th>
						<td>
							<?php echo $student->display_name;?>
						</td>
					</tr>
					<?php
				}
				?>
				<tr>
					<th>
						Class Date :
					</th>
					<td>
						<?php echo WCCB_Helper::display_date($booking->class_date);?>
					</td>
				</tr>
				<tr>
					<th>
						Class Time
					</th>
					<td>
						<?php echo $booking->class_time;?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						&nbsp;
					</td>
				</tr>

				<tr>
					<td colspan="2">
						Regards, <br><br>

						Team Elbyan
					</td>
				</tr>
			</table>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	public static function get_class_date_time_passed_content( $type , $booking , $student , $tutor ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title><?php echo get_option( 'blogname' );?></title>
		</head>
		<body>
			<table>
				<?php
				if ($type == 'student') {
					$welcome_text = __( 'Dear '.$student->display_name.', you have successfully completed your class today. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				if ($type == 'tutor') {
					$welcome_text = __( 'Dear '.$tutor->display_name.', your class shows that date and time has passed. You can update status by login into your account. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				if ($type == 'admin') {
					$welcome_text = __( 'Dear Admin, one student has completed his/her class today. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				?>
				<tr>
					<td colspan="2">
						<?php echo $welcome_text;?>
					</td>
				</tr>
				<tr>
					<th>
						Class Name :
					</th>
					<td>
						<a href="<?php echo get_permalink($booking->product_id);?>"><?php echo get_the_title($booking->product_id);?></a>
						
					</td>
				</tr>
				<?php
				if ($type == 'student' || $type == 'admin') {
					?>
					<tr>
						<th>
							Tutor Name :
						</th>
						<td>
							<?php echo $tutor->display_name;?>
						</td>
					</tr>
					<?php
				}
				if ($type == 'tutor' || $type == 'admin') {
					?>
					<tr>
						<th>
							Student Name :
						</th>
						<td>
							<?php echo $student->display_name;?>
						</td>
					</tr>
					<?php
				}
				?>
				<tr>
					<th>
						Class Date :
					</th>
					<td>
						<?php echo WCCB_Helper::display_date($booking->class_date);?>
					</td>
				</tr>
				<tr>
					<th>
						Class Time
					</th>
					<td>
						<?php echo $booking->class_time;?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						&nbsp;
					</td>
				</tr>

				<tr>
					<td colspan="2">
						Regards, <br><br>

						Team Elbyan
					</td>
				</tr>
			</table>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	public static function get_class_status_content( $type , $booking , $student , $tutor ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title><?php echo get_option( 'blogname' );?></title>
		</head>
		<body>
			<table>
				<?php
				if ($type == 'student') {
					$welcome_text = __( 'Dear '.$student->display_name.', tutor has updated the delivery status of your past class. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				if ($type == 'tutor') {
					$welcome_text = __( 'Dear '.$tutor->display_name.', you have successfully updated the delivery status of your past class. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				if ($type == 'admin') {
					$welcome_text = __( 'Dear Admin, tutor has updated the delivery status of past class. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				?>
				<tr>
					<td colspan="2">
						<?php echo $welcome_text;?>
					</td>
				</tr>
				<tr>
					<th>
						Class Name :
					</th>
					<td>
						<a href="<?php echo get_permalink($booking->product_id);?>"><?php echo get_the_title($booking->product_id);?></a>
						
					</td>
				</tr>
				<tr>
					<th>
						Delivery Status :
					</th>
					<td>
						<?php echo $booking->delivery_status;?>
						
					</td>
				</tr>
				<?php
				if ($type == 'student' || $type == 'admin') {
					?>
					<tr>
						<th>
							Tutor Name :
						</th>
						<td>
							<?php echo $tutor->display_name;?>
						</td>
					</tr>
					<?php
				}
				if ($type == 'tutor' || $type == 'admin') {
					?>
					<tr>
						<th>
							Student Name :
						</th>
						<td>
							<?php echo $student->display_name;?>
						</td>
					</tr>
					<?php
				}
				?>
				<tr>
					<th>
						Class Date :
					</th>
					<td>
						<?php echo WCCB_Helper::display_date($booking->class_date);?>
					</td>
				</tr>
				<tr>
					<th>
						Class Time
					</th>
					<td>
						<?php echo $booking->class_time;?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						&nbsp;
					</td>
				</tr>

				<tr>
					<td colspan="2">
						Regards, <br><br>

						Team Elbyan
					</td>
				</tr>
			</table>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	public static function get_class_completion_content( $type , $booking , $student , $tutor ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title><?php echo get_option( 'blogname' );?></title>
		</head>
		<body>
			<table>
				<?php
				if ($type == 'student') {
					$welcome_text = __( 'Dear '.$student->display_name.', you have successfully completed your class today. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				if ($type == 'tutor') {
					$welcome_text = __( 'Dear '.$tutor->display_name.', you have successfully completed your class today. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				if ($type == 'admin') {
					$welcome_text = __( 'Dear Admin, one student has completed his/her class today. Below are the class detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				}
				?>
				<tr>
					<td colspan="2">
						<?php echo $welcome_text;?>
					</td>
				</tr>
				<tr>
					<th>
						Class Name :
					</th>
					<td>
						<a href="<?php echo get_permalink($booking->product_id);?>"><?php echo get_the_title($booking->product_id);?></a>
						
					</td>
				</tr>
				<?php
				if ($type == 'student' || $type == 'admin') {
					?>
					<tr>
						<th>
							Tutor Name :
						</th>
						<td>
							<?php echo $tutor->display_name;?>
						</td>
					</tr>
					<?php
				}
				if ($type == 'tutor' || $type == 'admin') {
					?>
					<tr>
						<th>
							Student Name :
						</th>
						<td>
							<?php echo $student->display_name;?>
						</td>
					</tr>
					<?php
				}
				?>
				<tr>
					<th>
						Class Date :
					</th>
					<td>
						<?php echo WCCB_Helper::display_date($booking->class_date);?>
					</td>
				</tr>
				<tr>
					<th>
						Class Time
					</th>
					<td>
						<?php echo $booking->class_time;?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						&nbsp;
					</td>
				</tr>

				<tr>
					<td colspan="2">
						Regards, <br><br>

						Team Elbyan
					</td>
				</tr>
			</table>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	public static function get_hour_deducted_content( $hour_obj , $student , $hour ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title><?php echo get_option( 'blogname' );?></title>
		</head>
		<body>
			<table>
				<?php
				$welcome_text = __( 'Dear '.$student->display_name.', your purchased hours has been deducted. Below are the detail' , WC_CLASS_BOOKING_TEXT_DOMAIN);
				?>
				<tr>
					<td colspan="2">
						<?php echo $welcome_text;?>
					</td>
				</tr>
				<tr>
					<th>
						Class Name :
					</th>
					<td>
						<a href="<?php echo get_permalink($hour_obj->product_id);?>"><?php echo get_the_title($hour_obj->product_id);?></a>
						
					</td>
				</tr>
				<tr>
					<th>
						Deducted Hour
					</th>
					<td>
						<?php echo $hour;?>
					</td>
				</tr>
				<tr>
					<th>
						Deducted Date :
					</th>
					<td>
						<?php echo WCCB_Helper::display_date(date('d-m-Y'));?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						&nbsp;
					</td>
				</tr>

				<tr>
					<td colspan="2">
						Regards, <br><br>

						Team Elbyan
					</td>
				</tr>
			</table>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	

	
}