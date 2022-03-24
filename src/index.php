<?php
######################################################################
# Wi-Fi-voucher-o-matic-2.0
# Copyright (C) 2022 Marco Schiavello, Ivan Bertotto, ITIS Avogadro
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.If not, see <http://www.gnu.org/licenses/>.
######################################################################

include_once 'load.php';
include_once LIB_PATH.'/library.php';

if(isset($_POST['indexSubmit'])) {
    
}

printHead('Richiesta Token', 
          [ 'style.css' ],
          [ ],
          false);
?>

<?php include_once COMP_PATH.'/logoBox.php';?>

<div class="page-cont container container--gapM page-size">
    <h1 class="page-cont__title">Richiedi il tuo token</h1>
    
    <form class="page-cont__form container container--gapS" method="POST" action="index.php">
        <?php 
            inputText("name", "Nome"); 
            inputText("surname", "Cognome"); 
            inputText("email", "E-Mail"); 
        ?>
    </form>

    <input class="button" type="submit" name="indexSubmit" value="Richiedi">
</div>

<?php printFooter(); ?>