<?php

/**
 *  Tento controller zajistuje praci s ADMINEM.
 * Login, logout.
 */

namespace ds1\controllers_admin;

use Symfony\Component\HttpFoundation\Request;
use ds1\core\ds1_base_controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class user_admin_controller extends ds1_base_controller
{
    // MUSI BYT PUBLIC, aby se slo dostat k LOGIN FORMULARI
    protected $admin_public_forced = true;

    /**
     * Login.
     *
     * @param Request $request
     * @param $page
     * @return Response
     */
    public function loginAction(Request $request, $page = ""){
        // zavolat metodu rodice, ktera provede obecne hlavni kroky
        parent::indexAction($request, "");

        // TEST, jestli JE uzivatel prihlasen. Pokud ANO, tak redirect na DASHBOARD.
        if ($this->ds1->user_admin->isAdminLogged()) {
            // redirect na dashboard - v relative path je uz rovnou cast /admin dle contextu
            return new RedirectResponse(DS1_DOMAIN_RELATIVE_PATH_ADD.'/index.php', 307);
        }

        // action - prepare nebo go
        $action = $this->loadRequestParam($request,"action", "all", "prepare");
        //echo "action: ".$action;

        // zprava o pokusu o prihlaseni
        $result_msg = "";
        $result_ok = false;

        if ($action == "login_go") {
            // zkusit prihlasit
            $login = $this->loadRequestParam($request,"login", "post", "");
            $password = $this->loadRequestParam($request,"password", "post", "");

            // zavolat overeni recaptchy, pokud mam recaptchu
            if (trim(RECAPTCHA_SITE_KEY) != "") {
                $ok = $this->isRecaptchaOK();

                // zprava pro uzivatele
                if ($ok == false) {
                    $result_ok = false;
                    $result_msg = "Recaptcha nesouhlasí. Zaškrtněte, že nejste robot.";
                }
            }
            else {
                $ok = true;
            }

            // zavolat model
            if ($ok) {
                //printr($this->ds1->user_admin);
                $ok = $this->ds1->user_admin->Login($login, $password);

                if ($ok) {
                    // nastavit do session prihlaseneho uzivatele
                    $result_msg = "Přihlášení proběhlo úspěšně";
                    $content = "Sem by se to nemělo dostat. Mělo dojít k redirectu.";
                    $result_ok = true;

                    // redirect na dashboard - staci primo index.php, protoze v relative path uz mam /admin
                    // puvodne: /admin/index.php
                    return new RedirectResponse(DS1_DOMAIN_RELATIVE_PATH_ADD.'/index.php', 307);
                }
                else
                {
                    // chyba, nepovedlo se prihlasit
                    $result_msg = "Špatný uživatel nebo heslo";
                    $result_ok = false;
                }
            }


            // provest presmerovani dal
            if ($result_ok) {
                // je prihlasen, poslat na dashboard
            }
            else {
                // neni prihlasen, poslat na prepare
                $action = "prepare";
            }
        }

        if ($action == "prepare") {
            // login form - pouze vypsat formular
            // nacist z minisablony
            $content_params["msg"] = $result_msg;
            $content_params["result_ok"] = $result_ok;
            $content_params["base_url_link"] = $this->webGetBaseUrlLink();
            $content_params["form_submit_url"] = $this->makeUrlByRoute(DS1_ROUTE_ADMIN_LOGIN);
            $content_params["form_action"] = "login_go";

            $content = $this->renderPhp("admin/admin_login_form.inc.php", $content_params, true);
        }

        // vypsat sekci
        $main_params = array();
        $main_params["content"] = $content;
        $main_params["title"] = "Přihlášení uživatele";
        $main_params["meta_description"] = "Přihlášení uživatele.";
        $main_params["meta_keywords"] = "login";

        // vypsat hlavni template
        return $this->renderAdminTemplate($main_params, "login");
    }

    /**
     * API Login.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function apiLoginAction(Request $request){

        // zavolat metodu rodice, ktera provede obecne hlavni kroky
        parent::indexAction($request, "");

        // zprava o pokusu o prihlaseni
        $result_msg = "";
        $result_ok = false;

        // TEST, jestli JE uzivatel prihlasen. Pokud ANO, tak vratit ze ok
        if ($this->ds1->user_admin->isAdminLogged()) {
            $result_msg = "OK: uživatel je přihlášen.";
            $result_ok = true;
        }

        $json_data = file_get_contents("php://input");
        $post_data = (array) json_decode($json_data);   // zaroven pretypovat na pole
        //printr($post_data);


        if (array_key_exists("login", $post_data)) $login = $post_data["login"];
        else $login = "";

        if (array_key_exists("password", $post_data)) $password = $post_data["password"];
        else $password = "";

        if (array_key_exists("api_token", $post_data)) $api_token = $post_data["api_token"];
        else $api_token = "";

        // verit api token DS1_API_TOKEN, JINAK SE DAL NEBAVIT
        if ($api_token == DS1_API_TOKEN) {
            $ok = true;
        }
        else {
            $ok = false;
            $result_ok = false;
            $result_msg = "API token error.";
        }

        // zavolat model, pokud sedi api token
        if ($ok) {
                //printr($this->ds1->user_admin);
                $ok = $this->ds1->user_admin->Login($login, $password);

                if ($ok) {
                    // nastavit do session prihlaseneho uzivatele
                    $result_msg = "Přihlášení proběhlo úspěšně";
                    $result_ok = true;
                }
                else
                {
                    // chyba, nepovedlo se prihlasit
                    $result_msg = "Špatný uživatel nebo heslo";
                    $result_ok = false;
                }
        }

        // vratit vysledek
        $result["result_msg"] = $result_msg;
        $result["result_ok"] = $result_ok;
        $data_for_response = json_encode($result);

        // vratit v html
        //return new Response($data_for_response);

        // vratit json
        return new JsonResponse($data_for_response);
    }


    /**
     * Logout.
     *
     * @param Request $request
     * @param $page
     * @return Response
     */
    public function logoutAction(Request $request, $page = ""){
        // zavolat metodu rodice, ktera provede obecne hlavni kroky
        parent::indexAction($request, "");

        // nic to zatim nevraci
        $this->ds1->user_admin->Logout();

        // nacist z minisablony
        $content_params["msg"] = "Odhlášení proběhlo úspěšně.";
        $content_params["result_ok"] = true;
        $content_params["base_url_link"] = $this->webGetBaseUrlLink();
        $content_params["form_submit_url"] = $this->makeUrlByRoute(DS1_ROUTE_ADMIN_LOGIN);
        $content_params["form_action"] = "login_go";

        $content = $this->renderPhp("admin/admin_login_form.inc.php", $content_params, true);

        // vypsat sekci
        $main_params = array();
        $main_params["content"] = $content;
        $main_params["title"] = "Přihlášení uživatele";
        $main_params["meta_description"] = "Přihlášení uživatele.";
        $main_params["meta_keywords"] = "login";

        // vypsat hlavni template
        return $this->renderAdminTemplate($main_params, "login");
    }

}