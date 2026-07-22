@extends('layouts.app')

@section('title', '查询成绩 - 成绩查询系统')

@section('content')
    <header class="header">
        <a href="{{ route('parent.index') }}" class="back-link">
            ← 返回重新输入
        </a>
        <div class="logo">
            <div class="logo-icon">🔒</div>
            <h1>成绩查询</h1>
        </div>
        <p class="tagline">三重验证 · 隐私保护 · 安全查询</p>
    </header>

    <main class="main">
        @if(empty($code))
            <div class="form-container">
                <h2 class="form-title">输入查询链接</h2>
                <p style="text-align: center; color: #6b7280; margin-bottom: 20px;">请输入老师分享的考试查询链接</p>

                <form onsubmit="event.preventDefault(); submitCode();">
                    <div class="form-group">
                        <label for="code">查询码</label>
                        <input type="text" id="code" name="code" placeholder="请输入查询码" required>
                        <div class="upload-hint">
                            💡 查询码是链接中 /parent/ 后面的部分，例如：abc123def456
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">进入查询</button>
                </form>
                <script>
                    function submitCode() {
                        const code = document.getElementById('code').value.trim();
                        if (code) {
                            window.location.href = '/parent/' + encodeURIComponent(code);
                        }
                    }
                </script>
            </div>
        @elseif(!$exam)
            <div class="form-container">
                <div class="empty-state">
                    <div class="icon">❌</div>
                    <h3>链接无效或已过期</h3>
                    <p>请联系老师获取新的查询链接</p>
                </div>
                <button class="btn btn-secondary" onclick="location.href='{{ route('parent.index') }}'">返回重新输入</button>
            </div>
        @else
            <div class="form-container">
                <h2 class="form-title">{{ $exam->exam_name }}</h2>
                <p style="text-align: center; color: #6b7280; margin-bottom: 20px;">请验证以下信息查询成绩</p>

                @if(session('error'))
                    <div class="alert alert-error">
                        ⚠️ {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('parent.query.post') }}">
                    @csrf
                    <input type="hidden" name="code" value="{{ $code }}">

                    <div class="form-group">
                        <label for="student_id">学号</label>
                        <input type="text" id="student_id" name="student_id" placeholder="请输入学号" required value="{{ old('student_id') }}">
                    </div>

                    <div class="form-group">
                        <label for="student_name">学生姓名</label>
                        <input type="text" id="student_name" name="student_name" placeholder="请输入学生姓名" required value="{{ old('student_name') }}">
                    </div>

                    <div class="form-group">
                        <label for="parent_phone">家长手机号</label>
                        <input type="tel" id="parent_phone" name="parent_phone" placeholder="请输入家长手机号" required value="{{ old('parent_phone') }}">
                    </div>

                    <div class="form-group">
                        <label for="verify_code">短信验证码</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="verify_code" name="verify_code" placeholder="请输入验证码" required maxlength="6" style="flex: 1;">
                            <button type="button" class="btn btn-secondary" id="send_code_btn" onclick="sendSmsCode()" style="flex-shrink: 0; width: 160px; padding: 14px 10px; white-space: nowrap;">获取验证码</button>
                        </div>
                        <div class="upload-hint" style="margin-top: 8px;">
                            💡 验证码将发送到您的手机，有效期为 {{ env('SMS_CODE_EXPIRE_MINUTES', 5) }} 分钟
                        </div>
                    </div>

                    <div class="alert alert-info">
                        🔒 您的信息将严格保密，仅用于验证身份查询成绩
                    </div>

                    <button type="submit" class="btn btn-primary">查询成绩</button>
                </form>
            </div>
        @endif
    </main>

    <footer class="footer">
        <p>© 2026 学生成绩查询系统 | 完全免费 | 隐私保护</p>
    </footer>
@endsection

@section('scripts')
    <script>
        function sendSmsCode() {
            const phone = document.getElementById('parent_phone').value;
            const code = '{{ $code ?? '' }}';
            const btn = document.getElementById('send_code_btn');

            if (!phone) {
                alert('请先输入家长手机号');
                return;
            }

            btn.disabled = true;
            btn.textContent = '发送中...';

            fetch('{{ route('parent.sendCode') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ code: code, parent_phone: phone })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    startCountdown();
                } else {
                    alert(data.error);
                    btn.disabled = false;
                    btn.textContent = '获取验证码';
                }
            })
            .catch(error => {
                alert('发送失败，请稍后重试');
                btn.disabled = false;
                btn.textContent = '获取验证码';
            });
        }

        function startCountdown() {
            const btn = document.getElementById('send_code_btn');
            let count = 60;

            btn.textContent = count + '秒后重新获取';

            const timer = setInterval(() => {
                count--;
                btn.textContent = count + '秒后重新获取';

                if (count <= 0) {
                    clearInterval(timer);
                    btn.disabled = false;
                    btn.textContent = '获取验证码';
                }
            }, 1000);
        }
    </script>
@endsection