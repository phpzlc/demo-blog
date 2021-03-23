<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2021/3/22
 */

namespace PHPZlc\Admin\Strategy\Excel;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Export
{
    /**
     * 单个工作区最大行数
     *
     * @var int
     */
    public $sheetMaxSite = 60000;

    /**
     * 文件流输出
     *
     * @param $title
     * @param $spreadsheet
     * @param $format
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function output($title, Spreadsheet $spreadsheet, $format = 'Xls')
    {
        ob_end_clean();//清除缓冲区,避免乱码

        if($format == 'Xls'){
            header('Content-Type: application/vnd.ms-excel');
        }else{
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }

        header('Content-Disposition: attachment; filename="' . $this->exFileName($title) . date('YmdHis') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = IOFactory::createWriter($spreadsheet, $format);
        $objWriter->save('php://output');
        exit;
    }


    /**
     * 导出
     *
     * @param string $title  标题
     * @param array $head 表头 ["表头1", "表头2"]
     * @param array $data 内容 [{"1", "1"}]
     * @param bool $is_need 是否插入序号
     * @param array $mergeCells 合并单元格 [{0,1,0,2})] 0,1 表示合并开始单元格数组坐标， 0,2 代表合并结束单元格数组坐标
     * @param string $format
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function export($title, $head, $data, $is_need = false, $mergeCells = array(), $format = 'Xls')
    {
        set_time_limit(0);
        ini_set("memory_limit", "1024M"); // 设置php可使用内存

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if(empty($data)){
            $this->setHenderValue($sheet, $head, $is_need);
        }else{
            $data = array_chunk($data , $this->sheetMaxSite);

            foreach ($data as $key => $datum){
                if ($key > 0) {
                    $spreadsheet->createSheet();
                    $sheet = $spreadsheet->getActiveSheet();
                }

                $this->setHenderValue($sheet, $head, $is_need);

                foreach ($datum as $item => $value){
                    if ($is_need) {
                        $sheet->setCellValueExplicit('A' . ($item + 2), ($key * $this->sheetMaxSite) + ($item + 1), DataType::TYPE_STRING);
                    }

                    foreach ($value as $i => $v){
                        if($is_need){
                            $i = $i + 1;
                        }

                        $sheet->setCellValueExplicit($this->stringFromColumnIndex($i) . ($item + 2), $v, DataType::TYPE_STRING);
                    }
                }
            }
        }

        //合并单元格
        if(!empty($mergeCells)) {
            foreach ($mergeCells as $value) {
                $x1 = $value[0];
                $y1 = $value[1] + 2;
                $x2 = $value[2];
                $y2 = $value[3] + 2;

                if($is_need){
                    $x1 = $x1 + 1;
                    $x2 = $x2 + 1;
                }

                $x1 = $this->stringFromColumnIndex($x1);
                $x2 = $this->stringFromColumnIndex($x2);

                $column_key1 = ceil($y1 / $this->sheetMaxSite);
                $column_key2 = ceil($y2 / $this->sheetMaxSite);

                if($column_key1 == $column_key2){
                    $y1 = $y1 - ($this->sheetMaxSite * ($column_key1 - 1));
                    $y2 = $y2 - ($this->sheetMaxSite * ($column_key2 - 1));
                    $sheet = $spreadsheet->getSheet($column_key1 - 1);
                    $sheet->mergeCells($x1 . $y1 . ':' . $x2 . $y2);
                }else{
                    $y1 = $y1 - ($this->sheetMaxSite * ($column_key1 - 1));
                    $y2 = $y2 - ($this->sheetMaxSite * ($column_key2 - 1));
                    $sheet = $spreadsheet->getSheet($column_key1 - 1);
                    $sheet->mergeCells($x1 . $y1 . ':' . $x2 . ($sheetMaxSize + 1));
                    $sheet = $spreadsheet->getSheet($column_key2 - 1);
                    $sheet->mergeCells($x1 . '2' . ':' . $x2 . $y2);
                }
            }
        }

        $this->output($title, $spreadsheet, $format);
    }

    private function setHenderValue(Worksheet $worksheet, $head, $is_need)
    {
        if($is_need){
            $worksheet->setCellValueExplicit( 'A1', '序号', DataType::TYPE_STRING);
        }

        foreach ($head as $item => $value){
            if($is_need){
                $item = $item + 1;
            }

            $worksheet->setCellValueExplicit($this->stringFromColumnIndex($item) . '1', $value, DataType::TYPE_STRING);
        }
    }

    /**
     * 导出名称处理
     *
     * @param $name
     * @return string
     */
    private function exFileName($name)
    {
        $http_user_agent = $_SERVER['HTTP_USER_AGENT'];

        if(preg_match('/MSIE/i', $http_user_agent)){
            $name = urlencode($name);
        }

        return iconv('UTF-8', 'GBK//IGNORE', $name);
    }

    /**
     * 数字转字母
     * 导入excel时 第一栏是键值0->A 1->B 2->C 25->Z 26->AA
     *
     * @param int $pColumnIndex
     * @return mixed
     */
    private function stringFromColumnIndex($pColumnIndex = 0)
    {
        //  Using a lookup cache adds a slight memory overhead, but boosts speed
        //  caching using a static within the method is faster than a class static,
        //      though it's additional memory overhead
        static $_indexCache = array();

        if (!isset($_indexCache[$pColumnIndex])) {
            // Determine column string
            if ($pColumnIndex < 26) {
                $_indexCache[$pColumnIndex] = chr(65 + $pColumnIndex);
            } elseif ($pColumnIndex < 702) {
                $_indexCache[$pColumnIndex] = chr(64 + ($pColumnIndex / 26)) . chr(65 + $pColumnIndex % 26);
            } else {
                $_indexCache[$pColumnIndex] = chr(64 + (($pColumnIndex - 26) / 676)) . chr(65 + ((($pColumnIndex - 26) % 676) / 26)) . chr(65 + $pColumnIndex % 26);
            }
        }

        return $_indexCache[$pColumnIndex];
    }
}