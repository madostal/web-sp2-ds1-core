<?php
/**
 * Toto je DEFAULTNI user manager controller.
 * Pouzije se tento controller, pokud nebyl prepsan lokalnim ze slozky ds1-local.
 */
namespace ds1\controllers_admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use ds1\core\ds1_base_controller;


class user_manager_controller extends ds1_base_controller
{
    // timto rikam, ze je NUTNE PRIHLASENI ADMINA
    protected $admin_secured_forced = true; // vynuceno pro jistotu, ale mel by stacit kontext admin

    public function indexAction(Request $request, $page = "")
    {
        // zavolat metodu rodice, ktera provede obecne hlavni kroky a nacte parametry
        parent::indexAction($request, $page);

        // KONTROLA ZABEZPECENI
        // test, jestli je uzivatel prihlasen, pokud NE, tak redirect na LOGIN
        $this->checkAdminLogged();


        // pomocne pro test bcryptu
        //$pom = $this->ds1->user_manager->bcryptPassword("admin");
        //echo $pom; exit;

        // AKCE
        // action - typ akce
        $action = $this->loadRequestParam($request,"action", "all", "users_list_all");
        //echo "action: ".$action;

        // univerzalni content params
        $content_params = array();
        $content_params["base_url_link"] = $this->webGetBaseUrlLink();
        $content_params["page_number"] = $this->page_number;
        $content_params["route"] = $this->route;        // mam tam orders, je to automaticky z routingu
        $content_params["route_params"] = array();

        $content = "";

        // AKCE - VYPISY
        if ($action == "user_detail_show") {
            $user_id = $this->loadRequestParam($request,"user_id", "all", -1);
            $user = $this->ds1->user_manager->getUserById($user_id);

            if ($user_id > 0 && $user != null) {
                // vypis
                $content_params["user"] = $user;
                $content = $this->renderPhp("admin/user_manager/admin_user_detail_show.inc.php", $content_params, true);
            }
            else {
                $result_msg = "Uživatel nenalezen - ID nebylo získáno z URL.";
                $action = "users_list_all";
            }
        }

        if ($action == "users_list_all") {
            // vypsat vsechny uzivatele

            $count = 50;
            $where_array = array();
            // count_on_page a page se u prikazu count neuvazuje
            $total = $this->ds1->user_manager->adminLoadItems("count", 1, 1, $where_array);
            //echo "total: $total"; exit;

            // vygenerovat strankovani - obecna metoda
            $pagination_params["page_number"] = $this->page_number;
            $pagination_params["count"] = $count;
            $pagination_params["total"] = $total;
            $pagination_params["route"] = $this->route;
            $pagination_params["route_params"] = array();
            $pagination_html = $this->renderPhp("admin/partials/admin_pagination.inc.php", $pagination_params, true);
            // echo $pagination_html; exit;

            // parametry pro skript s obsahem
            $content_params["users_list_name"] = "všichni"; // dle filtru
            $content_params["user_detail_action"] = "user_detail_show";
            $content_params["users_count"] = $count;
            $content_params["users_total"] = $total;
            //$content_params["search_params"] = $search_params;
            $content_params["users_list"] = $this->ds1->user_manager->adminLoadItems("data", $this->page_number, $count, $where_array, "prijmeni", "asc");
            $content_params["pagination_html"] = $pagination_html;

            $content = $this->renderPhp("admin/user_manager/admin_users_list.inc.php", $content_params, true);
        }

        // renderovat hlavni sablonu
        $main_params = array();
        $main_params["content"] = $content;
        $main_params["title"] = "Registrovaní uživatelé - SE3";

        // vypsat hlavni template
        return $this->renderAdminTemplate($main_params);

        // pomocne
        //return new Response("Admin users eshop controller");
    }
}