<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            Detail uživatele - <?php echo "$user[jmeno] $user[prijmeni]"; ?>
        </div>
        <div class="card-body">
        <?php

            $titles = array();

            $titles["id"] = "ID";
            $titles["jmeno"] = "Jméno";
            $titles["prijmeni"] = "Příjmení";
            $titles["telefon"] = "Telefon";
            $titles["email"] = "Email";

            echo $this->helperPrintBasicTable($user, array("id", "jmeno", "prijmeni", "telefon", "email"), $titles);

            //printr($user);

            /*
            $titles_order = array();
            $titles_order["id"] = "ID";
            $titles_order["jmeno"] = "Jméno";
            $titles_order["prijmeni"] = "Příjmení";
            $titles_order["datum_vytvoreni"] = "Vytvořeno";

            echo "<br/><h2>Přehled objednávek</h2>";

            echo $this->helperPrintBasicTableRecords($orders_for_user,
                array("id", "jmeno", "prijmeni", "adresa_ulice", "adresa_mesto", "adresa_psc", "vs", "cena_celkem_s_dph", "created_date"), $titles_order);

            //printr($orders_for_user);
            */


        ?>
        </div>
    </div>
</div>
