/**
 * تطبيع البحث العربي — الجزء الأمامي
 * يُطبّع نصّ البحث قبل إرساله للخادم
 */
(function () {
	'use strict';

	/**
	 * تطبيع النصّ العربي
	 */
	function normalizeArabic(text) {
		if (!text) return text;

		return text
			// إزالة الحركات والتشكيل
			.replace(/[\u064B-\u065F\u0670]/g, '')
			// توحيد الألف: أ إ آ ٱ → ا
			.replace(/[\u0623\u0625\u0622\u0671]/g, '\u0627')
			// توحيد التاء المربوطة: ة → ه
			.replace(/\u0629/g, '\u0647')
			// توحيد الياء: ى → ي
			.replace(/\u0649/g, '\u064A')
			// توحيد الكاف الفارسية
			.replace(/\u06A9/g, '\u0643');
	}

	// اعتراض حقل البحث لتطبيع المدخلات
	document.addEventListener('DOMContentLoaded', function () {
		// مراقبة حقل البحث
		const observer = new MutationObserver(function () {
			const searchInput = document.querySelector('.b-search input[type="text"], input.e-search');
			if (searchInput && !searchInput.dataset.arabicNormalized) {
				searchInput.dataset.arabicNormalized = 'true';

				// تطبيع عند الإرسال
				searchInput.form && searchInput.form.addEventListener('submit', function () {
					searchInput.value = normalizeArabic(searchInput.value);
				});

				// تطبيع عند الضغط على Enter
				searchInput.addEventListener('keydown', function (e) {
					if (e.key === 'Enter') {
						this.value = normalizeArabic(this.value);
					}
				});
			}
		});

		const target = document.getElementById('rl-content') || document.body;
		observer.observe(target, { childList: true, subtree: true });
	});
})();
