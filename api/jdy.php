<?php
error_reporting(0);
$src = isset($_GET['src']) ? $_GET['src'] : "";
$currentUrl = "http" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "s" : "") . "://" . $_SERVER['HTTP_HOST'];
if (empty($src)) {
    echo <<<EOL
<title>酒店源神器</title>
    <body style="margin: 0; padding: 0; background: linear-gradient(to bottom, #3498db, #2980b9);">
        <div style="height: 100vh; display: flex; justify-content: center; align-items: center;">
            <form method="post">
                <div style="text-align: center;">
                    <p style="font-size: 18px; font-weight: bold; margin-right: 10px; color: #fff;">请输入酒店源IP/域名+端口地址：</p>
                    <input type="text" name="src" placeholder="格式：42.176.185.28:9901" required style="padding: 8px; font-size: 16px; border-radius: 5px 0 0 5px; border: 1px solid #ccc; border-right: none; height: 40px; outline: none;"><input type="submit" name="submit" value="点击获取" style="padding: 6px 12px; font-size: 16px; background-color: #e36c09; color: #fff; border: none; border-radius: 0 5px 5px 0; cursor: pointer; height: 40px; margin: 0;">
                </div>
            </form>
        </div>
    </body>
EOL;
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["submit"])) {
            $src = $_POST["src"];   
            // 重定向当前页面，并附带src参数
            header("Location: " . $currentUrl . "/api/?src=" . $src);
            exit; 
        }
    }
} else {
    // 设置超时时间为1秒，多数酒店源可用，个别酒店源服务器因响应速度太慢，可设置成10秒或者30秒
    $context = stream_context_create(['http' => ['timeout' => 3]]);
    $url = @file_get_contents("http://" . $src . "/iptv/live/1000.json", false, $context);
    if ($url === false || empty($url)) {
        $backupUrl = "http://" . $src . "/ZHGXTV/Public/json/live_interface.txt";
        $url = @file_get_contents($backupUrl, false, $context);
        $url = preg_replace("/,/",",",preg_replace("/\s+/","<br>",$url));
        if ($url === false || empty($url)) {
            echo "未找到酒店直播源<br>";
            exit; // 停止执行代码
        } else {
            echo $url;
        }
    } else {
        preg_match_all('|"name":\s*"(.*?)"|',$url,$nameMatches);
        if (!empty($nameMatches[1])) {
            preg_match_all('|"url":\s*"(.*?)"|',$url,$urlMatches);
            $channelData = array_combine($nameMatches[1],$urlMatches[1]);
            foreach ($channelData as $name => $channelUrl) {
                echo $name . ',http://' . $src . $channelUrl . "<br/>";
            }
        } else {
            echo "未找到酒店直播源<br>";
            exit; // 停止执行代码
        }
    }
}
?>
