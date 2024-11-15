<?php

namespace Database\Seeders;

use App\Models\NotificationTemplateLangs;
use App\Models\NotificationTemplates;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $notifications = [
            'new_lead' => 'New Lead', 'new_deal' => 'New Deal', 'new_estimate' => 'New Estimate', 'lead_to_deal_conversion' => 'Lead to Deal Conversion',
            'new_contract' => 'New Contract', 'new_payment' => 'New Payment', 'new_invoice' => 'New Invoice', 'invoice_status_updated' => 'Invoice Status Updated'
        ];

        $defaultTemplate = [
            'new_lead' => [
                'variables' => '{
                    "Lead Name": "lead_name",
                    "User Email": "lead_email",
                    "Company Name": "company_name",
                    "Lead Subject": "subject",
                    "App Name": "app_name",
                    "App Url": "app_url"
                    }',
                'lang' => [
                    'ar' => 'تم إنشاء عميل محتمل جديد {lead_name} بواسطة {company_name}.',
                    'da' => 'Nyt kundeemne {lead_name} er oprettet af {company_name}.',
                    'de' => 'Der neue Lead {lead_name} wird von {company_name} erstellt.',
                    'en' => 'New Lead {lead_name} is created by the {company_name}.',
                    'es' => 'El nuevo cliente potencial {lead_name} es creado por {company_name}.',
                    'fr' => 'Le nouveau prospect {lead_name} est créé par {company_name}.',
                    'it' => 'Il nuovo lead {lead_name} è stato creato da {company_name}.',
                    'ja' => '新しいリード {lead_name} が {company_name} によって作成されました。',
                    'nl' => 'Nieuwe lead {lead_name} wordt gemaakt door {company_name}.',
                    'pl' => 'Nowy potencjalny klient {lead_name} jest tworzony przez firmę {company_name}.',
                    'ru' => 'Новый Лид {lead_name} создан {company_name}.',
                    'pt' => 'O novo lead {lead_name} foi criado pela {company_name}.',
                    'tr' => 'Yeni Müşteri Adayı {lead_name}, {company_name} tarafından oluşturuldu.',
                    'zh' => '新潜在客户 {lead_name} 由 {company_name} 创建。',
                    'he' => 'הפניה חדשה {lead_name} נוצרת על-ידי {company_name}.',
                    'pt-br' => 'O novo lead {lead_name} é criado pelo {company_name}.',
                ],
            ],
            'new_deal' => [
                'variables' => '{
                    "Deal Name": "deal_name",
                    "Deal Price": "deal_price",
                    "Client Name": "client_name",
                    "Company Name": "company_name",
                    "App Name": "app_name",
                    "App Url": "app_url"
                    }',
                'lang' => [
                    'ar' => 'تم إنشاء الصفقة الجديدة {deal_name} بواسطة {company_name}.',
                    'da' => 'Ny aftale {deal_name} er oprettet af {company_name}.',
                    'de' => 'Neuer Deal {deal_name} wird von {company_name} erstellt.',
                    'en' => 'New Deal {deal_name} is created by {company_name}.',
                    'es' => 'El nuevo trato {deal_name} es creado por {company_name}.',
                    'fr' => 'La nouvelle offre {deal_name} est créée par {company_name}.',
                    'it' => 'La nuova offerta {deal_name} è stata creata da {company_name}.',
                    'ja' => '新しいディール {deal_name} は {company_name} によって作成されました。',
                    'nl' => 'Nieuwe deal {deal_name} is gemaakt door {company_name}.',
                    'pl' => 'New Deal {deal_name} jest tworzony przez {company_name}.',
                    'ru' => 'Новая сделка {deal_name} создана {company_name}.',
                    'pt' => 'O novo negócio {deal_name} foi criado por {company_name}.',
                    'tr' => '{deal_name} adlı Yeni Anlaşma, {company_name} tarafından oluşturuldu.',
                    'zh' => '新政 {deal_name} 由 {company_name} 创建。',
                    'he' => 'New Deal {deal_name} נוצר על-ידי {company_name}.',
                    'pt-br' => 'New Deal {deal_name} é criado por {company_name}.',
                ],
            ],
            'new_estimate' => [
                'variables' => '{
                    "Estimation Name": "estimation",
                    "Company Name": "company_name",
                    "Client Name": "client_name",
                    "Issue Date": "issue_date",
                    "App Name": "app_name",
                    "App Url": "app_url"
                    }',
                'lang' => [
                    'ar' => 'تم إنشاء تقدير جديد {estimation} بواسطة {company_name}.',
                    'da' => 'Nyt skøn {estimation} er oprettet af {company_name}.',
                    'de' => 'Neue Schätzung {estimation} wird von {company_name} erstellt.',
                    'en' => 'New Estimation {estimation} is created by {company_name}.',
                    'es' => 'La nueva estimación {estimation} es creada por {company_name}.',
                    'fr' => 'La nouvelle estimation {estimation} est créée par {company_name}.',
                    'it' => 'La nuova stima {estimation} è stata creata da {company_name}.',
                    'ja' => '新しい見積もり {estimation} は {company_name} によって作成されました。',
                    'nl' => 'Nieuwe schatting {estimation} is gemaakt door {company_name}.',
                    'pl' => 'Nowa prognoza {estimation} jest tworzona przez firmę {company_name}.',
                    'ru' => 'Новая оценка {estimation} создана {company_name}.',
                    'pt' => 'A nova estimativa {estimation} foi criada por {company_name}.',
                    'tr' => 'Yeni Tahmin {estimation}, {company_name} tarafından oluşturuldu.',
                    'zh' => '新估计 {估计} 由 {company_name} 创建。',
                    'he' => 'הערכה חדשה {estimation} נוצרת על-ידי {company_name}.',
                    'pt-br' => 'Nova estimativa {estimation} é criada por {company_name}.',
                ],
            ],
            'lead_to_deal_conversion' => [
                'variables' => '{
                    "Deal Name": "deal_name",
                    "Lead Name": "lead_name",
                    "Deal Price": "deal_price",
                    "Client Name": "client_name",
                    "Company Name": "company_name",
                    "App Name": "app_name",
                    "App Url": "app_url"
                    }',
                'lang' => [
                    'ar' => 'تم تحويل الصفقة من خلال الرصاص {lead_name} بواسطة {company_name}.',
                    'da' => 'Aftale konverteret via kundeemne {lead_name} af {company_name}.',
                    'de' => 'Geschäftsabschluss durch Lead {lead_name} von {company_name}.',
                    'en' => 'Deal converted through lead {lead_name} by {company_name}.',
                    'es' => 'Trato convertido a través del cliente potencial {lead_name} por {company_name}.',
                    'fr' => 'Offre convertie via le prospect {lead_name} par {company_name}.',
                    'it' => 'Offerta convertita tramite lead {lead_name} da {company_name}.',
                    'ja' => '{company_name} による見込み客 {lead_name} を通じて商談が成立しました。',
                    'nl' => 'Deal omgezet via lead {lead_name} door {company_name}.',
                    'pl' => 'Umowa przekonwertowana przez lead {lead_name} firmy {company_name}.',
                    'ru' => 'Сделка совершена через лид {lead_name} от {company_name}.',
                    'pt' => 'Negócio convertido por meio do lead {lead_name} por {company_name}.',
                    'tr' => '{company_name} tarafından {lead_name} müşteri adayı aracılığıyla dönüştürülen anlaşma.',
                    'zh' => '通过潜在客户 {lead_name} 转换为 {company_name} 的交易。',
                    'he' => 'עסקה שהומרה באמצעות ליד {lead_name} על-ידי {company_name}.',
                    'pt-br' => 'Negócio convertido através do lead {lead_name} por {company_name}.',
                ],
            ],
            'new_contract' => [
                'variables' => '{
                    "Contract": "contract",
                    "Contract Name": "contract_name",
                    "Client Name": "client_name",
                    "Contract Value": "contract_value",
                    "Company Name": "company_name",
                    "App Name": "app_name",
                    "App Url": "app_url"
                    }',
                'lang' => [
                    'ar' => 'تم إنشاء العقد الجديد {contract} بواسطة {company_name}.',
                    'da' => 'Ny kontrakt {contract} er oprettet af {company_name}.',
                    'de' => 'Neuer Vertrag {contract} wird von {company_name} erstellt.',
                    'en' => 'New Contract {contract} is created by {company_name}.',
                    'es' => 'El nuevo contrato {contrato} es creado por {company_name}.',
                    'fr' => 'Le nouveau contrat {contract} est créé par {company_name}.',
                    'it' => 'Nuovo contratto {contract} creato da {company_name}.',
                    'ja' => '新しい契約 {contract} が {company_name} によって作成されました。',
                    'nl' => 'Nieuw contract {contract} is gemaakt door {company_name}.',
                    'pl' => 'Nowa umowa {contract} zostaje utworzona przez firmę {company_name}.',
                    'ru' => 'Новый контракт {contract} создан {company_name}.',
                    'pt' => 'Novo contrato {contract} é criado por {company_name}.',
                    'tr' => 'Yeni Sözleşme {contract}, {company_name} tarafından oluşturuldu.',
                    'zh' => '新合约 {合约} 由 {company_name} 创建。',
                    'he' => 'חוזה חדש {contract} נוצר על-ידי {company_name}.',
                    'pt-br' => 'Novo contrato {contrato} é criado por {company_name}.',
                ],
            ],
            'new_payment' => [
                'variables' => '{
                    "Payer Name": "payer_name",
                    "Amount": "amount",
                    "Payment Type": "payment_type",
                    "Deal Name": "deal_name",
                    "Company Name": "company_name",
                    "App Name": "app_name",
                    "App Url": "app_url"
                    }',
                'lang' => [
                    'ar' => 'دفع {payer_name} مبلغ {amount}',
                    'da' => '{payer_name} betalte {amount}',
                    'de' => '{payer_name} hat {amount} bezahlt',
                    'en' => '{payer_name} paid {amount}.',
                    'es' => '{payer_name} pagó {amount}',
                    'fr' => '{payer_name} a payé {amount}',
                    'it' => '{payer_name} ha pagato {amount}',
                    'ja' => '{payer_name} さんが {amount} を支払いました',
                    'nl' => '{payer_name} heeft {amount} betaald',
                    'pl' => '{payer_name} zapłacił {amount}',
                    'ru' => '{payer_name} заплатил {amount}',
                    'pt' => '{payer_name} pagou {amount}',
                    'tr' => '{payer_name}, {amount} ödedi.',
                    'zh' => '{payer_name} 支付 {金额}。',
                    'he' => '{payer_name} שילם {סכום}.',
                    'pt-br' => '{payer_name} pago {valor}.',
                ],
            ],
            'new_invoice' => [
                'variables' => '{
                    "Invoice Name": "invoice",
                    "Deal Name": "deal_name",
                    "Company Name": "company_name",
                    "App Name": "app_name",
                    "App Url": "app_url"
                    }',
                'lang' => [
                    'ar' => 'تم إنشاء الفاتورة الجديدة {invoice} بواسطة {company_name}.',
                    'da' => 'Ny faktura {invoice} er oprettet af {company_name}.',
                    'de' => 'Neue Rechnung {invoice} wird von {company_name} erstellt.',
                    'en' => 'New Invoice {invoice} is created by {company_name}.',
                    'es' => 'La nueva factura {invoice} es creada por {company_name}.',
                    'fr' => 'La nouvelle facture {invoice} est créée par {company_name}.',
                    'it' => 'Nuova fattura {invoice} è stata creata da {company_name}.',
                    'ja' => '新しい請求書 {invoice} が {company_name} によって作成されました。',
                    'nl' => 'Nieuwe factuur {invoice} is gemaakt door {company_name}.',
                    'pl' => 'Nowa faktura {invoice} jest tworzona przez firmę {company_name}.',
                    'ru' => 'Новый счет {invoice} создан {company_name}.',
                    'pt' => 'A nova fatura {invoice} foi criada por {company_name}.',
                    'tr' => 'Yeni Fatura {invoice}, {company_name} tarafından oluşturuldu.',
                    'zh' => '新发票 {发票} 由 {company_name} 创建。',
                    'he' => 'חשבונית חדשה {invoice} נוצרת על-ידי {company_name}.',
                    'pt-br' => 'Nova fatura {invoice} é criada por {company_name}.',
                ],
            ],
            'invoice_status_updated' => [
                'variables' => '{
                    "Invoice Name": "invoice",
                    "Deal Name": "deal_name",
                    "status": "status",
                    "Company Name": "company_name",
                    "App Name": "app_name",
                    "App Url": "app_url"
                    }',
                'lang' => [
                    'ar' => 'تم تحديث حالة الفاتورة {invoice} بنجاح بواسطة {company_name}.',
                    'da' => 'Faktura {invoice}-status blev opdateret af {company_name}.',
                    'de' => 'Status der Rechnung {invoice} erfolgreich von {company_name} aktualisiert.',
                    'en' => 'Invoice {invoice} status successfully updated by {company_name}.',
                    'es' => 'Estado de la factura {invoice} actualizado con éxito por {company_name}.',
                    'fr' => 'Le statut de la facture {invoice} a été mis à jour avec succès par {company_name}.',
                    'it' => 'Stato della fattura {invoice} aggiornato con successo da {company_name}.',
                    'ja' => '請求書 {invoice} のステータスが {company_name} によって正常に更新されました。',
                    'nl' => 'Factuur {invoice}-status succesvol bijgewerkt door {company_name}.',
                    'pl' => 'Pomyślnie zaktualizowano stan faktury {invoice} przez firmę {company_name}.',
                    'ru' => 'Статус счета-фактуры {invoice} успешно обновлен {company_name}.',
                    'pt' => 'Status da fatura {invoice} atualizado com sucesso por {company_name}.',
                    'tr' => 'Fatura {invoice} durumu, {company_name} tarafından başarıyla güncellendi.',
                    'zh' => '发票 {发票} 状态已成功由 {company_name} 更新。',
                    'he' => 'מצב חשבונית {invoice} עודכן בהצלחה על-ידי {company_name}.',
                    'pt-br' => 'Status da fatura {fatura} atualizado com êxito por {company_name}.',
                ],
            ],
        ];

        $user = User::where('type', 'super admin')->first();

        foreach ($notifications as $k => $n) {
            $ntfy = NotificationTemplates::where('slug', $k)->count();
            if ($ntfy == 0) {
                $new = new NotificationTemplates();
                $new->name = $n;
                $new->slug = $k;
                $new->save();

                foreach ($defaultTemplate[$k]['lang'] as $lang => $content) {
                    NotificationTemplateLangs::create(
                        [
                            'parent_id' => $new->id,
                            'lang' => $lang,
                            'variables' => $defaultTemplate[$k]['variables'],
                            'content' => $content,
                            'created_by' => !empty($user) ? $user->id : 1,
                        ]
                    );
                }
            }
        }
    }
}
