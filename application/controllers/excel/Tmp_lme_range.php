<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class Tmp_lme_range extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('spreadsheet_lib');
        $this->load->library('session');
        $this->load->model('crud');
    }
    public function index()
    {
        if ($this->session->username != "") {
            $excel = $this->spreadsheet_lib->newSpreadsheet();
            $spreadsheet = $excel->getActiveSheet(0);
            $excel->getProperties()->setCreator('Pixelphy')
                ->setLastModifiedBy('Pixelphy')
                ->setTitle('Office 2007 XLSX Test Document')
                ->setSubject('Office 2007 XLSX Test Document')
                ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
                ->setKeywords('office 2007 openxml php')
                ->setCategory('PT BANSHU ELECTRIC INDONESIA');
            $style_col = array(
                'font' => array('bold' => true), // Set font nya jadi bold
                'alignment' => array(
                    'horizontal' => 'center', // Set text jadi ditengah secara horizontal (center)
                    'vertical' => 'center' // Set text jadi di tengah secara vertical (middle)
                ),
                'borders' => array(
                    'top' => array('borderStyle'  => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN), // Set border top dengan garis tipis
                    'right' => array('borderStyle'  => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN),  // Set border right dengan garis tipis
                    'bottom' => array('borderStyle'  => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN), // Set border bottom dengan garis tipis
                    'left' => array('borderStyle'  => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN) // Set border left dengan garis tipis
                ),
                'fill' => array(
                    'fillType' => PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => array('rgb' => 'FFC000')
                )
            );
            $style_row = [
                'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                'borders' => [
                    'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
                ]
            ];
            $style_row_center = array_merge($style_row, ['alignment' => ['horizontal' => 'center', 'vertical' => 'center']]);
            $style_row_right = array_merge($style_row, ['alignment' => ['horizontal' => 'right', 'vertical' => 'center']]);
            $styleRequired = array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => 'FF0000')
                )
            );
            // $spreadsheet = $excel->createSheet(0);
            //SHEET 1 ------------------------------------------------------------------------------------------------------------
            //Coloumn
            $spreadsheet->setCellValue('A1', 'Supplier ID');
            $spreadsheet->setCellValue('B1', 'LME');
            $spreadsheet->setCellValue('B2', 'Start');
            $spreadsheet->setCellValue('C2', 'End');
            $spreadsheet->setCellValue('D1', 'Remarks');
            $spreadsheet->setCellValue('E1', 'Type');
            $spreadsheet->mergeCells('A1:A2');
            $spreadsheet->mergeCells('B1:C1');
            $spreadsheet->mergeCells('D1:D2');
            $spreadsheet->mergeCells('E1:E2');
            $spreadsheet->getStyle('A1:A2')->applyFromArray($style_col);
            $spreadsheet->getStyle('B1:C1')->applyFromArray($style_col);
            $spreadsheet->getStyle('B2')->applyFromArray($style_col);
            $spreadsheet->getStyle('C2')->applyFromArray($style_col);
            $spreadsheet->getStyle('D1:D2')->applyFromArray($style_col);
            $spreadsheet->getStyle('E1:E2')->applyFromArray($style_col);
            $spreadsheet->getStyle('E1:E2')->applyFromArray($styleRequired);
            $spreadsheet->getStyle('A1:C2')->applyFromArray($styleRequired);
            // Add comment to Type column header
            $comment = $spreadsheet->getComment('E1');
            $comment->getText()->createTextRun("Option :\nLME/SHMET");
            $comment->setWidth('200pt');
            $comment->setHeight('60pt');
            //Set Width
            $spreadsheet->getColumnDimension('A')->setAutoSize(true);
            $spreadsheet->getColumnDimension('B')->setAutoSize(true);
            $spreadsheet->getColumnDimension('C')->setAutoSize(true);
            // Rename worksheet
            $spreadsheet->setTitle('Template');
            $spreadsheet = $excel->createSheet();
            $excel->setActiveSheetIndex(1);
            $spreadsheet->setTitle('Data Supplier');
            $columns = ['No', ' ID', ' Name'];
            $alfa = 'A';
            $row_no = 1;
            foreach ($columns as $key => $value) {
                $spreadsheet->setCellValue($alfa . $row_no, $value);
                $spreadsheet->getStyle($alfa . $row_no)->applyFromArray($style_col);
                $alfa++;
            }
            $datas = $this->db->select("number, name")->from("suppliers")->order_by("name")->get()->result();
            $row_no++;
            foreach ($datas as $index => $row) {
                $no = $index + 1;
                $alfa = 'A';
                $spreadsheet->setCellValue($alfa . $row_no, $no);
                $spreadsheet->getStyle($alfa . $row_no)->applyFromArray($style_row_center);
                $spreadsheet->getColumnDimension($alfa)->setAutoSize(true);
                foreach ($row as $key => $value) {
                    $alfa++;
                    $style = $style_row;
                    if (in_array($key, [''])) {
                        $style = $style_row_center;
                    }
                    if (in_array($key, [''])) {
                        $style = $style_row_right;
                    }
                    if (in_array($key, [''])) {
                        $spreadsheet->setCellValueExplicit($alfa . $row_no, $value, DataType::TYPE_STRING);
                    } else {
                        $spreadsheet->setCellValue($alfa . $row_no, $value);
                    }
                    $spreadsheet->getStyle($alfa . $row_no)->applyFromArray($style);
                    $spreadsheet->getColumnDimension($alfa)->setAutoSize(true);
                }
                $row_no++;
            }
            $excel->setActiveSheetIndex(0);
            // Redirect output to a client’s web browser (Xlsx)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Template LME Range.xlsx"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');
            $writer = IOFactory::createWriter($excel, 'Xlsx');
            $writer->save('php://output');
            exit;
        } else {
            redirect('login');
        }
    }
}
