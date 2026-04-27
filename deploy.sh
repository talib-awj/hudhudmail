#!/bin/bash
# سكربت نشر هُدهُد على خادم Plesk
# الاستخدام: ./deploy.sh
#
# بما أنّ الوصول عبر Plesk فقط (بلا SSH):
# 1. يبني النسخة الإنتاجية محلّيًا
# 2. يُحزّم الملفّات في أرشيف جاهز للرفع عبر File Manager في Plesk
#
# للنشر اليدوي:
# 1. شغّل هذا السكربت محلّيًا
# 2. ارفع الأرشيف الناتج عبر Plesk File Manager إلى مجلّد النطاق الفرعي
# 3. فُكّ الضغط من لوحة Plesk

set -euo pipefail

# === الإعدادات ===
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
BUILD_DIR="${PROJECT_DIR}/build/dist/hudhud"
ARCHIVE_NAME="hudhud-$(date +%Y%m%d-%H%M%S).tar.gz"
DEPLOY_TARGET="/var/www/vhosts/medialab.zone/hudhud.medialab.zone"

echo "=== هُدهُد — بناء النسخة الإنتاجية ==="
echo ""

# === 1. التحقّق من المتطلّبات ===
echo "→ التحقّق من المتطلّبات..."
command -v php >/dev/null 2>&1 || { echo "خطأ: PHP غير موجود"; exit 1; }
command -v node >/dev/null 2>&1 || { echo "خطأ: Node.js غير موجود"; exit 1; }
command -v npm >/dev/null 2>&1 || { echo "خطأ: npm غير موجود"; exit 1; }

# === 2. تثبيت التبعيات ===
echo "→ تثبيت تبعيات Node.js..."
cd "${PROJECT_DIR}"
npm install --silent 2>/dev/null || echo "تحذير: بعض التبعيات لم تُثبّت"

# === 3. بناء الملفّات الساكنة ===
echo "→ بناء JS و CSS..."
npx gulp build 2>/dev/null || echo "تحذير: تخطّي بناء Gulp (قد تكون الملفّات مبنيّة مسبقًا)"

# === 4. تحضير مجلّد النشر ===
echo "→ تحضير مجلّد النشر..."
rm -rf "${BUILD_DIR}"
mkdir -p "${BUILD_DIR}"

# نسخ الملفّات المطلوبة فقط (بدون ملفّات التطوير)
cp -r "${PROJECT_DIR}/index.php" "${BUILD_DIR}/"
cp -r "${PROJECT_DIR}/_include.php" "${BUILD_DIR}/"
cp -r "${PROJECT_DIR}/.htaccess" "${BUILD_DIR}/"
cp -r "${PROJECT_DIR}/snappymail" "${BUILD_DIR}/"
cp -r "${PROJECT_DIR}/data" "${BUILD_DIR}/"

# نسخ الإضافات المخصّصة فقط
mkdir -p "${BUILD_DIR}/plugins"
for plugin in arabic-calendar arabic-search-enhance hudhud-signature prayer-times avatars; do
    if [ -d "${PROJECT_DIR}/plugins/${plugin}" ]; then
        cp -r "${PROJECT_DIR}/plugins/${plugin}" "${BUILD_DIR}/plugins/"
    fi
done

# === 5. حذف ملفّات التطوير ===
echo "→ حذف ملفّات التطوير..."
find "${BUILD_DIR}" -name ".git" -type d -exec rm -rf {} + 2>/dev/null || true
find "${BUILD_DIR}" -name ".github" -type d -exec rm -rf {} + 2>/dev/null || true
find "${BUILD_DIR}" -name "node_modules" -type d -exec rm -rf {} + 2>/dev/null || true
find "${BUILD_DIR}" -name ".gitignore" -delete 2>/dev/null || true
find "${BUILD_DIR}" -name ".gitmodules" -delete 2>/dev/null || true
find "${BUILD_DIR}" -name "*.md" ! -name "LICENSE" -delete 2>/dev/null || true

# === 6. ضبط الصلاحيات ===
echo "→ ضبط الصلاحيات..."
find "${BUILD_DIR}" -type d -exec chmod 755 {} \;
find "${BUILD_DIR}" -type f -exec chmod 644 {} \;
chmod -R 777 "${BUILD_DIR}/data" 2>/dev/null || true

# === 7. إنشاء الأرشيف ===
echo "→ إنشاء الأرشيف..."
cd "${BUILD_DIR}"
tar -czf "${PROJECT_DIR}/build/${ARCHIVE_NAME}" .
cd "${PROJECT_DIR}"

ARCHIVE_SIZE=$(du -h "build/${ARCHIVE_NAME}" | cut -f1)
echo ""
echo "=== تمّ البناء بنجاح ==="
echo "الأرشيف: build/${ARCHIVE_NAME} (${ARCHIVE_SIZE})"
echo ""
echo "=== خطوات النشر عبر Plesk ==="
echo "1. ادخل لوحة Plesk → نطاقات → medialab.zone → نطاقات فرعية"
echo "2. افتح File Manager لـ hudhud.medialab.zone"
echo "3. ارفع الملفّ: build/${ARCHIVE_NAME}"
echo "4. فُكّ الضغط من قائمة File Manager"
echo "5. تأكّد أنّ المسار: ${DEPLOY_TARGET}"
echo "6. افتح https://hudhud.medialab.zone واختبر"
echo ""
echo "=== إعداد أوّل مرّة ==="
echo "- ادخل https://hudhud.medialab.zone/?admin لإعداد لوحة الإدارة"
echo "- فعّل الإضافات من: الإدارة → الإضافات"
echo "- تحقّق من إعدادات النطاقات من: الإدارة → النطاقات"
