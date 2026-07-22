@extends('layouts.app')

@section('title', '老师页面 - 成绩查询系统')

@section('content')
    <header class="header">
        <a href="{{ route('index') }}" class="back-link">
            ← 返回首页
        </a>
        <a href="{{ route('auth.logout') }}" class="back-link" style="float: right; margin-left: auto;">
            退出登录 →
        </a>
        <div class="logo">
            <div class="logo-icon">👨‍🏫</div>
            <h1>老师页面</h1>
        </div>
        <p class="tagline">创建考试 · 上传成绩 · 生成链接</p>
    </header>

    <main class="main">
        <div class="form-container">
            <h2 class="form-title">创建考试</h2>
            <a href="{{ route('teacher.create') }}" class="btn btn-primary">创建新考试</a>
        </div>

        <div class="form-container">
            <h2 class="form-title">历史考试</h2>

            @if($examList->isEmpty())
                <div class="empty-state">
                    <div class="icon">📋</div>
                    <h3>暂无考试记录</h3>
                    <p>创建您的第一个考试吧</p>
                </div>
            @else
                <div class="exam-list">
                    @foreach($examList as $exam)
                        <div class="exam-item">
                            <h3>{{ $exam->exam_name }}</h3>
                            <p><strong>科目：</strong>{{ implode('、', $exam->subjects) }}</p>
                            <p><strong>学生数：</strong>{{ $exam->total_students }} 人</p>
                            <p><strong>创建时间：</strong>{{ $exam->created_at->format('Y年m月d日 H:i') }}</p>
                            <p><strong>有效期至：</strong>{{ $exam->expires_at->format('Y年m月d日 H:i') }}</p>
                            <div class="actions">
                                <button class="btn btn-primary" onclick="copyLink('{{ $exam->exam_code }}')">复制链接</button>
                                <form method="POST" action="{{ route('teacher.destroy', $exam->id) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary" onclick="return confirm('确定删除此考试吗？')">删除</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </main>

    <footer class="footer">
        <p>© 2026 学生成绩查询系统 | 完全免费 | 隐私保护</p>
    </footer>
@endsection

@section('scripts')
    <script>
        function copyLink(examCode) {
            const link = '{{ url('/parent/') }}/' + examCode;
            navigator.clipboard.writeText(link).then(function() {
                alert('链接已复制到剪贴板！');
            }, function(err) {
                const input = document.createElement('input');
                input.value = link;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
                alert('链接已复制到剪贴板！');
            });
        }
    </script>
@endsection