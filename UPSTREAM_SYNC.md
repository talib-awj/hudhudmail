# مزامنة هدهد مع SnappyMail الأصلي

## الإستراتيجية

تخصيصاتنا محصورة في:
- ثيم `Hudhud/` و `Hudhud-Dark/` — ملفّات مستقلّة لا تتعارض
- إضافات `arabic-calendar/`، `arabic-search-enhance/`، `hudhud-signature/`، `prayer-times/` — مجلّدات مستقلّة
- ملفّات تهيئة في `data/` — خاصّة بنا
- تعديلات محدودة في: `Application.php`، `Login.html`، `Index.html`، `manifest.json`، `favicon.svg`

## المزامنة الدوريّة (كلّ شهر)

### 1. جلب آخر التحديثات من المنبع

```bash
git fetch upstream
```

### 2. مراجعة التغييرات

```bash
git log --oneline master..upstream/master | head -20
```

### 3. دمج التحديثات

```bash
git merge upstream/master
```

### 4. حلّ التعارضات (إن وُجدت)

الملفّات المُحتمل تعارضها:
- `snappymail/v/0.0.0/app/libraries/RainLoop/Config/Application.php` — تحقّق من بقاء إعداداتنا
- `snappymail/v/0.0.0/app/templates/Views/User/Login.html` — تحقّق من بقاء شعار هدهد
- `snappymail/v/0.0.0/app/templates/Index.html` — تحقّق من theme-color
- `snappymail/v/0.0.0/static/manifest.json` — أَبقِ نسختنا
- `snappymail/v/0.0.0/static/favicon.svg` — أَبقِ نسختنا

**قاعدة:** عند التعارض، أَبقِ تعديلاتنا في الملفّات أعلاه.

### 4. اختبار محلّي

```bash
npm install && npx gulp build
# افتح في المتصفّح وتحقّق
```

### 5. رفع التحديث

```bash
git push origin master
```

### 6. إعادة النشر

```bash
./deploy.sh
# ارفع الأرشيف الناتج عبر Plesk
```

## ملاحظات

- `upstream` يُشير إلى `https://github.com/the-djmaze/snappymail.git`
- `origin` يُشير إلى `https://github.com/talib-awj/hudhudmail.git`
- الثيمات والإضافات المستقلّة **لن تتعارض أبدًا** مع تحديثات المنبع
- التعارضات تحدث فقط في الملفّات الخمسة المذكورة أعلاه
