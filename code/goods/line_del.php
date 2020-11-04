<?php
require_once('../config.php');

//$id = trim(preg_replace("#[^0-9]#", '', $id));

$ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");

if ($id == '') {
    ShowMsg("参数无效！", $$ENV_GOBACK_URL);
    exit();
}

$query = "DELETE FROM `#@__line` WHERE id in($id)";
//dump($query);

if ($dsql->ExecuteNoneQuery($query)) {

    ShowMsg("成功删除！", $$ENV_GOBACK_URL);
    exit();

} else {
    ShowMsg("删除失败！", $$ENV_GOBACK_URL);
    exit();


}




