@extends('layouts.app')

@section('title', '老师登录 - 成绩查询系统')

@section('content')
    <header class="header">
        <a href="{{ route('index') }}" class="back-link">
            ← 返回首页
        </a>
        <div class="logo">
            <div class="logo-icon">👨‍🏫</div>
            <h1>老师登录</h1>
        </div>
        <p class="tagline">请输入密码登录老师页面</p>
    </header>

    <main class="main">
        <div class="form-container">
            <h2 class="form-title">教师认证</h2>

            @if(session('error'))
                <div class="alert alert-error">
                    ⚠️ {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('auth.login.post') }}">
                @csrf

                <div class="form-group">
                    <label for="password">登录密码</label>
                    <input type="password" id="password" name="password" placeholder="请输入密码" required>
                </div>

                <button type="submit" class="btn btn-primary">登录</button>
            </form>
        </div>
    </main>

    <footer class="footer">
        <p>© 2026 学生成绩查询系统 | 完全免费 | 隐私保护</p>
    </footer>
@endsection