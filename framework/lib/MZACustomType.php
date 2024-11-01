<?php

if (class_exists("MZACustomType"))
    return;

    abstract class MZACustomType{

        protected $className;
        protected $customTypeName;

        protected $sections = null;
        protected $params = null;
        protected $taxonomies = null;
        protected $editColumns = null;

        protected $builder = null;

        protected $customBoxTitle = "Details";

        protected $myPath;
        protected $myURL;

        abstract protected function setup();

        public static function factory($module, $path){
            if (include_once $path . "/". $module . '/' . $module . '.php') {
                return new $module;
            } else {
                throw new Exception('Module not found!');
            }
        }

        function __construct(){

            $this->className = get_class($this);
            $this->customTypeName = strtolower($this->className);

            $this->myPath = dirname(__FILE__) . "/" . $this->className;
            $this->myPath = str_replace("lib/", "", $this->myPath);

            $this->myURL = plugins_url('', $this->myPath) . "/" . $this->className;

            $this->setup();

            add_action('init', array($this, "registerType"));

            $this->builder =  new MZAFormBuilderForTypes($this->className, $this->className, $this->sections, array($this, "update_sections"));


            //add custom columns to admin edit post list (if defined)
            if ($this->editColumns){
                add_filter( 'manage_edit-' . $this->customTypeName . '_columns', array($this, 'columns_edit') ) ;
                add_action( 'manage_'.$this->customTypeName. '_posts_custom_column', array($this, 'columns_content'), 10, 2 );
            }

        }


        public function update_sections($sections, $post_id){
            $this->sections = $sections;

            foreach($this->sections as $key_section => $section){
               foreach ($section["fields"] as $key_field => $field){
                   if ($post_id){
                       update_post_meta($post_id, "_". $key_field, $this->sections[$key_section]["fields"][$key_field]["value"]);
                   }
               }
           }
        }

        public function columns_edit($columns){
            return $this->editColumns;
        }

        public function different_meta_values( $meta_key ) {
            global $wpdb;

            return $wpdb->get_results( $wpdb->prepare("SELECT distinct meta_value
                    FROM $wpdb->postmeta m inner join $wpdb->posts p
                    on m.post_id = p.id
                    where m.meta_value!='' p.post_type = '%s' and m.meta_key = '%s'  ", $this->customTypeName, $this->get_field_name( $meta_key )), ARRAY_A );
        }


        //setup

        public function registerType(){


            $sections = $this->get_sections();

            if (!empty($sections)){
                $this->params['register_meta_box_cb'] =  array($this, 'add_areas_custom_box');
            }

            if (file_exists($this->myPath . "/icon.png")){
                $this->params['menu_icon'] =  $this->myURL . "/icon.png";
            }

            register_post_type($this->customTypeName, $this->params);

            $taxonomies = $this->get_taxonomies();
            if (!empty($taxonomies)){
                foreach($taxonomies as $key => $taxonomy ){
                    register_taxonomy($key, $this->customTypeName, $taxonomy);
                }
            }

        }

        public function add_areas_custom_box(){
            $sections = $this->get_sections();
            if (!empty($sections)){
                add_meta_box( $this->customTypeName . '_areas_custom_box', $this->customBoxTitle,
                    array($this,'inner_custom_box'), $this->customTypeName, 'normal', 'high' );
            }
        }

        public function inner_custom_box($post){

            global $post;

            $this->load_sections($post->ID);

            $this->builder->sections = $this->sections;

            $this->builder->do_form();
        }


        private function load_sections($post_id){
            $customs = get_post_custom_keys($post_id);
            foreach($this->sections as $key_section => $section){
                foreach ($section["fields"] as $key_field => $field){
                    $val = "";

                    if (is_array($customs) && in_array("_" . $key_field, $customs)){
                        $val = $this->get_meta($post_id, $key_field);
                    }else{
                        if (isset($field["default"]))
                            $val = $field["default"];
                    }
                    $this->sections[$key_section]["fields"][$key_field]["value"] = $val;
                }
            }
        }


        private function _get($args=array()){
            global $wpdb;

            $defaults = array(
                "count" => 0
            );

            $args= wp_parse_args($args, $defaults);

            $query = array(
                "post_type" => $this->customTypeName,
                "post_status" => array("publish"),
                "suppress_filters" => 0,
                "order" => "DESC",
                "orderby" => "date",
                "posts_per_page" => -1
            );

            if ($args["count"] > 0){
                $query["posts_per_page"] = $args["count"];
            }

            $objects = get_posts($query);

            return $objects;
        }
        

        public function get_meta($post_id, $meta_name){
            return get_post_meta($post_id, "_" . $meta_name, true);
        }

        public function set_meta($post_id, $meta_name, $value){
            return update_post_meta($post_id, "_" . $meta_name, $value);
        }

        public function get_name(){
            return $this->customTypeName;
        }

        // Filters

        private function get_sections(){
            return apply_filters( $this->className . "_sections", $this->sections );
        }

        private function get_taxonomies(){
            return apply_filters( $this->className . "_taxonomies", $this->taxonomies );
        }



    }

?>
