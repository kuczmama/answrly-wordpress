<?php 
    /*
    Plugin Name: Answrly
    Plugin URI: https://www.answrly.com
    Description: The answrly plugin allows you to easily <strong>answer your user"s questions for money</strong>.  To get started: 1) Click the "Activate" link to the left of this description, 2) Sign up as an expert at <a href="https://www.answrly.com/expert_signup">answrly.com</a> 3) Answrly will appear as an available widget.  4) Place Answrly Widget at desired location ie. Sidebar, Content Bottom, etc.  5)  In the Answrly Widget settings enter your answrly username and password to link your acount to wordpress  6) Customize Widget Options if desired 7) You"re ready to start making money with your blog
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
			"classname" => "answrly_widget",
			"description" => "Answrly lets your users pay you for your knowledge",
		);
		parent::__construct( "answrly_widget", "Answrly", $widget_ops );
	}

	function widget( $args, $instance ) {
		// Widget output
		$title = apply_filters( "widget_title", ( ! empty( $instance["widget_title"] ) ) ? sanitize_text_field( $instance["widget_title"] ) : __("Ask me a question") );
		// before and after widget arguments are defined by themes
		echo $args["before_widget"];
		if ( ! empty( $title ) ) {
			echo $args["before_title"] . sanitize_title($title) . $args["after_title"];
		}

		?>
		<div id="error-message" style="color: #ff0000; font-weight: 900;text-align: center;">
			<!-- Display the error message -->
		</div>
		<form method="post" id="question-form">
			<textarea form="question-form" name="question" placeholder="Type your question here..." ></textarea>
    		<input type="submit" value="Ask" name="submit">
    		<!-- Show "Become and answrly affiliate link -->
    		<?php if($instance["powered_link"] == true){ ?>
    			<small>
    				Become an 
    				<a href="<?php echo esc_url("https://www.answrly.com/expert_signup") ?>">
    					answrly affiliate
    				</a>
    			</small>
    		<?php } ?>
    		<!-- /end becfome and answrly affiliate link -->
		</form>
		<?php
		if(isset($_POST["submit"])) {
			// Create question
			$data = array(
				"question[body]"  => sanitize_text_field($_POST["question"]),
				"question[expert_id]" => $instance["expert_id"],
				"question[price]" => ((int)$instance["price"] * 100)
			);
			answrly_display(answrly_curl("https://www.answrly.com/questions", $data));
		}
		echo $args["after_widget"];
	}

	function update( $new_instance, $old_instance ) {
		$instance = array();
		
		$instance["username"] = ( ! empty( $new_instance["username"] ) ) ? sanitize_text_field( $new_instance["username"] ) : "";
		$instance["widget_title"] = ( ! empty( $new_instance["widget_title"] ) ) ? sanitize_text_field( $new_instance["widget_title"] ) : "";
		$instance["password"] = ( ! empty( $new_instance["password"] ) ) ? sanitize_text_field( $new_instance["password"] ) : "";
		$instance["price"] = ( ! empty( filter_var($new_instance["price"], FILTER_VALIDATE_INT))) ? sanitize_text_field( $new_instance["price"] ) : "";
		$instance["powered_link"] = sanitize_text_field($new_instance["powered_link"]) == "on";
		$data = array(
			"user[username]"  => $instance["username"],
			"user[password]" => $instance["password"]
		);
		$instance["json"] = answrly_curl(esc_url("https://www.answrly.com/get_user"), $data);
		
		if(answrly_is_json($instance["json"], FALSE)) {
			$json = json_decode($instance["json"]);
			$instance["expert_id"] = sanitize_text_field($json->{"id"});
			$instance["username"] = sanitize_text_field($json->{"username"});

		}
		
		return $instance;
	}

	function form( $instance ) {
		$username = ! empty( $instance["username"] ) ? $instance["username"] : "";
		$password = ! empty( $instance["password"] ) ? $instance["password"] : "";
		$price = ! empty( $instance["price"] ) ? $instance["price"] : "25";
		$widget_title = ! empty( $instance["widget_title"] ) ? $instance["widget_title"] : "Ask me a question";
		$powered_link = $instance["powered_link"];
		?>
		<div style="margin-top: 1em;">
			<div style="text-align: left; font-weight: 900;">
				Answrly.com Account Info
			</div>
			<div class="error-message">
				<?php echo !answrly_is_json($instance["json"]) ? $instance["json"] : ""; ?>
			</div>
			
			<!-- Username -->
			<label 
				for="<?php echo esc_attr( $this->get_field_id( "username" ) ); ?>">
				<?php _e( esc_attr( "Username:")) ?>
				<span style="float: right;">
					New user? 
					<a href=<?php echo esc_url("https://www.answrly.com/expert_signup") ?>>
						Sign up now!
					</a>
				</span>
			</label> 
			<input 
				class="widefat"
				id="<?php echo esc_attr( $this->get_field_id( "username" ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( "username" ) ); ?>"
				type="text"
				placeholder="<?php echo esc_attr("Username") ?>"
				value="<?php echo esc_attr( $username ); ?>">
			<!-- /Username -->
			<label 
				for="<?php echo esc_attr( $this->get_field_id( "password" ) ); ?>">
				<?php _e( esc_attr( "Password:" ) ); ?>
			</label>
			<!-- Password -->
			<input 
				class="widefat" 
				id="<?php echo esc_attr( $this->get_field_id( "password" ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( "password" ) ); ?>"
				type="password" 
				placeholder="<?php echo esc_attr("Password") ?>"
				value="<?php echo esc_attr( $password ); ?>">
			<!-- /Password -->
		</div>
		
		
		<!-- Option -->
		<div style="margin-top: 1em;">
			<div style="text-align: left; font-weight: 900;">
				<?php echo esc_html("Question Options") ?>
			</div>
			<!-- Price -->
			<label 
				for="<?php echo esc_attr( $this->get_field_id( "price" ) ); ?>">
				<?php echo _e(esc_attr("Price To Charge User: (dollars) ")); ?>
				<a style="float: right;" href=<?php echo esc_url("https://www.answrly.com/pricing") ?>>(Pricing Details)</a>
			</label> 
			<input 
				class="widefat" 
				id="<?php echo esc_attr( $this->get_field_id( "price" ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( "price" ) ); ?>"
				type="number"
				onkeypress="<?php echo esc_js("return event.charCode >= 48 && event.charCode <= 57") ?>"
				value="<?php echo esc_attr( $price ); ?>">
			<!-- /Price -->
			<!-- Widget Title -->	
			<label 
				for="<?php echo esc_attr( $this->get_field_id( "widget_title" ) ); ?>">
				<?php _e( esc_attr( "Widget Title: " ) ); ?>
			</label> 
			<input 
				class="widefat" 
				id="<?php echo esc_attr( $this->get_field_id( "widget_title" ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( "widget_title" ) ); ?>"
				type="text"
				value="<?php echo esc_attr( $widget_title ); ?>" />
			<!-- /Widget Title -->
			<!-- Powered by answrly tag -->
			<input class="checkbox"
					type="checkbox"
					<?php if(esc_attr($powered_link)){ echo "checked" ;} ?>
					id="<?php echo esc_attr($this->get_field_id( "powered_link" )); ?>" 
					name="<?php echo esc_attr($this->get_field_name( "powered_link" )); ?>" /> 
			<label for="<?php echo esc_attr($this->get_field_id( "powered_link" )); ?>">
				<?php _e(esc_attr("Show 'Become an answrly affiliate link?'")); ?>
			</label>
			<!-- /End powered by answrly tag -->
		</div>
		<!-- /options -->
		<?php
	}
}


function answrly_is_json($string, $return_data = false) {
	$data = json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE) ?
		($return_data ?
			$data : TRUE) : FALSE;
}

function answrly_curl($url, $data){
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

function answrly_display_error($err){ ?>
	<script type="text/javascript">
		document.getElementById ("error-message"). innerHTML = "<?php echo esc_html($err); ?>";
	</script><?php 
}
		
function answrly_display($str) {
	preg_match("#[-a-zA-Z0-9@:%_\+.~\#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~\#?&//=]*)?#si", $str, $matches);
	if (!filter_var($matches[0], FILTER_VALIDATE_URL) === false) {?>
	    <script>
	    	window.location.replace("<?php echo esc_url($matches[0]); ?>");
	    </script> <?php
	} else { 
		answrly_display_error($str);
	}
 }

function answrly_register_widgets() {
	register_widget( "Answrly" );
}

add_action( "widgets_init", "answrly_register_widgets" );

 ?>