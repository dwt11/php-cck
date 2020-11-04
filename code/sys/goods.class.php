<?php


if (!defined('DWTINC')) exit('Request Error!');

/**
 * ���ܹ���
 *
 * @version        $Id: sysFunction.class.php 151005
 * @package
 * @copyright
 * @license
 * @link
 */
class goods
{
    var $dsql;

    //php5���캯��
    function __construct()
    {
        $this->dsql = 0;
    }

    function goods()
    {
        $this->__construct();
    }

    //������
    function Close()
    {
    }


    /**
     *
     * �Ѿ�����Ʒ���ݿ��еĹ���
     *$urladd_temp  ��ǰ��¼ѡ�е�ֵ
     * @return array
     */

    function getInGoodsArray($urladd_temp)
    {

        $this->dsql = $GLOBALS['dsql'];
        $sys_goods_array = array();
        $query = " SELECT f.* FROM `#@__sys_goods` f  where urladd!='$urladd_temp'    ";
        $this->dsql->SetQuery($query);
        $sqlid = time();
        $this->dsql->Execute($sqlid);//$sqlid ʹ��ʱ���ǲ�ѯ,����������Ĳ�ѯ�ظ�
        while ($row = $this->dsql->GetObject($sqlid)) {
            $id = $row->id;//0
            $dir = $row->dir;//1
            $urladd = $row->urladd;//1
            //����˳������ݿ���ͬ
            $sys_goods_array[$dir][] = "$urladd";
        }
        //dump($sys_goods_array);
        return $sys_goods_array;
    }


    /**�����ʽ:   �ļ������ƣ��ļ����ƣ��ļ�����˵�����⣬�Ƿ���ת���Ƿ��в�������\r\n");
     *
     *$urladd_temp  ��ǰ��¼ѡ�е�ֵ
     *
     * @return string
     */
    function getDirFileOption($urladd_temp="")
    {
        require_once(DEDEDATA . "/sys_function_data.php");//���빦�ܵ��ı��ļ�
        $baseConfigFunArray = array();
        if (is_array($GLOBALS['baseConfigFunArray'])) $baseConfigFunArray = $GLOBALS['baseConfigFunArray'];
        //����Ѿ����������ݿ���Ĺ��ܵ�ַ ��������,���ļ��е��ж�
        //������ݿ����Ѿ����˴˹���,�����г�
        $inDateArray = $this->getInGoodsArray($urladd_temp);


        if ($baseConfigFunArray) {
            $rertur_arry = array();   //Ҫ���ص�OPTION�ַ������ȷ�������飬ÿ������ �жϸ�������1������������ļ���������0��
            //���ı��ļ����ж�
            foreach ($baseConfigFunArray as $key => $row) {
                //dump($key);
                $dirName = $key;//����ļ�������
                for ($funi = 0; $funi < count($row); $funi++) {
                    if (isset($row[$funi])) {
                        $fun_info = explode(',', $row[$funi]);  //��ȡ���ļ�������
                        $funFile = "";
                        //dump($dirName);
                        //dump($fileName_dep_array);
                        if (count($fun_info) == 1) {
                            //�ļ���
                            $funTitle = $fun_info[0];
                        } else {
                            //��ȡ�ļ�����
                            //dump($fun_info);
                            $funUrladd = $dirName . "/" . $fun_info[0];
                            $funFile = $fun_info[1];
                            $funTitle = $fun_info[1];
                        }

                        //�鿴�ļ��� �Ƿ�ʵ�ʴ���,���Ҳ�����ת����
                        //dump($funFile);
                        if ($funFile == "") //���ֻ��Ŀ¼,����ʵ�ʹ��ܵĵ�ַ,�������ɫ����,�û�����ʱ ��ʾ�û� ���������ѡ
                        {
                            $rertur_arry[$key][] = "<option value='0' style='background-color:#DFDFDB;color:#888888' >" . $funTitle . "</option>\r\n";
                        } else {
                            if (file_exists($GLOBALS["cfg_basedir"] . "/" . $funUrladd)) {
                                //����ļ��еĹ���,δ�����ݿ�����ӹ� ����ʾ
                               // dump(array_key_exists($dirName,$inDateArray));
                                if (!(count($inDateArray) > 0 && array_key_exists($dirName,$inDateArray) && in_array($funUrladd, $inDateArray[$dirName]))) {
                                   // dump($funUrladd);
                                    //dump($urladd_temp);
                                    $selected="";
                                    if($urladd_temp==$funUrladd)$selected=" selected ";
                                    $rertur_arry[$key][] = "<option value='" . $funUrladd . "' $selected>&nbsp;&nbsp;" . $funTitle . "</option>\r\n";
                                }
                            }
                        }
                    }
                }
            }
        }

        $rtuStr = "";
        //��������ļ���������0  ����� option
        foreach ($rertur_arry as $key => $row) {
            //dump($row);
            if (count($row) > 1) {
                foreach ($row as $value) {
                    $rtuStr .= $value;
                }
            }
        }

        return $rtuStr;

    }
}//End Class