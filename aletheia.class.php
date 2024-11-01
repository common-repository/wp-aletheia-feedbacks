<?php

final class Aletheia{

    /* MEMBERS */

    public $modules = array("MZAFeedback" => null);
    private $modules_path = 'framework';
    private $myPath;

    /* PUBLIC METHODS */
    public function __construct(){

        $this->myPath = dirname(__FILE__);

        $this->load_modules();

        $this->customTaxonomies();
        $this->customPostTypes();
        $this->customAddons();
        $this->hooks();
    }

    public function load_modules(){

        require_once $this->myPath . "/" . $this->modules_path . '/lib/MZAFormBuilderForTypes.php';
        require_once $this->myPath . "/" . $this->modules_path . '/lib/MZAFormBuilderForSettings.php';
        require_once $this->myPath . "/" . $this->modules_path . '/lib/MZACustomType.php';
        require_once $this->myPath . "/" . $this->modules_path . '/lib/MZASettings.php';
    }


    /* PRIVATE METHODS */

    private function customTaxonomies(){
    }

    private function customPostTypes(){

        foreach ($this->modules as $module => $object){
            $this->modules[$module] = MZACustomType::factory($module, $this->myPath . "/" . $this->modules_path);
        }

    }

    private function customAddons(){
    }

    private function hooks(){
    }

}
?>