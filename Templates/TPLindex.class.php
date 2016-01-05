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

        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="well well-sm">
                        <h3>Вписване</h3>
                        <form action="?page=login" method="post">
                            <input type="email"
                                   class="form-control"
                                   name="loginEmail"
                                   placeholder="Въведете електронната си поща!"
                                   required><br>

                            <input type="password"
                                   class="form-control"
                                   name="loginPassword"
                                   placeholder="Въведете парола!"
                                   required><br>

                            <a href="?page=registration" class="btn btn-default">Регистрирай се!</a>

                            <button type="submit"
                                    class="btn btn-primary"
                                    name="loginSubmit">Впиши ме <span class="glyphicon glyphicon-arrow-right"></span></button>

                        </form>

                    </div>
                </div>
            </div>
        </div>

        <?php
    }
}