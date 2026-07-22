@extends('layouts.app')

@section('title', '创建考试 - 成绩查询系统')

@section('content')
    <header class="header">
        <a href="{{ route('teacher.index') }}" class="back-link">
            ← 返回列表
        </a>
        <div class="logo">
            <div class="logo-icon">📝</div>
            <h1>创建考试</h1>
        </div>
        <p class="tagline">上传成绩数据，生成查询链接</p>
    </header>

    <main class="main">
        <div class="form-container">
            <h2 class="form-title">创建考试</h2>

            @if(session('error'))
                <div class="alert alert-error">
                    ⚠️ {{ session('error') }}
                </div>
            @endif

            @if(isset($success))
                <div class="alert alert-success">
                    ✅ 考试创建成功！以下是查询链接：
                </div>
                <div class="link-box">
                    <label>专属查询链接</label>
                    <input type="text" value="{{ $queryLink }}" readonly onclick="this.select()">
                </div>
                <div class="alert alert-info">
                    💡 将此链接分享到班级群，家长即可查询成绩。链接有效期为 {{ env('LINK_EXPIRE_DAYS', 30) }} 天。
                </div>
                <button class="btn btn-primary" onclick="location.href='{{ route('teacher.create') }}'">创建新考试</button>
            @else
                <form method="POST" action="{{ route('teacher.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="exam_name">考试名称</label>
                        <input type="text" id="exam_name" name="exam_name" placeholder="例如：2024年期中考试" required>
                    </div>

                    <div class="form-group">
                        <label>上传方式</label>
                        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                            <button type="button" class="btn btn-secondary" onclick="switchUpload('paste')" style="flex: 1;">复制粘贴</button>
                            <button type="button" class="btn btn-primary" onclick="switchUpload('file')" style="flex: 1;">上传文件</button>
                        </div>

                        <div id="paste-area">
                            <textarea id="scores_data" name="scores_data" placeholder="学号	姓名	家长手机号	语文	数学	英语
2024001	张三	13800138000	95	92	88
2024002	李四	13900139000	88	90	95
2024003	王五	13700137000	92	85	90"></textarea>
                            <div class="upload-hint">
                                💡 提示：在Excel中选择数据区域，按Ctrl+C复制，然后在此粘贴。列顺序：学号、姓名、家长手机号、各科成绩。
                            </div>
                        </div>

                        <div id="file-area" style="display: none;">
                            <div class="upload-box" id="upload-box" onclick="document.getElementById('excel_file').click()">
                                <div class="upload-icon">📁</div>
                                <div class="upload-text">点击选择文件或拖拽文件到此处</div>
                                <div class="upload-hint">支持 .xlsx 和 .xls 格式</div>
                            </div>
                            <input type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls" required style="display: none;" onchange="handleFileSelect(this)">
                            <div id="file-preview" style="display: none; margin-top: 15px;">
                                <div class="file-info">
                                    <span class="file-icon">📄</span>
                                    <span class="file-name" id="file-name"></span>
                                    <span class="file-size" id="file-size"></span>
                                    <button class="remove-file-btn" onclick="removeFile()">✕</button>
                                </div>
                            </div>
                            <div class="upload-hint">
                                💡 提示：上传Excel文件(.xlsx或.xls)，第一行必须是表头：学号、姓名、家长手机号、各科成绩。
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">生成查询链接</button>
                </form>
            @endif
        </div>
    </main>

    <footer class="footer">
        <p>© 2026 学生成绩查询系统 | 完全免费 | 隐私保护</p>
    </footer>
@endsection

@section('scripts')
    <script>
        function switchUpload(type) {
            const pasteArea = document.getElementById('paste-area');
            const fileArea = document.getElementById('file-area');

            if (type === 'paste') {
                pasteArea.style.display = 'block';
                fileArea.style.display = 'none';
                document.getElementById('excel_file').removeAttribute('required');
                document.getElementById('scores_data').setAttribute('required', '');
            } else {
                pasteArea.style.display = 'none';
                fileArea.style.display = 'block';
                document.getElementById('scores_data').removeAttribute('required');
                document.getElementById('excel_file').setAttribute('required', '');
            }
        }

        function handleFileSelect(input) {
            const file = input.files[0];
            if (!file) return;

            const fileSize = file.size / 1024 / 1024;
            if (fileSize > 10) {
                alert('文件大小不能超过10MB');
                input.value = '';
                return;
            }

            const allowedExtensions = ['.xlsx', '.xls'];
            const fileName = file.name.toLowerCase();
            const isValid = allowedExtensions.some(ext => fileName.endsWith(ext));

            if (!isValid) {
                alert('请上传有效的Excel文件（.xlsx或.xls格式）');
                input.value = '';
                return;
            }

            document.getElementById('file-name').textContent = file.name;
            document.getElementById('file-size').textContent = '(' + (file.size / 1024).toFixed(1) + ' KB)';
            document.getElementById('file-preview').style.display = 'block';

            const uploadBox = document.getElementById('upload-box');
            uploadBox.classList.add('file-selected');
        }

        function removeFile() {
            document.getElementById('excel_file').value = '';
            document.getElementById('file-preview').style.display = 'none';
            document.getElementById('upload-box').classList.remove('file-selected');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const uploadBox = document.getElementById('upload-box');

            uploadBox.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadBox.classList.add('drag-over');
            });

            uploadBox.addEventListener('dragleave', function(e) {
                e.preventDefault();
                uploadBox.classList.remove('drag-over');
            });

            uploadBox.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadBox.classList.remove('drag-over');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    document.getElementById('excel_file').files = files;
                    handleFileSelect(document.getElementById('excel_file'));
                }
            });
        });
    </script>
@endsection