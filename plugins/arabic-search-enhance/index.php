<?php

/**
 * إضافة تحسين البحث العربي لـ هُدهُد
 * تُطبّع النصّ العربي عند البحث:
 * - الألف بأشكالها (ا، أ، إ، آ) تُعامل واحدة
 * - التاء المربوطة والهاء (ة، ه) تُعامل واحدة
 * - الياء بأشكالها (ي، ى) تُعامل واحدة
 * - تجاهل الحركات والتنوين
 */

class ArabicSearchEnhancePlugin extends \RainLoop\Plugins\AbstractPlugin
{
	const
		NAME = 'Arabic Search Enhance',
		AUTHOR = 'Hudhud',
		URL = 'https://hudhud.medialab.zone',
		VERSION = '1.0.0',
		RELEASE = '2026-04-28',
		REQUIRED = '2.33.0',
		CATEGORY = 'General',
		DESCRIPTION = 'تحسين البحث العربي: تطبيع الألف والياء والتاء المربوطة وتجاهل الحركات';

	public function Init(): void
	{
		// اعتراض استعلام البحث قبل إرساله لـ IMAP
		$this->addHook('json.before-MessageList', 'normalizeSearch');
		$this->addJs('js/arabic-search.js');
	}

	/**
	 * تطبيع استعلام البحث العربي قبل إرساله لخادم IMAP
	 */
	public function normalizeSearch(array &$aRequest): void
	{
		if (isset($aRequest['Search']) && \is_string($aRequest['Search'])) {
			$aRequest['Search'] = self::normalizeArabic($aRequest['Search']);
		}
	}

	/**
	 * تطبيع النصّ العربي
	 * يُزيل الحركات ويُوحّد أشكال الحروف المتشابهة
	 */
	public static function normalizeArabic(string $text): string
	{
		// إزالة الحركات والتشكيل العربي
		// U+064B-U+065F: فتحة، ضمّة، كسرة، تنوين، شدّة، سكون، إلخ
		$text = \preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text);

		// توحيد أشكال الألف: أ إ آ ٱ → ا
		$text = \str_replace(
			["\u{0623}", "\u{0625}", "\u{0622}", "\u{0671}"],
			"\u{0627}",
			$text
		);

		// توحيد التاء المربوطة والهاء: ة → ه
		$text = \str_replace("\u{0629}", "\u{0647}", $text);

		// توحيد الياء: ى (ألف مقصورة) → ي
		$text = \str_replace("\u{0649}", "\u{064A}", $text);

		// توحيد الكاف: ك (عربية) و ک (فارسية)
		$text = \str_replace("\u{06A9}", "\u{0643}", $text);

		return $text;
	}
}
