<?php
/**
 * 获得字段创建SQL信息170108
 *
 * @access    public
 *
 * @param     string $dtype     字段类型
 * @param     string $fieldname 字段名称
 * @param     string $dfvalue   默认值
 *
 * @return    array
 */
function GetFieldMake($dtype, $fieldname, $dfvalue)
{
    $fields = array();
    if ($dtype == "int" || $dtype == "datetime"|| $dtype == "date") {
        if ($dfvalue == "" || preg_match("#[^0-9-]#", $dfvalue)) {
            $dfvalue = 0;
        }
        $fields[0] = " `$fieldname` int(11) NOT NULL default '$dfvalue';";
        $fields[1] = "int(11)";
    } else if ($dtype == "stepselect") {
        if ($dfvalue == "" || preg_match("#[^0-9\.-]#", $dfvalue)) {
            $dfvalue = 0;
        }
        $fields[0] = " `$fieldname` char(20) NOT NULL default '$dfvalue';";
        $fields[1] = "char(20)";
    } else if ($dtype == "float") {
        if ($dfvalue == "" || preg_match("#[^0-9\.-]#", $dfvalue)) {
            $dfvalue = 0;
        }
        $fields[0] = " `$fieldname` float(11,2) NOT NULL default '$dfvalue';";
        $fields[1] = "float(11,2)";
    }  else if ($dtype == "multitext" || $dtype == "htmltext") {
        $fields[0] = " `$fieldname` mediumtext;";
        $fields[1] = "mediumtext";
    }  else if ($dtype == "checkbox"||$dtype == "select" || $dtype == "radio") {
        //此类型的默认值为逗号分隔的第一个选项，
        //表单显示时，从模型中读出，然后多个显示
        $dfvalue_array=explode(",",$dfvalue);
        $dfvalue_t = $dfvalue_array[0];
        $mxlen = 250;
        $fields[0] = " `$fieldname` varchar($mxlen) NOT NULL default '$dfvalue_t';";
        $fields[1] = "varchar($mxlen)";
    }  else {
        //类型为 varchar img  的都默认为varchar
        if (empty($dfvalue)) {
            $dfvalue = '';
        }
        $mxlen = 250;
        $fields[0] = " `$fieldname` varchar($mxlen) NOT NULL default '$dfvalue';";
        $fields[1] = "varchar($mxlen)";
    }
    return $fields;
}

