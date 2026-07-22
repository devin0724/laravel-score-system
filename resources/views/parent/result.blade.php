@extends('layouts.app')

@section('title', '成绩查询结果 - 成绩查询系统')

@section('content')
    <header class="header">
        <a href="{{ route('parent.index') }}" class="back-link">
            ← 返回首页
        </a>
        <div class="logo">
            <div class="logo-icon">🎉</div>
            <h1>查询结果</h1>
        </div>
        <p class="tagline">{{ $exam->exam_name }}</p>
    </header>

    <main class="main">
        <div class="result-card">
            <h2 class="result-title">{{ $exam->exam_name }}</h2>

            <div class="student-info">
                <p><strong>学号：</strong>{{ $scores->student_id }}</p>
                <p><strong>姓名：</strong>{{ $scores->student_name }}</p>
                <p><strong>家长手机号：</strong>{{ $scores->parent_phone }}</p>
            </div>

            <table class="scores-table">
                <thead>
                    <tr>
                        <th>科目</th>
                        <th>成绩</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($scores->scores as $subject => $score)
                        <tr>
                            <td>{{ $subject }}</td>
                            <td>{{ $score }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="alert alert-info" style="margin-top: 20px;">
                💡 以上成绩仅供参考，请以学校正式通知为准
            </div>
        </div>
    </main>

    <footer class="footer">
        <p>© 2026 学生成绩查询系统 | 完全免费 | 隐私保护</p>
    </footer>
@endsection