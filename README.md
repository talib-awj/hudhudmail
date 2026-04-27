<div align="center">

# هُدهُد 🕊️

**بريدك بهيئة عربية**

عميل بريد ويب عصري بهويّة عربية مستقلّة، مبنيّ على [SnappyMail](https://github.com/the-djmaze/snappymail)

[![بناء هدهد](https://github.com/talib-awj/hudhudmail/actions/workflows/build.yml/badge.svg)](https://github.com/talib-awj/hudhudmail/actions)
[![رخصة AGPL](https://img.shields.io/badge/license-AGPL--3.0-blue.svg)](LICENSE)

</div>

---

## المرجع الرمزي

**الهدهد** — أوّل ناقل بريد موثّق في التراث القرآني. ورد ذكره في سورة النمل حاملًا رسالة سليمان عليه السلام إلى بلقيس ملكة سبأ:

> «اذهب بكتابي هذا فألقه إليهم ثمّ تولّ عنهم فانظر ماذا يرجعون» — النمل: ٢٨

## الميزات

- **واجهة عربية أصيلة** — خطّ Tajawal محلّي، اتجاه RTL، ألوان مستوحاة من ريش الهدهد
- **التاريخ الهجري** — يظهر بجانب الميلادي بخوارزمية أمّ القرى (بدون API خارجي)
- **بحث عربي ذكي** — يتجاهل اختلافات الألف والياء والتاء المربوطة والحركات
- **أوقات الصلاة** — الصلوات الخمس في الشريط الجانبي (الافتراضي: مسقط)
- **توقيع هدهد** — توقيع أنيق «أُرسلت بـهدهد» لكلّ مستخدم
- **وضع داكن** — ثيم Hudhud-Dark بألوان دافئة
- **اكتشاف تلقائي** — يتعرّف على إعدادات البريد حسب النطاق
- **خصوصية كاملة** — لا Google Fonts، لا خدمات خارجية، كلّ شيء محلّي

## بنية الملفّات

```
hudhud/
├── snappymail/v/0.0.0/
│   ├── themes/
│   │   ├── Hudhud/          ← ثيم هدهد (فاتح)
│   │   └── Hudhud-Dark/     ← ثيم هدهد (داكن)
│   └── static/
│       ├── fonts/tajawal/   ← خطّ Tajawal محلّي
│       └── favicon.svg      ← أيقونة الهدهد
├── plugins/
│   ├── arabic-calendar/     ← التاريخ الهجري
│   ├── arabic-search-enhance/ ← تطبيع البحث العربي
│   ├── hudhud-signature/    ← التوقيع الافتراضي
│   └── prayer-times/        ← أوقات الصلاة
├── data/_data_/_default_/
│   └── domains/             ← تهيئة النطاقات
├── add-domain.sh            ← إضافة نطاق جديد
├── deploy.sh                ← سكربت النشر
├── DEPLOY.md                ← دليل النشر على Plesk
├── UPSTREAM_SYNC.md         ← دليل المزامنة مع SnappyMail
└── CHANGELOG.ar.md          ← سجلّ التغييرات بالعربية
```

## التثبيت السريع

```bash
# استنساخ المستودع
git clone https://github.com/talib-awj/hudhudmail.git
cd hudhudmail

# بناء النسخة الإنتاجية
npm install
npx gulp build

# إضافة نطاق جديد
./add-domain.sh example.com

# النشر
./deploy.sh
```

راجع [DEPLOY.md](DEPLOY.md) للتفاصيل الكاملة.

## العلاقة بـ SnappyMail

هدهد **افتراع** (fork) من SnappyMail. نحترم المشروع الأصلي ونلتزم بما يلي:

- جميع التخصيصات في ملفّات **منفصلة** (ثيمات وإضافات)
- التعديلات على ملفّات النواة **محدودة جدًّا** (5 ملفّات فقط)
- المزامنة الدورية مع المنبع الأصلي (راجع [UPSTREAM_SYNC.md](UPSTREAM_SYNC.md))
- الإشارة الواضحة للمصدر في الواجهة والتوثيق

## المساهمة

1. افترع المستودع (Fork)
2. أنشئ فرعًا لميزتك: `git checkout -b feature/اسم-الميزة`
3. نفّذ تعديلاتك والتزم: `git commit -m "أَضِف ميزة كذا"`
4. ارفع الفرع: `git push origin feature/اسم-الميزة`
5. افتح طلب دمج (Pull Request)

**قاعدة:** رسائل commit بالعربية بصيغة فعل الأمر.

## الرخصة

هدهد مرخّص تحت **AGPLv3** — نفس رخصة SnappyMail.

هذا يعني:
- يحقّ لك استخدامه وتعديله ونشره بحرّية
- **يجب** إبقاء المصدر مفتوحًا إذا نشرته كخدمة عبر الشبكة
- **يجب** الإشارة إلى المصدر الأصلي (SnappyMail)
- أيّ افتراع منك يجب أن يحمل نفس الرخصة

---

<div align="center">

مبنيّ على [SnappyMail](https://github.com/the-djmaze/snappymail) · من [مختبر الإعلام](https://medialab.zone)

</div>
