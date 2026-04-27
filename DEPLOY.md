# دليل نشر هُدهُد على خادم Plesk

## المتطلّبات

- خادم Plesk مع PHP 8.2 فأعلى
- نطاق فرعي `hudhud.medialab.zone` مُعدّ في Plesk
- شهادة SSL (Let's Encrypt) مفعّلة

## إنشاء النطاق الفرعي في Plesk

1. ادخل **لوحة Plesk** ← **نطاقات** ← **medialab.zone**
2. اختر **نطاقات فرعية** ← **إضافة نطاق فرعي**
3. اكتب `hudhud` في حقل الاسم
4. المسار الافتراضي: `/httpdocs` (اتركه كما هو)
5. اضغط **موافق**

## ضبط PHP

1. من صفحة النطاق الفرعي `hudhud.medialab.zone`
2. اختر **PHP Settings**
3. اضبط:
   - **PHP version**: 8.2 أو أعلى
   - **PHP handler**: PHP-FPM (مُفضّل للأداء)
   - **memory_limit**: 128M كحدّ أدنى
   - **upload_max_filesize**: 25M
   - **post_max_size**: 30M
   - **max_execution_time**: 60

## تفعيل شهادة SSL

1. من صفحة النطاق الفرعي ← **SSL/TLS Certificates**
2. اختر **Let's Encrypt**
3. أدخل بريدك الإلكتروني
4. فعّل **Redirect from HTTP to HTTPS**
5. اضغط **Install**

## إضافة سجلّ DNS

إذا لم يكن موجودًا تلقائيًا:

| النوع | الاسم | القيمة |
|---|---|---|
| A | hudhud.medialab.zone | عنوان IP الخادم |

عادةً Plesk يُضيفه تلقائيًا عند إنشاء النطاق الفرعي.

## النشر

### الطريقة 1: عبر سكربت البناء (مُفضّلة)

```bash
# على جهازك المحلّي
./deploy.sh

# سيُنتج أرشيفًا في build/hudhud-YYYYMMDD-HHMMSS.tar.gz
```

ثمّ:
1. افتح **Plesk** ← **File Manager** لـ `hudhud.medialab.zone`
2. ارفع ملفّ الأرشيف `.tar.gz`
3. اضغط بالزرّ الأيمن ← **Extract Files**
4. تأكّد أنّ `index.php` في جذر المجلّد

### الطريقة 2: رفع يدوي

1. ابنِ المشروع: `npm install && npx gulp build`
2. ارفع هذه الملفّات/المجلّدات عبر File Manager:
   - `index.php`
   - `_include.php`
   - `.htaccess`
   - `snappymail/`
   - `data/`
   - `plugins/` (الإضافات المُختارة فقط)

## الإعداد الأوّلي

1. افتح `https://hudhud.medialab.zone/?admin`
2. في أوّل زيارة، سيطلب إنشاء كلمة مرور للإدارة
3. من لوحة الإدارة:
   - **عام**: تأكّد من اللغة (العربية) والثيم (Hudhud)
   - **النطاقات**: تحقّق من وجود نطاقاتك الخمسة
   - **الإضافات**: فعّل:
     - `arabic-calendar`
     - `arabic-search-enhance`
     - `hudhud-signature`
     - `prayer-times`
     - `avatars`

## قائمة الفحص بعد النشر

- [ ] فتح `https://hudhud.medialab.zone` — تظهر شاشة الدخول بهوية هدهد
- [ ] تسجيل الدخول ببريد من `alabri.om`
- [ ] تسجيل الدخول ببريد من `bahja.om`
- [ ] تسجيل الدخول ببريد من `medialab.zone`
- [ ] تسجيل الدخول ببريد من `visitalhamra.om`
- [ ] تسجيل الدخول ببريد من `rhythmom.com`
- [ ] إرسال رسالة اختبارية واستلامها
- [ ] التاريخ الهجري ظاهر في الواجهة
- [ ] البحث بكلمة عربية مع تطبيع الألف
- [ ] أوقات الصلاة ظاهرة في الشريط الجانبي
- [ ] الواجهة تعمل على الجوّال (responsive)
- [ ] الوضع الداكن يعمل (Hudhud-Dark)
- [ ] الفوتر يعرض «مبنيّ على SnappyMail»

## تعديل الإعدادات لخوادم خارج Plesk

### Office 365

```bash
IMAP_HOST=outlook.office365.com IMAP_PORT=993 \
SMTP_HOST=smtp.office365.com SMTP_PORT=587 \
./add-domain.sh contoso.com
```

### Gmail (Google Workspace)

```bash
IMAP_HOST=imap.gmail.com IMAP_PORT=993 \
SMTP_HOST=smtp.gmail.com SMTP_PORT=465 \
./add-domain.sh example.com
```

### خادم مخصّص

```bash
IMAP_HOST=mail.example.com IMAP_PORT=993 \
SMTP_HOST=mail.example.com SMTP_PORT=465 \
./add-domain.sh example.com
```

عدّل ملفّ `data/_data_/_default_/domains/example.com.json` يدويًا إذا احتجت إعدادات متقدّمة.
