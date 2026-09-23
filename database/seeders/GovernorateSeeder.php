<?php

namespace Database\Seeders;

use App\Models\Governorate;
use App\Models\Shop;
use Illuminate\Database\Seeder;

class GovernorateSeeder extends Seeder
{
    /**
     * @var list<array{0: string, 1: string}>
     */
    protected array $governorates = [
        ['القاهرة', 'Cairo'],
        ['الجيزة', 'Giza'],
        ['الإسكندرية', 'Alexandria'],
        ['الدقهلية', 'Dakahlia'],
        ['البحر الأحمر', 'Red Sea'],
        ['البحيرة', 'Beheira'],
        ['الفيوم', 'Fayoum'],
        ['الغربية', 'Gharbia'],
        ['الإسماعيلية', 'Ismailia'],
        ['المنوفية', 'Monufia'],
        ['المنيا', 'Minya'],
        ['القليوبية', 'Qalyubia'],
        ['الوادي الجديد', 'New Valley'],
        ['السويس', 'Suez'],
        ['أسوان', 'Aswan'],
        ['أسيوط', 'Asyut'],
        ['بني سويف', 'Beni Suef'],
        ['بورسعيد', 'Port Said'],
        ['دمياط', 'Damietta'],
        ['الشرقية', 'Sharqia'],
        ['جنوب سيناء', 'South Sinai'],
        ['كفر الشيخ', 'Kafr El Sheikh'],
        ['مطروح', 'Matrouh'],
        ['الأقصر', 'Luxor'],
        ['قنا', 'Qena'],
        ['شمال سيناء', 'North Sinai'],
        ['سوهاج', 'Sohag'],
    ];

    public function run(): void
    {
        foreach (Shop::query()->get() as $shop) {
            foreach ($this->governorates as $index => [$name, $nameEn]) {
                Governorate::withoutGlobalScopes()->firstOrCreate(
                    ['shop_id' => $shop->id, 'name' => $name],
                    ['name_en' => $nameEn, 'shipping_cost' => 75, 'sort_order' => $index]
                );
            }
        }
    }
}
