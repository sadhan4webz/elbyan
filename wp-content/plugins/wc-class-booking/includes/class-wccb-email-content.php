<?php
defined( 'ABSPATH' ) || die();

class WCCB_Email_Content {

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
						<?php echo $booking->class_time?>
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
						<?php echo $booking->class_time?>
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
						<?php echo $booking->class_time?>
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
						<?php echo $booking->class_time?>
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
						<?php echo $booking->class_time?>
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
						<?php echo $booking->class_time?>
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