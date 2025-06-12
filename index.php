<?php
// copyright 2025 @ x2in
$jsonFilePath = 'navigation.json';

// 读取 JSON 文件
if (file_exists($jsonFilePath)) {
    $jsonContent = file_get_contents($jsonFilePath);
    $links = json_decode($jsonContent, true);
} else {
    $links = [];
    // 如果文件不存在，创建并写入空数组
    file_put_contents($jsonFilePath, json_encode($links, JSON_PRETTY_PRINT));
}

// 对链接按照排序序号排序
usort($links, function($a, $b) {
    return $a['sort_order'] <=> $b['sort_order'];
});
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>简单导航页面</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center; /* 垂直居中 */
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #333435; /* 设置背景为深黑色 */
        }
        .main-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: flex-start; /* 图标左对齐 */
            width: 80%; /* 左右预留 10% 的空白 */
            max-width: 1024px;
            padding: 20px;
            box-sizing: border-box;
        }
        .link-item {
            text-align: center;
            width: calc((100% - 140px) / 8); /* 一行显示 8 个图标，7 个 gap 共 140px */
            transition: transform 0.3s ease; /* 添加过渡效果 */
        }
        .link-item:hover {
            transform: translateY(-3px); /* 鼠标悬停时向上移动 3px */
        }
        .link-item img {
            width: 64px;
            height: 64px;
            object-fit: contain;
            transition: box-shadow 0.3s ease; /* 添加过渡效果 */
            box-shadow: 0 8px 8px rgba(0, 0, 0, 0.3); /* 添加 3px 模糊阴影 */
        }
        .link-item img:hover {
            box-shadow: 0 5px 10px 2px rgba(0, 191, 255, 0.7); /* 鼠标悬停时添加淡蓝色发光效果 */
        }
        .link-item a {
            color: #fff; /* 设置超链接字体为白色 */
            text-decoration: none;
        }

        /* 媒体查询，针对移动端设备 */
        @media (max-width: 768px) {
            .main-container {
                max-width: 100%;
                justify-content: space-between; /* 移动端图标均匀分布 */
            }
            .link-item {
                width: calc((100% - 60px) / 4); /* 一行显示 4 个图标 */
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <?php 
        // 限制显示 24 个图标
        $count = 0;
        foreach ($links as $link): 
            if ($count >= 24) break;
        ?>
            <div class="link-item">
                <a href="<?php echo $link['url']; ?>" target="_blank">
                    <img src="<?php echo $link['icon']; ?>" alt="<?php echo $link['name']; ?>">
                    <p><?php echo $link['name']; ?></p>
                </a>
            </div>
        <?php 
            $count++;
        endforeach; 
        ?>
    </div>
</body>
</html>
