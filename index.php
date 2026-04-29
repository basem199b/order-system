<?php
session_start();

/* ===== بيانات تسجيل الدخول ===== */
$users = [
    "admin" => ["pass" => "1234", "role" => "manager"],
    "emp1" => ["pass" => "1111", "role" => "employee"],
];

/* ===== تسجيل الدخول ===== */
if (isset($_POST['login'])) {
    $u = $_POST['username'];
    $p = $_POST['password'];

    if (isset($users[$u]) && $users[$u]['pass'] == $p) {
        $_SESSION['user'] = $u;
        $_SESSION['role'] = $users[$u]['role'];
        header("Location: ?");
        exit;
    } else {
        $error = "بيانات الدخول غير صحيحة";
    }
}

/* ===== تسجيل الخروج ===== */
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ?");
    exit;
}

/* ===== بيانات الطلبات (مؤقتة) ===== */
if (!isset($_SESSION['orders'])) {
    $_SESSION['orders'] = [];
}

/* ===== إضافة طلب ===== */
if (isset($_POST['add_order']) && $_SESSION['role'] == 'manager') {
    $id = $_POST['order_id'];
    $qty = (int)$_POST['qty'];

    $_SESSION['orders'][$id] = [
        "qty" => $qty,
        "done" => 0,
        "employee" => null,
        "status" => "new"
    ];
}

/* ===== استلام الطلب ===== */
if (isset($_POST['take_order'])) {
    $id = $_POST['order_id'];

    if ($_SESSION['orders'][$id]['employee'] == null) {
        $_SESSION['orders'][$id]['employee'] = $_SESSION['user'];
        $_SESSION['orders'][$id]['status'] = "processing";
    }
}

/* ===== تجهيز الطلب ===== */
if (isset($_POST['scan'])) {
    $id = $_POST['order_id'];

    if ($_SESSION['orders'][$id]['employee'] == $_SESSION['user']) {
        if ($_SESSION['orders'][$id]['done'] < $_SESSION['orders'][$id]['qty']) {
            $_SESSION['orders'][$id]['done']++;
        } else {
            $msg = "❌ لا يمكن إضافة كمية أكثر من الفاتورة";
        }
    }
}

/* ===== إغلاق الطلب ===== */
if (isset($_POST['finish'])) {
    $id = $_POST['order_id'];

    if ($_SESSION['orders'][$id]['done'] == $_SESSION['orders'][$id]['qty']) {
        $_SESSION['orders'][$id]['status'] = "done";
    } else {
        $msg = "❌ لا يمكن إغلاق الطلب، يوجد نقص";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>نظام الطلبات</title>
<style>
body { font-family: Tahoma; background:#f5f5f5; padding:20px; }
.box { background:#fff; padding:20px; border-radius:10px; max-width:500px; margin:auto; }
input,button { padding:10px; width:100%; margin-top:10px; }
.order { background:#eee; padding:10px; margin-top:10px; }
</style>
</head>

<body>

<div class="box">

<?php if (!isset($_SESSION['user'])): ?>

<h2>تسجيل الدخول</h2>

<form method="post">
<input name="username" placeholder="اسم المستخدم">
<input name="password" type="password" placeholder="كلمة المرور">
<button name="login">دخول</button>
</form>

<?php if (isset($error)) echo "<p>$error</p>"; ?>

<?php else: ?>

<h3>مرحباً <?php echo $_SESSION['user']; ?></h3>
<a href="?logout">تسجيل خروج</a>

<hr>

<?php if ($_SESSION['role'] == 'manager'): ?>

<h3>إضافة طلب</h3>
<form method="post">
<input name="order_id" placeholder="رقم الطلب">
<input name="qty" type="number" placeholder="الكمية">
<button name="add_order">إضافة</button>
</form>

<hr>

<h3>جميع الطلبات</h3>
<?php foreach ($_SESSION['orders'] as $id => $o): ?>
<div class="order">
طلب: <?php echo $id ?><br>
الكمية: <?php echo $o['qty'] ?><br>
تم: <?php echo $o['done'] ?><br>
الحالة: <?php echo $o['status'] ?><br>
الموظف: <?php echo $o['employee'] ?: "—" ?>
</div>
<?php endforeach; ?>

<?php else: ?>

<h3>استلام طلب</h3>
<form method="post">
<input name="order_id" placeholder="رقم الطلب">
<button name="take_order">فتح الطلب</button>
</form>

<hr>

<?php foreach ($_SESSION['orders'] as $id => $o): ?>

<?php if ($o['employee'] == $_SESSION['user']): ?>

<div class="order">
طلب: <?php echo $id ?><br>
الكمية: <?php echo $o['qty'] ?><br>
تم: <?php echo $o['done'] ?><br>

<form method="post">
<input type="hidden" name="order_id" value="<?php echo $id ?>">
<button name="scan">📦 مسح منتج</button>
<button name="finish">✅ إنهاء الطلب</button>
</form>
</div>

<?php elseif ($o['employee'] != null): ?>

<div class="order">
طلب: <?php echo $id ?><br>
⚠️ قيد التجهيز بواسطة موظف آخر
</div>

<?php endif; ?>

<?php endforeach; ?>

<?php endif; ?>

<?php if (isset($msg)) echo "<p>$msg</p>"; ?>

<?php endif; ?>

</div>

</body>
</html>
