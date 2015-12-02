<?php
/**
 * Created by PhpStorm.
 * User: Georgievi
 * Date: 10.11.2015 ã.
 * Time: 14:10 ÷.
 */

class TPLerror extends Template {
    public function Title() {
        return "Page not found";
    }

    public function Body() {
        ?>
            <h1> No such page exists</h1>
        <?php
    }
}