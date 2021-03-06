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

require_once 'load.php';
require_once LIB_PATH.'/library.php';

$errors = array( 1 => 'Hai troppe richeste attive aspetta al massimo un ora per riprovare',
                 2 => 'Hai già 10 token aspetta che scadano',
                 3 => 'Errore nel inviare la mail con il token di autenticazione',
                 4 => 'Non ci sono più token disponibili aspetta che l\'ammistratore della rete li rimetta',
                 5 => 'Email non valida');


if(isset($_POST['indexSubmit'])) {
    $db = DB::instace();
    $authCode = strtoupper(bin2hex(random_bytes(30)));

    $email = trim($_POST['email']);
    $name = trim($_POST['name']);
    $surname = trim($_POST['surname']);

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: index.php?err=5');
        die();
    }
    
    $privDomDuration = privDomainDuration($email); 
    $ramainingTokens = $db->numberRemainingToken($privDomDuration);
    if($ramainingTokens <= 5) {
        notifyLackingOfToken($privDomDuration, $ramainingTokens > 0 ? $ramainingTokens - 1 : 0);
        if($ramainingTokens === 0) {
            header('Location: index.php?err=4');
            die();
        }
    }

    $queryRes = $db->genericSimpleSelect([ 'COUNT(*)' ], 'users', array( 'user_email' => $email));

    $numUser = (int) mysqli_fetch_array($queryRes)[0];

    if($numUser !== 1) {
        $db->genericSimpleInsert(array('user_name' => $name,
                                       'user_surname' => $surname,
                                       'user_email' => $email), 'users');
    }

    $queryRes = $db->genericSimpleSelect([ 'user_id' ], 'users', array( 'user_email' => $email));

    $userId = mysqli_fetch_array($queryRes)[0];

    if($db->numberOfToken($userId, 'auth') >= 10) {
        header('Location: index.php?err=1');
        die();
    } else if($db->numberOfToken($userId, 'wifi') >= 10) {
        header('Location: index.php?err=2');
        die();
    }

    $db->genericSimpleInsert(array('auth_code_value' => $authCode,
                                   'fk_user_id' => $userId), 'auth_codes');

    $emailRes = sendMail($email, 
                         $name.' '.$surname,
                         'Avo Wi-Fi - Autenticazione',
                         'Gentile '.$name.' '.$surname.', il suo codice di autenticazione della richesta che dovrà inserire nella pagina apposita è: <br><br> <strong>'.$authCode.'</strong> <br><br> o può direttamente <br/><br><a class="button" style="color:#FFFFFF" href="'.baseUrl().'authCode.php?authCode='.$authCode.'">Cliccare Qui</a>');

    if(!$emailRes) {
        header('Location: index.php?err=3');
        die();
    }

    header('Location: authCode.php');
    die();
}

printHead('Richiesta Token', 
          [ 'style.css' ],
          [ ],
          true);
?>

<div class="page-cont container container--gapM page-size">
    <h1 class="page-cont__title">Richiedi il tuo token</h1>
    
    <form class="page-cont__form container container--gapXS" method="POST" action="index.php">
        <?php 
            inputText("name", "Nome"); 
            inputText("surname", "Cognome"); 
            inputText("email", "E-Mail", "email"); 
        ?>
        <input class="button button--marginTop" type="submit" name="indexSubmit" value="Invia">
        <?php echo isset($_GET['err']) ? '<h5 class="err">' . $errors[$_GET['err']] . '</h5>' : '' ?>
    </form>
</div>

<?php printFooter(); ?>
