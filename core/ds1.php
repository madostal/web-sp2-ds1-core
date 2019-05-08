<?php

namespace ds1\core;

// symfony session
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;

class ds1 extends ds1_base_model
{
    public $loader = null;

    public $user_admin = null; // objekt admina
    public $user_manager = null; // sprava uzivatelu

    /** @var ds1_session */
    public $session = null;

    // todo
    public $helper = null;

    // moduly pro admina a pro web
    public $modules_admin = null;
    public $modules_web = null;

    // musi byt public, aby k tomu mohli vsichni primo
    public $symfony_url_generator = null;



    /**
     * se3 constructor.
     * @param $se3_loader - simple eshop 3 loader
     * @param $symfony_url_generator UrlGenerator
     */
    public function __construct($ds1_loader, $symfony_url_generator = null)
    {
        $this->loader = $ds1_loader;

        // symfony url generator - generuje url dle definice routes
        $this->symfony_url_generator = $symfony_url_generator;

        // vlastni session na obaleni
        $this->session = new ds1_session();
        $this->session->start();

        // vytvorit ostatni objekty, ktere budu potrebovat asi vzdy
        // pripadne by je slo prenest do index.php jako zavislosti
        $this->user_admin = new ds1_user_admin();

        $this->user_manager = new ds1_user_manager();

    }

    public function setModulesForAdmin($modules) {

        // nacist konfiguraci k modulum = settings_file, kde jsou konstanty pro vsechny moduly
        if ($modules != null)
            foreach ($modules as $index => $module) {

                $add_module = true;

                // zpracovat jen moduly typu admin_plugin = DS1_MODULE_TYPE_ADMIN_PLUGIN
                if (array_key_exists("type", $module))
                    if ($module["type"] == DS1_MODULE_TYPE_ADMIN_PLUGIN) {
                        // je to ok
                        $add_module = true;
                    }
                    else {
                        // nepridavat
                        $add_module = false;
                    }


                if (isset($module["settings_file"]) && $add_module) {
                    //printr($module);
                    $settings_file = DS1_DIR_ADMIN_MODULES_FROM_ADMIN.$module["name"]."/".$module["settings_file"].".php";
                    //printr($settings_file);

                    if (file_exists($settings_file) && is_file($settings_file)) {
                        // nacist soubor
                        include_once($settings_file);
                    }
                }

                if ($add_module == false) {
                    // nechci pridavat, tak vyhodim ze seznamu modulu = vyhodit api moduly
                    unset($modules[$index]);
                }
            }

        // pridat az na konci
        $this->modules_admin = $modules;
    }

    public function getModulesForAdmin() {
        return $this->modules_admin;
    }

    /**
     *  Prepsat connect u rodice
     */
    public function Connect()
    {
        // zavolat rodice
        parent::Connect();

        // doplnit neco vlastniho
        if ($this->GetPDOConnection() != null) {
            $connection = $this->GetPDOConnection();

            //echo "mam connection"; exit;
            // poslat to mym objektum
            $this->user_admin->SetPDOConnection($connection);
            $this->user_manager->SetPDOConnection($connection);
        }
    }

    public function getPokus() {
        return "pokus z ds1";
    }
}