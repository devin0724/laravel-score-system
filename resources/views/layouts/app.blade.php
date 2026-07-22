<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 480px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        .logo-icon {
            font-size: 40px;
        }

        .header h1 {
            color: white;
            font-size: 28px;
            font-weight: 700;
        }

        .tagline {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }

        .main {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .card-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            flex-shrink: 0;
        }

        .card h2 {
            color: #333;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .card p {
            color: #888;
            font-size: 14px;
            line-height: 1.5;
        }

        .card-arrow {
            margin-left: auto;
            color: #667eea;
            font-size: 24px;
            font-weight: 300;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
        }

        .form-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .form-title {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            text-align: center;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            outline: none;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            min-height: 200px;
            resize: vertical;
            font-family: monospace;
        }

        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .link-box {
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .link-box label {
            display: block;
            font-size: 12px;
            color: #64748b;
            margin-bottom: 10px;
        }

        .link-box input {
            width: 100%;
            padding: 12px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            font-family: monospace;
            word-break: break-all;
            outline: none;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .result-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .result-title {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            text-align: center;
            margin-bottom: 25px;
        }

        .student-info {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .student-info p {
            font-size: 16px;
            color: #374151;
            margin-bottom: 8px;
        }

        .student-info p:last-child {
            margin-bottom: 0;
        }

        .student-info strong {
            color: #667eea;
        }

        .scores-table {
            width: 100%;
            border-collapse: collapse;
        }

        .scores-table th,
        .scores-table td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .scores-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
        }

        .scores-table td {
            font-size: 16px;
            color: #374151;
        }

        .scores-table tr:last-child td {
            border-bottom: none;
        }

        .upload-hint {
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 12px;
            font-size: 12px;
            color: #92400e;
            margin-top: 8px;
            line-height: 1.6;
        }

        .exam-list {
            margin-top: 20px;
        }

        .exam-item {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid #e2e8f0;
        }

        .exam-item h3 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .exam-item p {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .exam-item .actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }

        .exam-item .btn {
            flex: 1;
            padding: 10px;
            font-size: 14px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
        }

        .empty-state .icon {
            font-size: 60px;
            margin-bottom: 15px;
        }

        .empty-state h3 {
            font-size: 18px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .empty-state p {
            font-size: 14px;
        }

        .upload-box {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .upload-box:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .upload-box.drag-over {
            border-color: #667eea;
            background: #e0e7ff;
            transform: scale(1.02);
        }

        .upload-box.file-selected {
            border-color: #10b981;
            background: #ecfdf5;
        }

        .upload-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .upload-text {
            font-size: 16px;
            color: #374151;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .upload-box .upload-hint {
            margin-top: 10px;
            font-size: 13px;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
        }

        .file-icon {
            font-size: 28px;
        }

        .file-name {
            flex: 1;
            font-size: 15px;
            color: #374151;
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .file-size {
            font-size: 14px;
            color: #6b7280;
        }

        .remove-file-btn {
            background: #fee2e2;
            color: #dc2626;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .remove-file-btn:hover {
            background: #fecaca;
            transform: scale(1.1);
        }

        @media (max-width: 480px) {
            body {
                padding: 15px;
            }

            .card {
                padding: 20px;
            }

            .card-icon {
                width: 50px;
                height: 50px;
                font-size: 25px;
            }

            .form-container {
                padding: 20px;
            }

            .form-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        @yield('content')
    </div>
    @yield('scripts')
</body>
</html>