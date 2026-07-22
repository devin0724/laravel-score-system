@extends('layouts.app')

@section('title', '学生成绩查询系统')

@section('content')
    <header class="header">
        <div class="logo">
            <div class="logo-icon">📝</div>
            <h1>成绩查询系统</h1>
        </div>
        <p class="tagline">安全便捷 · 免费使用 · 注重隐私</p>
    </header>

    <main class="main">
        <div class="card teacher-card" onclick="location.href='{{ route('auth.login') }}'">
            <div class="card-icon">👨‍🏫</div>
            <h2>我是老师</h2>
            <p>创建考试 · 上传成绩 · 生成查询链接</p>
            <div class="card-arrow">→</div>
        </div>

        <div class="card parent-card" onclick="location.href='{{ route('parent.index') }}'">
            <div class="card-icon">👨‍👩‍👧</div>
            <h2>我是家长</h2>
            <p>输入链接 · 验证信息 · 查询孩子成绩</p>
            <div class="card-arrow">→</div>
        </div>
    </main>

    <footer class="footer">
        <p>© 2026 学生成绩查询系统 | 完全免费 | 隐私保护</p>
    </footer>
@endsection