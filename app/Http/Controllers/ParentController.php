<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\QueryLog;
use App\Models\Score;
use App\Services\ExcelService;
use App\Services\RateLimitService;
use App\Services\SmsService;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    public function index()
    {
        return view('parent.index');
    }

    public function showQuery($code)
    {
        $exam = Exam::where('exam_code', $code)
            ->where('expires_at', '>', now())
            ->first();

        return view('parent.query', compact('exam', 'code'));
    }

    public function sendCode(Request $request, SmsService $smsService)
    {
        $request->validate([
            'code' => 'required',
            'parent_phone' => 'required',
        ]);

        $exam = Exam::where('exam_code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$exam) {
            return response()->json(['success' => false, 'error' => '考试链接无效或已过期']);
        }

        $parentPhone = trim($request->parent_phone);

        if (!$this->validatePhone($parentPhone)) {
            return response()->json(['success' => false, 'error' => '手机号格式不正确']);
        }

        $result = $smsService->sendSmsCode($parentPhone, $exam->id, $exam->exam_name);
        return response()->json($result);
    }

    public function query(Request $request, RateLimitService $rateLimitService, SmsService $smsService)
    {
        $clientIp = $request->ip();

        if (!$rateLimitService->checkRateLimit($clientIp)) {
            return back()->with('error', '查询过于频繁，请稍后再试');
        }

        $request->validate([
            'code' => 'required',
            'student_id' => 'required',
            'student_name' => 'required',
            'parent_phone' => 'required',
            'verify_code' => 'required',
        ]);

        $exam = Exam::where('exam_code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$exam) {
            return back()->with('error', '考试链接无效或已过期');
        }

        $studentId = trim($request->student_id);
        $studentName = trim($request->student_name);
        $parentPhone = trim($request->parent_phone);
        $verifyCode = trim($request->verify_code);

        $excelService = new ExcelService();
        if (!$excelService->validateStudentId($studentId)) {
            return back()->with('error', '学号格式不正确（应为4-20位数字）');
        }

        if (!$excelService->validatePhone($parentPhone)) {
            return back()->with('error', '手机号格式不正确');
        }

        $verifyResult = $smsService->verifySmsCode($parentPhone, $exam->id, $verifyCode);

        if (!$verifyResult['success']) {
            return back()->with('error', $verifyResult['error'])
                ->withInput($request->only('student_id', 'student_name', 'parent_phone'));
        }

        $scores = Score::where('exam_id', $exam->id)
            ->where('student_id', $studentId)
            ->where('student_name', $studentName)
            ->where('parent_phone', $parentPhone)
            ->first();

        if (!$scores) {
            return back()->with('error', '未找到匹配的成绩信息，请检查输入是否正确')
                ->withInput($request->only('student_id', 'student_name', 'parent_phone'));
        }

        QueryLog::create([
            'exam_id' => $exam->id,
            'student_id' => $studentId,
            'ip_address' => $clientIp,
        ]);

        return view('parent.result', compact('exam', 'scores'));
    }

    private function validatePhone($phone)
    {
        return preg_match('/^1[3-9]\d{9}$/', $phone);
    }
}