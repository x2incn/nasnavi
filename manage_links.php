<?php
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

// 遍历 images 目录获取所有 PNG 文件
$imageDir = 'images';
$imageFiles = [];
if (is_dir($imageDir)) {
    $dir = opendir($imageDir);
    while (($file = readdir($dir)) !== false) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'png') {
            $imageFiles[] = $imageDir . '/' . $file;
        }
    }
    closedir($dir);
}

// 处理删除请求
if (isset($_GET['delete_id'])) {
    foreach ($links as $key => $link) {
        if ($link['id'] == $_GET['delete_id']) {
            unset($links[$key]);
            break;
        }
    }
    // 重新索引数组
    $links = array_values($links);
    file_put_contents($jsonFilePath, json_encode($links, JSON_PRETTY_PRINT));
}

// 处理编辑请求
$editLink = null;
if (isset($_GET['edit_id'])) {
    foreach ($links as $link) {
        if ($link['id'] == $_GET['edit_id']) {
            $editLink = $link;
            break;
        }
    }
}

// 处理表单提交请求
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $url = $_POST['url'];
    $icon = $_POST['icon'];
    $sortOrder = isset($_POST['sort_order']) ? $_POST['sort_order'] : 0;

    if (isset($_POST['edit_id'])) {
        // 处理更新请求
        foreach ($links as &$link) {
            if ($link['id'] == $_POST['edit_id']) {
                $link['name'] = $name;
                $link['url'] = $url;
                $link['icon'] = $icon;
                $link['sort_order'] = $sortOrder;
                break;
            }
        }
    } else {
        // 处理新增请求
        $newId = count($links) > 0 ? max(array_column($links, 'id')) + 1 : 1;
        $newLink = [
            'id' => $newId,
            'name' => $name,
            'url' => $url,
            'icon' => $icon,
            'sort_order' => $sortOrder
        ];
        $links[] = $newLink;
    }

    // 将更新后的数组写回 JSON 文件
    file_put_contents($jsonFilePath, json_encode($links, JSON_PRETTY_PRINT));
    header("Location: manage_links.php");
    exit;
}

usort($links, function($a, $b) {
    return $a['sort_order'] <=> $b['sort_order'];
});
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>管理导航</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- 引入 jQuery 和 jQuery UI 库 -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <style>
        table { width: 1024px; border-collapse: collapse; margin-top: 20px; }

        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        a.delete-link { color: red; text-decoration: none; }
        form { margin-bottom: 20px; }
        input[type="text"] { margin: 5px 10px; padding: 5px; }
        input[type="number"] { margin: 5px 10px; padding: 5px; }
        select { margin: 5px 10px; padding: 5px; }
        input[type="submit"] { padding: 5px 10px; }
        #edit-dialog, #add-dialog { display: none; }
        a:link, a:visited {
            text-decoration: none;
            color: #222222;
        }
    </style>
    <script>
        $(document).ready(function() {
            const initDialog = (id, buttons) => {
                $(id).dialog({
                    autoOpen: false,
                    modal: true,
                    width: 400,
                    buttons
                });
            };

            initDialog("#edit-dialog", {
                "更新": function() { $(this).find("form").submit(); },
                "取消": function() { $(this).dialog("close"); }
            });

            initDialog("#add-dialog", {
                "添加": function() { $(this).find("form").submit(); },
                "取消": function() { $(this).dialog("close"); }
            });

            $(".edit-link").click(function(e) {
                e.preventDefault();
                const row = $(this).closest('tr');
                $('#edit-dialog form')
                    .find('input[name="edit_id"]').val(this.href.split("=")[1]).end()
                    .find('input[name="name"]').val(row.find('td:nth-child(2)').text()).end()
                    .find('input[name="url"]').val(row.find('td:nth-child(3)').text()).end()
                    .find('select[name="icon"]').val(row.find('td:nth-child(4) img').attr('src')).end()
                    .find('input[name="sort_order"]').val(row.find('td:nth-child(5)').text()); 
                $("#edit-dialog").dialog("open");
            });

            $("#add-button").click(function(e) {
                e.preventDefault();
                $('#add-dialog form input').val('');
                $('#add-dialog form select').val('');
                $("#add-dialog").dialog("open");
            });
        });
    </script>
</head>
<body>
    <h1>管理导航</h1>
    <button id="add-button">添加新链接</button>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>名称</th>
                <th>URL</th>
                <th>图标</th>
                <th>排序</th> 
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($links as $link): ?>
            <tr>
                <td><?php echo $link['id']; ?></td>
                <td><?php echo $link['name']; ?></td>
                <td><?php echo $link['url']; ?></td>
                <td><img src="<?php echo $link['icon']; ?>" alt="<?php echo $link['name']; ?>" width="32" height="32"></td>
                <!-- 显示排序序号 -->
                <td><?php echo $link['sort_order']; ?></td> 
                <td>
                    <a href="?edit_id=<?php echo $link['id']; ?>" class="edit-link">【编辑】</a>
                    <a href="?delete_id=<?php echo $link['id']; ?>" class="delete-link" onclick="return confirm('确定要删除这条记录吗？')">【删除】</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <!-- 新增对话框 -->
    <div id="add-dialog">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <label for="name">名称:</label>
            <input type="text" id="name" name="name" placeholder="名称" required><br>
            <label for="url">URL:</label>
            <input type="text" id="url" name="url" placeholder="URL" required><br>
            <label for="icon">图标:</label>
            <select id="icon" name="icon" required>
                <?php foreach ($imageFiles as $file): ?>
                    <option value="<?php echo $file; ?>"><?php echo $file; ?></option>
                <?php endforeach; ?>
            </select><br>
            <label for="sort_order">排序:</label>
            <input type="number" id="sort_order" name="sort_order" placeholder="排序序号" value="0" required><br>
        </form>
    </div>

    <!-- 编辑对话框 -->
    <div id="edit-dialog">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <input type="hidden" name="edit_id" value="">
            <label for="name">名称:</label>
            <input type="text" id="name" name="name" value="" required><br>
            <label for="url">URL:</label>
            <input type="text" id="url" name="url" value="" required><br>
            <label for="icon">图标:</label>
            <select id="icon" name="icon" required>
                <?php foreach ($imageFiles as $file): ?>
                    <option value="<?php echo $file; ?>"><?php echo $file; ?></option>
                <?php endforeach; ?>
            </select><br>
            <label for="sort_order">排序:</label>
            <input type="number" id="sort_order" name="sort_order" placeholder="排序序号" value="0" required><br>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            const initDialog = (id, buttons) => {
                $(id).dialog({
                    autoOpen: false,
                    modal: true,
                    width: 400,
                    buttons
                });
            };

            initDialog("#edit-dialog", {
                "更新": function() { $(this).find("form").submit(); },
                "取消": function() { $(this).dialog("close"); }
            });

            initDialog("#add-dialog", {
                "添加": function() { $(this).find("form").submit(); },
                "取消": function() { $(this).dialog("close"); }
            });

            $(".edit-link").click(function(e) {
                e.preventDefault();
                const row = $(this).closest('tr');
                $('#edit-dialog form')
                    .find('input[name="edit_id"]').val(this.href.split("=")[1]).end()
                    .find('input[name="name"]').val(row.find('td:nth-child(2)').text()).end()
                    .find('input[name="url"]').val(row.find('td:nth-child(3)').text()).end()
                    .find('select[name="icon"]').val(row.find('td:nth-child(4) img').attr('src')).end()
                    .find('input[name="sort_order"]').val(row.find('td:nth-child(5)').text()); 
                $("#edit-dialog").dialog("open");
            });

            $("#add-button").click(function(e) {
                e.preventDefault();
                $('#add-dialog form input').val('');
                $('#add-dialog form select').val('');
                $("#add-dialog").dialog("open");
            });
        });
    </script>
</body>
</html>