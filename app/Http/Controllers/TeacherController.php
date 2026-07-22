<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Score;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class TeacherController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.teacher');
    }

    public function index()
    {
        $examList = Exam::orderBy('created_at', 'desc')->get();
        return view('teacher.index', compact('examList'));
    }

    public function create()
    {
        return view('teacher.create');
    }

    public function store(Request $request, ExcelService $excelService)
    {
        $request->validate([
            'exam_name' => 'required|max:255',
        ]);

        $examName = trim($request->exam_name);
        $scoresData = trim($request->scores_data ?? '');

        if (empty($scoresData) && !$request->hasFile('excel_file')) {
            return back()->with('error', '请输入成绩数据或上传Excel文件');
        }

        $result = null;

        if ($request->hasFile('excel_file')) {
            $file = $request->file('excel_file');

            if ($file->getSize() > 10 * 1024 * 1024) {
                return back()->with('error', '文件大小超过限制，最大允许10MB');
            }

            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['xlsx', 'xls'])) {
                return back()->with('error', '不支持的文件格式，请上传.xlsx或.xls文件');
            }

            $filePath = $file->getRealPath();
            $originalExt = $file->getClientOriginalExtension();
            $result = $excelService->parseExcelFile($filePath, $originalExt);
        } else {
            $result = $excelService->parseExcelContent($scoresData);
        }

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        $examCode = $this->generateExamCode();
        $expireDays = env('LINK_EXPIRE_DAYS', 30);
        $expiresAt = now()->addDays($expireDays);

        $exam = Exam::create([
            'exam_name' => $examName,
            'exam_code' => $examCode,
            'subjects' => $result['subjects'],
            'total_students' => count($result['students']),
            'expires_at' => $expiresAt,
        ]);

        foreach ($result['students'] as $student) {
            Score::create([
                'exam_id' => $exam->id,
                'student_id' => $student['student_id'],
                'student_name' => $student['student_name'],
                'parent_phone' => $student['parent_phone'],
                'scores' => $student['scores'],
            ]);
        }

        $queryLink = url('/parent/' . $examCode);
        return view('teacher.create', compact('queryLink', 'exam'))->with('success', true);
    }

    public function destroy($id)
    {
        Exam::findOrFail($id)->delete();
        return redirect()->route('teacher.index');
    }

    private function generateExamCode()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $code = '';
        for ($i = 0; $i < 16; $i++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }

        if (Exam::where('exam_code', $code)->exists()) {
            return $this->generateExamCode();
        }

        return $code;
    }

    public function copyLink($examCode)
    {
        $link = url('/parent/' . $examCode);
        return response()->json(['link' => $link]);
    }
}