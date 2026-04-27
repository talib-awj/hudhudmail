/**
 * إضافة التقويم الهجري — الجزء الأمامي
 * تعرض التاريخ الهجري بجانب الميلادي
 */
(function () {
	'use strict';

	// أسماء الأشهر الهجرية
	const HIJRI_MONTHS = [
		'', 'محرّم', 'صفر', 'ربيع الأوّل', 'ربيع الآخر',
		'جمادى الأولى', 'جمادى الآخرة', 'رجب', 'شعبان',
		'رمضان', 'شوّال', 'ذو القعدة', 'ذو الحجّة'
	];

	// أيّام الأسبوع بالعربية
	const WEEKDAYS = [
		'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء',
		'الخميس', 'الجمعة', 'السبت'
	];

	/**
	 * تحويل ميلادي إلى هجري — خوارزمية أمّ القرى
	 */
	function gregorianToHijri(year, month, day) {
		// حساب يوم جوليان
		if (month <= 2) { year--; month += 12; }
		const A = Math.floor(year / 100);
		const B = 2 - A + Math.floor(A / 4);
		let jd = Math.floor(365.25 * (year + 4716))
				+ Math.floor(30.6001 * (month + 1))
				+ day + B - 1524;

		// تحويل من جوليان إلى هجري
		jd = jd - 1948440 + 10632;
		const n = Math.floor((jd - 1) / 10631);
		jd = jd - 10631 * n + 354;

		const j = Math.floor((10985 - jd) / 5316)
				* Math.floor(50 * jd / 17719)
				+ Math.floor(jd / 5670)
				* Math.floor(43 * jd / 15238);

		jd = jd - Math.floor((30 - j) / 15)
			* Math.floor(17719 * j / 50)
			- Math.floor(j / 16)
			* Math.floor(15238 * j / 43)
			+ 29;

		const hMonth = Math.floor(24 * jd / 709);
		const hDay = jd - Math.floor(709 * hMonth / 24);
		const hYear = 30 * n + j - 30;

		return { year: hYear, month: hMonth, day: hDay };
	}

	/**
	 * تحويل رقم إلى أرقام عربية
	 */
	function toArabicNum(num) {
		return String(num).replace(/[0-9]/g, d => '٠١٢٣٤٥٦٧٨٩'[d]);
	}

	/**
	 * تنسيق التاريخ الهجري
	 */
	function formatHijri(date) {
		const h = gregorianToHijri(date.getFullYear(), date.getMonth() + 1, date.getDate());
		return toArabicNum(h.day) + ' ' + HIJRI_MONTHS[h.month] + ' ' + toArabicNum(h.year);
	}

	/**
	 * إضافة التاريخ الهجري إلى عناصر التاريخ في الواجهة
	 */
	function injectHijriDates() {
		// إضافة التاريخ الهجري في ترويسة الرسالة المفتوحة
		const dateElements = document.querySelectorAll('.messageItemHeader .date, .messageView .date-header, [data-bind*="moment"]');
		dateElements.forEach(el => {
			if (el.dataset.hijriAdded) return;

			const dateText = el.getAttribute('title') || el.textContent;
			const parsed = new Date(dateText);

			if (!isNaN(parsed.getTime())) {
				const hijri = formatHijri(parsed);
				const hijriSpan = document.createElement('span');
				hijriSpan.className = 'hudhud-hijri-date';
				hijriSpan.textContent = ' · ' + hijri;
				el.appendChild(hijriSpan);
				el.dataset.hijriAdded = 'true';
			}
		});

		// إضافة التاريخ الهجري في شريط الحالة
		updateHeaderHijri();
	}

	/**
	 * تحديث التاريخ الهجري في الترويسة العلوية
	 */
	function updateHeaderHijri() {
		let hijriBar = document.getElementById('hudhud-hijri-bar');
		if (!hijriBar) {
			// إنشاء شريط التاريخ الهجري
			const toolbar = document.querySelector('#rl-right .b-header, .layout-right .e-toolbar');
			if (toolbar) {
				hijriBar = document.createElement('div');
				hijriBar.id = 'hudhud-hijri-bar';
				hijriBar.className = 'hudhud-hijri-bar';

				const now = new Date();
				const hijri = formatHijri(now);
				const weekday = WEEKDAYS[now.getDay()];
				hijriBar.textContent = weekday + '، ' + hijri;

				toolbar.parentNode.insertBefore(hijriBar, toolbar);
			}
		} else {
			const now = new Date();
			const hijri = formatHijri(now);
			const weekday = WEEKDAYS[now.getDay()];
			hijriBar.textContent = weekday + '، ' + hijri;
		}
	}

	// مراقبة تغييرات الـDOM لإضافة التاريخ الهجري ديناميكيًّا
	const observer = new MutationObserver(() => {
		requestAnimationFrame(injectHijriDates);
	});

	// بدء المراقبة عند تحميل التطبيق
	addEventListener('rl.view.message', () => injectHijriDates());

	document.addEventListener('DOMContentLoaded', () => {
		const target = document.getElementById('rl-content') || document.body;
		observer.observe(target, { childList: true, subtree: true });
		injectHijriDates();
	});

	// تحديث التاريخ كلّ دقيقة
	setInterval(updateHeaderHijri, 60000);
})();
