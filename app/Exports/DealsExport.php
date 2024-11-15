<?php

namespace App\Exports;
use App\Models\Deal;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DealsExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function collection()
    {
        $data = Deal::where('created_by', \Auth::user()->ownerId())->get();

        foreach ($data as $k => $deal) {
            unset( $deal->sources, $deal->products, $deal->notes, $deal->labels, $deal->order);
            $created_bys = User::find($deal->created_by);
            $created_by = $created_bys->name;
            $data[$k]['created_by'] = $created_by;
        }

        return $data;
    }

    public function headings(): array
    {

        return [
        "Id",
        "Name",
        "Price",
        "pipeline_id",
        "Stage_id",
        "Status",
        "Phone",
        "created_by",
        "is_active",
        "created_at",
        "updated_at",


        ];
    }
}
