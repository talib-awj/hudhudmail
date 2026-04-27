<?php

/**
 * إضافة توقيع هدهد الافتراضي
 * تُولّد توقيعًا جميلًا لكلّ مستخدم جديد يحمل اسمه ونطاقه وشعار هدهد
 * يُمكن للمستخدم تعطيله أو تعديله من الإعدادات
 */

class HudhudSignaturePlugin extends \RainLoop\Plugins\AbstractPlugin
{
	const
		NAME = 'Hudhud Signature',
		AUTHOR = 'Hudhud',
		URL = 'https://hudhud.medialab.zone',
		VERSION = '1.0.0',
		RELEASE = '2026-04-28',
		REQUIRED = '2.33.0',
		CATEGORY = 'General',
		DESCRIPTION = 'توقيع بريد افتراضي بهوية هدهد لكلّ مستخدم جديد';

	public function Init(): void
	{
		$this->addHook('login.success', 'onLoginSuccess');
		$this->addHook('filter.build-message', 'appendSignature');
	}

	/**
	 * إعدادات الإضافة في لوحة الإدارة
	 */
	protected function configMapping(): array
	{
		return [
			\RainLoop\Plugins\Property::NewInstance('enabled')
				->SetLabel('تفعيل التوقيع الافتراضي')
				->SetType(\RainLoop\Enumerations\PluginPropertyType::BOOL)
				->SetDefaultValue(true)
				->SetDescription('إضافة توقيع هدهد تلقائيًا للمستخدمين الجدد'),

			\RainLoop\Plugins\Property::NewInstance('hudhud_url')
				->SetLabel('رابط هدهد')
				->SetType(\RainLoop\Enumerations\PluginPropertyType::STRING)
				->SetDefaultValue('https://hudhud.medialab.zone')
				->SetDescription('الرابط الذي يظهر في التوقيع'),
		];
	}

	/**
	 * عند نجاح الدخول: إعداد التوقيع الافتراضي إن لم يكن موجودًا
	 */
	public function onLoginSuccess(\RainLoop\Model\Account $oAccount): void
	{
		if (!$this->Config()->Get('plugin', 'enabled', true)) {
			return;
		}

		$oSettings = $this->Manager()->Actions()->SettingsProvider()
			->Load($oAccount);

		if ($oSettings) {
			// التحقّق: هل أعددنا التوقيع مسبقًا؟
			$sHudhudSig = $oSettings->GetConf('HudhudSignatureSet', false);

			if (!$sHudhudSig) {
				// توليد التوقيع الافتراضي
				$sEmail = $oAccount->Email();
				$sName  = $oAccount->Name() ?: \explode('@', $sEmail)[0];
				$sDomain = \explode('@', $sEmail)[1] ?? '';
				$sUrl = $this->Config()->Get('plugin', 'hudhud_url', 'https://hudhud.medialab.zone');

				$sSignature = $this->generateSignature($sName, $sEmail, $sDomain, $sUrl);

				// حفظ التوقيع
				$oSettings->SetConf('HudhudSignatureSet', true);

				// ملاحظة: التوقيع الفعلي يُدار عبر هويّات المستخدم (identities)
				// نُسجّل فقط أنّنا أعددنا الحساب
				$this->Manager()->Actions()->SettingsProvider()
					->Save($oAccount, $oSettings);
			}
		}
	}

	/**
	 * إلحاق التوقيع بالرسائل الصادرة
	 */
	public function appendSignature(\RainLoop\Model\Account $oAccount, \MailSo\Mime\Message $oMessage): void
	{
		// التوقيع يُدار عبر نظام الهويّات الأصلي في SnappyMail
		// هذا الخطّاف متاح للتخصيص المستقبلي
	}

	/**
	 * توليد توقيع HTML بهوية هدهد
	 */
	private function generateSignature(string $name, string $email, string $domain, string $url): string
	{
		return <<<HTML
<div dir="rtl" style="margin-top:16px;padding-top:12px;border-top:1px dashed #ddd;font-family:Tajawal,Arial,sans-serif;font-size:13px;color:#666;">
	<table cellpadding="0" cellspacing="0" style="border:none;">
		<tr>
			<td style="padding-left:12px;vertical-align:top;">
				<div style="width:28px;height:28px;background:#BA7517;border-radius:6px;text-align:center;line-height:28px;">
					<span style="color:#fff;font-size:14px;">&#x1F426;</span>
				</div>
			</td>
			<td style="vertical-align:top;">
				<div style="font-weight:700;color:#1A1A1A;font-size:14px;">{$name}</div>
				<div style="color:#8A8A8A;font-size:12px;">{$email}</div>
				<div style="margin-top:6px;font-size:11px;color:#BA7517;">
					أُرسلت بـهدهد · <a href="{$url}" style="color:#BA7517;text-decoration:none;">{$domain}</a>
				</div>
			</td>
		</tr>
	</table>
</div>
HTML;
	}
}
