<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            Seznam uživatelů - <?php echo $users_list_name; ?>
        </div>
        <div class="card-body">

            <?php

            if ($users_list != null) {
                //printr($users_list); exit;

                echo "<table class='table table-condensed table-bordered table-striped table-hover'>";
                echo "<tr>
                                    <th>#</th>
                                    <th>příjmení</th>
                                    <th>jméno</th>
                                    <th>telefon</th>
                                    <th>email</th>
                                    <th>datum vytvoření</th>
                                    <th>&nbsp;</th>
                                </tr>";

                foreach ($users_list as $user) {
                    // detail uzivatele
                    $route_params["action"] = $user_detail_action;
                    $route_params["user_id"] = $user["id"];
                    $url_detail = $this->makeUrlByRoute($route, $route_params);

                    echo "<tr>";

                        echo "<td>$user[id]</td>";
                        echo "<td>$user[prijmeni]</td>";
                        echo "<td>$user[jmeno]</td>";
                        echo "<td>$user[telefon]</td>";
                        echo "<td>$user[email]</td>";
                        echo "<td>$user[datum_vytvoreni]</td>";
                        echo "<td>
                                  <a href=\"$url_detail\" class='btn btn-primary btn-sm'><i class=\"icon-pencil\"></i></a>
                              </td>";

                    echo "</tr>";
                }

                echo "</table>";
            }
            else {
                echo "Žádní uživatelé nenalezeni.";
            }


            // stranovani
            echo $pagination_html;
            ?>
        </div>
    </div>
</div>