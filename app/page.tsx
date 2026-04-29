"use client";
import React, { useState } from 'react';

// بيانات الموظفين والطلبات (تجهيز أولي)
const EMPLOYEES = [
  { name: "أحمد محمد", pin: "11" },
  { name: "خالد عبدالله", pin: "22" }
];

export default function OrderSystem() {
  const [step, setStep] = useState('login'); // login, menu, scanning
  const [pin, setPin] = useState('');
  const [user, setUser] = useState(null);
  const [order, setOrder] = useState(null);

  // دالة تسجيل الدخول
  const handleLogin = () => {
    const found = EMPLOYEES.find(u => u.pin === pin);
    if (found) {
      setUser(found);
      setStep('menu');
    } else {
      alert("الرمز غير صحيح");
    }
  };

  // محاكاة التعرف على طلب بشاير
  const mockScanOrder = () => {
    setOrder({
      id: "256178922",[cite: 1]
      customer: "بشاير",[cite: 1]
      items: [
        { sku: "006020129", name: "منتج 1", qty: 1, scanned: 0 },[cite: 1]
        { sku: "006020114", name: "منتج 2", qty: 1, scanned: 0 }[cite: 1]
      ]
    });
    setStep('scanning');
  };

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col items-center p-4 dir-rtl" dir="rtl">
      <div className="w-full max-w-md bg-white rounded-2xl shadow-lg p-6 mt-10">
        
        {/* العناوين الرئيسية */}
        <h1 className="text-2xl font-bold text-center text-gray-800 mb-2">نظام تجهيز الطلبات</h1>
        <hr className="mb-6" />

        {/* 1. شاشة الدخول */}
        {step === 'login' && (
          <div className="space-y-4">
            <p className="text-center text-gray-600">يرجى إدخال الرمز السري</p>
            <input 
              type="password" 
              className="w-full p-4 border-2 rounded-xl text-center text-2xl tracking-widest focus:border-blue-500 outline-none"
              value={pin}
              onChange={(e) => setPin(e.target.value)}
              placeholder="****"
            />
            <button 
              onClick={handleLogin}
              className="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl transition-all"
            >
              دخول
            </button>
          </div>
        )}

        {/* 2. القائمة الرئيسية */}
        {step === 'menu' && (
          <div className="space-y-4 text-center">
            <h2 className="text-xl">مرحباً، <span className="font-bold text-blue-600">{user.name}</span></h2>
            <button 
              onClick={mockScanOrder}
              className="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-6 rounded-xl text-lg shadow-md"
            >
              بدء تجهيز طلب جديد
            </button>
            <button 
              onClick={() => setStep('login')}
              className="w-full bg-gray-200 text-gray-700 py-3 rounded-xl"
            >
              تسجيل خروج
            </button>
          </div>
        )}

        {/* 3. شاشة التجهيز (بناءً على طلب بشاير) */}
        {step === 'scanning' && order && (
          <div className="space-y-4">
            <div className="bg-blue-50 p-4 rounded-xl border border-blue-100">
              <p><strong>رقم الطلب:</strong> {order.id}[cite: 1]</p>
              <p><strong>العميل:</strong> {order.customer}[cite: 1]</p>
            </div>
            
            <div className="space-y-3">
              {order.items.map((item, idx) => (
                <div key={idx} className="flex justify-between items-center p-4 border rounded-xl bg-white shadow-sm">
                  <div>
                    <p className="font-bold">{item.name}</p>
                    <p className="text-xs text-gray-500">SKU: {item.sku}</p>
                  </div>
                  <div className="text-lg font-mono">{item.scanned} / {item.qty}</div>
                </div>
              ))}
            </div>

            <button 
              className="w-full bg-blue-600 text-white py-4 rounded-xl font-bold opacity-50 cursor-not-allowed"
              disabled
            >
              اعتماد التجهيز (قريباً)
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
