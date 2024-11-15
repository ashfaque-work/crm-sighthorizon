<?php

namespace Database\Seeders;
use App\Models\Label;
use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
   public function run()
    {
        Label::create(
            [
            'name'       => 'Ad-Campaign',
            'color'      => 'primary',
            'category'   => 'Lead Source',
            'pipeline_id'=> 1,
            'created_by' => 1,
            ],
            [
            'name'       => 'Justdial',
            'color'      => 'primary',
            'category'   => 'Lead Source',
            'pipeline_id'=> 1,
            'created_by' => 1,
            ]
            
        // 'Tawk'
        // 'Knowlarity'
        // 'Website'
        // 'Cold-Calling'
        );
    }
}
