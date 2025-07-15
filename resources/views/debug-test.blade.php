<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debugbar 测试页面</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .user-item, .product-item {
            padding: 10px;
            margin: 5px 0;
            background: #f9f9f9;
            border-radius: 4px;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        h2 {
            color: #666;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Debugbar 测试页面</h1>
        
        <div class="section">
            <h2>📊 页面信息</h2>
            <p><strong>当前时间:</strong> {{ now() }}</p>
            <p><strong>用户数量:</strong> {{ $users->count() }}</p>
            <p><strong>商品数量:</strong> {{ $products->count() }}</p>
        </div>

        <div class="section">
            <h2>👥 用户列表</h2>
            @foreach($users as $user)
                <div class="user-item">
                    <strong>{{ $user->real_name ?? $user->username }}</strong>
                    <br>
                    <small>邮箱: {{ $user->email }}</small>
                    <br>
                    <small>创建时间: {{ $user->created_at }}</small>
                </div>
            @endforeach
        </div>

        <div class="section">
            <h2>📦 商品列表</h2>
            @foreach($products as $product)
                <div class="product-item">
                    <strong>{{ $product->name }}</strong>
                    <br>
                    <small>编码: {{ $product->code }}</small>
                    <br>
                    <small>价格: ¥{{ $product->price }}</small>
                </div>
            @endforeach
        </div>

        <div class="section">
            <h2>ℹ️ 使用说明</h2>
            <p>如果 Debugbar 正常工作，你应该在页面右下角看到一个调试工具栏。</p>
            <p>工具栏包含以下信息：</p>
            <ul>
                <li>🔍 <strong>查询</strong> - 显示执行的 SQL 查询</li>
                <li>⏱️ <strong>时间</strong> - 显示页面加载时间</li>
                <li>💾 <strong>内存</strong> - 显示内存使用情况</li>
                <li>📝 <strong>日志</strong> - 显示应用日志</li>
                <li>🎯 <strong>路由</strong> - 显示当前路由信息</li>
            </ul>
        </div>
    </div>
</body>
</html> 