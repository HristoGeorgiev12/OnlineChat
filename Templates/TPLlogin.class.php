<?php
/**
 * Created by PhpStorm.
 * User: Georgievi
 * Date: 12.11.2015 г.
 * Time: 16:28 ч.
 */

class TPLlogin extends Template {
    protected function loginCheck() {
        //Login into Account;
        if(isset($_POST['loginSubmit'])) {
            $insertParams = array();
            $insertParams['email'] = $this->aParam['loginEmail'];
            $insertParams['password'] = md5($this->aParam['loginPassword']);

            //return matching results;
            $result = $this->selectWhere('chat','users',$insertParams);

            if(!empty($result)) {
                $_SESSION['successfulLogin']=$result['nickName'];
                $_SESSION["userId"]=$result["id"];
                $_SESSION["check"] = 12;
                header("Location:?page=chat");
                exit;
            }else{
//                TODO: if not valid locate to a new page(like facebook)
//                    TODO; rewrite to message if no email or password match;
                $_SESSION['unsuccessfulLogin']="Грешна парола.";
                header("Location:?page=index");
                exit;
            }
        }


        //Logout from account;
        if(isset($_POST['logout'])) {
            session_destroy();
            header("Location:?page=index");
            exit;
        }

    }

    protected function Title() {
        return "LoginCheck";
    }

    protected function Body() {
        echo "hi";
       echo  $this->loginCheck();
        var_dump($this->loginCheck());
    }
}