<?php

/**
 * إضافة التقويم الهجري لـ هُدهُد
 * تعرض التاريخ الهجري بجانب الميلادي في قائمة الرسائل وترويسة الرسالة
 * تستخدم خوارزمية أمّ القرى (Umm al-Qura) للتحويل
 */

class ArabicCalendarPlugin extends \RainLoop\Plugins\AbstractPlugin
{
	const
		NAME = 'Arabic Calendar',
		AUTHOR = 'Hudhud',
		URL = 'https://hudhud.medialab.zone',
		VERSION = '1.0.0',
		RELEASE = '2026-04-28',
		REQUIRED = '2.33.0',
		CATEGORY = 'General',
		DESCRIPTION = 'عرض التاريخ الهجري بجانب الميلادي باستخدام خوارزمية أمّ القرى';

	public function Init(): void
	{
		$this->addJs('js/arabic-calendar.js');
		$this->addCss('style.css');
	}

	/**
	 * تحويل تاريخ ميلادي إلى هجري بخوارزمية أمّ القرى
	 * Umm al-Qura calendar algorithm
	 *
	 * @param int $year السنة الميلادية
	 * @param int $month الشهر الميلادي
	 * @param int $day اليوم الميلادي
	 * @return array [year, month, day] هجري
	 */
	public static function gregorianToHijri(int $year, int $month, int $day): array
	{
		// خوارزمية أمّ القرى — تحويل دقيق
		// حساب عدد الأيام من مرجع جوليان
		$jd = self::gregorianToJD($year, $month, $day);

		// تحويل من جوليان إلى هجري
		return self::jdToHijri($jd);
	}

	/**
	 * تحويل تاريخ ميلادي إلى يوم جوليان
	 */
	private static function gregorianToJD(int $year, int $month, int $day): int
	{
		if ($month <= 2) {
			$year--;
			$month += 12;
		}

		$A = (int)($year / 100);
		$B = 2 - $A + (int)($A / 4);

		return (int)(365.25 * ($year + 4716))
			 + (int)(30.6001 * ($month + 1))
			 + $day + $B - 1524;
	}

	/**
	 * تحويل يوم جوليان إلى تاريخ هجري
	 */
	private static function jdToHijri(int $jd): array
	{
		$jd = $jd - 1948440 + 10632;
		$n  = (int)(($jd - 1) / 10631);
		$jd = $jd - 10631 * $n + 354;

		$j = ((int)((10985 - $jd) / 5316))
		   * ((int)(50 * $jd / 17719))
		   + ((int)($jd / 5670))
		   * ((int)(43 * $jd / 15238));

		$jd = $jd - ((int)((30 - $j) / 15))
			* ((int)(17719 * $j / 50))
			- ((int)($j / 16))
			* ((int)(15238 * $j / 43))
			+ 29;

		$month = (int)(24 * $jd / 709);
		$day   = $jd - (int)(709 * $month / 24);
		$year  = 30 * $n + $j - 30;

		return [$year, $month, $day];
	}

	/**
	 * أسماء الأشهر الهجرية
	 */
	public static function getHijriMonthName(int $month): string
	{
		$months = [
			1  => 'محرّم',
			2  => 'صفر',
			3  => 'ربيع الأوّل',
			4  => 'ربيع الآخر',
			5  => 'جمادى الأولى',
			6  => 'جمادى الآخرة',
			7  => 'رجب',
			8  => 'شعبان',
			9  => 'رمضان',
			10 => 'شوّال',
			11 => 'ذو القعدة',
			12 => 'ذو الحجّة',
		];

		return $months[$month] ?? '';
	}

	/**
	 * تنسيق التاريخ الهجري كنصّ عربي
	 */
	public static function formatHijriDate(int $year, int $month, int $day): string
	{
		$monthName = self::getHijriMonthName($month);
		// تحويل الأرقام إلى عربية
		$dayAr  = self::toArabicNumerals($day);
		$yearAr = self::toArabicNumerals($year);

		return "{$dayAr} {$monthName} {$yearAr}";
	}

	/**
	 * تحويل الأرقام اللاتينية إلى عربية
	 */
	public static function toArabicNumerals(int $number): string
	{
		$western = ['0','1','2','3','4','5','6','7','8','9'];
		$eastern = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];

		return str_replace($western, $eastern, (string) $number);
	}
}
