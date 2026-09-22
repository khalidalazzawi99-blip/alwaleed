<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $companyId = DB::table('companies')->where('code', 'SIPPAR')->value('id');

        if (! $companyId) {
            return;
        }

        $names = [
            'الوطن العربي للكارتون | The Arab World of Cartons',
            'مطبعة النهرين | Al-Nahrain Printing Press',
            'تخليص جمركي | Customs Clearance',
            'معمل كركوك | Kirkuk Factory',
            'ابو مهند | Abu Muhannad',
            'مطبعة هافين | Haven Press',
            'خميس العراقي | Khamis Al-Iraqi',
            'هافين | Haven',
            'كرافت الوسام | Craft AL-Wissam',
            'مصنع المتحدة | United Factory',
            'ابو مريم | Abu Maryam',
            'رسول أبو إبراهيم | Rasoul Abu Ibrahim',
            'مطبعة ايلاف | Elaf Printing',
            'معمل الجودي | Al-Judi laboratory',
            'زياد الوزيرية | Ziad Al-Waziriya',
            'مطبعة الصافي | Al-Safi Printing Press',
            'GOSTARAN PAK . CO',
            'شركة الأنوار المتحدة | Al-Anwar United Company',
            'مؤيد كركوك | Muayyad Kirkuk',
            'مصنع الفارابي | Al-Farabi Factory',
            'معمل الجريسي | Al-Juraisi Laboratory',
            'أحمد ناهد | Ahmed Nahed',
            'مطبعة إبداع بغداد',
            'الشركة العراقية للكارتون | Iraqi Carton Company',
            'معمل الرتال | Al-Ratal Factory',
            'شركة اغلفة الخليج العربي | Arabian Gulf Packaging Company',
            'شركة اهل الحرة',
            'خميس المصري | Khamis Al-Masry',
            'ازاد أربيل | Azad Erbil',
            'شركة زين العراق | Zain Al-Iraq Company',
            'غيث حمرة | Ghaith Hamra',
            'شركة مدار الايام | MADR ALAYAM GENERAL',
            'شركة الرسالة | Al-Resala Company',
            'معمل مجتبى | Mujtaba Laboratory',
            'مطبعة لمسات | Lamsat Printing Press',
            'السعدي | Al-Saadi',
            'مصنع اللؤلؤة للكارتون | Lulu\'a Carton Factory',
            'مطبعة الطباع | Al-Tabbaa Printing Press',
            'الكارتون العراقية | Iraqi Carton',
            'معمل حسان | Hassan Factory',
            'ثامر عباس محمد | تخليص تركيا',
            'مصطفى اربيل | Mustafa Erbil',
            'محمد الساعدي | MOHAMMED ALSAIDY',
            'معمل كركوك | Kirkuk Laboratory',
            'سما الكمال | Sama AL-Kamal',
            'مصطفي الموصلي | Mustafa Al-Mawsili',
            'كرافت الإحسان | Craft Al-ihsaan',
            'مصنع الشموخ | Al-Shumoukh Factory',
            'مطبعة سمارت | Smart Printing Press',
            'مانع الدليمي | Manea Al-Dulaimi',
            'مطبعة نور باك | Noor Pak Printing Press',
            'مصنع السدير | Al-Sudair Factory',
            'شمس بغداد | Shams Baghdad',
            'مطبعة توباك | TOPACK Printing Press',
            'مطبعة المنتصر',
            'أحمد دهوك | Ahmed Dohuk',
            'مجد | Majd',
            'أنور | Anwar',
            'مطبعة الواحة الخضراء | Al-wahat Al-khadra\' Printing Press',
            'الطيف الذهبي | Golden spectrum',
            'حسين فرهود | Hussein Farhoud',
            'مصنع كوديا | Kudia factory',
        ];

        foreach ($names as $name) {
            if (DB::table('customers')->where('company_id', $companyId)->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->exists()) {
                continue;
            }

            DB::table('customers')->insert([
                'company_id' => $companyId,
                'name' => $name,
                'integration_id' => 'C-'.Str::ulid(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Imported customers are retained to avoid deleting later transactions.
    }
};
