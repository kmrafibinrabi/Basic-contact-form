<?php
/**
 * Plugin Name: Simple Contact Form
 * Description: Minimalistic Contact Form
 * Author: K M Rafi Bin RABI
 * AUthor URI: kmraf.website
 * Version:1.0.0
 * Text Domain: simple-contact-form
 * 
 */

if (!defined('ABSPATH')) {
    echo 'silence is golden';
    exit();
}

class SimpleContactForm {

    public function __construct() {
        //CREATE CUSTOM POST
        add_action('init', array($this, 'create_custom_post_type'));

        //ADD ASSETS(JS,CSS)
        add_action('wp_enqueue_scripts', array($this, 'load_assets'));

        //ADD SHORTCODE
        add_shortcode('contact-form', array($this, 'load_shortcode'));

        //ADD FOOTER SCRIPTS
        add_action('wp_footer', array($this, 'load_scripts'));

        //REGISTER REST API
        add_action('rest_api_init', array($this, 'register_rest_api'));
    }

    public function create_custom_post_type() {
        $args = array(
            'public' => true,
            'has_archive' => true,
            'supports' => array('title'),
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'labels' => array(
                'name' => 'Contact Form',
                'singular_name' => 'Contact Form Entry'
            ),
            'menu_icon' => 'dashicons-media-text'
        );
        register_post_type('simple_contact_form', $args);
    }

    public function load_assets() {
        wp_enqueue_style(
            'simple-contact-form',
            plugin_dir_url(__FILE__) . 'css/simple-contact-form.css',
            array(),
            '1',
            'all'
        );

        wp_enqueue_script(
            'simple-contact-form',
            plugin_dir_url(__FILE__) . 'js/simple-contact-form.js',
            array('jquery'),
            '1',
            true
        );
    }

    public function load_shortcode() 
    {?>
        <div class="simple-contact-form">
            <h1>Send us an email</h1>
            <p>Please fill out the form below</p>
            <form id="simple-contact-form__form">
    <div class="form-group mb-2">    
            <input type="text" name="name" placeholder="Name" class="form-control">
    </div>
    <div class="form-group mb-2"> 
                <input type="email" name="email" placeholder="Email" class="form-control">
                </div>
                <div class="form-group mb-2"> 
                <input type="tel" name="phone" placeholder="Phone" class="form-control">
                </div>
                <div class="form-group mb-2"> 
                    <textarea name="message" placeholder="Type your message" class="form-control"></textarea> 
                    </div>
                    <div class="form-group"> 
                    <button type="submit" class="btn btn-success btn-block">Send Message</button>
                    </div>
            </form>
        </div>
        <?php
        
    }


 
public function load_scripts() 
{?>
     <script>  

(function ($){


    var nonce = '<?php echo wp_create_nonce('wp_rest'); ?>';
    $('#simple-contact-form__form').submit (function(event){
        event.preventDefault();


    var form= $(this).serialize();

    $.ajax
({

method: 'post',
url: '<?php echo get_rest_url(null, 'simple-contact-form/v1/send-email');?>',
headers: {'X-WP-Nonce': nonce } ,
data: form


})


     
});  

})(jQuery)


     </script>

<?php }



public function register_rest_api() 
{
    register_rest_route(
        'simple-contact-form/v1',
        'send-email',
        array(
            'methods'  => 'POST',
            'callback' => array($this, 'handle_contact_form')
        
            ));
}

public function handle_contact_form($data)
{

    $headers = $data->get_headers();
    $params = $data-> get_params();
    $nonce = $headers['x_wp_nonce'][0]; 
    
    if(!wp_varify_nonce($nonce,'wp_rest'))
    {
    
        return new WP_REST_Response('massage not sent',422);
    }
    $post_id = wp_insert_post([
    
    
    
        'post_type' =>'simple_contact_form',
        'post_title' => 'contact enquiry',
        'post_status' => 'publish'
    ]);
    if($post_id){

        return new WP_REST_Response('thank you',200);
    }

}
 
 
}


  new SimpleContactForm;  

