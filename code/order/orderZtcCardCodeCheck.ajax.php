<?php
require_once("../config.php");
/*直通车实体卡号校验是否存在*/

$checkCardCode=ValidateZtcCardCodeISon($cardcode);
if($checkCardCode=="可以使用"){
    echo "true";
} else {
    echo "false";
}
exit;

