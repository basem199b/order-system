<?php
session_start();

$db = new PDO("sqlite:data.db");

// إنشاء الجداول
$db->exec("CREATE TABLE IF NOT EXISTS users (
id INTEGER PRIMARY KEY,
username TEXT,
password TEXT,
role TEXT
)");

$db->exec("CREATE TABLE IF NOT EXISTS orders (
id INTEGER PRIMARY KEY,
order_no TEXT,
status TEXT,
assigned_to INTEGER
)");

// أول تشغيل
if($db->query("SELECT COUNT(*) FROM users")->fetchColumn()==0){
$db->exec("INSERT INTO users VALUES
(1,'admin','1234','admin'),
(2,'emp','1234','employee')");
}

// تسجيل الدخول
if(isset($_POST['login'])){
$q=$db->prepare("SELECT * FROM users WHERE username=? AND password=?");
$q->execute([$_POST['u'],$_POST['p']]);
$user=$q->fetch();
if($user){
$_SESSION['id']=$user['id'];
$_SESSION['role']=$user['role'];
header("Location:/"); exit;
}
}

// إضافة طلب
if(isset($_POST['add']) && $_SESSION['role']=='admin'){
$db->prepare("INSERT INTO orders (order_no,status) VALUES (?,?)")
->execute([$_POST['order'],'new']);
}

// استلام طلب
if(isset($_GET['take'])){
$db->prepare("UPDATE orders SET assigned_to=?,status='working' WHERE id=? AND assigned_to IS NULL")
->execute([$_SESSION['id'],$_GET['take']]);
}

// إنهاء طلب
if(isset($_GET['done'])){
$db->prepare("UPDATE orders SET status='done' WHERE id=? AND assigned_to=?")
->execute([$_GET['done'],$_SESSION['id']]);
}
?>

<html dir="rtl">
<body style="font-family:tahoma">

<?php if(!isset($_SESSION['id'])): ?>
<form method="post">
<input name="u" placeholder="المستخدم"><br>
<input name="p" type="password" placeholder="كلمة المرور"><br>
<button name="login">دخول</button>
</form>
<p>admin / 1234</p>

<?php else: ?>

<h2>النظام</h2>

<?php if($_SESSION['role']=='admin'): ?>
<form method="post">
<input name="order" placeholder="رقم الطلب">
<button name="add">إضافة طلب</button>
</form>
<?php endif; ?>

<hr>

<?php
$orders=$db->query("SELECT * FROM orders")->fetchAll();
foreach($orders as $o):
?>

<div>
طلب: <?=$o['order_no']?> |
حالة: <?=$o['status']?>

<?php if($o['status']=='new'): ?>
<a href="?take=<?=$o['id']?>">استلام</a>
<?php endif; ?>

<?php if($o['assigned_to']==$_SESSION['id'] && $o['status']=='working'): ?>
<a href="?done=<?=$o['id']?>">تم</a>
<?php endif; ?>

</div>

<?php endforeach; ?>

<?php endif; ?>

</body>
</html>
