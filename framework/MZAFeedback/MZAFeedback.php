<?php

    class MZAFeedback extends MZACustomType{

        private $settings;

        public function __construct(){
            parent::__construct();

            //Show form (only to selected roles in settings)
            add_action('init', array($this, "enqueue_form"));
            // Add the feedback form in the Admin Bar
            add_action( 'admin_bar_menu', array($this,'admin_bar'), 99);


            //Process form
            add_action( 'wp_ajax_nopriv_mzafeedback', array($this, 'ajax') );
            add_action( 'wp_ajax_mzafeedback', array($this, 'ajax') );

            // Remove "add new" link in menu
            add_action('admin_menu', array($this,'remove_menus'));

            // Add XML-RPC server
            add_filter('xmlrpc_methods', array($this, 'xmlrpc_methods'));


        }


        function admin_bar() {

            if ($this->should_show() && $this->settings->get_setting("design", "layout") == "adminbar"){

                $form = $this->get_form(false);

                global $wp_admin_bar;
                $wp_admin_bar->add_menu( array(
                    'parent' => false,
                    'id' => 'feedbacks',
                    'title' => __('Feedback', 'MZAFeedback'),
                    'href' => false,
                    'meta' => array( 'html' => $form, 'class' => 'menupop', 'onclick' => '', 'target' => '', 'title' => '' )
                ));

                add_action('admin_footer', array($this, "show_scripts"),9999);
                add_action('wp_footer', array($this, "show_scripts"),9999);
            }
        }

        function xmlrpc_methods($methods){
            $methods['mzafeedback.add'] = array($this, 'xmlrpc_get');
            return $methods;
        }

        /* MZACustomType and MZASettings setup */
        protected function setup(){
        
            $this->customBoxTitle = __("Feedback information", "MZAFeedback");

            $this->sections = array(
                "general" => array(
                    "title" => __("Feedback Info","MZAFeedback"),
                    "fields" => array(
                        "url" => array(
                            "title" =>  __('Feedback URL','MZAFeedback'),
                            "type" => "text",
                        ),
                        "page" => array(
                            "title" =>  __('Feedback Page where the Feedback was placed','MZAFeedback'),
                            "type" => "text",
                        ),
                        "site" => array(
                            "title" =>  __('Website where the Feedback was placed','MZAFeedback'),
                            "type" => "text",
                        ),
                    )
                )
            );


            if (is_admin()){

                $this->editColumns = array(
                    'cb' => '<input type="checkbox" />',
                    'title' => __( 'Title', 'MZAFeedback' )
                );

                $this->editColumns['site'] = __( ' Site ', 'MZAFeedback' );

                $this->editColumns = array_merge($this->editColumns, array(
                    'page' => '<img src="'. esc_url($this->myURL) .'/link.gif"/>' . __( 'Page', 'MZAFeedback' ),
                    'user' => __( 'User', 'MZAFeedback' ),
                    'category' => __( 'Category', 'MZAFeedback' ),
                    'date' => __( 'Date', 'MZAFeedback' )
                ));

            }

            $labels = array(
                'name' => __( 'Feedbacks', "MZAFeedback"),
                'singular_name' => __( 'Feedback', "MZAFeedback"),
                'add_new' => __( '', "MZAFeedback"),  //Hack to remove the link from edit page
                'add_new_item' => __( '', 'MZAFeedback' ), //Hack to remove the link from edit page
                'edit_item' => __( 'Edit Feedback', "MZAFeedback"),
                'new_item' => __( 'New Feedback', "MZAFeedback"),
                'view_item' => __( 'Ver Feedback', "MZAFeedback"),
                'search_items' => __( 'Search Feedback', "MZAFeedback"),
                'not_found' => __( 'Feedbacks not found', "MZAFeedback"),
                'not_found_in_trash' => __('Feedbacks not found', "MZAFeedback"),
                'parent_item_colon' => __( 'Parent Feedback:', 'MZAFeedback' ),
                'menu_name' => __( 'Feedback', 'MZAFeedback' ),
                'all_items' => __( 'All Feedbacks', 'MZAFeedbacks')
            );


            $this->params = array(
                'labels' => $labels,
                'public' => false,
                'show_ui' => true,
                'capability_type' => 'post',
                'hierarchical' => false,
                'rewrite' => array('Feedback'),
                'menu_position' => 1000,
                'supports' => array('title', 'editor')
            );


            $this->taxonomies = array(
                "feedback-category" => array(
                    'hierarchical' => false,
                    'labels' => array(
                                    'name' => __( 'Categories', 'MZAFeedback' ),
                                    'singular_name' => __( 'Category', 'MZAFeedback' ),
                                    'search_items' =>  __( 'Search Categories', 'MZAFeedback' ),
                                    'all_items' => __( 'All Categories', 'MZAFeedback' ),
                                    'parent_item' => __( 'Parent Category', 'MZAFeedback' ),
                                    'parent_item_colon' => __( 'Parent Category:', 'MZAFeedback' ),
                                    'edit_item' => __( 'Edit Category', 'MZAFeedback' ),
                                    'update_item' => __( 'Update Category', 'MZAFeedback' ),
                                    'add_new_item' => __( 'Add New Category', 'MZAFeedback' ),
                                    'new_item_name' => __( 'New Category', 'MZAFeedback' ),
                                    'menu_name' => __( 'Categories' ),
                                ),
                    'show_ui' => true,
                    'query_var' => true,
                    'rewrite' => array( 'slug' => 'milestone' ),
                )
            );

            $sections = array(

                "general" => array(
                    "title" => __("General","MZAFeedback"),
                    "fields" => array(
                        "role" => array(
                            "title" => __("Show feedback form only to users within this user role:","MZAFeedback"),
                            "type" => "role",
                            "default" => "administrator"
                        )
                    )
                ),
                "design" => array(
                    "title" => __("Layout and design","MZAFeedback"),
                    "fields" => array(
                        "logo" => array(
                            "title" => __("Your company logo","MZAFeedback"),
                            "description" => __("Max. Size 250x45px. URL starting with http://", "MZAFeedback"),
                            "type" => "text"
                        ),
                        "layout" => array(
                            "title" => __("Feedback form placement","MZAFeedback"),
                            "type" => "radio",
                            "default" => "topright",
                            "options" => array(
                                "topright" => __("Top Right", "MZAFeedback"),
                                "topleft" => __("Top Left", "MZAFeedback"),
                                "bottomright" => __("Bottom Right", "MZAFeedback"),
                                "bottomleft" => __("Bottom Left", "MZAFeedback"),
                                "adminbar" => __("Admin Bar - ( Warning: Only works when the Admin Bar is visible, both in front end and admin  )", "MZAFeedback")
                            )
                        )
                    )
                ),
                "remote" => array(
                    "title" => __("Remote WordPress", "MZAFeedback"),
                    "description" => __("Setting this up will post each new feedback to a remote WordPress so you can centralize all your client's feedback. That WordPress needs to have XML-RPC enabled (in Settings->Writing) and this same plugin activated.","MZAFeedback"),
                    "fields" => array(
                        "xmlrpc" => array(
                            "title" => __("Post local feedbacks to remote WordPress:","MZAFeedback"),
                            "type" => "checkbox",
                            "options" => array(
                                "true" => ""
                            )
                        ),
                        "xmlrpc_url" => array(
                            "title" => __("Remote WordPress URL", "MZAFeedback"),
                            "type" => "text",
                            "description" => __("URL starting with http:// and without trailing slash", "MZAFeedback")
                        ),
                        "xmlrpc_user" => array(
                            "title" => __("Remote WordPress Username", "MZAFeedback"),
                            "type" =>"text"
                        ),
                        "xmlrpc_pass" => array(
                            "title" => __("Remote WordPress Password", "MZAFeedback"),
                            "type" => "password"
                        ),
                    )
                )
            );

            $this->settings = new MZASettings($this->customTypeName, 'edit.php?post_type=' . $this->customTypeName, $sections);
            $this->settings->settingsPageTitle = __("Feedback settings", "MZAFeedback");
            $this->settings->settingsLinkTitle = __("Settings", "MZAFeedback");

        }

        private function should_show(){

           if (is_user_logged_in()){
                global $wp_roles;
                $current_user = wp_get_current_user();
                $roles = $current_user->roles;
                $role = array_shift($roles);
                return ( $role == $this->settings->get_setting("general", "role") );
            } else {
                return false;
            }
        }


        private function get_form($show_button = true){

            $class = "mzatop mzaright";
            $subclass = "";

            switch ($this->settings->get_setting("design", "layout")){
                case "topright";
                    $class = "mzatop mzaright";
                    break;
                case "topleft";
                    $class = "mzatop mzaleft";
                    break;
                case "bottomright";
                    $class = "mzabottom mzaright";
                    break;
                case "bottomleft";
                    $class = "mzabottom mzaleft";
                    break;
                case "adminbar":
                    $subclass=" class='ab-sub-wrapper' ";
                    $class = "adminbar";
                    break;
            }

            $categories = wp_dropdown_categories('taxonomy=feedback-category&echo=0&name=MZAFCategory&hide_if_empty=true&hide_empty=0');
            if ($categories != ""){
                $categories = "<label for='MZAFCategory'>" . __("Feedback category:", "MZAFeedback") ."</label>" . $categories;
                $categories = str_replace("\"", '\'', $categories);
            }

            $logo = $this->settings->get_setting("design", "logo");
            if ($logo != ""){
                $logo = "<img src='". esc_url($logo) ."'/>";
                $logo = "<div id='MZAFLogo'>" . $logo . "</div>";
                $logo = wptexturize($logo);
            }

            $nonce = wp_nonce_field('MZAFeedbackForm', "MZAFeedbackForm_nonce", true, false);
            $nonce = str_replace("\"", '\'', $nonce);

            $page = $this->get_current_page_name();
            $page = esc_attr($page);

            if ($show_button){
                $form ="<div id='MZAFeedbackWindow' class='". $class ."'>";
            }else{


                $form = "<ul" . $subclass . "><li><span id='MZAFeedbackWindow'>";
            }

            if ($show_button){
                $form .="<div id='MZAFeedbackButton'>
                            <span>" . __("Feedback", "MZAFeedback") ."</span>
                        </div>";
            }

            $form .= $logo;

            $form .=    "<form name='MZAFeedbackForm' id='MZAFeedbackForm' action=''>";

            $form .= $nonce;
            $form .=        "<input type='hidden' name='MZAFeedbackPage' value='" . $page ."'>
                            <fieldset>
                                <label for='MZAFTitle'>" . __("Feedback title:", "MZAFeedback") ."</label>
                                <input type='text' name='MZAFTitle' id='MZAFTitle'/>

                                <label for='MZAFFeedback'>" . __("Feedback content:", "MZAFeedback") ."</label>
                                <textarea name='MZAFFeedback' id='MZAFFeedback'></textarea>

                                ". $categories ."

                                <input type='submit' name='MZAFFSubmit' id='MZAFFSubmit' value='" . __("Submit Feedback", "MZAFeedback") . "'  />

                            </fieldset>
                        </form>";
            if ($show_button){
                $form .="</div>";
            }else{
                $form .= "</span></li></ul>";
            }


           $form = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $form);

           return $form;
        }

        public function insert_form(){

            $form = $this->get_form(true);

            ?>

            <script type="text/javascript">
            /* <![CDATA[ */
            jQuery("body").append("<?php echo $form; ?>");
            /* ]]> */
            </script>

            <?php

            echo $this->show_scripts();
        }


        public function show_scripts(){
            $jsSide = "Top";
            switch ($this->settings->get_setting("design", "layout")){
                case "topright";
                    $jsSide = "Top";
                    break;
                case "topleft";
                    $jsSide = "Top";
                    break;
                case "bottomright";
                    $jsSide = "Bottom";
                    break;
                case "bottomleft";
                    $jsSide = "Bottom";
                    break;
                case "adminbar";
                    $jsSide = "Top";
                    break;
            }


            ?>

            <script type="text/javascript">
            /* <![CDATA[ */

                jQuery(document).ready(function() {

                    <?php if (!is_admin()){ ?>
                    var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' );?>';
                    <?php } ?>

                    jQuery("#MZAFeedbackButton").click(function($){
                        if ( jQuery("#MZAFeedbackWindow").css("margin-<?php echo $jsSide; ?>") == "-355px"){
                              jQuery("#MZAFeedbackWindow").animate({
                                  margin<?php echo $jsSide; ?>: 0
                              }, 700);
                       }else{
                            jQuery("#MZAFeedbackWindow").animate({
                                  margin<?php echo $jsSide; ?>: -375
                              }, 300, function(){
                                  jQuery("#MZAFeedbackWindow").animate({
                                    margin<?php echo $jsSide; ?>: -355
                                  }, 150);
                              });
                        }
                    });

                    jQuery("#MZAFeedbackForm").submit(function(){

                        jQuery("#MZAFFSubmit").attr("disabled", "disabled");

                        jQuery("#MZAFFSubmit").attr("value", "<?php _e("Sending...", "MZAFeedback");  ?>");

                        jQuery("#MZAFFSubmit").css("background", "#D45500 no-repeat right url(<?php echo $this->myURL;?>/loader.gif)");

                        jQuery.post(ajaxurl, {
                                action : 'mzafeedback',
                                form: jQuery("#MZAFeedbackForm").serialize()
                            },
                            function( response ) {
                                jQuery("#MZAFeedbackButton").click();
                                jQuery('#MZAFeedbackForm').each (function(){
                                  this.reset();
                                });
                                jQuery("#MZAFFSubmit").removeAttr("disabled");
                                jQuery("#MZAFFSubmit").attr("value", "<?php _e("Submit Feedback", "MZAFeedback")  ?>");
                                jQuery("#MZAFFSubmit").css("background", "");

                            }
                        );
                        return false;
                    });

                });
           /* ]]> */
            </script>
            <?php

        }

        /* Hooks */

        public function ajax(){

            header( "Content-Type: application/json" );

            if (!isset($_POST["form"])){
                $response = json_encode( array( 'success' => false, 'message' => 'Form data not posted' ) );
                echo $response;
                die();
            }

            $data = wp_parse_args($_POST["form"]);

            if ( !isset($data["MZAFeedbackForm_nonce"]) ||  !wp_verify_nonce( $data["MZAFeedbackForm_nonce"], 'MZAFeedbackForm' )  ){
                $response = json_encode( array( 'success' => false, 'message' => 'Invalid nonce!' ) );
                echo $response;
                die();
            }

            $my_post = array(
                'post_type' => $this->customTypeName,
                'post_title' => esc_attr($data["MZAFTitle"]),
                'post_content' => esc_textarea($data["MZAFFeedback"]),
                'post_status' => 'publish',
                'post_author' => get_current_user_id()
            );

            $new_id = wp_insert_post( $my_post );

            if ( isset($data["MZAFCategory"]) )
                wp_set_object_terms( $new_id, array(intval($data["MZAFCategory"])), 'feedback-category');

            if ( isset($data["_wp_http_referer"]) )
                $this->set_meta($new_id, "url", "http://" . $_SERVER['HTTP_HOST'] . $data["_wp_http_referer"]);

            if ( isset($data["MZAFeedbackPage"]) )
                $this->set_meta($new_id, "page", $data["MZAFeedbackPage"]);

            $this->xmlrpc_put($new_id);

            $response = json_encode( array( 'success' => true, 'message' =>  '' ) );
            echo $response;

            die();
        }

        public function enqueue_form(){

            if ($this->should_show()):

                add_action("wp_enqueue_scripts", array($this, "enqueue_scripts"),1);
                
                if ($this->settings->get_setting("design", "layout") != "adminbar"){
                    add_action("wp_footer", array($this, "insert_form"));
                    add_action("admin_footer", array($this, "insert_form"));

                }

                add_action("wp_print_styles", array($this, "load_css"));
                add_action("admin_print_styles", array($this, "load_css"));
                               
            endif;
        }

        public function enqueue_scripts(){
            wp_enqueue_script("jquery");
        }

        public function load_css(){

            wp_register_style("MZAFeedback_css", $this->myURL . "/styles.dev.css");
            wp_enqueue_style("MZAFeedback_css");

        }

        private function get_current_page_name(){
            if (is_admin()){
                $current = get_admin_page_title();
            }else{
                if (is_front_page()){
                    $current = "Homepage";
                }else{
                    $current = wp_title('&raquo;', false);
                }
            }
            return $current;
        }

        public function remove_menus () {
            remove_submenu_page("edit.php?post_type=mzafeedback", "post-new.php?post_type=mzafeedback");
        }


        // custom columns for admin edit post lists
        public function columns_content( $column, $post_id ) {
            global $post;

            switch( $column ) {



                case 'site' :
                    $site = $this->get_meta($post_id, "site");
                    echo sprintf( '%s', esc_html($site) );
                    break;

                case 'page' :
                    $url = $this->get_meta($post_id, "url");
                    $page = $this->get_meta($post_id, "page");
                    echo sprintf( '<a href="%s">%s</a>', esc_url($url), esc_html($page) );
                    break;

                case 'user' :
                    echo sprintf( '<a href="%s">%s</a>',
                                esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'author' => get_the_author_meta('ID') ), 'edit.php' ) ),
                                esc_html( get_the_author() )
                            );
                    break;
                
                case 'category' :
                    $terms = get_the_terms( $post_id, 'feedback-category' );
                    if ( !empty( $terms ) ) {
                        foreach ( $terms as $term ) {
                            $out[] = sprintf( '<a href="%s">%s</a>',
                                esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'feedback-category' => $term->slug ), 'edit.php' ) ),
                                esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'feedback-category', 'display' ) )
                            );
                        }

                        echo join( ', ', $out );
                    }
                    break;

                default :
                    break;
            }

        }


        //XML-RPC server and client
        public function xmlrpc_get($args){
            $username	= $args[0];
            $password	= $args[1];
            $data = $args[2];
            global $wp_xmlrpc_server;

            if ( !$user = $wp_xmlrpc_server->login($username, $password) ) {
                return $wp_xmlrpc_server->error;
            }
            $my_post = array(
                'post_type' => $this->customTypeName,
                'post_title' => esc_attr($data["title"]),
                'post_content' => esc_textarea($data["content"]),
                'post_status' => 'publish',
                'post_author' => $user->ID
            );

            $new_id = wp_insert_post( $my_post );

        
            if ( isset($data["category"]) ){

                foreach(maybe_unserialize( $data["category"] ) as $cat){
                    $id = term_exists( $cat->name, 'feedback-category' );
                    if ( !$id ){
                        $id = wp_insert_term( $cat->name, 'feedback-category' );
                    }

                    wp_set_object_terms( $new_id, $id->term_id, 'feedback-category');
                }
            }

            if ( isset($data["url"]) )
                $this->set_meta($new_id, "url",  esc_url($data["url"]));

            if ( isset($data["page"]) )
                $this->set_meta($new_id, "page", $data["page"]);

            if ( isset($data["site"]) )
                $this->set_meta($new_id, "site", esc_attr($data["site"]));



            return "OK!";
        }

        private function xmlrpc_put($post_id){

            if ($this->settings->get_setting("remote", "xmlrpc") == "true"){

                $post = get_post($post_id);
                if ($post){
                    $data = array(
                        "title" => esc_html($post->post_title),
                        "content" => esc_html($post->post_content),
                        "site" => get_bloginfo("name"),
                        "url" => $this->get_meta($post->ID, "url"),
                        "page" => $this->get_meta($post->ID, "page"),
                        "category" => maybe_serialize( get_the_terms( $post->ID, "feedback-category" ) )
                    );

                    $params = array($this->settings->get_setting("remote", "xmlrpc_user"), $this->settings->get_setting("remote", "xmlrpc_pass"), $data);
                    $params = xmlrpc_encode_request('mzafeedback.add', $params);

                    $request = new WP_Http;

                    return $request->request($this->settings->get_setting("remote", "xmlrpc_url")."/xmlrpc.php",
                        array('method' => 'POST', 'body' => $params));

                   
                }
            }
            return false;
        }
    }

?>