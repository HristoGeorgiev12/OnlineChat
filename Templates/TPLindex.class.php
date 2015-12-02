<?php
/**
 * Created by PhpStorm.
 * User: Georgievi
 * Date: 10.11.2015 г.
 * Time: 14:10 ч.
 */

class TPLindex extends Template {

    protected function Title() {
        return "Index";
    }

    protected function Body() {
            if(isset($_SESSION['unsuccessfulLogin'])) {
                echo $_SESSION['unsuccessfulLogin'];
            }elseif(isset($_SESSION['successfulLogin'])) {
                header("Location:?page=chat");
            }
        ?>
            <h1>Влезте в профила си!</h1>
            <form action="?page=login" method="post">
                <input type="email"
                        name="loginEmail"
                        placeholder="Въведете електронната си поща!"
                        required><br>
                <input type="password"
                        name="loginPassword"
                        placeholder="Въведете парола!"
                        required><br>
                <input type="submit"
                        name="loginSubmit"
                        value="Впиши ме">
            </form>
            <a href="?page=registration">Регистрирай се!</a>
        <?php
    }
}