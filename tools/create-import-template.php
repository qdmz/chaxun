<?php
// tools/create-import-template.php
if (!class_exists('PHPExcel_IOFactory')) {
    die("需要PHPExcel库");
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

$objPHPExcel = new PHPExcel();

// 设置属性
$objPHPExcel->getProperties()
    ->setCreator("Excel数据查询系统")
    ->setLastModifiedBy("系统管理员")
    ->setTitle("用户批量导入模板")
    ->setSubject("用户数据")
    ->setDescription("用户批量导入模板文件");

// 设置当前工作表
$objPHPExcel->setActiveSheetIndex(0);
$sheet = $objPHPExcel->getActiveSheet();
$sheet->setTitle('用户数据模板');

// 设置表头
$headers = [
    'username' => '用户名（必填）',
    'password' => '密码（必填）', 
    'real_name' => '真实姓名（必填）',
    'email' => '邮箱',
    'phone' => '电话',
    'department' => '部门',
    'status' => '状态（1=正常, 0=禁用）',
    'is_approved' => '审核状态（1=已审核, 0=待审核）'
];

$col = 0;
foreach ($headers as $key => $value) {
    $sheet->setCellValueByColumnAndRow($col, 1, $value);
    
    // 设置列宽
    $sheet->getColumnDimensionByColumn($col)->setWidth(20);
    
    // 设置表头样式
    $sheet->getStyleByColumnAndRow($col, 1)
        ->getFont()->setBold(true);
    $sheet->getStyleByColumnAndRow($col, 1)
        ->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()->setRGB('E6E6FA');
    
    $col++;
}

// 添加示例数据
$examples = [
    ['user001', 'password123', '张三', 'zhangsan@example.com', '13800138000', '技术部', 1, 1],
    ['user002', 'password456', '李四', 'lisi@example.com', '13900139000', '市场部', 1, 0],
    ['user003', 'password789', '王五', '', '13700137000', '', 0, 1]
];

$row = 2;
foreach ($examples as $example) {
    $col = 0;
    foreach ($example as $value) {
        $sheet->setCellValueByColumnAndRow($col, $row, $value);
        $col++;
    }
    $row++;
}

// 添加说明
$sheet->setCellValueByColumnAndRow(0, $row+1, '说明：');
$sheet->mergeCellsByColumnAndRow(0, $row+1, 7, $row+1);
$sheet->getStyleByColumnAndRow(0, $row+1)->getFont()->setBold(true);

$sheet->setCellValueByColumnAndRow(0, $row+2, '1. 必填字段：用户名、密码、真实姓名');
$sheet->setCellValueByColumnAndRow(0, $row+3, '2. 用户名必须唯一，不能重复');
$sheet->setCellValueByColumnAndRow(0, $row+4, '3. 密码至少6位，导入后会自动加密');
$sheet->setCellValueByColumnAndRow(0, $row+5, '4. 审核状态为0时，用户需要管理员审核才能登录');
$sheet->setCellValueByColumnAndRow(0, $row+6, '5. 导入前请删除示例数据行');

// 保存文件
$filename = '用户批量导入模板_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');

exit;