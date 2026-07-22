@extends('layouts.app')

@section('title', '家长查询 - 成绩查询系统')

@section('content')
    <header class="header">
        <a href="{{ route('index') }}" class="back-link">
            ← 返回首页
        </a>
        <div class="logo">
            <div class="logo-icon">👨‍👩‍👧</div>
            <h1>家长查询</h1>
        </div>
        <p class="tagline">输入查询码 · 验证信息 · 查询成绩</p>
    </header>

    <main class="main">
        <div class="form-container">
            <h2 class="form-title">输入查询链接</h2>
            <p style="text-align: center; color: #6b7280; margin-bottom: 20px;">请输入老师分享的考试查询链接</p>

            <form method="GET" action="{{ route('parent.query', 'code') }}">
                <div class="form-group">
                    <label for="code">查询码</label>
                    <input type="text" id="code" name="code" placeholder="请输入查询码" required>
                    <div class="upload-hint">
                        💡 查询码是链接中 /parent/ 后面的部分，例如：abc123def456
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">进入查询</button>
            </form>
        </div>
    </main>

    <footer class="footer">
        <p>© 2026 学生成绩查询系统 | 完全免费 | 隐私保护</p>
    </footer>
@endsection