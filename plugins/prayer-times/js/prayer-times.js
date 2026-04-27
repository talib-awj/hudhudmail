/**
 * إضافة أوقات الصلاة — الجزء الأمامي
 */
(function () {
	'use strict';

	const PRAYER_NAMES = {
		fajr: 'الفجر',
		sunrise: 'الشروق',
		dhuhr: 'الظهر',
		asr: 'العصر',
		maghrib: 'المغرب',
		isha: 'العشاء'
	};

	const PRAYER_ICONS = {
		fajr: '🌅',
		sunrise: '☀️',
		dhuhr: '🌤',
		asr: '⛅',
		maghrib: '🌇',
		isha: '🌙'
	};

	/**
	 * تحويل وقت "HH:MM" إلى أرقام عربية
	 */
	function toArabicTime(time) {
		return time.replace(/[0-9]/g, d => '٠١٢٣٤٥٦٧٨٩'[d]);
	}

	/**
	 * تحديد الصلاة الحالية/القادمة
	 */
	function getCurrentPrayer(times) {
		const now = new Date();
		const currentMinutes = now.getHours() * 60 + now.getMinutes();
		const order = ['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha'];

		for (let i = order.length - 1; i >= 0; i--) {
			const [h, m] = times[order[i]].split(':').map(Number);
			if (currentMinutes >= h * 60 + m) {
				return { current: order[i], next: order[(i + 1) % order.length] };
			}
		}
		return { current: 'isha', next: 'fajr' };
	}

	/**
	 * بناء لوحة أوقات الصلاة
	 */
	function buildPrayerPanel(data) {
		const { city, times } = data;
		const { current, next } = getCurrentPrayer(times);

		let html = '<div class="hudhud-prayer-panel" dir="rtl">';
		html += '<div class="hudhud-prayer-header">';
		html += '<span class="hudhud-prayer-title">أوقات الصلاة</span>';
		html += '<span class="hudhud-prayer-city">' + city + '</span>';
		html += '</div>';
		html += '<div class="hudhud-prayer-list">';

		const order = ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'];
		for (const key of order) {
			const isNext = key === next;
			html += '<div class="hudhud-prayer-item' + (isNext ? ' next' : '') + '">';
			html += '<span class="hudhud-prayer-name">' + PRAYER_NAMES[key] + '</span>';
			html += '<span class="hudhud-prayer-time">' + toArabicTime(times[key]) + '</span>';
			html += '</div>';
		}

		html += '</div></div>';
		return html;
	}

	/**
	 * إدراج اللوحة في الشريط الجانبي
	 */
	function injectPanel() {
		if (document.querySelector('.hudhud-prayer-panel')) return;

		rl.pluginRemoteRequest(function (iError, data) {
			if (iError || !data || !data.Result) return;

			const sidebar = document.querySelector('.b-folders');
			if (!sidebar) return;

			const panel = document.createElement('div');
			panel.innerHTML = buildPrayerPanel(data.Result);
			sidebar.appendChild(panel.firstElementChild);
		}, 'GetPrayerTimes');
	}

	// مراقبة تحميل الشريط الجانبي
	document.addEventListener('DOMContentLoaded', function () {
		const observer = new MutationObserver(function () {
			if (document.querySelector('.b-folders') && !document.querySelector('.hudhud-prayer-panel')) {
				injectPanel();
			}
		});

		const target = document.getElementById('rl-content') || document.body;
		observer.observe(target, { childList: true, subtree: true });
	});

	// تحديث كلّ 5 دقائق
	setInterval(function () {
		const panel = document.querySelector('.hudhud-prayer-panel');
		if (panel) panel.remove();
		injectPanel();
	}, 300000);
})();
