<?php

namespace App\Services;

use ZipArchive;

class ExcelService
{
    public function parseExcelFile($filePath, $originalExtension = null)
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'xls']) && !empty($originalExtension)) {
            $ext = strtolower($originalExtension);
        }

        if ($ext === 'xlsx') {
            return $this->parseXlsxFile($filePath);
        } elseif ($ext === 'xls') {
            return $this->parseXlsFile($filePath);
        }

        return ['error' => '不支持的文件格式，请上传.xlsx或.xls文件'];
    }

    public function parseExcelContent($content)
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        if (count($lines) < 2) {
            return ['error' => '数据格式错误，至少需要表头和一行数据'];
        }

        $headers = array_map('trim', explode("\t", $lines[0]));

        $requiredFields = ['学号', '姓名', '家长手机号'];
        $fieldIndices = [];
        foreach ($requiredFields as $field) {
            $idx = array_search($field, $headers);
            if ($idx === false) {
                return ['error' => "缺少必需列: {$field}"];
            }
            $fieldIndices[$field] = $idx;
        }

        $subjects = [];
        for ($i = 0; $i < count($headers); $i++) {
            $header = trim($headers[$i]);
            if (!in_array($header, $requiredFields)) {
                $subjects[] = $header;
            }
        }

        if (empty($subjects)) {
            return ['error' => '请至少包含一列成绩数据'];
        }

        $students = [];
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            $data = array_map('trim', explode("\t", $line));

            $studentId = isset($data[$fieldIndices['学号']]) ? $data[$fieldIndices['学号']] : '';
            $studentName = isset($data[$fieldIndices['姓名']]) ? $data[$fieldIndices['姓名']] : '';
            $parentPhone = isset($data[$fieldIndices['家长手机号']]) ? $data[$fieldIndices['家长手机号']] : '';

            if (empty($studentId) || empty($studentName) || empty($parentPhone)) {
                continue;
            }

            $scores = [];
            foreach ($subjects as $subject) {
                $idx = array_search($subject, $headers);
                $score = isset($data[$idx]) ? $data[$idx] : '';
                $scores[$subject] = $score;
            }

            $students[] = [
                'student_id' => $studentId,
                'student_name' => $studentName,
                'parent_phone' => $parentPhone,
                'scores' => $scores
            ];
        }

        if (empty($students)) {
            return ['error' => '未找到有效学生数据'];
        }

        return [
            'success' => true,
            'subjects' => $subjects,
            'students' => $students
        ];
    }

    private function getSharedStrings($zip)
    {
        $sharedStrings = [];

        $xmlContent = $zip->getFromName('xl/sharedStrings.xml');
        if ($xmlContent !== false) {
            $xml = simplexml_load_string($xmlContent);
            if ($xml) {
                foreach ($xml->si as $si) {
                    $value = '';
                    if (isset($si->t)) {
                        $value = (string)$si->t;
                    } elseif (isset($si->r)) {
                        foreach ($si->r->t as $t) {
                            $value .= (string)$t;
                        }
                    }
                    $sharedStrings[] = $value;
                }
            }
        }

        return $sharedStrings;
    }

    private function parseXlsxFile($filePath)
    {
        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            return ['error' => '无法读取Excel文件，请确保文件未损坏'];
        }

        $sharedStrings = $this->getSharedStrings($zip);

        $xmlContent = '';
        $sheetNames = ['xl/worksheets/sheet1.xml', 'xl/worksheets/sheet.xml'];

        foreach ($sheetNames as $sheetName) {
            $xmlContent = $zip->getFromName($sheetName);
            if ($xmlContent !== false) {
                break;
            }
        }
        $zip->close();

        if (empty($xmlContent)) {
            return ['error' => '无法读取工作表内容，请确保文件包含有效的工作表'];
        }

        $xml = simplexml_load_string($xmlContent);
        if (!$xml) {
            return ['error' => '无法解析Excel文件内容'];
        }

        $namespaces = $xml->getNamespaces(true);
        $ns = $namespaces[''] ?: '';
        $sheetData = $xml->children($ns)->sheetData;

        $rows = [];
        foreach ($sheetData->row as $row) {
            $cells = [];
            $maxCol = 0;

            foreach ($row->c as $cell) {
                $cellAttrs = $cell->attributes();
                $colLetter = (string)$cellAttrs['r'];
                preg_match('/^([A-Z]+)(\d+)$/', $colLetter, $matches);
                $col = 0;
                if (isset($matches[1])) {
                    $colLetter = $matches[1];
                    for ($i = 0; $i < strlen($colLetter); $i++) {
                        $col = $col * 26 + (ord($colLetter[$i]) - ord('A') + 1);
                    }
                    $col--;
                }

                $maxCol = max($maxCol, $col);
                $value = '';

                $cellType = (string)$cellAttrs['t'];

                if (isset($cell->v)) {
                    $v = (string)$cell->v;

                    if ($cellType === 's' && isset($sharedStrings[(int)$v])) {
                        $value = $sharedStrings[(int)$v];
                    } elseif ($cellType === 'n') {
                        $value = $v;
                        if (strpos($value, '.') !== false && rtrim($value, '0') === rtrim($value, '0.')) {
                            $value = rtrim($value, '0');
                            $value = rtrim($value, '.');
                        }
                    } else {
                        $value = $v;
                    }
                } elseif (isset($cell->t)) {
                    $value = (string)$cell->t;
                }

                while (count($cells) <= $col) {
                    $cells[] = '';
                }
                $cells[$col] = trim($value);
            }

            if (!empty(array_filter($cells))) {
                $rows[] = $cells;
            }
        }

        return $this->parseRows($rows);
    }

    private function parseXlsFile($filePath)
    {
        $content = file_get_contents($filePath);

        if (strlen($content) < 512) {
            return ['error' => '文件内容太小，可能不是有效的Excel文件'];
        }

        $offset = 0;
        $rows = [];
        $maxRow = 0;

        while ($offset < strlen($content) - 4) {
            $recordType = ord($content[$offset]) | (ord($content[$offset + 1]) << 8);
            $recordLength = ord($content[$offset + 2]) | (ord($content[$offset + 3]) << 8);
            $offset += 4;

            if ($recordLength <= 0 || $offset + $recordLength > strlen($content)) {
                break;
            }

            if ($recordType == 0x0200) {
                $row = ord($content[$offset]) | (ord($content[$offset + 1]) << 8);
                $maxRow = max($maxRow, $row);
            } elseif ($recordType == 0x0201) {
                $col = ord($content[$offset]) | (ord($content[$offset + 1]) << 8);
                $row = ord($content[$offset + 2]) | (ord($content[$offset + 3]) << 8);
                $offset += 4;

                $type = ord($content[$offset]);
                $offset++;

                $length = ord($content[$offset]) | (ord($content[$offset + 1]) << 8);
                $offset += 2;

                if ($offset + $length > strlen($content)) {
                    $offset += $recordLength - 8;
                    continue;
                }

                $value = substr($content, $offset, $length);
                $offset += $length;

                if ($type == 0x0010 || $type == 0x0030) {
                    try {
                        $value = iconv('UTF-16LE', 'UTF-8', $value);
                    } catch (\Exception $e) {
                        $value = trim($value);
                    }
                } else {
                    $value = trim($value);
                }

                while (count($rows) <= $row) {
                    $rows[] = [];
                }
                while (count($rows[$row]) <= $col) {
                    $rows[$row][] = '';
                }
                $rows[$row][$col] = $value;
            } else {
                $offset += $recordLength;
            }
        }

        $validRows = [];
        foreach ($rows as $rowData) {
            if (!empty(array_filter($rowData))) {
                $validRows[] = $rowData;
            }
        }

        return $this->parseRows($validRows);
    }

    private function parseRows($rows)
    {
        if (count($rows) < 2) {
            return ['error' => '数据格式错误，至少需要表头和一行数据'];
        }

        $headers = $rows[0];

        $requiredFields = ['学号', '姓名', '家长手机号'];
        $fieldIndices = [];
        foreach ($requiredFields as $field) {
            $idx = array_search($field, $headers);
            if ($idx === false) {
                foreach ($headers as $i => $h) {
                    if (strpos($h, $field) !== false) {
                        $idx = $i;
                        break;
                    }
                }
            }
            if ($idx === false) {
                return ['error' => "缺少必需列: {$field}，请确保表头包含这三列"];
            }
            $fieldIndices[$field] = $idx;
        }

        $subjects = [];
        for ($i = 0; $i < count($headers); $i++) {
            $header = trim($headers[$i]);
            if (!in_array($header, $requiredFields)) {
                $subjects[] = $header;
            }
        }

        if (empty($subjects)) {
            return ['error' => '请至少包含一列成绩数据（如语文、数学等）'];
        }

        $students = [];
        for ($i = 1; $i < count($rows); $i++) {
            $data = $rows[$i];

            $studentId = isset($data[$fieldIndices['学号']]) ? $data[$fieldIndices['学号']] : '';
            $studentName = isset($data[$fieldIndices['姓名']]) ? $data[$fieldIndices['姓名']] : '';
            $parentPhone = isset($data[$fieldIndices['家长手机号']]) ? $data[$fieldIndices['家长手机号']] : '';

            $studentId = preg_replace('/\s+/', '', $studentId);
            $parentPhone = preg_replace('/\s+/', '', $parentPhone);

            if (empty($studentId) || empty($studentName) || empty($parentPhone)) {
                continue;
            }

            $scores = [];
            foreach ($subjects as $subject) {
                $idx = array_search($subject, $headers);
                $score = isset($data[$idx]) ? $data[$idx] : '';
                $score = trim($score);
                $scores[$subject] = $score;
            }

            $students[] = [
                'student_id' => $studentId,
                'student_name' => $studentName,
                'parent_phone' => $parentPhone,
                'scores' => $scores
            ];
        }

        if (empty($students)) {
            return ['error' => '未找到有效学生数据，请检查数据格式是否正确'];
        }

        return [
            'success' => true,
            'subjects' => $subjects,
            'students' => $students
        ];
    }

    public function validatePhone($phone)
    {
        return preg_match('/^1[3-9]\d{9}$/', $phone);
    }

    public function validateStudentId($studentId)
    {
        return preg_match('/^[\d]{4,20}$/', $studentId);
    }
}