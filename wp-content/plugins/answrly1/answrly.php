<?php 
    /*
    Plugin Name: Answrly
    Plugin URI: https://www.answrly.com
    Description: The answrly plugin allows you to easily <strong>answer your user's questions for money</strong>.  To get started: 1) Click the "Activate" link to the left of this description, 2) Sign up as an expert at <a href="https://www.answrly.com/expert_signup">answrly.com</a> 3) Answrly will appear as an available widget.  4) Place Answrly Widget at desired location ie. Sidebar, Content Bottom, etc.  5)  In the Answrly Widget settings enter your answrly username and password to link your acount to wordpress  6) Customize Widget Options if desired 7) You're ready to start making money with your blog
    Author: Mark Kuczmarski
    Version: 1.0
    Author URI: https://www.answrly.com
*/
?>
<style type="text/css">
	.section-header{
		text-align: left;
		font-weight: 900;
	}
	
	.error-message {
		color: #ff0000;
		font-weight: 900;
		text-align: center;
	}
	
	.align-right {
		text-align: center;
		float: right;
	}
	
	.align-middle {
		text-align: center;
		margin-left: auto;
		margin-right: auto;
	}
	.section {
		margin-top: 1em;
	}
</style>
 
 
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
		$title = apply_filters( 'widget_title', ( ! empty( $instance['widget_title'] ) ) ? strip_tags( $instance['widget_title'] ) : __("Ask me a question") );
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		?>
		<div id="error-message" class='error-message'><!-- Display the error message --></div>
		<form method="post" id='question-form'>
			<textarea form='question-form' name='question' placeholder='Type your question here...' ></textarea>
    		<input type="submit" value="Ask" name="submit">
    		<?php if($instance['powered_link'] == true){ ?>
    			<small>Become an <a href='https://www.answrly.com/expert_signup'>answrly affiliate</a></small>
    		<?php } ?>
		</form>
		<?php
		if(isset($_POST['submit'])) {
			// Create question
			$data = array(
				"question[body]"  => $_POST['question'],
				"question[expert_id]" => $instance['expert_id'],
				"question[price]" => ((int)$instance['price'] * 100)
			);
			display(curl('https://www.answrly.com/questions', $data));
		}
		echo $args['after_widget'];
	}

	function update( $new_instance, $old_instance ) {
		$instance = array();
		
		$instance['username'] = ( ! empty( $new_instance['username'] ) ) ? strip_tags( $new_instance['username'] ) : '';
		$instance['widget_title'] = ( ! empty( $new_instance['widget_title'] ) ) ? strip_tags( $new_instance['widget_title'] ) : '';
		$instance['password'] = ( ! empty( $new_instance['password'] ) ) ? strip_tags( $new_instance['password'] ) : '';
		$instance['price'] = ( ! empty( $new_instance['price'] ) ) ? strip_tags( $new_instance['price'] ) : '';
		$instance['powered_link'] = $new_instance['powered_link'];
		$data = array(
			"user[username]"  => $instance['username'],
			"user[password]" => $instance['password']
		);
		$instance['json'] = curl('https://www.answrly.com/get_user', $data);
		
		if(is_json($instance['json'], FALSE)) {
			$json = json_decode($instance['json']);
			$instance['expert_id'] = $json->{'id'};
			$instance['username'] = $json->{'username'};

		}
		
		return $instance;
	}

	function form( $instance ) {
		$username = ! empty( $instance['username'] ) ? $instance['username'] : '';
		$password = ! empty( $instance['password'] ) ? $instance['password'] : '';
		$price = ! empty( $instance['price'] ) ? $instance['price'] : '25';
		$widget_title = ! empty( $instance['widget_title'] ) ? $instance['widget_title'] : 'Ask me a question';
		$powered_link = $instance['powered_link'];
		?>
		<div class='section'>
			<div class="section-header">
				Answrly.com Account Info <span class='align-right'>New user? <a href='https://www.answrly.com/expert_signup'>Sign up now!</a></span>
			</div>
			<div class="error-message">
				<?php echo !is_json($instance['json']) ? $instance['json'] : ''; ?>
			</div>
			
			<!-- Username -->
			<label 
				for="<?php echo esc_attr( $this->get_field_id( 'username' ) ); ?>">
				<?php _e( esc_attr( 'Username:')) ?>
			</label> 
			<input 
				class="widefat" 
				id="<?php echo esc_attr( $this->get_field_id( 'username' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'username' ) ); ?>"
				type="text"
				placeholder="Username"
				value="<?php echo esc_attr( $username ); ?>">
			<!-- /Username -->
			<label 
				for="<?php echo esc_attr( $this->get_field_id( 'password' ) ); ?>">
				<?php _e( esc_attr( 'Password:' ) ); ?>
			</label>
			<!-- Password -->
			<input 
				class="widefat" 
				id="<?php echo esc_attr( $this->get_field_id( 'password' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'password' ) ); ?>"
				type="password" 
				placeholder="Password"
				value="<?php echo esc_attr( $password ); ?>">
			<!-- /Password -->
		</div>
		
		
		<!-- Option -->
		<div class='section'>
			<div class='section-header'>
				Question Options
			</div>
			<!-- Price -->
			<label 
				for="<?php echo esc_attr( $this->get_field_id( 'price' ) ); ?>">
				<?php _e('Price To Charge User: (dollars) '); ?>
				<a class='align-right' href='https://www.answrly.com/pricing'>(Pricing Details)</a>
			</label> 
			<input 
				class="widefat" 
				id="<?php echo esc_attr( $this->get_field_id( 'price' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'price' ) ); ?>"
				type="number"
				onkeypress='return event.charCode >= 48 && event.charCode <= 57'
				value="<?php echo esc_attr( $price ); ?>">
			<!-- /Price -->
			<!-- Widget Title -->	
			<label 
				for="<?php echo esc_attr( $this->get_field_id( 'widget_title' ) ); ?>">
				<?php _e( esc_attr( 'Widget Title: ' ) ); ?>
			</label> 
			<input 
				class="widefat" 
				id="<?php echo esc_attr( $this->get_field_id( 'widget_title' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'widget_title' ) ); ?>"
				type="text"
				value="<?php echo esc_attr( $widget_title ); ?>" />
			<!-- /Widget Title -->
			<!-- Powered by answrly tag -->
			<input class="checkbox"
					type="checkbox" <?php checked( $instance[ 'powered_link' ], 'on' ); ?> 
					id="<?php echo $this->get_field_id( 'powered_link' ); ?>" 
					name="<?php echo $this->get_field_name( 'powered_link' ); ?>" /> 
			<label for="<?php echo $this->get_field_id( 'powered_link' ); ?>">
				<?php _e(esc_attr('Show "Become an answrly affiliate link?"')); ?>
			</label>
			<!-- /End powered by answrly tag -->
		</div>
		<?php
	}
}


function is_json($string,$return_data = false) {
	$data = json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE) ?
		($return_data ?
			$data : TRUE) : FALSE;
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

function display_error($err){ ?>
	<script type='text/javascript'>
		document.getElementById ("error-message"). innerHTML = "<?php echo $err; ?>";
	</script><?php 
}
		
function display($str) {
	preg_match('#[-a-zA-Z0-9@:%_\+.~\#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~\#?&//=]*)?#si', $str, $matches);
	if (!filter_var($matches[0], FILTER_VALIDATE_URL) === false) {?>
	    <script>
	    	window.location.replace("<?php echo $matches[0]; ?>");
	    </script> <?php
	} else { 
		display_error($str);
	}
 }

function myplugin_register_widgets() {
	register_widget( 'Answrly' );
}

add_action( 'widgets_init', 'myplugin_register_widgets' );

 ?>