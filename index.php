<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام كل زق  التمور اللوجستي</title>
    
    <!-- مكتبة قارئ الباركود -->
    <script src="https://unpkg.com/html5-qrcode"></script>

    <style>
        :root { --primary: #8b4513; --success: #28a745; --danger: #dc3545; --bg: #f4f4f9; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: var(--bg); margin: 0; padding: 10px; direction: rtl; text-align: right; color: #333; }
        .container { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .hidden { display: none !important; }
        input { width: 100%; padding: 15px; margin: 10px 0; border-radius: 8px; border: 2px solid #ddd; font-size: 16px; box-sizing: border-box; color: black; }
        button { width: 100%; padding: 15px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 18px; margin: 5px 0; }
        .data-card { background: #fff8f0; border: 1px solid #e0d0c0; padding: 15px; border-radius: 10px; margin: 15px 0; }
        .data-line { margin: 8px 0; font-size: 15px; border-bottom: 1px dashed #dcc; padding-bottom: 5px; }
        .data-line b { color: var(--primary); }
        .product-card { border: 2px solid #eee; padding: 15px; border-radius: 10px; margin-top: 10px; display: flex; justify-content: space-between; align-items: center; }
        .product-card.completed { border-color: var(--success); background: #f0fff4; }
        .alert { padding: 12px; border-radius: 8px; color: white; text-align: center; margin: 10px 0; display: none; font-weight: bold; }
        #reader { width: 100%; border-radius: 12px; overflow: hidden; margin-bottom: 15px; border: 2px solid var(--primary); background: #000; }
    </style>
</head>
<body>

<div class="container">
    <!-- 1. شاشة الدخول -->
    <div id="login-screen">
        <h2 style="text-align:center; color: var(--primary);">نظام أطايب التمور</h2>
        <p style="text-align:center;">يرجى إدخال الرمز السري للموظف</p>
        <input type="password" id="emp-pin" placeholder="أدخل الرمز (11 أو 22)" inputmode="numeric">
        <button onclick="login()">دخول النظام</button>
    </div>

    <!-- 2. القائمة الرئيسية -->
    <div id="menu-screen" class="hidden">
        <h3 id="welcome-msg" style="color: var(--primary);"></h3>
        <button onclick="startProcess()">بدء تجهيز طلب</button>
        <button onclick="location.reload()" style="background:#777;">تسجيل خروج</button>
    </div>

    <!-- 3. واجهة التجهيز -->
    <div id="action-screen" class="hidden">
        <h3 id="step-title">امسح باركود الفاتورة</h3>
        <div id="reader"></div>
        
        <div id="status-alert" class="alert"></div>

        <!-- الإدخال اليدوي للطوارئ -->
        <div style="background:#eee; padding:10px; border-radius:8px; margin-bottom:10px;">
            <small>إدخال يدوي (رقم الطلب أو SKU):</small>
            <input type="text" id="manual-input" placeholder="اكتب هنا...">
            <button style="background:#6c757d; padding:10px; font-size:14px;" onclick="handleManualInput()">تأكيد الإدخال</button>
        </div>

        <!-- كرت بيانات العميل (بشاير) -->
        <div id="order-info-card" class="data-card hidden">
            <div class="data-line"><b>رقم الطلب:</b> <span id="view-order-id"></span></div>
            <div class="data-line"><b>اسم العميل:</b> <span id="view-cust-name"></span></div>
            <div class="data-line"><b>رقم الجوال:</b> <span id="view-cust-phone"></span></div>
            <div class="data-line"><b>الموظف:</b> <span id="view-emp-name"></span></div>
        </div>

        <div id="products-list"></div>

        <button id="save-btn" class="hidden" onclick="finalize()" style="background:var(--success); margin-top:20px;">اعتماد وحفظ الوقت</button>
        <button onclick="location.reload()" style="background:#999; margin-top:10px;">إلغاء</button>
    </div>
</div>

<script>
    const EMPLOYEES = [
        { name: "أحمد محمد", pin: "11" },
        { name: "خالد عبدالله", pin: "22" }
    ];

    let currentEmp = null;
    let currentOrder = null;
    let html5QrCode = null;

    function login() {
        const pin = document.getElementById('emp-pin').value;
        const user = EMPLOYEES.find(u => u.pin === pin);
        if(user) {
            currentEmp = user;
            document.getElementById('login-screen').classList.add('hidden');
            document.getElementById('menu-screen').classList.remove('hidden');
            document.getElementById('welcome-msg').innerText = "مرحباً: " + user.name;
        } else {
            alert("الرمز خطأ! جرب 11");
        }
    }

    function startProcess() {
        document.getElementById('menu-screen').classList.add('hidden');
        document.getElementById('action-screen').classList.remove('hidden');
        initScanner();
    }

    function initScanner() {
        html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: 250 }, (text) => {
            if(!currentOrder) decodeOrder(text);
            else decodeProduct(text);
        }).catch(err => showAlert("الكاميرا لا تعمل بدون HTTPS", "red"));
    }

    function handleManualInput() {
        const val = document.getElementById('manual-input').value;
        if(!val) return;
        if(!currentOrder) decodeOrder(val); else decodeProduct(val);
        document.getElementById('manual-input').value = "";
    }

    function decodeOrder(id) {
        if(id === "256178922") {[cite: 1]
            currentOrder = {
                id: id,
                customer: "بشاير",[cite: 1]
                phone: "0501654403",[cite: 1]
                items: [
                    { sku: "006020129", name: "سلة خوص معمول فاخر", qty: 1, scanned: 0 },[cite: 1]
                    { sku: "006020114", name: "سلة كليجا ميني", qty: 1, scanned: 0 }[cite: 1]
                ]
            };
            showOrder();
        } else { showAlert("رقم طلب غير مسجل", "red"); }
    }

    function showOrder() {
        document.getElementById('view-order-id').innerText = currentOrder.id;[cite: 1]
        document.getElementById('view-cust-name').innerText = currentOrder.customer;[cite: 1]
        document.getElementById('view-cust-phone').innerText = currentOrder.phone;[cite: 1]
        document.getElementById('view-emp-name').innerText = currentEmp.name;
        document.getElementById('order-info-card').classList.remove('hidden');
        document.getElementById('step-title').innerText = "امسح المنتجات الآن";
        renderItems();
        showAlert("تم تحميل بيانات فاتورة بشاير", "green");[cite: 1]
    }

    function decodeProduct(sku) {
        let item = currentOrder.items.find(i => i.sku === sku);
        if(!item) { showAlert("المنتج ليس في الفاتورة", "red"); return; }
        if(item.scanned >= item.qty) return;
        item.scanned++;
        renderItems();
        showAlert("تم تحديث: " + item.name, "green");
    }

    function renderItems() {
        const list = document.getElementById('products-list');
        list.innerHTML = currentOrder.items.map(i => `
            <div class="product-card ${i.scanned === i.qty ? 'completed' : ''}">
                <div><b>${i.name}</b><br><small>SKU: ${i.sku}</small></div>
                <div style="font-size:20px;">${i.scanned} / ${i.qty}</div>
            </div>
        `).join('');
        if(currentOrder.items.every(i => i.scanned === i.qty)) {
            document.getElementById('save-btn').classList.remove('hidden');
        }
    }

    function finalize() {
        const time = new Date().toLocaleTimeString('ar-SA');
        alert("تم الحفظ!\nالموظف: " + currentEmp.name + "\nالوقت: " + time);
        location.reload();
    }

    function showAlert(msg, color) {
        const div = document.getElementById('status-alert');
        div.innerText = msg; div.style.background = color === "green" ? "var(--success)" : "var(--danger)";
        div.style.display = "block"; setTimeout(() => div.style.display = "none", 3000);
    }
</script>
</body>
</html>
