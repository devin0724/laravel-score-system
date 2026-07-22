<?php

namespace App\Services;

use App\Models\Score;
use App\Models\SmsCode;
use Illuminate\Support\Facades\Http;

class SmsService
{
    public function generateSmsCode()
    {
        $length = env('SMS_CODE_LENGTH', 6);
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= rand(0, 9);
        }
        return $code;
    }

    public function checkSmsSendInterval($phone, $examId)
    {
        $interval = env('SMS_SEND_INTERVAL_SECONDS', 60);

        $lastCode = SmsCode::where('phone', $phone)
            ->where('exam_id', $examId)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastCode) {
            $lastSendTime = strtotime($lastCode->created_at);
            if (time() - $lastSendTime < $interval) {
                return $interval - (time() - $lastSendTime);
            }
        }

        return 0;
    }

    public function sendSmsCode($phone, $examId, $examName)
    {
        $remaining = $this->checkSmsSendInterval($phone, $examId);
        if ($remaining > 0) {
            return ['success' => false, 'error' => "请{$remaining}秒后再获取验证码"];
        }

        $count = Score::where('exam_id', $examId)->where('parent_phone', $phone)->count();

        if ($count == 0) {
            return ['success' => false, 'error' => '该手机号未登记，请确认手机号是否正确'];
        }

        $apiUrl = env('SMS_API_SEND_URL');

        $fullUrl = $apiUrl . '?opera_type=0&mobile=' . urlencode($phone);
        $response = Http::post($fullUrl);

        if (!$response->successful()) {
            return ['success' => false, 'error' => '短信发送失败，请稍后重试'];
        }

        $data = $response->json();

        if (!$data) {
            return ['success' => false, 'error' => '短信发送失败，请稍后重试'];
        }

        if (isset($data['infoMap']['flag']) && $data['infoMap']['flag'] != '1') {
            $errorMsg = isset($data['infoMap']['reason']) ? $data['infoMap']['reason'] : '短信发送失败';
            return ['success' => false, 'error' => $errorMsg];
        }

        if (isset($data['success']) && $data['success'] === false) {
            $errorMsg = isset($data['message']) ? $data['message'] : '短信发送失败';
            return ['success' => false, 'error' => $errorMsg];
        }

        if (isset($data['code']) && $data['code'] != 0) {
            $errorMsg = isset($data['message']) ? $data['message'] : '短信发送失败';
            return ['success' => false, 'error' => $errorMsg];
        }

        $expireMinutes = env('SMS_CODE_EXPIRE_MINUTES', 5);
        $expiresAt = now()->addMinutes($expireMinutes);

        SmsCode::updateOrCreate(
            ['phone' => $phone, 'exam_id' => $examId],
            ['code' => '', 'expires_at' => $expiresAt, 'is_used' => 0]
        );

        return ['success' => true, 'message' => '验证码已发送，请注意查收'];
    }

    public function verifySmsCode($phone, $examId, $code)
    {
        $apiUrl = env('SMS_API_VERIFY_URL');

        $fullUrl = $apiUrl . '?mobile=' . urlencode($phone) . '&verification_code=' . urlencode($code);
        $response = Http::post($fullUrl);

        if (!$response->successful()) {
            return ['success' => false, 'error' => '验证码验证失败，请稍后重试'];
        }

        $data = $response->json();

        if (!$data) {
            return ['success' => false, 'error' => '验证码验证失败，请稍后重试'];
        }

        if (isset($data['infoMap']['flag']) && $data['infoMap']['flag'] != '1') {
            $errorMsg = isset($data['infoMap']['reason']) ? $data['infoMap']['reason'] : '验证码无效或已过期，请重新获取';
            return ['success' => false, 'error' => $errorMsg];
        }

        if (isset($data['success']) && $data['success'] === false) {
            $errorMsg = isset($data['message']) ? $data['message'] : '验证码无效或已过期，请重新获取';
            return ['success' => false, 'error' => $errorMsg];
        }

        if (isset($data['code']) && $data['code'] != 0) {
            $errorMsg = isset($data['message']) ? $data['message'] : '验证码无效或已过期，请重新获取';
            return ['success' => false, 'error' => $errorMsg];
        }

        SmsCode::where('phone', $phone)->where('exam_id', $examId)->update(['is_used' => 1]);

        return ['success' => true];
    }
}