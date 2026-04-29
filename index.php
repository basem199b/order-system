<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <!-- وسوم لضمان ظهور اللغة العربية بوضوح في سيرفر Vultr -->
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام أطايب التمور - Vultr</title>
    
    <!-- مكتبة قارئ الباركود -->
    <script src="https://unpkg.com/html5-qrcode"></script>

    <style>
        :root { --primary: #8b4513; --success: #28a745; --danger: #dc3545; --gray: #f4f4f9; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--gray); margin: 0; padding: 10px; direction: rtl; text-align: right; color: #333; }
        .container { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .hidden { display: none !important; }
        input { width: 100%; padding: 15px; margin: 10px 0; border-radius: 8px; border: 2px solid #ddd; font-size: 16px; box-sizing: border-box; }
        button { width: 100%; padding: 15px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 18px; transition: 0.3s; }
        button:active { transform: scale(0.98); }
        .data-card { background: #fff8f0; border: 1px solid #e0d0c0; padding: 15px; border-radius: 10px; margin: 15px 0; }
        .data-line { margin: 8px 0; font-size: 15px; border-bottom: 1px dashed #dcc; padding-bottom: 5px; }
        .data-line b { color: var(--primary); }
        .product-card { border: 2px solid #eee; padding: 15px; border-radius: 10px; margin-top: 10px; display: flex; justify-content: space-between; align-items: center; }
        .product-card.completed { border-color: var(--success); background: #f0fff4; }
        .alert { padding: 12px; border-radius: 8px; color: white; text-align: center; margin: 10px 0; display: none; font-weight: bold; }
        #reader { width: 100%; border-radius: 12px; overflow: hidden; margin-bottom: 15px; border: 2px solid #8b4513; }
    </style>
</head>
<body>

<div class="container">
    <!-- شاشة تسجيل الدخول -->
    <div id="login-screen">
        <h2 style="text-align:center; color: var(--primary);">نظام أطايب التمور اللوجستي</h2>
        <p style="text-align:center; font-size: 14px;">يرجى إدخال الرمز السري للموظف للمتابعة</p>
        <input type="password" id="emp-pin" placeholder="أدخل الرمز (مثلاً 11)" inputmode="numeric">
        <button onclick="login()">دخول النظام</button>
    </div>

    <!-- شاشة القائمة الرئيسية -->
    <div id="menu-screen" class="hidden">
        <h3 id="welcome-msg" style="color: var(--primary);"></h3>
        <button onclick="startProcess()">بدء تجهيز طلب</button>
        <button onclick="location.reload()" style="background:#777; margin-top: 10px; font-size: 14px;">تسجيل خروج</button>
    </div>

    <!-- شاشة تجهيز الطلب -->
    <div id="action-screen" class="hidden">
        <h3 id="step-title">امسح باركود الفاتورة</h3>
        <div id="reader"></div>
        
        <div id="status-alert" class="alert"></div>

        <!-- خيار الإدخال اليدوي للطوارئ -->
        <div id="manual-section" style="background:#eee; padding:10px; border-radius:8px; margin-bottom:10px;">
            <small>إذا تعذر المسح، أدخل الرقم يدوياً:</small>
            <input type="text" id="manual-input" placeholder="رقم الطلب أو SKU المنتج">
            <button style="background:#6c757d; font-size: 14px;" onclick="handleManualInput()">تأكيد الإدخال اليدوي</button>
        </div>

        <!-- كرت بيانات العميل -->
        <div id="order-info-card" class="data-card hidden">
            <div class="data-line"><b>رقم الطلب:</b> <span id="view-order-id"></span></div>
            <div class="data-line"><b>اسم العميل:</b> <span id="view-cust-name"></span></div>
            <div class="data-line"><b>رقم الجوال:</b> <span id="view-cust-phone"></span></div>
            <div class="data-line"><b>الموظف:</b> <span id="view-emp-name"></span></div>
        </div>

        <div id="products-list"></div>

        <button id="save-btn" class="hidden" onclick="finalize()" style="background:var(--success); margin-top:20px;">اعتماد التجهيز وحفظ الوقت</button>
        <button onclick="location.reload()" style="background:#999; margin-top:10px; font-size: 14px;">إلغاء التجهيز</button>
    </div>
</div>

<script>
    // بيانات الموظفين الثابتة
    const EMPLOYEES = [
        { id: "101", name: "أحمد محمد", pin: "11" },
        { id: "102", name: "خالد عبدالله", pin: "22" }
    ];

    let currentEmp = null;
    let currentOrder = null;
    let html5QrCode = null;

    // دالة تسجيل الدخول
    function login() {
        const pin = document.getElementById('emp-pin').value;
        const user = EMPLOYEES.find(u => u.pin === pin);
        
        if(user) {
            currentEmp = user;
            document.getElementById('login-screen').classList.add('hidden');
            document.getElementById('menu-screen').classList.remove('hidden');
            document.getElementById('welcome-msg').innerText = "مرحباً بك: " + user.name;
        } else {
            alert("الرمز السري غير صحيح! جرب 11 أو 22");
        }
    }

    // بدء عملية التجهيز وفتح الكاميرا
    function startProcess() {
        document.getElementById('menu-screen').classList.add('hidden');
        document.getElementById('action-screen').classList.remove('hidden');
        
        html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: 250 }, (text) => {
            if(!currentOrder) decodeOrder(text);
            else decodeProduct(text);
        }).catch(err => {
            showAlert("يرجى تفعيل الكاميرا أو استخدام الرابط الآمن HTTPS", "red");
        });
    }

    // معالجة الإدخال اليدوي
    function handleManualInput() {
        const val = document.getElementById('manual-input').value;
        if(!val) return;
        
        if(!currentOrder) {
            decodeOrder(val);
        } else {
            decodeProduct(val);
        }
        document.getElementById('manual-input').value = "";
    }

    // التعرف على الطلب (بناءً على فاتورة بشاير #256178922)
    function decodeOrder(id) {
        if(id === "256178922") {[cite: 1, 2]
            currentOrder = {
                id: id,
                customer: "بشاير",[cite: 1, 2]
                phone: "0501654403",[cite: 1, 2]
                items: [
                    { sku: "006020129", name: "سلة خوص معمول فاخر", qty: 1, scanned: 0 },[cite: 1, 2]
                    { sku: "006020114", name: "سلة كليجا ميني", qty: 1, scanned: 0 }[cite: 1, 2]
                ]
            };
            showOrderData();
        } else {
            showAlert("رقم الطلب غير صحيح!", "red");
        }
    }

    function showOrderData() {
        document.getElementById('view-order-id').innerText = currentOrder.id;
        document.getElementById('view-cust-name').innerText = currentOrder.customer;
        document.getElementById('view-cust-phone').innerText = currentOrder.phone;
        document.getElementById('view-emp-name').innerText = currentEmp.name;
        
        document.getElementById('order-info-card').classList.remove('hidden');
        document.getElementById('step-title').innerText = "امسح منتجات الطلب";
        renderItems();
        showAlert("تم التعرف على الطلب بنجاح", "green");
    }

    // التعرف على باركود المنتج (SKU)
    function decodeProduct(sku) {
        let item = currentOrder.items.find(i => i.sku === sku);
        if(!item) {
            showAlert("هذا المنتج غير موجود في فاتورة بشاير!", "red");[cite: 1, 2]
            return;
        }
        if(item.scanned >= item.qty) {
            showAlert("تم اكتمال الكمية المطلوبة من هذا المنتج", "red");
            return;
        }

        item.scanned++;
        renderItems();
        showAlert("تم تجهيز: " + item.name, "green");
    }

    function renderItems() {
        const list = document.getElementById('products-list');
        list.innerHTML = currentOrder.items.map(i => `
            <div class="product-card ${i.scanned === i.qty ? 'completed' : ''}">
                <div>
                    <b>${i.name}</b><br>
                    <small style="color:#666">SKU: ${i.sku}</small>
                </div>
                <div style="font-size: 20px; font-weight: bold;">${i.scanned} / ${i.qty}</div>
            </div>
        `).join('');

        const allDone = currentOrder.items.every(i => i.scanned === i.qty);
        if(allDone) {
            document.getElementById('save-btn').classList.remove('hidden');
            showAlert("اكتمل التجهيز! يمكنك الحفظ الآن", "green");
        }
    }

    function finalize() {
        const now = new Date();
        const time = now.toLocaleTimeString('ar-SA');
        alert("تم الحفظ بنجاح!\nالموظف: " + currentEmp.name + "\nالوقت: " + time);
        location.reload();
    }

    function showAlert(msg, color) {
        const div = document.getElementById('status-alert');
        div.innerText = msg;
        div.style.background = color === "green" ? "var(--success)" : "var(--danger)";
        div.style.display = "block";
        setTimeout(() => { div.style.display = "none"; }, 3000);
    }
</script>

</body>
</html>
