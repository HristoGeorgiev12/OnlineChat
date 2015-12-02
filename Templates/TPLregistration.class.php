<?php
/**
 * Created by PhpStorm.
 * User: Georgievi
 * Date: 10.9.2015 г.
 * Time: 19:29 ч.
 */

class TPLregistration extends Template {
    private function insertRegistrationArray() {
        if(isset($_POST['submitRegistration'])) {
            //If the passwords match, continue with the registration;
            if($this->aParam['userPassword'] == $this->aParam['userPasswordConfirm']) {

                $insertParams = array();
                $insertParams['nickName'] = $this->aParam['nickName'];
                $insertParams['email'] = $this->aParam['userEmail'];
                $insertParams['password'] = md5($this->aParam['userPassword']);

                //check is the Email in users DB
                    //if true, don`t insert
                    //else insert
                $emailCheck = $this->selectWhere('chat','users',$insertParams);
                if(empty($emailCheck)) {
                    //insert into DB;
                    $this->insert('chat','users',$insertParams);
//                    return 'Вие успешно създадохте своя профил!';
                    $_SESSION['successfulLogin']=$insertParams['nickName'];
                    header("Location:?page=chat");
                }else {
                    return "Вече има създаден профил с този Email адрес.";
                }
            }else {
                return "Въведениете от вас пароли не съвпадат.";
            }
        }elseif(isset($_POST['returnToPreviousPage'])) {
            header('Location:?page=chat');
            exit;
        }
    }

    public function Title() {
        return "Create an account";
    }

    public function Body() {

        echo $this->insertRegistrationArray();

        ?>
        <h3>Регистрация</h3>
        <form action="" method="post">

            <input type="text"
                   name="nickName"
                   placeholder="Име"
                   required><br>

            <input type="email"
                   name="userEmail"
                   placeholder="Емайл адрес"
                   required><br>

            <input type="password"
                   name="userPassword"
                   placeholder="Парола"
                   required><br>

            <input type="password"
                   name="userPasswordConfirm"
                   placeholder="Повторни Паролата"
                   required><br>

            <input type="submit"
                   name="submitRegistration"
                   value="Регистрирай ме"
                   required><br>

            <a href="?page=index" > << Обратно към главната станица.</a>
        </form>
        <?php
    }

}