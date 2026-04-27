#!/bin/bash
# سكربت إضافة نطاق جديد لـ هُدهُد
# الاستخدام: ./add-domain.sh example.com
# يُولّد ملفّ تهيئة النطاق بإعدادات Plesk الافتراضية

set -euo pipefail

# === التحقّق من المدخلات ===
if [ -z "${1:-}" ]; then
    echo "الاستخدام: $0 <domain>"
    echo "مثال: $0 example.com"
    exit 1
fi

DOMAIN="$1"
# مسار ملفّات النطاقات — عدّله إذا كان مختلفًا في بيئتك الإنتاجية
DOMAINS_DIR="${HUDHUD_DATA_DIR:-./data/_data_/_default_/domains}"

# التحقّق من وجود المجلّد
if [ ! -d "$DOMAINS_DIR" ]; then
    echo "خطأ: مجلّد النطاقات غير موجود: $DOMAINS_DIR"
    echo "عيّن المتغيّر HUDHUD_DATA_DIR أو أنشئ المجلّد يدويًا"
    exit 1
fi

# التحقّق من عدم وجود ملفّ سابق
if [ -f "${DOMAINS_DIR}/${DOMAIN}.json" ]; then
    echo "تحذير: ملفّ التهيئة موجود مسبقًا: ${DOMAINS_DIR}/${DOMAIN}.json"
    read -p "هل تريد الكتابة فوقه؟ (n/y) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "تمّ الإلغاء."
        exit 0
    fi
fi

# === إعدادات قابلة للتخصيص ===
IMAP_HOST="${IMAP_HOST:-mail.${DOMAIN}}"
IMAP_PORT="${IMAP_PORT:-993}"
SMTP_HOST="${SMTP_HOST:-mail.${DOMAIN}}"
SMTP_PORT="${SMTP_PORT:-465}"
SIEVE_ENABLED="${SIEVE_ENABLED:-true}"

# === توليد ملفّ التهيئة ===
cat > "${DOMAINS_DIR}/${DOMAIN}.json" << EOF
{
    "IMAP": {
        "host": "${IMAP_HOST}",
        "port": ${IMAP_PORT},
        "type": 1,
        "timeout": 300,
        "shortLogin": false,
        "lowerLogin": true,
        "sasl": ["SCRAM-SHA-256", "SCRAM-SHA-1", "PLAIN", "LOGIN"],
        "ssl": {
            "verify_peer": true,
            "verify_peer_name": true,
            "allow_self_signed": false,
            "SNI_enabled": true,
            "disable_compression": true,
            "security_level": 1
        }
    },
    "SMTP": {
        "host": "${SMTP_HOST}",
        "port": ${SMTP_PORT},
        "type": 1,
        "timeout": 60,
        "shortLogin": false,
        "lowerLogin": true,
        "sasl": ["SCRAM-SHA-256", "SCRAM-SHA-1", "PLAIN", "LOGIN"],
        "ssl": {
            "verify_peer": true,
            "verify_peer_name": true,
            "allow_self_signed": false,
            "SNI_enabled": true,
            "disable_compression": true,
            "security_level": 1
        },
        "useAuth": true,
        "setSender": false,
        "usePhpMail": false
    },
    "Sieve": {
        "host": "mail.${DOMAIN}",
        "port": 4190,
        "type": 0,
        "timeout": 10,
        "shortLogin": false,
        "lowerLogin": true,
        "sasl": ["PLAIN", "LOGIN"],
        "ssl": {
            "verify_peer": false,
            "verify_peer_name": false,
            "allow_self_signed": true,
            "SNI_enabled": true
        },
        "enabled": ${SIEVE_ENABLED}
    },
    "whiteList": ""
}
EOF

echo "تمّ إنشاء تهيئة النطاق: ${DOMAIN}"
echo "  IMAP: ${IMAP_HOST}:${IMAP_PORT} (SSL)"
echo "  SMTP: ${SMTP_HOST}:${SMTP_PORT} (SSL)"
echo "  Sieve: ${SIEVE_ENABLED}"
echo ""
echo "للتخصيص، استخدم متغيّرات البيئة:"
echo "  IMAP_HOST=imap.example.com SMTP_HOST=smtp.example.com $0 example.com"
echo ""
echo "مثال Office 365:"
echo "  IMAP_HOST=outlook.office365.com IMAP_PORT=993 SMTP_HOST=smtp.office365.com SMTP_PORT=587 $0 example.com"
