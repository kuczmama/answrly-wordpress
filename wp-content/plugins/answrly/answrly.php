<?php 
    /*
    Plugin Name: Answrly
    Plugin URI: https://www.answrly.com
    Description: Are you really good at something?  Car Repair? Medical Stuff? Investment Guru? Do all your users want to give you money for your advice? The answrly plugin allows you to easily <strong>answer your user's questions for money</strong>.  To get started: 1) Click the "Activate" link to the left of this description, 2) Sign up as an expert at <a href="https://www.answrly.com/expert_signup">answrly.com</a> 3) Go to your answrly configuration page, and login with your account information.
    Author: Mark Kuczmarski
    Version: 1.0
    Author URI: https://www.answrly.com
*/
?>
 
 <?php
class Answrly extends WP_Widget {

	function __construct() {
		// Instantiate the parent object
		$widget_ops = array( 
			'classname' => 'answrly_widget',
			'description' => 'Answrly Widget Description',
		);
		parent::__construct( 'answrly_widget', 'Answrly', $widget_ops );
	}

	function widget( $args, $instance ) {
		// Widget output
		
		echo "Write a question to " . $instance['username'];
		?>
		<form method="post" id='question-form'>
			<textarea form='question-form' name='question'>
Ask a question!
			</textarea>
    		<input type="submit" value="click" name="submit"> <!-- assign a name for the button -->
		</form>
		
		<?php
		function display($result)
		{
    		echo $result;
		}
		if(isset($_POST['submit']))
		{
			$data = array(
				"question[body]"  => $_POST['question'],
				"question[expert_id]" => $instance['user_id']
			);
			display(curl('https://www.answrly.com/questions', $data));
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = array();
		
		$instance['username'] = ( ! empty( $new_instance['username'] ) ) ? strip_tags( $new_instance['username'] ) : '';
		$instance['password'] = ( ! empty( $new_instance['password'] ) ) ? strip_tags( $new_instance['password'] ) : '';
		$data = array(
			"user[username]"  => $instance['username'],
			"user[password]" => $instance['password']
		);
		$instance['json'] = curl('https://www.answrly.com/get_user', $data);
		
		if(is_json($instance['json'], FALSE)){
			$json = json_decode($instance['json']);
			$instance['user_id'] = $json->{'id'};
			$instance['username'] = $json->{'username'};
		}
		
		return $instance;
	}

	function form( $instance ) {
		//Title
		?>
		<div style="color: #ff0000; font-weight: 900;text-align: center;">
			<?php echo !is_json($instance['json']) ? $instance['json'] : ''; ?>
		</div>
		<?php
		// Username
		$username = ! empty( $instance['username'] ) ? $instance['username'] : __( 'Username', 'text_domain' );
		?>
		<label 
			for="<?php echo esc_attr( $this->get_field_id( 'username' ) ); ?>">
			<?php _e( esc_attr( 'Username:')) ?>
		</label> 
		<input 
			class="widefat" 
			id="<?php echo esc_attr( $this->get_field_id( 'username' ) ); ?>"
			name="<?php echo esc_attr( $this->get_field_name( 'username' ) ); ?>"
			type="text" 
			value="<?php echo esc_attr( $username ); ?>">
		<?php
		//Password
		$password = ! empty( $instance['password'] ) ? $instance['password'] : __( 'password', 'text_domain' );
		?>
		<label 
			for="<?php echo esc_attr( $this->get_field_id( 'password' ) ); ?>">
			<?php _e( esc_attr( 'Password:' ) ); ?>
		</label> 
		<input 
			class="widefat" 
			id="<?php echo esc_attr( $this->get_field_id( 'password' ) ); ?>"
			name="<?php echo esc_attr( $this->get_field_name( 'password' ) ); ?>"
			type="password" 
			value="<?php echo esc_attr( $password ); ?>">
		<?php
	}
}
function is_json($string,$return_data = false) {
	$data = json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;
}

function curl($url, $data){
	// Get cURL resource
	$curl = curl_init();
	// Set some options - we are passing in a useragent too here
	curl_setopt_array($curl, array(
	    CURLOPT_RETURNTRANSFER => 1,
	    CURLOPT_URL => $url,
	    CURLOPT_POST => 1,
	    CURLOPT_POSTFIELDS => $data
	));
	// Send the request & save response to $resp
	$resp = curl_exec($curl);
	// Close request to clear up some resources
	curl_close($curl);
	return $resp;
}
function myplugin_register_widgets() {
	register_widget( 'Answrly' );
}

add_action( 'widgets_init', 'myplugin_register_widgets' );

 ?>