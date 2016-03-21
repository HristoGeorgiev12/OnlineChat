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
        <div class="container" >
            <div class="row">
                <div class="col-md-4">
                    <div class="well well-sm center">
                        <h4>Регистрация</h4>
                        <form action="" method="post">

                            <input type="text"
                                   class="form-control"
                                   name="nickName"
                                   placeholder="Име"
                                   required><br>

                            <input type="email"
                                   class="form-control"
                                   name="userEmail"
                                   placeholder="Емайл адрес"
                                   required><br>

                            <input type="password"
                                   class="form-control"
                                   name="userPassword"
                                   placeholder="Парола"
                                   required><br>

                            <input type="password"
                                   class="form-control"
                                   name="userPasswordConfirm"
                                   placeholder="Повторни Паролата"
                                   required><br>

                            <a href="?page=index" class="btn btn-warning"><span class="glyphicon glyphicon-circle-arrow-left"></span> Назад</a>
<!---->
<!--                            <input type="submit"-->
<!--                                   class="btn btn-primary pull-right"-->
<!--                                   name="submitRegistration"-->
<!--                                   value="Регистрирай ме!"-->
<!--                                   required>-->

                            <button type="submit" class="btn btn-primary pull-right" name="submitRegistration">Регистрирай ме <span class="glyphicon glyphicon-user"></span></button>


                        </form>
                    </div>
                </div>
            </div>

        </div>



        <?php
    }

}