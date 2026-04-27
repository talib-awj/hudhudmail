<?php

/**
 * إضافة أوقات الصلاة لـ هُدهُد
 * تعرض مواقيت الصلوات الخمس في شريط جانبي صغير
 * الحساب محلّي بالكامل بدون أيّ API خارجي
 * الافتراضي: مسقط، عُمان (23.5880° N, 58.3829° E)
 */

class PrayerTimesPlugin extends \RainLoop\Plugins\AbstractPlugin
{
	const
		NAME = 'Prayer Times',
		AUTHOR = 'Hudhud',
		URL = 'https://hudhud.medialab.zone',
		VERSION = '1.0.0',
		RELEASE = '2026-04-28',
		REQUIRED = '2.33.0',
		CATEGORY = 'General',
		DESCRIPTION = 'عرض أوقات الصلوات الخمس بحسب الموقع الجغرافي (الافتراضي: مسقط)';

	public function Init(): void
	{
		$this->addJs('js/prayer-times.js');
		$this->addCss('style.css');
		$this->addTemplate('templates/PrayerTimesPanel.html');
		$this->addJsonHook('GetPrayerTimes', 'DoGetPrayerTimes');
	}

	protected function configMapping(): array
	{
		return [
			\RainLoop\Plugins\Property::NewInstance('latitude')
				->SetLabel('خطّ العرض')
				->SetType(\RainLoop\Enumerations\PluginPropertyType::STRING)
				->SetDefaultValue('23.5880')
				->SetDescription('خطّ العرض (الافتراضي: مسقط)'),

			\RainLoop\Plugins\Property::NewInstance('longitude')
				->SetLabel('خطّ الطول')
				->SetType(\RainLoop\Enumerations\PluginPropertyType::STRING)
				->SetDefaultValue('58.3829')
				->SetDescription('خطّ الطول (الافتراضي: مسقط)'),

			\RainLoop\Plugins\Property::NewInstance('method')
				->SetLabel('طريقة الحساب')
				->SetType(\RainLoop\Enumerations\PluginPropertyType::SELECTION)
				->SetDefaultValue('UmmAlQura')
				->SetDescription('طريقة حساب أوقات الصلاة')
				->SetAllowedInJs(true),

			\RainLoop\Plugins\Property::NewInstance('city_name')
				->SetLabel('اسم المدينة')
				->SetType(\RainLoop\Enumerations\PluginPropertyType::STRING)
				->SetDefaultValue('مسقط')
				->SetDescription('يظهر في الواجهة')
				->SetAllowedInJs(true),
		];
	}

	/**
	 * نقطة API لجلب أوقات الصلاة
	 */
	public function DoGetPrayerTimes(): array
	{
		$lat = (float) $this->Config()->Get('plugin', 'latitude', '23.5880');
		$lng = (float) $this->Config()->Get('plugin', 'longitude', '58.3829');
		$city = $this->Config()->Get('plugin', 'city_name', 'مسقط');

		$times = self::calculate($lat, $lng);

		return $this->jsonResponse(__FUNCTION__, [
			'city' => $city,
			'times' => $times,
		]);
	}

	/**
	 * حساب أوقات الصلاة — طريقة أمّ القرى
	 * Fajr: 18.5° تحت الأفق، Isha: 90 دقيقة بعد المغرب (في رمضان 120 دقيقة)
	 */
	public static function calculate(float $lat, float $lng, ?int $timestamp = null): array
	{
		$timestamp = $timestamp ?? \time();
		$date = \getdate($timestamp);

		$year  = $date['year'];
		$month = $date['mon'];
		$day   = $date['mday'];

		// حساب يوم السنة
		$dayOfYear = (int) \date('z', $timestamp) + 1;

		// المعادلة الزمنية وزاوية ميل الشمس
		$D = $dayOfYear;
		$g = 357.529 + 0.98560028 * $D;
		$q = 280.459 + 0.98564736 * $D;
		$L = $q + 1.915 * \sin(\deg2rad($g)) + 0.020 * \sin(\deg2rad(2 * $g));

		$e = 23.439 - 0.00000036 * $D;
		$RA = \rad2deg(\atan2(\cos(\deg2rad($e)) * \sin(\deg2rad($L)), \cos(\deg2rad($L)))) / 15;

		$decl = \rad2deg(\asin(\sin(\deg2rad($e)) * \sin(\deg2rad($L))));

		$eqTime = ($q / 15) - $RA;

		// وقت الظهر بالتوقيت المحلّي (UTC+4 لعُمان)
		$timezone = 4; // UTC+4 for Oman
		$dhuhr = 12 + $timezone - $lng / 15 - $eqTime;

		// حساب زاوية الساعة
		$fajrAngle = 18.5; // أمّ القرى
		$sunriseAngle = 0.833;

		$cosHA = function($angle) use ($lat, $decl) {
			$val = (-\sin(\deg2rad($angle)) - \sin(\deg2rad($lat)) * \sin(\deg2rad($decl)))
				/ (\cos(\deg2rad($lat)) * \cos(\deg2rad($decl)));
			return \max(-1, \min(1, $val));
		};

		$haFajr = \rad2deg(\acos($cosHA($fajrAngle))) / 15;
		$haSunrise = \rad2deg(\acos($cosHA($sunriseAngle))) / 15;
		$haAsr = \rad2deg(\acos($cosHA(
			\rad2deg(\atan(1 / (1 + \tan(\deg2rad(\abs($lat - $decl))))))
		))) / 15;

		// حساب الأوقات
		$fajr    = $dhuhr - $haFajr;
		$sunrise = $dhuhr - $haSunrise;
		$asr     = $dhuhr + $haAsr;
		$maghrib = $dhuhr + $haSunrise;
		$isha    = $maghrib + 1.5; // 90 دقيقة بعد المغرب (أمّ القرى)

		$format = function($time) {
			$hours = (int) $time;
			$minutes = (int) \round(($time - $hours) * 60);
			if ($minutes == 60) { $hours++; $minutes = 0; }
			return \sprintf('%02d:%02d', $hours % 24, $minutes);
		};

		return [
			'fajr'    => $format($fajr),
			'sunrise' => $format($sunrise),
			'dhuhr'   => $format($dhuhr),
			'asr'     => $format($asr),
			'maghrib' => $format($maghrib),
			'isha'    => $format($isha),
		];
	}
}
